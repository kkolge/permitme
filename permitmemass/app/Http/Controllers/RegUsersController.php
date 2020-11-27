<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\RegUser;
use Illuminate\Support\Facades\Auth;
use App\LinkLocUser;
use App\Society;
use App\Charts\ReportChartLine;
use App\IotData;
use Carbon\Carbon;
use App\User;

class RegUsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userId = Auth::user()->id;
        $lLoc =LinkLocUser::where('userid','=',$userId)->get();
        $lLoc = $lLoc[0]->locationid;
        //dd($lLoc);

        $regUser = RegUser::where('locationid','=',$lLoc)->paginate(10);
        if(count($regUser) != 0){
            return view('regusers.index', compact('regUser'));
        }
        else{
            return view('regusers.index',compact('regUser'))->with('error','No users registered for your location');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('regusers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this -> validate($request, [
            'name' => 'required|max:100',
            'phoneno' => 'required|digits:10|gt:0',
            'tagid' => 'required|max:20',
            'coverimage' => 'image|nullable|max:1999',
            'aadharno' => 'max:16'
        ]);

        //handling the file upload 
        if($request->hasFile('coverimage')){
            $fileNameWithExt = $request->file('coverimage')->getClientOriginalName();
            //getting the filename
            $filename = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
            //getting the extension
            $extension = $request->file('coverimage')->getClientOriginalExtension();
            //final filename
            $fileNameToStore = $filename."_".time().".".$extension;
            //now finally uploading 
            $path = $request->file('coverimage')->storeAs('public/coverimages',$fileNameToStore);
        }
        else{
            $fileNameToStore = 'noImage.jpg';
        }

        $user = new RegUser();
        $user->name = $request->input('name');
        $user->phoneno = $request->input('phoneno');
        $user->coverimage = $fileNameToStore;
        $user->tagid = $request->input('tagid');
        $user->AadharNo = $request->input('aadharno');
        $user->isactive = $request->input('isactive');

        //getting location id of the logged in user
        $linkLocId = LinkLocUser::where('userid','=',Auth::user()->id)->first();
        //dd ($linkLocId);
        $loc = Society::where('id','=',$linkLocId->locationid)->first();
        //dd($loc);
        $user->locationid = $loc->id;


        $user ->save();

        return redirect('/reguser')->with('success', 'User added successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //dd("I am in show");
        $stf = RegUser::find($id);
        
        
        $idata = iotdata::where('identifier',$id)
            ->where('created_at','>=',Carbon::today()->subDays(15))
            ->where('created_at','<=',Carbon::today())
            ->orderBy('created_at')->get();
        //dd($idata);
        $lbl = collect([]);
        $tempVal = collect([]);
        $spo2Val = collect([]);
        foreach ($idata as $i){
            $lbl->push($i->created_at->format('Y-m-d H:i:s'));
            $tempVal->push($i->temp);
            $spo2Val->push($i->spo2);
        }

        $chart1 = new ReportChartLine();
        $chart1->labels($lbl);

        $chart1->dataset('Temperature','bar',$tempVal)
            ->backgroundColor('orange');
        $chart1->title("Temperature data for last 15 days");
        
        $chart2 = new ReportChartLine();
        $chart2->labels($lbl);
        $chart2->title("SPO2 Data for last 15 days");
        $chart2->dataset('SPO2','bar',$spo2Val)
            ->backgroundColor('blue');
        //$chart->title("SPO2 data for all users");

        return view('regusers.show',compact('stf','chart1', 'chart2'));//->with('stf',$stf);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //return ('I am here');

        $user = RegUser::find($id);
        //dd($user);
        
        return view('regusers.edit')->with('user', $user);
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
        $this -> validate($request, [
            'name' => 'required|max:100',
            'phoneno' => 'required|digits:10',
            'tagid' => 'required|max:20',
            'coverimage' => 'image|nullable|max:1999',
            'aadharno' => 'max:16'
        ]);

        //handling the file upload 
        if($request->hasFile('coverimage')){
            $fileNameWithExt = $request->file('coverimage')->getClientOriginalName();
            //getting the filename
            $filename = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
            //getting the extension
            $extension = $request->file('coverimage')->getClientOriginalExtension();
            //final filename
            $fileNameToStore = $filename."_".time().".".$extension;
            //now finally uploading 
            $path = $request->file('coverimage')->storeAs('public/coverimages',$fileNameToStore);
        }


        $user = RegUser::find($id);
        $user->name = $request->input('name');
        $user->phoneno = $request->input('phoneno');
        if($request->hasFile('coverimage')){
            $user->coverimage = $fileNameToStore;
        }
        $user->tagid = $request->input('tagid');
        $user->AadharNo = $request->input('aadharno');
        $user->isactive = $request->input('isactive');
        //getting location id of the logged in user
        $linkLocId = LinkLocUser::where('userid','=',Auth::user()->id)->first();
        //dd ($linkLocId);
        $loc = Society::where('id','=',$linkLocId->locationid)->first();
        //dd($loc);
        $user->locationid = $loc->id;

            $user ->save();
            return redirect('/reguser')->with('success', 'User added successfully');
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
