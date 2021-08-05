<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\LinkBedPatient;
use App\Bed;
use App\RegUser;

class LinkBedPatientController extends Controller
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
        if(!Auth::user()->hasRole(['Super Admin'])){
            abort(403);
        }
        
        $linkBP = LinkBedPatient::where('linkHospitalBedUser.locationid','=',session('GlocationId'))
        ->where('linkHospitalBedUser.isactive','=',true)
        ->join('bedMaster','bedMaster.id','=','linkHospitalBedUser.bedid')
        ->join('reguser','linkHospitalBedUser.patientid','=','reguser.id')
        ->where('bedMaster.isactive','=',true)
        ->where('reguser.isactive','=',true)
        ->select('linkHospitalBedUser.id','bedMaster.bedno','reguser.name', 'reguser.phoneno', 'reguser.AadharNo','linkHospitalBedUser.created_at')
        ->orderBy('bedMaster.bedno','asc')
        ->paginate(50);

        return view('/hospital/linkUserBed/index',compact('linkBP'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $locId = session('GlocationId');
        //get regusers to this location
        $beds = Bed::where('locationid','=',$locId)
        ->where('isactive','=',true)
        ->orderBy('bedno','asc')
        ->pluck('bedno','id');
        
        //get beds for this location
        $regUsers = RegUser::where('locationid','=',$locId)
        ->where('isactive','=',true)
        ->orderBy('name','asc')
        ->pluck('name','id');

        return view('/hospital/linkUserBed/create',compact('beds','regUsers'));
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
            'bedno' => 'required',
            'userid' => 'required',
        ]);
        
        // add a check if the bed is already assigned to another user 
        $lnk = LinkBedPatient::where('bedid','=',$request->input('bedno'))
        ->where('isactive','=',true)
        ->count();
        if($lnk > 0){
            //bed already assigned
            return back()->withErrors('Error: Bed already assigned to another user. Please select another bed');
        }

        $link = new LinkBedPatient();
        $link->locationid = session('GlocationId');
        $link->bedid = $request->input('bedno');
        $link->patientid = $request->input('userid');
        $link->isactive = $request->input('isactive');

        $link->save();

        return redirect ('/hospital/linkUserBed')->with('success','Patient assigned to bed successfully');
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
        //dd($id);
        $parts = explode('+',$id);
        $recId = $parts[0];
        $act = $parts[1];
        //dd($recId, $action);

        $locId = session('GlocationId');
        //get regusers to this location
        $beds = Bed::where('locationid','=',$locId)
        ->where('isactive','=',true)
        ->orderBy('bedno','asc')
        ->pluck('bedno','id');
        
        //get beds for this location
        $regUsers = RegUser::where('locationid','=',$locId)
        ->where('isactive','=',true)
        ->orderBy('name','asc')
        ->pluck('name','id');

        $link = LinkBedPatient::where('linkHospitalBedUser.id','=',$recId)
        ->join('bedMaster','bedMaster.id','=','linkHospitalBedUser.bedid')
        ->join('reguser','linkHospitalBedUser.patientid','=','reguser.id')
        ->select('linkHospitalBedUser.id','bedMaster.bedno','reguser.name','linkHospitalBedUser.isactive' )
        ->first();

        $link = LinkBedPatient::find($recId);
        //dd($link);

        return view('/hospital/linkUserBed/edit',compact('beds', 'regUsers', 'link', 'act'));
        

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
            'bedno' => 'required',
            'userid' => 'required',
        ]);

        //getting the old record
        $link = LinkBedPatient::find($id);
        $link->bedId = $request->input('bedno');
        $link->patientId = $request->input('userid');
        $link->isactive = $request->input('isactive');

        if($link->isDirty()){
            $link->save();
            return redirect('/hospital/linkUserBed')->with('success', 'Updated bed assignment successfully!');
        }
        else{
            return redirect('/hospital/linkUserBed')->with('error', 'Nothing to update!');
        }
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
