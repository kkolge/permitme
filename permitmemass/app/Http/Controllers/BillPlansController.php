<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BillPlans;
use Illuminate\Support\Facades\Auth;

class BillPlansController extends Controller
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
        $plans = BillPlans::where('isactive','=',true)->orderBy('name', 'desc')->orderBy('created_at','desc')->paginate(50);
        return view('billplans.index', compact('plans'));
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

        return view('billplans.create');
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
            'name' => 'required|min:5|max:50',
            'description' => 'required|min:10',
            'secdeposit' => 'required|gt:0',
            'hostingcharges' => 'required|numeric',
            'rent' => 'required|numeric',
            'txcharges' => 'required|numeric',
            'hardwareamc' => 'required|numeric',
            'softwareamc' => 'required|numeric',
            'training' => 'numeric',
            'ins' => 'numeric'
        ]);
        
        
            $plan = new BillPlans();
            $plan->name = $request->input('name');
            $plan->description = $request->input('description');
            $plan->secdeposit = $request->input('secdeposit');
            $plan->hostingcharges = $request->input('hostingcharges');
            $plan->rentpermonth = $request->input('rent');
            $plan->transactionrate = $request->input('txcharges');
            $plan->hardwareamcrate = $request->input('hardwareamc');
            $plan->softwareamcrate = $request->input('softwareamc');
            $plan->trainingcost = $request->input('training');
            $plan->installationandsetupcost = $request->input('ins');
            $plan->isactive = $request->input('isactive');
            $plan->save();

        return redirect('/billplans')->with ('success', 'Bill Plan added successfully');
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

        $plan = BillPlans::find($id);

        return view('billplans.edit',compact('plan'));
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

        $plan = BillPlans::find($id);

        return view('billplans.edit',compact('plan'));
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
            'name' => 'required|min:5|max:50',
            'description' => 'required|min:10',
            'secdeposit' => 'required|gt:0',
            'hostingcharges' => 'required|numeric',
            'rent' => 'required|numeric',
            'txcharges' => 'required|numeric',
            'hardwareamc' => 'required|numeric',
            'softwareamc' => 'required|numeric',
            'training' => 'numeric',
            'ins' => 'numeric'
        ]);
        
        
        $plan = BillPlans::find($id);
        $plan->name = $request->input('name');
        $plan->description = $request->input('description');
        $plan->secdeposit = $request->input('secdeposit');
        $plan->hostingcharges = $request->input('hostingcharges');
        $plan->rentpermonth = $request->input('rent');
        $plan->transactionrate = $request->input('txcharges');
        $plan->hardwareamcrate = $request->input('hardwareamc');
        $plan->softwareamcrate = $request->input('softwareamc');
        $plan->trainingcost = $request->input('training');
        $plan->installationandsetupcost = $request->input('ins');
        $plan->isactive = $request->input('isactive');
        $plan->save();
        

        return redirect('/billplans') ->with ('success', 'Bill Plan updated successfully.');
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
