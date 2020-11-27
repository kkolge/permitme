<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Device;
use App\Society;
use App\LinkLocUser;
use App\LinkLocDev;
use App\User;
use App\IotData;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        /* Add data to session about the user
        * adding following data
        * 1. Location name
        * 2. Devices linked to location 
        * 3. LATER - IotData table name 
        * 4. Designation
        * 
        * To get the relevant data we need user ->linkLocUser -> Location ->LinkLocDev -> dev 
        */
        //getting the linked locaion id from the user if
        try { 
            $linkLocId = LinkLocUser::where('userid','=',Auth::user()->id)->first();
            //dd(Auth::user()->id);
            //dd($linkLocId);
            //dd ($linkLocId);
            $loc = Society::find($linkLocId->locationid);//->toArray();
            //dd($loc['id']);
            //$dev = LinkLocDev::all();
            $devIdList = LinkLocDev::where('locationid','=',$loc['id'])
                ->select('deviceid')
                ->get()
                ->toArray();
            //dd ($dev);
            
            /*$devIdList = array();
            $cnt = 0;
            foreach ($dev as $d){
                
                $devIdList[$cnt++] = $d->deviceid;
            }*/
            //dd ($devIdList);
            if(count($devIdList) > 0){
                $devNameList = Device::whereIn('id',$devIdList)
                    ->select('serial_no')
                    ->get();
            }
            //dd($devNameList);
        }
        catch (Exception $e){
            //do nothing
        }
        session([
            'GDesignation' => $linkLocId->designation,
            'GlocationName' =>$loc->name,
            'GlocationId'=>$loc->id,
            'GnoOfResidents' => $loc->noofresidents,
            'Gcity' => $loc->city,
            'GisActive' => $loc->isactive,
            'GDevId' => $devNameList
        ]);
        
        //getting data for dashboard
        //Total scaned till date
        $totScan = IotData::whereIn('deviceid',$devNameList)->get()->count();
        //dd($totScan);
        //Total Scanned Today
        $totScanToday = IotData::whereIn('deviceid',$devNameList)
            ->where('created_at','>=',Carbon::today())
            ->get()
            ->count();
        //dd($totScanToday);
        //Total Scanned with SPO2 below 93
        $totScanSPO2 = IotData::whereIn('deviceid',$devNameList)
            ->where('spo2','<',93)
            ->get()
            ->count();
        //dd($totScanSPO2);
        //Total Scanned with SPO2 beow 93 TODAY
        $totScanTodaySPO2 = IotData::whereIn('deviceid',$devNameList)
        ->where('created_at','>=',Carbon::today())
        ->where('spo2','<',93)
        ->get()
        ->count();
        //dd($totScanTodaySPO2);
        //Total Scanned with Temp above 99
        $totScanTemp = IotData::whereIn('deviceid',$devNameList)
            ->where('temp','>',99)
            ->get()
            ->count();
        //dd($totScanTemp);
        //Total Scanned with Temp above 99 TODAY
        $totScanTodayTemp = IotData::whereIn('deviceid',$devNameList)
        ->where('created_at','>=',Carbon::today())
        ->where('temp','>',99)
        ->get()
        ->count();
        //dd($totScanTodayTemp);
        return view('home',compact('totScan',
            'totScanToday',
            'totScanSPO2',
            'totScanTodaySPO2',
            'totScanTemp',
            'totScanTodayTemp'));
    }
}
