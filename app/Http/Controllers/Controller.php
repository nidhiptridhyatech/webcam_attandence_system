<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use GuzzleHttp\Client;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
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
}
