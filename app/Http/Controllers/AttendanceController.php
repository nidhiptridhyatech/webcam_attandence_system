<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Validator;

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
        
    print_r($_POST);exit;
    $img = $_POST['image'];
    $webcam_path = "images/webcam/users/";
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
    //test Face API
    $headers = [
        'Content-Type' => 'application/json',
        'Ocp-Apim-Subscription-Key' => 'b3bde8e39f654398bba68bac2a3ae0f5',
    ];
    // $body = '{
    //     "url": "https://content-static.upwork.com/uploads/2014/10/01073427/profilephoto1.jpg"
    // }';
    $body = '{
        "url": "'.$avatar_url.'"
    }';
    
   // $client = new Client();
    $client = new Client([
        'headers' => $headers
    ]);

    $res = $client->request('POST', 'https://westcentralus.api.cognitive.microsoft.com/face/v1.0/detect?returnFaceId=true', [
        'body' => $body
    ]);

    if ($res->getStatusCode() == 200) { // 200 OK
        $response_data = json_decode($res->getBody()->getContents());
        $face_id = $response_data[0]->faceId;
        //$face_id = collect(json_decode($res->getBody()->getContents(),true))->pluck('faceId')->first();
        //print"<pre>";print_r($response_data[0]->faceId);exit;
    }
    //Face API end
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'avatar' => $avatar,
            'face_id' => $face_id,
        ]);
        // if (isset($data['avatar'])) {
        //     $user->addMediaFromRequest('avatar')->toMediaCollection('avatars');
        // }
    
        }
        return view("attendance");
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
