<?php

namespace App\Http\Controllers;

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
        Log::debug('in store method');
        $jsonReq = json_decode(file_get_contents("php://input"),true);
        //validating the request
        \Validator::make($jsonReq,[
            'deviceid' => 'required|min:5|max:12',
            'identifier' => 'required',
            'temp' => 'required|numeric',
            'spo2' => 'required|numeric|max:100',
            'hbcount' => 'required|numeric',
            'flagstatus' => 'required'
        ]); 
        
        $dev = Device::where('serial_no',$jsonReq['deviceid'])->firstOrFail();
        $iot = new iotData();
        $iot -> identifier = $jsonReq['identifier'];
        $iot -> deviceid = $jsonReq['deviceid'];
        $iot -> temp = $jsonReq['temp'];
        $iot -> spo2 = $jsonReq['spo2'];
        $iot -> hbcount = $jsonReq['hbcount'];
        if($jsonReq['spo2'] <= 93 || $jsonReq['temp'] > 94){
            $iot-> flagstatus = true;
        }
        else{
            $iot->flagstatus = false;
        }
        $iot->save();
        Log::debug('Saved');
        $sendSMS = vLocDev::where('serial_no','=',$jsonReq['deviceid'])->first();
        Log::debug('Location: '.$jsonReq['deviceid'].' smsNotification:'.$sendSMS->smsnotification);
        if($sendSMS->smsnotification == true){
            Log::debug('Sending SMS');
            $txtMsg = 'Thank you for screening. SPO2:'.$jsonReq['spo2'].'% Temp:'.$jsonReq['temp'].'F @';
            $txtMsg = $txtMsg.Carbon::now()->format('Y-m-d h:i:s').'.';
            if($iot->flagstatus){
                $txtMsg = $txtMsg.' You need to see a doctor';
            }
            else{
                $txtMsg = $txtMsg.' Have a Good Day';
            }

            Log::debug($txtMsg);
            //start sending email notifiaction 
            $pnumber = $jsonReq['identifier'];
            //Check if registeres user 
            if($jsonReq['identifier'] < 6000000000){
                //attempt to get phone number form regusers 
                $pno = RegUser::where('id','=',$jsonReq['identifier'])->first();
                if($pno != null){
                    //dd($pno->phoneno);
                    $pnumber = $pno->phoneno;
                }
                else{
                    //dd("got null");
                }
            }

            Log::debug('sending Notification');
            //$iot->notify(new iotDataNotification($txtMsg, $jsonReq['identifier']));
            $iot->notify(new iotDataNotification($txtMsg, $pnumber));
            Log::debug('sent Notification');
        } 
        else{
            Log::debug('NOT Sending SMS');
        }

        $respJson  = json_encode(array(
            'deviceid' => $jsonReq['deviceid'], //$dev->serialno, //$iot->deviceid,
            'identifier' => $iot->identifier,
            'resp' => 'OK',
        ));

        return $respJson.'Message Sent';
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
            'cardid' => 'required|max:20'
        ]); 
        
        $reqDeviceId = $jsonReq['deviceid'];
        $reqCardId = $jsonReq['cardid'];

        //return $reqDeviceId."  ".$reqCardId;
        //find if the device exists
        $dev = Device::where('serial_no',$reqDeviceId)->firstOrFail();

        //getting the staff details 
        $user = RegUser::where('tagid',$reqCardId)->firstOrFail();
        //return $reqDeviceId."  ".$reqCardId;//."  ".$dev;

        //now we have both device and staff identified so sending response as json 
        $respJson  = json_encode(array(
            'deviceid' => $reqDeviceId,
            'userid' => "".$user->id."",
            'username' => $user->name,
            'flag' => "1"
        ));

        return $respJson;

    }
}
