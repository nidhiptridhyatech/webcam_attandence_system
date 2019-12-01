<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Validator;
use App\FrontUser;
use App\Attendance;
use File;
use Redirect;

class AttendanceController extends Controller
{
    public function MakeAttendance(Request $request)
    {
        if(isset($_POST['image']))
        {
            //voice verification manual code
            // $filename = public_path('login_audio/2019-11-26T10_47_49.389Z.wav');
            // $binary_content = file_get_contents($filename, true);
            // $this->verifyVoice($binary_content);exit;
            //voice verification code end
            $validator = Validator::make($request->all(), [
                'phone_number' => 'required|digits:10',
                'image' => 'required',
            ],
            [
                'phone_number.required' => 'Mobile No. is required to make attendance.',
                'image.required' => 'Please provide snapshot to make attendance.',
                'phone_number.digits' => 'Please enter valid Mobile No.'
            ]    
            );
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput($request->all());
            }
            //check for existing user based on mobile
            $existingUser = FrontUser::select('front_users.*','geofences.latitude','geofences.longitude','geofences.radius')->where('front_users.deleted_at',NULL)->where('front_users.phone_number',$request->phone_number)->leftjoin('geofences','front_users.geofence_id','=','geofences.id')->get()->toArray();
            if(empty($existingUser))
            {
                return redirect()->back()->with('error', 'Mobile No. does not match our records.')->withInput($request->all());            
            }
            //get current location for employee.
            $login_latitude = $_POST['login_lati'];
            $login_longitude = $_POST['login_long'];
            $login_address = $this->getUserLocation($login_latitude,$login_longitude);
            //get image fron snap taken by webcam
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
            $is_verified = false;
            $user_verified = 0;
            $fuser = $existingUser[0];
            //check if the attendance exist for user
            $existing_attendance = Attendance::where('front_user_id',$fuser['id'])->whereDate('created_at', '=', date('Y-m-d'))->count();
            
            //verify face using face API
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
                if(isset($fuser['latitude']) && isset($fuser['longitude']))
                {
                    $radius = isset($fuser['radius'])?$fuser['radius']:setting('site.allowed_radius');
                    $geofence_latitude = $fuser['latitude'];
                    $geofence_longitude = $fuser['longitude'];
                    $login_radius = $this->getDistance($login_latitude,$login_longitude,$geofence_latitude,$geofence_longitude);
                    if($login_radius>$radius)
                    {
                        $user_verified = 0;
                        return redirect()->back()->with('error','You can not make attendance from this location.');           
                    }
                }
                if($user_verified == 1)
                {
                    if($existing_attendance > 0)
                    {
                        return redirect()->back()->with('info', $fuser['first_name'].' '.$fuser['last_name'].', Your attendance for today has already been made.');            
                    }
                    //make attendance logic 
                    $attendance = Attendance::create([
                        'front_user_id' => $fuser['id'],
                        'login_latitude' => $login_latitude,
                        'login_longitude' => $login_longitude,
                        'login_address' => $login_address,
                        'login_image' => $avatar,
                    ]);
                    return redirect()->back()->withSuccess($fuser['first_name'].' '.$fuser['last_name'].', your attendance has been made successfully!');
                }
                }
                }
               
               if($user_verified == 0)
               {
                   //remove uploaded image from login attempt 
                    $image_path = $avatar;  
                    if(File::exists($image_path)) {
                    File::delete($image_path);
                    }
                   return redirect()->back()->with('error', 'Face not verified! Please try again.')->withInput($request->all());   
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
            $face_id = 0;
        }
        return $face_id;
    }
    public function verifyFaces($login_face_id,$front_user_face_id)
    {
        $verified = false;
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
            $verified = false;
        }

        return $verified;
    }
    function getDistance($latitude1, $longitude1, $latitude2, $longitude2) {  
        $earth_radius = 6371;  
          
        $dLat = deg2rad($latitude2 - $latitude1);  
        $dLon = deg2rad($longitude2 - $longitude1);  
          
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLon/2) * sin($dLon/2);  
        $c = 2 * asin(sqrt($a));  
        $d = $earth_radius * $c;  
        
        return $d;  
    }  
    public function getUserLocation($latitude,$longitude)
    {
        if(!empty($latitude) && !empty($longitude)){ 
            //Send request and receive json data by latitude and longitude 
            $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($latitude).','.trim($longitude).'&sensor=false&key=AIzaSyBtWnExOjgjgPQRyVXZ1E0AvvSj75Y5Ods'; 
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
            return $location;
            //print"<pre>";print_r($location); exit;
        } 
    }
    public function EnrollVoice(Request $request)
    {
//        print_r($request->verification_id);exit;
        //uniqid();
        //$request->verification_id = "164af3ab-9b70-4722-abbb-03d406520402";
        $audio_file_path = public_path('audio/enroll/');
        //echo $audio_file_path;exit;
        $input = $_FILES['audio_data']['tmp_name']; //get the temporary name that PHP gave to the uploaded file 
        //$output = $audio_file_path.$_FILES['audio_data']['name'].".wav"; //letting the client control the filename is a rather bad idea 
        $filename = $audio_file_path.uniqid().".wav"; //letting the client control the filename is a rather bad idea 
        //move the file from temp name to local folder using $output name 
        move_uploaded_file($input, $filename);
        //exit;
        // $filename = public_path('login_audio/2019-11-26T10_47_49.389Z.wav');
        $binary_content = file_get_contents($filename, true);
        // $this->verifyVoice($binary_content);exit;
        //voice verification code end

        $voice_verification_id = '';
        $headers = [
            'Content-Type' => 'application/octet-stream',
            'Ocp-Apim-Subscription-Key' => config('voiceapi.voice_subscription_key'),
        ];
        $body = $binary_content;
        $client = new Client([
            'headers' => $headers
        ]);
        try
        {
            $res = $client->request('POST', config('voiceapi.voice_api_endpoint').'verificationProfiles/'.$request->verification_profile_id.'/enroll', [
                'body' => $body
            ]);
            
            if ($res->getStatusCode() == 200) { // 200 OK
                $response_data = json_decode($res->getBody()->getContents());
                //print"<pre>";print_r($response_data);exit;
                $enrollment_status = ($response_data->enrollmentStatus=='Enrolled' && $response_data->remainingEnrollments==0)?2:1;
                FrontUser::where('voice_profile_id', $request->verification_id)->update(array('voice_enrollment_status' => $enrollment_status,'remaining_voice_enrollments' => $response_data->remainingEnrollments));
                return Redirect::back()->with('message','Operation Successful !');

                //$voice_verification_id = $response_data->verificationProfileId;
            }
        }
        catch (\Exception $ex)
        {
            //remove uploaded audio 
            $audio_path = $filename;  
            if(File::exists($audio_path)) {
            File::delete($audio_path);
            }
            print"<pre>";print_r($ex->getMessage());exit;   
        }

        return $voice_verification_id;
    }
    public function verifyVoice($binary_data,$verification_id)
    {
        $verified = false;
        $headers = [
            'Content-Type' => 'application/octet-stream',
            'Ocp-Apim-Subscription-Key' => '83ea574c33d9415abe0494c22f8a2583',
        ];
        $body = $binary_data;
        $client = new Client([
            'headers' => $headers
        ]);
        try
        {
            $res = $client->request('POST', 'https://westus.api.cognitive.microsoft.com/spid/v1.0/verificationProfiles/'.$verification_id.'/enroll', [
                'body' => $body
            ]);
            
            if ($res->getStatusCode() == 200) { // 200 OK
                $response_data = json_decode($res->getBody()->getContents());
                print"<pre>";print_r($response_data);exit;
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
            print"<pre>";print_r($ex->getMessage());exit;
            $verified = false;
        }

        return $verified;
    }
}
