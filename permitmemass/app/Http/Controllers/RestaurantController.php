<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\LinkLocUser;
use App\LinkLocDev;
use DB;
use App\Device;
use App\IotData;
use Carbon\Carbon;
use App\Exports\dayReportDetailExport;
use Maatwebsite\Excel\Facades\Excel;

class RestaurantController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dayReport(){
        //getting the logged in User
        $user = Auth::user()->id;
        //get the location that user is attached to 
        $loc = LinkLocUser::where('userid','=',$user)->get()->first();
        //dd($loc);
        //getting list of linked devices
        $devIdList = LinkLocDev::where('locationid','=',$loc->locationid)
            ->select('deviceid')
            ->get()
            ->toArray();
        //dd($devIdList);
        
        if(count($devIdList) > 0){
            $devNameList = Device::whereIn('id',$devIdList)
                ->select('serial_no')
                ->get()
                ->toArray();
        }
        else {
            return view('/restaurant/dayReport')->with('error', 'No device associated with your restaurant');
        }

        $visitReportByDay = IotData::whereIn('deviceid',$devNameList)
        ->where('created_at','>=',Carbon::today()->subDays(15))
        ->where('created_at','<=',Carbon::today()->addDays(1))
        ->select(DB::raw('count(*) as count'),DB::raw('Date(created_at) as date'))
        ->groupBy(DB::raw('Date(created_at)'))
        ->orderBy(DB::raw('Date(created_at)'))
        ->paginate(15);

        
      //dd($allData);
        return view('restaurant.dayReport', compact('visitReportByDay'));
    }

    
    public function dayReportDetail($date){
        //getting the logged in User
        $user = Auth::user()->id;
        //get the location that user is attached to 
        $loc = LinkLocUser::where('userid','=',$user)->get()->first();
        //dd($loc);
        //getting list of linked devices
        $devIdList = LinkLocDev::where('locationid','=',$loc->locationid)
            ->select('deviceid')
            ->get()
            ->toArray();
        //dd($devIdList);
        
        if(count($devIdList) > 0){
            $devNameList = Device::whereIn('id',$devIdList)
                ->select('serial_no')
                ->get()
                ->toArray();
        }
        else {
            return view('/restaurant/dayReportDetail')->with('error', 'No device associated with your restaurant');
        }

        $visitReportByDay = IotData::whereIn('deviceid',$devNameList)
        ->where(DB::raw('Date(created_at)'),'=',new Carbon($date))
        ->select('identifier','temp','spo2','hbcount','created_at')
        ->orderBy(DB::raw('Date(created_at)'))
        ->paginate(50);

        
      //dd($allData);
        return view('restaurant.dayReportDetail', compact('visitReportByDay','date'));
    }

    public function export($date) 
    {
        return Excel::download(new dayReportDetailExport($date), 'DayReport-'.$date.'.xlsx');
    }
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
}
