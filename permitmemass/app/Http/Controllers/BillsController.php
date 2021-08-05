<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BillPlans;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BillsController extends Controller
{
    /**
     * Display's list of all the locations for which the bills are available and the list of last 6 bills
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        //Superadmin billing will be a separate process
        /*if(Auth::user()->hasRole(['Super Admin'])){
            $locationsWithBills = DB::table('locationbillplanlink')
            ->join('location','locationbillplanlink.locationid','=','location.id')
            ->join('billplan','locationbillplanlink.planid','=','billplan.id')
            ->select(DB::raw('concat(location.state, ":", location.district, ":", location.taluka, ":", location.city, ":", location.pincode, ":", location.name) as lname'),'billplan.name as bname', 'location.id')
            ->orderby('lname','desc')
            ->get();
        }
        else 
        */
        if(!Auth::user()->hasRole(['Super Admin'])){
            abort(403);
        }

        if(Auth::user()->hasRole(['Location Admin'])){
            $locationsWithBills = DB::table('locationbillplanlink')
            ->join('location','locationbillplanlink.locationid','=','location.id')
            ->join('billplan','locationbillplanlink.planid','=','billplan.id')
            ->where('location.id','=',session('GlocationId'))
            ->orWhere('parent','=',session('GlocationId'))
            ->select(DB::raw('concat(location.state, ":", location.district, ":", location.taluka, ":", location.city, ":", location.pincode, ":", location.name) as lname'),'billplan.name as bname','location.id')
            ->orderby('lname','desc')
            ->get();
        }
        else{
            //TO:DO - directly show the bill list
            $locationsWithBills = DB::table('locationbillplanlink')
            ->join('location','locationbillplanlink.locationid','=','location.id')
            ->join('billplan','locationbillplanlink.planid','=','billplan.id')
            ->where('location.id','=',session('GlocationId'))
            ->select(DB::raw('concat(location.state, ":", location.district, ":", location.taluka, ":", location.city, ":", location.pincode, ":", location.name) as lname'),'billplan.name as bname','location.id')
            ->orderby('lname','desc')
            ->get();
        }

        return view ('bills.index',compact('locationsWithBills'));

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
