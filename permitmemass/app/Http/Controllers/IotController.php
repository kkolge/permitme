<?php

namespace App\Http\Controllers;

use App\DevAuths;
use Illuminate\Http\Request;
use App\Device;
use App\IotData;
use App\RegUser;
use App\vLocDev;
use App\Notifications\sendAlertEmail;
use App\Notifications\iotDataNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use ClickSend;
use ClickSend\Model\SmsMessage;
use GuzzleHttp;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\LinkLocDev;

//use GuzzelHttp\Guzzel;

class IotController extends Controller
{
    use \Illuminate\Notifications\Notifiable;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Function to check if the received token is in the db and the device matches and if it is active
     * parameters 
     * token, device id, md5 of the device
     * 
     * Return values -
     * E01 - md5 of device does not match
     * E02 - token does not exist 
     * E03 - token and device does not match
     * E04 - token is no saved on the device. request a nenewal
     */
    function validateToken(string $token, string $devmd5, string $deviceid ){
        if($devmd5 != md5($deviceid)){
            return('E01');
        }

        $devAuth = DevAuths::where('token','=',$token)->get();
        if($devAuth->count() == 1){
            //check if token matches the device
            if($devAuth->deviceid == $deviceid){
                //check if the devupdated is set to 1 else ask to resend the request to calidate
                if($devAuth->devupdated == true){
                    //update the updated_at field
                    $devAuth->updated_at == Carbon::now();
                    $devAuth->save();
                    return ('SUCCESS');
                }
                else{
                    return ('E04');
                }
            }
            else{ // token and device do not march
                return('E03'); // ask to contact support
            }
        }
        else{ //token does not exist
            return ('E02'); // ask to contact support
        }
        
    }

    /**
     * Validate the request coming from the device with RFID
     */
    public function validateRFID(Request $request)
    {
        //This will be used to validate the RFID Tag ID in the staff table 
        //the data is received as a JSON Post data
        //data is sent in following format 
        //deviceid:<device id>
        //cardid:<card id>
        //return 'in validate ';
        $jsonReq = json_decode(file_get_contents("php://input"),true);
        //validating the request
        //return $jsonReq;
        \Validator::make($jsonReq,[
            'deviceid' => 'required|min:5|max:12',
            'random1' => 'required|min:16|max:16',
            'random2' => 'required|min:16|max:16',
            'cardid' => 'required|max:20'
        ]); 
        
        $reqDeviceId = $jsonReq['deviceid'];
        $devmd5 = $jsonReq['random1'];
        $token = $jsonReq['random2'];
        $reqCardId = $jsonReq['cardid'];

        //lets validate the device, token and devupdated
        $valResult = $this->validateToken($token, $devmd5, $reqDeviceId);
        if($valResult != 'SUCCESS'){
            $respJson  = json_encode(array(
                'status' => 'error',
                'random1' => $devmd5,
                'reason' => $valResult
            ));
            return ($respJson);
        }

        $user = RegUser::where('tagid',$reqCardId)->get();
        if($user->count == 0){ //user not found. return error
            $respJson  = json_encode(array(
                'status' => 'error',
                'random1' => $devmd5,
                'reason' => 'E11'
            ));
            return ($respJson);
        }

        $iotData = IotData::where('identifier','=',$user->phoneno)->orderBy('created_at','desc')->take(1);
        
        //now we have both device and staff identified so sending response as json 
        $respJson  = json_encode(array(
            'status' => 'success',
            'random1' => $devmd5,
            'random2' => $token,
            'username' => $user->name,
            'identifier' => $user->phoneno,
            'flagstatus' => $iotData->flagstatus ?? 0
        ));

        return $respJson;

    }

    //Added for V2

    /*
    function name : validateDevice 
    This function is used to validate the device incoming request is if is coming wihout the token. 
    following parameters are expected with the request 
    1. deviceid - this will be the device id of the device 
    2. mac address - this will be the mac address of the device 
    3. a md5 hash for the device id 
    4. last token
    */
    public function validateDevice(){
        $jsonReq = json_decode(file_get_contents("php://input"),true);
        //validating the request
        //return $jsonReq;
        \Validator::make($jsonReq,[
            'deviceid' => 'required|min:5|max:12',
            'macid' => 'required|max:8',
            'random1' => 'required|string|min:16|max:16',
            'randon2' => 'required|string|min:16|max:16'
        ]); 
        
        $reqDeviceId = $jsonReq['deviceid'];
        $reqCardId = $jsonReq['macid'];
        $devmd5 = $jsonReq['random1'];
        $lastToken = $jsonReq['random2'];

        //Step 1 validate that the device hash is correct 
        if(md5($reqDeviceId) == $devmd5){
            //the hashing is correct 
            //check if the device exists
            $dev = Device::where('serial_no','=',$reqDeviceId)->get();
            if($dev->count() == 1){
                //before proceeding further, lets check if the device, location and the link are active
                if(!$dev->isactive) {
                    $respJson  = json_encode(array(
                        'status' => 'error',
                        'random1' => md5($reqDeviceId),
                        'reason' => 'E22'
                    ));
                    return($respJson);
                }
                $linkAndLocationActive = LinkLocDev::where('LinkLocDev.deviceid','=',$dev->id)
                    ->join('location', 'location.id','LinkLocDev.locationid')
                    ->where('location.isactive','=',true)
                    ->where('LinkLocDev.isactive','=',true)
                    ->get()
                    ->count();
                if($linkAndLocationActive == 0){
                    if(!$dev->isactive) {
                        $respJson  = json_encode(array(
                            'status' => 'error',
                            'random1' => md5($reqDeviceId),
                            'status' => 'E23'
                        ));
                        return($respJson);
                    }
                }

                //get the last record from the devauth table for this device id 
                $lastAuth = DevAuths::where('deviceid','=',$dev->serial_no)->orderby('updated_at','desc')->take(1);
                if($lastAuth->count() == 1) { //device had past authintacations
                    //checking if the tokens march 
                    if($lastToken == $lastAuth->token || $lastAuth->devupdated){
                        //last token on the server matches the one sent by the device
                        //update is active for the last row to false
                        if($lastAuth->isactive == true){
                            $lastAuth->isactive = false;
                            $lastAuth->devupdated = true;
                            $lastAuth->save();
                        }

                        //generate new row and send the new token to the device
                        //save the details in devauth table 
                        $devauth = new DevAuths();
                        $devauth->deviceid = $dev->$reqDeviceId;
                        $devauth->token = Str::random(16);
                        $devauth->isactime = true;
                        $devauth->devupdated = false;
                        $devauth->save();
                    
                        //Sending response
                        $respJson  = json_encode(array(
                            'status' => 'success',
                            'random1' => md5($reqDeviceId),
                            'random2' => $devauth->token,
                            'hbcount' => env('CUTOFF_PULSE'),
                            'spo2' => env('CUTOFF_SPO2'),
                            'temp' => env('CUTOFF_TEMP')
                        ));
        
                        return($respJson);
                    }
                    elseif($lastAuth->devupdated) {
                        $respJson = json_encode(array(
                            'status' => 'error',
                            'random1' => md5($reqDeviceId),
                            'reason' => 'E24'
                        ));
                        return($respJson);
                    }
                }
                elseif ($lastToken != "0000000000000000"){ // assuming that the device does not have any last token information
                    $devauth = new DevAuths();
                    $devauth->deviceid = $dev->$reqDeviceId;
                    $devauth->token = Str::random(16);
                    $devauth->isactime = true;
                    $devauth->devupdated = false;
                    $devauth->save();
                
                    //Sending response
                    $respJson  = json_encode(array(
                        'status' => 'success',
                        'random' => md5($reqDeviceId),
                        'random2' => $devauth->token,
                        'hbcount' => env('CUTOFF_PULSE'),
                        'spo2' => env('CUTOFF_SPO2'),
                        'temp' => env('CUTOFF_TEMP')
                    ));
    
                    return($respJson);
                }
                //check if the last token id is all zeros - is this device connecting for the first time 
            }   
            else{
                $respJson = json_encode(array(
                    'status' => 'error',
                    'random1' => '0000',
                    'reason' => 'E21'
                ));
                return($respJson);
            }
        }
        else{
            $respJson = json_encode(array(
                'status' => 'error',
                'random1' => md5($reqDeviceId),
                'reason' => 'E25'
            ));
            return($respJson);
        }
    }

    /*
    This function is to update the status of the token if is has been saved successfully on the device 
    parameters 
    1. random1 - md5 hash of the device id
    2. randonm2 - last token
    3. status - either success or update
    */
    public function updateTokenSuccess(){
        $jsonReq = json_decode(file_get_contents("php://input"),true);
        //validating the request
        //return $jsonReq;
        \Validator::make($jsonReq,[
            'random1' => 'required|string|min:16|max:16',
            'randon2' => 'required|string|min:16|max:16',
            'status' => 'required'|'string'|'min:5'
        ]); 
        
        $devmd5 = $jsonReq['random1'];
        $lastToken = $jsonReq['random2'];
        $status = $jsonReq['status'];
        
        //check the status message first 
        if($status == 'error'){
            $respJson = json_encode(array(
                'random1' => $devmd5,
                'random2' => $lastToken, 
                'status' => 'error'
            ));
            return($respJson);
        }
        $tokenRow = DevAuths::where('token','=',$lastToken)->get();
        if($tokenRow->count() == 1){
            // row against this token exists 
            
            //check for device id 
            if(md5($tokenRow->deviceid) == $devmd5){
                //double confirmation update the status  
                if($status == 'success'){
                    $tokenRow->devupdated = true;
                }
                elseif($status == 'update'){
                    $tokenRow->updated_at = Carbon::now();
                }
                $tokenRow->save();

                $respJson = json_encode(array(
                    'random2' => $lastToken, 
                    'status' => 'success'
                ));
                return($respJson);
            }
            else{
                $respJson = json_encode(array(
                    'random1' => $devmd5,
                    'random2' => $lastToken, 
                    'status' => 'error'
                ));
                return($respJson);
            }
        }
        else{
            $respJson = json_encode(array(
                'random1' => $devmd5,
                'random2' => $lastToken, 
                'status' => 'error'
            ));
            return($respJson);
        }
    }

    public function saveDeviceData(){
        Log::debug('in store method');
        $jsonReq = json_decode(file_get_contents("php://input"),true);
        //validating the request
        \Validator::make($jsonReq,[
            'random1' => 'required|min:16|max:16',
            'random2' => 'required|min:16|max:16',
            'deviceid' => 'required|min:5|max:12',
            'identifier' => 'required',
            'temp' => 'required|numeric|gt:85|lt:105',
            'spo2' => 'required|numeric|max:100',
            'hbcount' => 'required|numeric'
        ]); 

        
        $devmd5 = $jsonReq['random1'];
        $token = $jsonReq['random2'];
        $deviceId = $jsonReq['deviceid'];
        $identifier = $jsonReq['identifier'];
        $temp = $jsonReq['temp'];
        $spo2 = $jsonReq['spo2'];
        $hbcount = $jsonReq['hbcount'];
        $flagstatus = $jsonReq['flasstatus'];

        //lets validate the device, token and devupdated
        $valResult = $this->validateToken($token, $devmd5, $deviceId);
        if($valResult != 'SUCCESS'){
            $respJson  = json_encode(array(
                'status' => 'error',
                'random1' => $devmd5,
                'reason' => $valResult
            ));
            return ($respJson);
        }
        
        $iot = new iotData();
        $iot -> identifier = $identifier;
        $iot -> deviceid = $deviceId;
        $iot -> temp = $temp;
        $iot -> spo2 = $spo2;
        $iot -> hbcount = $hbcount;
        if($hbcount <= env('CUTOFF_PULSE') ||$spo2 <= env('CUTOFF_SPO2') || $temp > env('CUTOFF_TEMP')){
            $iot->flagstatus = true;
        }
        else{
            $iot->flagstatus = false;
        }
        $iot->save();
        
        

        $respJson  = json_encode(array(
            'status' => 'success',
            'random1' => $devmd5,
            'random2' => $token,
        ));

        return $respJson;
    }

    function sendSMS($deviceid, $spo2, $temp, $hbcount, $identifier, $iot ){
        $sendSMS = vLocDev::where('serial_no','=',$deviceid)->first();
        if($sendSMS->smsnotification == true){
            $txtMsg = 'Thank you for screening. SPO2:'.$spo2.'% Temp:'.$temp.'F @';
            $txtMsg = $txtMsg.Carbon::now()->format('Y-m-d h:i:s').'.';
            if($iot->flagstatus){
                $txtMsg = $txtMsg.' You need to see a doctor';
            }
            else{
                $txtMsg = $txtMsg.' Have a Good Day';
            }

            //start sending email notifiaction 
            $pnumber = $identifier;
            //Check if registeres user 
        
            $iot->notify(new iotDataNotification($txtMsg, $pnumber));
            Log::debug('sent Notification');
        } 
        else{
            Log::debug('NOT Sending SMS');
        }
    }
}
