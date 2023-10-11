<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TraccarUser;

class DevicesController extends Controller
{
    //

    public function getDevices(){
        $header = \request()->header();
        $query = \request()->query();
        $user = $header['php-auth-user'];
        $password = $header['php-auth-pw'];
        $ids = [];
        if (isset($query['id'])){
            $deviceId = $query['id'];
            array_push($ids, $deviceId);
        }
        if (isset($query['uniqueId'])){
            $uniqueId = $query['uniqueId'];
            array_push($ids, $uniqueId);
        }
        if (!empty($ids)){
            $condition = [
                'id' => $ids
            ];
            $content = json_encode($condition);
        }else{
            $content = null;
        }
        $user = TraccarUser::where('email' , '=' , $header['php-auth-user'][0])->first();
        $url = 'https://djazfleet-dz.com/api/get_devices?lang=en&user_api_hash=' . $user->hash;
        $response = $this->cUrlGetData($url , $content);
        $devices = [];
        foreach ($response as $item){
            foreach ($item['items'] as $device){
                $value['id'] = $device['device_data']['id'];
                $value['attributes'] = [];
                $value['groupId'] = 0;
                $value['calendarId'] = 0;
                $value['name'] = $device['device_data']['name'];
                $value['uniqueId'] = $device['device_data']['imei'];
                $value['status'] = ($device['online'] == 'ack' || $device['online'] == 'online' ) ? 'online' : 'offline';
                $value['lastUpdate'] = $device['time'];
                $value['positionId'] = $device['device_data']['traccar']['latestPosition_id'];
                $value['phone'] = null;
                $value['model'] = null;
                $value['contact'] = null;
                $value['category'] = null;
                $value['disabled'] = false;
                $value['expirationTime'] = false;
                array_push($devices , $value);
            }
        }
        return response()->json($devices);
    }

    public function getPositions(){
        $header = \request()->header();
        $query = \request()->query();
        $deviceId = isset($query['deviceId']) ? $query['deviceId'] : null;
        $unformatedFrom = $query['from'];
        $unformatedTo = $query['to'];
        $dateFrom = substr($unformatedFrom,0,10);
        $timeFrom = substr($unformatedFrom,11,5);
        $dateTo = substr($unformatedTo,0,10);
        $timeTo = substr($unformatedTo,11,5);
        $user = TraccarUser::where('email' , '=' , $header['php-auth-user'][0])->first();
        $positions = [];
        ini_set('max_execution_time', 300);
        if (!empty($deviceId)){
            $url = 'https://djazfleet-dz.com/api/get_history?lang=en&user_api_hash=' . $user->hash.
                '&device_id='.$deviceId.'&from_date='.$dateFrom.'&from_time='.$timeFrom.'&to_date='.$dateTo.'&to_time='.$timeTo;
            $response = $this->cUrlGetData($url,null);
            if (!empty($response['items'])){
                foreach ($response['items'][1]['items'] as $item){
                    $value['id'] = $item['id'];
                    $value['attributes']['distance'] = (float)substr($item['other_arr'][1], 10);
                    $value['attributes']['totalDistance'] = (float)substr($item['other_arr'][2], 15);
                    $value['attributes']['motion'] = (bool)substr($item['other_arr'][3], 8);
                    $value['deviceId'] = $item['device_id'];
                    $value['protocol'] = $response['device']['traccar']['protocol'];
                    $value['serverTime'] = $item['server_time'];
                    $value['deviceTime'] = $item['server_time'];
                    $value['fixTime'] = $item['raw_time'];
                    $value['outdated'] = false;
                    $value['valid'] = true;
                    $value['latitude'] = $item['lat'];
                    $value['longitude'] = $item['lng'];
                    $value['altitude'] = (float)$item['altitude'];
                    $value['speed'] = $item['sensors_data'][0]['value'];
                    $value['course'] = $item['course'];
                    $value['address'] = null;
                    $value['accuracy'] = 0.0;
                    $value['network'] = null;
                    $value['geofenceIds'] = null;
                    array_push($positions,$value);
                }
            }
        }else{
            $url = 'https://djazfleet-dz.com/api/get_devices?lang=en&user_api_hash=' . $user->hash;
            $responseDevices = $this->cUrlGetData($url , null);
            foreach ($responseDevices as $responseDevice){
                foreach ($responseDevice['items'] as $device){
                    $url = 'https://djazfleet-dz.com/api/get_history?lang=en&user_api_hash=' . $user->hash.
                        '&device_id='.$device['id'].'&from_date='.$dateFrom.'&from_time='.$timeFrom.'&to_date='.$dateTo.'&to_time='.$timeTo;
                    $response = $this->cUrlGetData($url,null);
                    if (!empty($response['items'])){
                        foreach ($response['items'][1]['items'] as $item){
                            $value['id'] = $item['id'];
                            $value['attributes']['distance'] = (float)substr($item['other_arr'][1], 10);
                            $value['attributes']['totalDistance'] = (float)substr($item['other_arr'][2], 15) ;
                            $value['attributes']['motion'] = (bool)substr($item['other_arr'][3], 8);
                            $value['deviceId'] = $item['device_id'];
                            $value['protocol'] = $response['device']['traccar']['protocol'];
                            $value['serverTime'] = $item['server_time'];
                            $value['deviceTime'] = $item['server_time'];
                            $value['fixTime'] = $item['raw_time'];
                            $value['outdated'] = false;
                            $value['valid'] = true;
                            $value['latitude'] = $item['lat'];
                            $value['longitude'] = $item['lng'];
                            $value['altitude'] = (float)$item['altitude'];
                            $value['speed'] = $item['sensors_data'][0]['value'];
                            $value['course'] = $item['course'];
                            $value['address'] = null;
                            $value['accuracy'] = 0.0;
                            $value['network'] = null;
                            $value['geofenceIds'] = null;
                            array_push($positions,$value);
                        }
                    }
                }
            }
        }

        return response()->json($positions);
    }

    public function cUrlGetData($url, $post_fields = null)
    {
        $headers = ["Content-Type:application/json"];
        $ch = curl_init();
        $timeout = 3000;
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($post_fields && !empty($post_fields)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        }
        if ($headers && !empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $data = utf8_encode($data);
        $data = json_decode($data, JSON_UNESCAPED_UNICODE);
        return $data;
    }
}
