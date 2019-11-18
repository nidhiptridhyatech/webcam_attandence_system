<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Validator;
use App\FrontUser;
use App\Attendance;

class AttendanceController extends Controller
{
    public function MakeAttendance(Request $request)
    {
        if(isset($_POST['image']))
        {
            $validator = Validator::make($request->all(), [
                'image' => ['required'],
            ]);
            if ($validator->fails()) {
                return redirect()->back()->withErrors(['image' => 'Please provide snapshot to make attendance.']);
            }
            $frontUsers = FrontUser::where('deleted_at',NULL)->get()->toArray();
            if(!empty($frontUsers))
            {
                $img = $_POST['image'];
                $webcam_path = "images/webcam/login/";
                $folderPath = public_path($webcam_path);
                $image_parts = explode(";base64,", $img);
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1];
            
                $image_base64 = base64_decode($image_parts[1]);
                $fileName = uniqid() . '.png';
            
                $file = $folderPath . $fileName;
                file_put_contents($file, $image_base64);
                $avatar = $webcam_path.$fileName;
                $avatar_url = url('/'.$avatar);
                $user_verified = 0;
               foreach($frontUsers as $f=>$fuser)
               {
                   if(isset($fuser['avatar']))
                   {
                    // $login_face_url = "https://content-static.upwork.com/uploads/2014/10/01073427/profilephoto1.jpg";
                    // $front_face_url = "https://content-static.upwork.com/uploads/2014/10/01073427/profilephoto1.jpg";     
                    $login_face_url = $avatar_url;
                    $front_face_url = url('/storage/'.$fuser['avatar']);
                    $login_face_id = $this->getFaceIdFromImage($login_face_url);
                    $front_face_id = $this->getFaceIdFromImage($front_face_url);
                    $is_verified = $this->verifyFaces($login_face_id,$front_face_id);
                    if($is_verified == true)
                    {
                        $user_verified = 1;
                        //make attendance logic 
                        $attendance = Attendance::create([
                            'front_user_id' => $fuser['id'],
                        ]);
                    }
                   }
               } 
               if($user_verified == 0)
               {
                   //remove uploaded image from login attempt 
                   return redirect()->back()->withErrors(['image' => 'Sorry, Face not verified! Please try again.']);   
               }
            }
            }
            return view("attendance");
    }
    public function getFaceIdFromImage($face_url)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Ocp-Apim-Subscription-Key' => config('faceapi.subscription_key'),
        ];
        $body = '{
            "url": "'.$face_url.'"
        }';
        $client = new Client([
            'headers' => $headers
        ]);
        try{
            $res = $client->request('POST', config('faceapi.api_endpoint').'detect?returnFaceId=true', [
                'body' => $body
            ]);
    
            if ($res->getStatusCode() == 200) { // 200 OK
                $response_data = json_decode($res->getBody()->getContents());
                $face_id = $response_data[0]->faceId;
            }
        }
        catch (\Exception $ex)
        {
        return redirect()->back()->withErrors(['image' => 'Sorry, Face not identified! Please try again.']);
        }
        return $face_id;
    }
    public function verifyFaces($login_face_id,$front_user_face_id)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Ocp-Apim-Subscription-Key' => config('faceapi.subscription_key'),
        ];
        $body = '{
            "faceId1": "'.$login_face_id.'",
            "faceId2": "'.$front_user_face_id.'",
        }';
        $client = new Client([
            'headers' => $headers
        ]);
        try
        {
            $res = $client->request('POST', config('faceapi.api_endpoint').'verify', [
                'body' => $body
            ]);
            if ($res->getStatusCode() == 200) { // 200 OK
                $response_data = json_decode($res->getBody()->getContents());
                if($response_data->isIdentical == 1 && $response_data->confidence >= 0.7)
                {
                    $verified = true;
                }
                else
                {
                    $verified = false;
                }
            }
        }
        catch (\Exception $ex)
        {
        return redirect()->back()->withErrors(['image' => 'Sorry, Face not identified! Please try again.']);
        }

        return $verified;
    }
    public function getUserLocation(Request $request)
    {
        if(!empty($_POST['latitude']) && !empty($_POST['longitude'])){ 
            //Send request and receive json data by latitude and longitude 
            $url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($_POST['latitude']).','.trim($_POST['longitude']).'&sensor=false'; 
            $json = @file_get_contents($url); 
            $data = json_decode($json); 
            $status = $data->status; 
            if($status=="OK"){ 
                //Get address from json data 
                $location = $data->results[0]->formatted_address; 
            }else{ 
                $location =  ''; 
            } 
            //Print address 
            echo $location; 
        } 
    }
}
