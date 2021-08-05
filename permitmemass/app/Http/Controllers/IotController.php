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
use Illuminate\Support\Facades\DB;


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
        if(strtoupper($devmd5) != strtoupper(md5($deviceid))){
            return('E01');
        }

        $devAuth = DevAuths::where('token','=',$token)->get();
        if($devAuth->count() == 1){
            //check if token matches the device
            if($devAuth[0]->deviceid == $deviceid){
                //check if the devupdated is set to 1 else ask to resend the request to calidate
                if($devAuth[0]->devupdated == true){
                    //update the updated_at field
                    $devAuth[0]->updated_at == Carbon::now();
                    $devAuth[0]->save();
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
            'random1' => 'required|min:16|max:32',
            'random2' => 'required|min:16|max:32',
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
        if($user->count() == 0){ //user not found. return error
            $respJson  = json_encode(array(
                'status' => 'error',
                'random1' => $devmd5,
                'reason' => 'E11'
            ));
            return ($respJson);
        }

        $iotData = IotData::where('identifier','=',$user[0]->phoneno)->orderBy('created_at','desc')->take(1)->get();
        
        //now we have both device and staff identified so sending response as json 
        $respJson  = json_encode(array(
            'status' => 'success',
            'random1' => $devmd5,
            'random2' => $token,
            'username' => $user[0]->name,
            'identifier' => $user[0]->phoneno,
            'flagstatus' => $iotData[0]->flagstatus ?? 0,
           //TO:DO - remove for V6 'useractive' => $user->isactive ?? 0
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
        //dd($lastToken);

        //Step 1 validate that the device hash is correct 
        //dd (strtoupper(md5($reqDeviceId)), strtoupper($devmd5));
        if(strtoupper(md5($reqDeviceId)) == strtoupper($devmd5)){
            //the hashing is correct 

            //check if the device exists
            $dev = Device::where('serial_no','=',$reqDeviceId)->get();
            if($dev->count() == 1){
                //before proceeding further, lets check if the device, location and the link are active
                if(!$dev[0]->isactive) {
                    $respJson  = json_encode(array(
                        'status' => 'error',
                        'random1' => md5($reqDeviceId),
                        'reason' => 'E22'
                    ));
                    return($respJson);
                }
                //dd('device active');

                //Checking if link and the location is active 
                //TO-DO - split this to check location and link separately and send different error code
                $linkAndLocationActive = LinkLocDev::where('LinkLocDev.deviceid','=',$dev[0]->id)
                    ->join('location', 'location.id','LinkLocDev.locationid')
                    ->where('location.isactive','=',true)
                    ->where('LinkLocDev.isactive','=',true)
                    ->get()
                    ->count();
                    //dd($linkAndLocationActive);
                if($linkAndLocationActive == 0){
                        $respJson  = json_encode(array(
                            'status' => 'error',
                            'random1' => md5($reqDeviceId),
                            'reason' => 'E23'
                        ));
                        return($respJson);
                }

                //dd('device, link and location active');

                //get the last record from the devauth table for this device id 
                $lastAuth = DevAuths::where('deviceid','=',$dev[0]->serial_no)->orderby('updated_at','desc')->take(1)->get();
                //dd($lastAuth->count());
                //dd($lastAuth);

                //dd($lastAuth->tokenlastToken);
                if($lastAuth->count() == 1) { //device had past authintacations
                    //checking if the tokens match 
                    //dd($lastAuth[0]->token, $lastAuth[0]->devupdated, $lastToken);
                    if($lastToken == $lastAuth[0]->token || !$lastAuth[0]->devupdated){
                        //last token on the server matches the one sent by the device
                        //update is active for the last row to false
                        if($lastAuth[0]->isactive == true){
                            $lastAuth[0]->isactive = false;
                            //$lastAuth[0]->devupdated = true;
                            $lastAuth[0]->save();
                        }
                        //dd('devupdated set');

                        //generate new row and send the new token to the device
                        //save the details in devauth table 
                        $devauth = new DevAuths();
                        $devauth->deviceid = $reqDeviceId;
                        $devauth->token = Str::random(16);
                        $devauth->isactive = true;
                        $devauth->devupdated = false;
                        $devauth->save();
                        //dd('new row added to devauth');

                        

                        //Sending response
                        $respJson  = json_encode(array(
                            'status' => 'success',
                            'random1' => md5($reqDeviceId),
                            'random2' => $devauth->token,
                            'hbcount' => env('CUTOFF_PULSE'),
                            'spo2' => env('CUTOFF_SPO2'),
                            'temp' => env('CUTOFF_TEMP'),
                            'devtype' => $dev[0]->devtype
                        ));
                       
                        //dd($respJson);
                        return($respJson);
                    }
                    elseif($lastAuth[0]->devupdated) {
                        $respJson = json_encode(array(
                            'status' => 'error',
                            'random1' => md5($reqDeviceId),
                            'reason' => 'E24'
                        ));
                        return($respJson);
                    }else{
                        $respJson = json_encode(array(
                            'status' => 'error',
                            'random1' => md5($reqDeviceId),
                            'reason' => 'E26'
                        ));
                        return($respJson);
                    }
                }
                elseif ($lastToken == "0000000000000000"){ // assuming that the device does not have any last token information
                    //dd('in null token 2');
                    $devauth = new DevAuths();
                    $devauth->deviceid = $reqDeviceId;
                    $devauth->token = Str::random(16);
                    $devauth->isactive = true;
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
                'status' => 'error',
                'random1' => $devmd5,
                'random2' => $lastToken 
            ));
            return($respJson);
        }
        $tokenRow = DevAuths::where('token','=',$lastToken)->get();
        if($tokenRow->count() == 1){
            // row against this token exists 
            
            //check for device id 
            //dd($tokenRow[0]->deviceid, md5($tokenRow[0]->deviceid), $devmd5);
            if(strtoupper(md5($tokenRow[0]->deviceid)) == strtoupper($devmd5)){
                //double confirmation update the status  
                if($status == 'success'){
                    $tokenRow[0]->devupdated = true;
                }
                elseif($status == 'update'){
                    $tokenRow[0]->updated_at = Carbon::now();
                }
                $tokenRow[0]->save();

                $respJson = json_encode(array(
                    'status' => 'success', 
                    'random2' => $lastToken 
                ));
                return($respJson);
            }
            else{
                $respJson = json_encode(array(
                    'status' => 'error',
                    'random1' => $devmd5,
                    'random2' => $lastToken, 
                    'reason' => 'E32'
                ));
                return($respJson);
            }
        }
        else{
            $respJson = json_encode(array(
                'status' => 'error',
                'random1' => $devmd5,
                'random2' => $lastToken, 
                'reason' => 'E31'
            ));
            return($respJson);
        }
    }

    public function saveDeviceData(){
        //Log::debug('in store method');
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
        //$flagstatus = $jsonReq['flagstatus'];

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
        
        //Ketan Add for V3
        /*
            Simple truth table approach 
            if pulse rate is high - value = 1
            if spo2 is low - value = 2
            if temp is high - value = 4
            updateValue gets the value based on the above parameters so we can set the proper column to be updated 
	    */
        $totalFlagCount = 0;
        if ($hbcount > env('CUTOFF_PULSE')){
            $totalFlagCount = $totalFlagCount + 1;
        }
        if($spo2 < env('CUTOFF_SPO2')){
            $totalFlagCount = $totalFlagCount + 2;
        }
        if($temp > env('CUTOFF_TEMP')){
            $totalFlagCount = $totalFlagCount + 4;
        }

        $iot = new iotData();
        $iot -> identifier = $identifier;
        $iot -> deviceid = $deviceId;
        $iot -> temp = $temp;
        $iot -> spo2 = $spo2;
        $iot -> hbcount = $hbcount;
        if($totalFlagCount > 0){
            $iot->flagstatus = true;
        }
        else{
            $iot->flagstatus = false;
        }
        $iot->save();
        
        
        $err = DB::statement('call after_iotdata_insert(?,?,?)',[$deviceId, $totalFlagCount, Carbon::now()]);
        //dd($err);
        $respJson  = json_encode(array(
            'status' => 'success',
            'random1' => $devmd5,
            'random2' => $token
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
