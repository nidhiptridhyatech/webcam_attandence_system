<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Validator;
use App\FrontUser;
use App\User;
use App\Geofence;
use File;
use Redirect;
use Storage;

class EnrollmentController extends Controller
{
    public function MakeEnrollment(Request $request)
    {
        //Get list of all organizations and sub-organizations.
        $organizations = User::whereIn('role_id',[3,4])->pluck("name","id")->toArray();
        if (isset($_POST['enroll']))
        {
            //Validate Input Data
            $validator = Validator::make($request->all(), [
                'organization' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',
                'phone_number' => 'required|digits:10|unique:front_users',
                'adhar_no' => 'required|regex:/^([0-9]){12}$/|unique:front_users',
                'image' => 'required',
            ],
            [
                'first_name.required' => 'First Name is required.',
                'last_name.required' => 'Last Name is required.',
                'image.required' => 'Please provide snapshot to make attendance.',
                'adhar_no.required' => 'Adhar No. is required.',
                'adhar_no.regex' => 'Please enter valid Adhar No.',
                'adhar_no.unique' => 'Adhar No. has already been enrolled.',
                'phone_number.required' => 'Mobile No. is required to make attendance.',
                'phone_number.digits' => 'Please enter valid Mobile No.',
                'phone_number.unique' => 'Mobile No. has already been enrolled.',
            ]    
            );
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput($request->all());
            }
            //get image fron snap taken by webcam
            $img = $_POST['image'];
            $webcam_path = "front-users/enroll/";
            $image_parts = explode(";base64,", $img);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $fileName = uniqid() . '.png';
            $file = Storage::disk('public')->path($webcam_path.$fileName);
            file_put_contents($file, $image_base64);
            $avatar = $webcam_path.$fileName;
            $avatar_url = url('/storage/'.$avatar);
            //get geofence id of organization
            //$geo_id = Geofence::select('id')->where('user_id',$request->organization)->get()->toArray();
            //print_r($geo_id);exit;
            
            //get list of all users to check if face has already been enrolled.
            $existingUser = FrontUser::where('deleted_at',NULL)->get()->toArray();
            If(!empty($existingUser))
            {
                foreach($existingUser as $fuser)
                {
                    //verify face using face API
                    if(isset($fuser['avatar']))
                    {
                        // $login_face_url = "https://content-static.upwork.com/uploads/2014/10/01073427/profilephoto1.jpg";
                        // $front_face_url = "https://content-static.upwork.com/uploads/2014/10/01073427/profilephoto1.jpg";     
                        $enroll_face_url = $avatar_url;
                        $front_face_url = url('/storage/'.$fuser['avatar']);
                        $login_face_id = $this->getFaceIdFromImage($enroll_face_url);
                        $front_face_id = $this->getFaceIdFromImage($front_face_url);
                        
                        $is_verified = $this->verifyFaces($login_face_id,$front_face_id);
                        if($is_verified == true)
                        {
                            return redirect()->back()->with('error','This Face has already been enrolled.');           
                            
                        }
                    }
                }
            
            }
            
            $front_user = FrontUser::create([
                'parent_id' => $request->organization,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone_number' => $request->phone_number,
                'avatar' => $avatar,
                'adhar_no' => $request->adhar_no,
                //'geofence_id' => $request->organization,
                ]);
            return redirect()->back()->withSuccess('You are successfully enrolled!');
        }
        
        //print"<pre>";print_r($organizations);exit;
        return view("enrollment",compact('organizations'));
    }
}
