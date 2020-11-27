<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\AllDataReport;
use DB;
use App\Device;
use Illuminate\Support\Facades\Auth;
use App\LinkLocUser;
use App\LinkLocDev;
use App\IotData;
use Carbon\Carbon;
use App\Charts\ReportChartLine;
use App\RegUser;

class ReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * This controller is used to generate all reports. 
     * All report related data is generated in various methods and passed to 
     * the reports.
     */

     /**
     *All Data Report 
     * @return \Illuminate\Http\Response
     */
	public function allDataReport(){
        // throw all sensor data 
       
            /*$allData = DB::select('select i.deviceid, d.id, l.locationid,  i.identifier, ln.name, i.temp, i.spo2, i.hbcount, i.created_at from iotdata as i
            join device as d on i.deviceid = d.serial_no
            join LinkLocDev as l on d.id = l.deviceid
            join location as ln on l.locationid = ln.id
            order by i.created_at desc');//->paginate(15);
            */
            $allData = DB::table('iotdata')
                ->join('device','iotdata.deviceid','=','device.serial_no')
                ->join('LinkLocDev','LinkLocDev.deviceid','=','device.id')
                ->join('location','LinkLocDev.locationid','=','location.id')
                ->select('location.name as lname', 
                    'iotdata.identifier as identifier',
                    'iotdata.temp as temp', 'iotdata.spo2 as spo2',
                    'iotdata.hbcount as hbcount', 'iotdata.created_at as created_at'
                     )
                ->orderBy('created_at','desc')
                ->paginate(50);

        
      //dd($allData);
        return view('reports.allData', compact('allData'));

    }
    
    public function allDataLocationReport(){
        // throw all sensor data 
       
            /*$allData = DB::select('select i.deviceid, d.id, l.locationid,  i.identifier, ln.name, i.temp, i.spo2, i.hbcount, i.created_at from iotdata as i
            join device as d on i.deviceid = d.serial_no
            join LinkLocDev as l on d.id = l.deviceid
            join location as ln on l.locationid = ln.id
            order by i.created_at desc');//->paginate(15);
            */
            $allData = DB::table('iotdata')
                ->join('device','iotdata.deviceid','=','device.serial_no')
                ->join('LinkLocDev','LinkLocDev.deviceid','=','device.id')
                ->join('location','LinkLocDev.locationid','=','location.id')
                ->where('location.id','=',session('GlocationId'))
                ->select('location.name as lname', 
                    'iotdata.identifier as identifier',
                    'iotdata.temp as temp', 'iotdata.spo2 as spo2',
                    'iotdata.hbcount as hbcount', 'iotdata.created_at as created_at'
                     )
                ->orderBy('created_at','desc')
                ->paginate(50);

        
      //dd($allData);
        return view('reports.allData', compact('allData'));

    }



    /**
     * SPO2 Low Data Report
     * Shows data of users with SPO2 below 93 on daily basis
     */
    public function GenerateSPO2LowReport(){
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
            return view('/reports/SPO2Report')->with('error', 'No device associated with your location');
        }

        $lowSpo215Days = IotData::whereIn('deviceid',$devNameList)
        ->where('created_at','>=',Carbon::today()->subDays(15))
        ->where('created_at','<=',Carbon::today()->addDays(1))
        ->where('spo2','<',93)
        ->select(DB::raw('count(*) as count'),DB::raw('Date(created_at) as date'))
        ->groupBy(DB::raw('Date(created_at)'))
        ->orderBy(DB::raw('Date(created_at)'),'desc')
        ->get();

        //dd($lowSpo215Days);
        //creating data for chart
        $lbl = collect([]);
        $values = collect([]);
        foreach($lowSpo215Days as $spo2){
            $lbl->push($spo2->date);
            $values->push($spo2->count);
        }
        //dd($lbl);
        //dd($values);


        $spo2Chart = new ReportChartLine();
        //get array for labels
        $spo2Chart->labels($lbl);
        //dd($spo2Chart);
        $spo2Chart->dataset('SPO2 data', 'bar',$values)
            ->backgroundColor('red');
            //->options=[legend->position('right')];
        //$spo2Chart->title('SPO2 data for last 15 days');


        return view('reports.SPO2Report',compact('lowSpo215Days', 'spo2Chart'));
        
    }

    public function SPO2DetailsByDate($date){
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
            return view('/reports/SPO2ReportByDate')->with('error', 'No device associated with your location');
        }

        $lowSpo2OnDate = IotData::whereIn('deviceid',$devNameList)
        ->where(DB::raw('Date(created_at)'),'=',new Carbon($date))
        ->where('spo2','<',93)
        ->select('identifier','temp','spo2','hbcount','created_at')
        ->orderBy(DB::raw('Date(created_at)'))
        ->paginate(50);
        //dd($lowSpo2OnDate);
        /*
        foreach($lowSpo2OnDate as $rec){
            //dd($rec->identifier);
            if($rec->identifier < 6000000000){
                dd('inside if with '.$rec->identifier);
                $usr = RegUser::find($rec->identifier);
                dd($usr);
                if($usr->count() > 0){
                    $rec->identifier = $usr->name;
                    $rec->save();
                    dd('saved record', $rec->identifier);
                }
                //
            }
        }
        */
        //dd($lowSpo2OnDate);
        return view('/reports/SPO2ReportByDate',compact('date','lowSpo2OnDate'));
    }

    /**
     * Function to generate High Temperature report for last 15 days
     */
    public function GenerateTempHighReport(){
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
            return view('/reports/TempReport')->with('error', 'No device associated with your location');
        }

        $highTemp15Days = IotData::whereIn('deviceid',$devNameList)
        ->where('created_at','>=',Carbon::today()->subDays(15))
        ->where('created_at','<=',Carbon::today()->addDays(1))
        ->where('temp','>',94.5)
        ->select(DB::raw('count(*) as count'),DB::raw('Date(created_at) as date'))
        ->groupBy(DB::raw('Date(created_at)'))
        ->orderBy(DB::raw('Date(created_at)'))
        ->paginate(10);

        //dd($lowSpo215Days);
        //creating data for chart
        $lbl = collect([]);
        $values = collect([]);
        foreach($highTemp15Days as $temp){
            $lbl->push($temp->date);
            $values->push($temp->count);
        }
        //dd($lbl);
        //dd($values);


        $tempChart = new ReportChartLine();
        //get array for labels
        $tempChart->labels($lbl);
        //dd($spo2Chart);
        $tempChart->dataset('Temp Data', 'bar',$values)
            ->backgroundColor('red');
        //$spo2Chart->title('SPO2 data for last 15 days');


        return view('reports.TempReport',compact('highTemp15Days', 'tempChart'));
        
    }

    /**
     * High Temperature report by date
     */

     public function TempDetailsByDate($date){
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
            return view('/reports/TempDetailsByDate')->with('error', 'No device associated with your location');
        }

        $highTempOnDate = IotData::whereIn('deviceid',$devNameList)
        ->where(DB::raw('Date(created_at)'),'=',new Carbon($date))
        ->where('temp','>',94.5)
        ->select('identifier','temp','spo2','hbcount','created_at')
        ->orderBy(DB::raw('Date(created_at)'))
        ->paginate();
        //dd($lowSpo2OnDate);

        foreach($highTempOnDate as $rec){
            //dd($rec->identifier);
            if($rec->identifier < 1000000000){
                //dd('inside if with '.$rec->identifier);
                $usr = RegUser::find($rec->identifier);
                //dd($usr);
                if($usr->count() > 0){
                    $rec->identifier = $usr->name;
                }
                //$rec->save();
            }
        }
        
        //dd($lowSpo2OnDate);
        return view('/reports/TempDetailsByDate',compact('date','highTempOnDate'));
   
     }

     /**
      * Report to show data of any user for last 15 days 
      */
      public function UserReportSearch(Request $request){
        

        $id = $request->input('identifier');
        $this -> validate($request, [
            'identifier' => 'required|gt:0'
        ]);
        //dd ($id);
        return $this->UserReport($id);
      }

      
      public function UserReport($identifier){

        //The identifier should be 10 digit number
        /**/
          //dd($identifier);

        $iotData = IotData::where('identifier','=',$identifier)
            ->where('created_at','<=',Carbon::today()->addDays(1))
            ->where('created_at','>=',Carbon::today()->subDays(15))
            ->orderBy('created_at','desc')
            ->get();
        //dd($iotData);

        if(count($iotData) > 0){
            $lbl = collect([]);
            $valuesTemp = collect([]);
            $valuesSpo2 = collect([]);
            foreach($iotData as $data){
                $lbl->push($data->created_at->format('Y-m-d h:i:s'));
                $valuesTemp->push($data->temp);
                $valuesSpo2->push($data->spo2);
            }
        //dd($lbl);
        //dd($values);


            $spo2Chart = new ReportChartLine();
            //get array for labels
            $spo2Chart->labels($lbl);
        //dd($spo2Chart);
            $spo2Chart->dataset('SPO2 data', 'bar',$valuesSpo2)
                ->backgroundColor('red');
            
            $tempChart = new ReportChartLine();
            $tempChart->labels($lbl);
            $tempChart->dataset('Temperature data','bar',$valuesTemp)
                ->backgroundColor('blue');
            return view('reports.userReport',compact('identifier','iotData','spo2Chart','tempChart'));
        }
        else{
            return view('reports.userReport',compact('identifier','iotData'))->with('error','No data available for this user');
        }
      }
    /**
     * 
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
