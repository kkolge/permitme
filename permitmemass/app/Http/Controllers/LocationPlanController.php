<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\LocationPlanLink;
use Illuminate\Support\Facades\DB;
use App\BillPlans;
use App\Society;
use Illuminate\Support\Facades\Auth;

class LocationPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        if(!Auth::user()->hasRole(['Super Admin'])){
            abort(403);
        }
        $locationplanlinks = DB::table('locationbillplanlink')
        ->join('location','location.id','=','locationbillplanlink.locationid')
        ->join ('billplan','billplan.id','=','locationbillplanlink.planid')
        ->where('locationbillplanlink.isactive','=',true)
        ->where('location.isactive','=',true)
        ->where('billplan.isactive','=',true)
        ->select(DB::raw('concat(location.name,":",location.pincode,":",location.city,":",location.taluka,":",location.district,":",location.state) as lname, billplan.name as pname, locationbillplanlink.id as id', 'locationbillplanlink.planstartdate','locationbillplanlink.planenddate'))
        ->orderBy('lname', 'desc')
        ->orderBy('locationbillplanlink.planstartdate','desc')
        ->orderBy('locationbillplanlink.planenddate','desc')
        ->paginate(50);

        dd($locationplanlinks); // DO A ISNULL CHECK 
        return view('linklocationbillplan.index', compact('locationplanlinks'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(!Auth::user()->hasRole(['Super Admin'])){
            abort(403);
        }

        $locations = Society::where('location.isactive','=',true)
        ->join('locationbillplanlink','locationbillplanlink.locationid','<>','location.id')
        ->select(DB::raw('concat(location.name," : ",location.pincode," : ",location.city," : ",location.taluka," : ",location.district," : ",location.state) as lname, location.id'))
        ->get()->pluck('lname','id');

        $billPlans = BillPlans::where('isactive','=',true)
        ->select(DB::raw('concat(name, " : ", description) as plan, id'))
        ->get()->pluck('plan','id');

        //dd($locations, $billPlans);

        return view('linklocationbillplan.create', compact('locations', 'billPlans'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(!Auth::user()->hasRole(['Super Admin'])){
            abort(403);
        }

        $this -> validate($request, [
            'location' => 'required|numeric',
            'plan' => 'required|numeric',
        ]);

        $link = new LocationPlanLink();
        $link->locationid = $request->input('location');
        $link->planid = $request->input('plan');
        $link->isactive = $request->input('isactive');
        
        $link->save();

        return redirect('/linklocationbillplan')->with('success', 'Bill plan successfully linked to Location');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(!Auth::user()->hasRole(['Super Admin'])){
            abort(403);
        }

        $link = LocationPlanLink::find($id);
        dd($link);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if(!Auth::user()->hasRole(['Super Admin'])){
        abort(403);
        }

        $link = LocationPlanLink::find($id);
        //dd($link);
        $locations = Society::where('location.isactive','=',true)
        //->join('locationbillplanlink','locationbillplanlink.locationid','<>','location.id')
        ->select(DB::raw('concat(location.name," : ",location.pincode," : ",location.city," : ",location.taluka," : ",location.district," : ",location.state) as lname, location.id'))
        ->get()->pluck('lname','id');

        $billPlans = BillPlans::where('isactive','=',true)
        ->select(DB::raw('concat(name, " : ", description) as plan, id'))
        ->get()->pluck('plan','id');

        return view ('linklocationbillplan.edit', compact('link','locations', 'billPlans'));
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
        if(!Auth::user()->hasRole(['Super Admin'])){
            abort(403);
        }

        $this -> validate($request, [
            'location' => 'required|numeric',
            'plan' => 'required|numeric',
        ]);
        //dd($request->input('location'), $id);
        $link = LocationPlanLink::find($id);
        //$link->locationid = $request->input('location');
        $link->planid = $request->input('plan');
        $link->isactive = $request->input('isactive');
        $link->save();

        return redirect('/linklocationbillplan')->with('success', 'Bill plan Location Link Updated Successfully');
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
