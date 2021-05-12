<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\LinkLocUser;
use App\User;
use App\Society;
use DB;
use Illuminate\Support\Facades\Auth;


class LinkLocUserController extends Controller
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

        //getting logged in users location 
        //$userId = Auth::user()->id;
        //dd($userId);
        //$lLoc = LinkLocUser::where('userid','=',$userId)->first();//->locationid;
        //dd($lLoc);

        //if($lLoc != null){
        //$link = LinkLocUser::all();
        $link = DB::table('linklocusers')
            ->Join('location','linklocusers.locationid','=','location.id')
            ->Join('users','linklocusers.userid','=','users.id')
            ->select ('location.name as lname','users.name as uname','linklocusers.isactive','linklocusers.id', 'linklocusers.phoneno1', 'linklocusers.designation')
            //->where ('location.id','=',$lLoc)
            ->orderBy('lname')
            ->orderBy('uname')
            ->paginate(10);
            //dd($link);
        
        return view('linkLocUsers.index',compact('link'));
        
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

        $loc = Society::orderBy('name')->pluck('name','id')->toArray();
        $user = User::orderBy('name')->pluck('name','id')->toArray();
        return view('linkLocUsers.create',compact("loc","user"));
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
            'Location' => 'required|numeric|gt:0',
            'User' => 'required|numeric|gt:0',
            'designation' => 'required',
            'phoneno' => 'required|digits:10'
        ]);

        $linkU = LinkLocUser::where('userid','=',$request->input('User'))->get();
        //dd(count($linkU));
        if(count($linkU) != 0){
            return redirect('/linkUser')->with('error','User already linked to other Location');
        }

        $lnk = new LinkLocUser();
        $lnk->locationid = $request->input('Location');
        $lnk->userid = $request->input('User');
        $lnk->designation = $request->input('designation');
        $lnk->phoneno1 = $request->input('phoneno');
        $lnk->isactive = $request->input('isactive');

        $lnk->save();

        return redirect('/linkUser')->with('success','User linked to Location');
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
        if(!Auth::user()->hasRole(['Super Admin'])){
            abort(403);
        }
        //dd($id);
        $links = DB::table('linklocusers')
            ->Join('location','linklocusers.locationid','=','location.id')
            ->Join('users','linklocusers.userid','=','users.id')
            ->where('linklocusers.id','=',$id)
            ->select ('location.name as lname',
                'users.name as uname',
                'linklocusers.phoneno1', 
                'linklocusers.designation',
                'linklocusers.isactive',
                'linklocusers.id',
                'linklocusers.locationid',
                'linklocusers.userid',
                )
            ->orderBy('location.name')
            ->get()->first();
        //dd($links);

        $loc = Society::all()->pluck('name','id')->toArray();
        $user = User::all()->pluck('name','id')->toArray();
        $lnk = LinkLocUser::find($id);
        //dd($lnk);
//dd($links);
        return view('linkLocUsers.edit',compact('links','lnk','loc','user'));
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
            'Location' => 'required|numeric|gt:0',
            'User' => 'required|numeric|gt:0',
            'designation' => 'required',
            'phoneno' => 'required|digits:10'
        ]);
        
        
        //dd($id);
        $lnk = LinkLocUser::find($id);
        $lnk->locationid = $request->input('Location');
        $lnk->userid = $request->input('User');
        $lnk->designation = $request->input('designation');
        $lnk->phoneno1 = $request->input('phoneno');
        $lnk->isactive = $request->input('isactive');
        if($lnk->isDirty()){
            $lnk->update();
            return redirect('/linkUser')->with('success','Location User link updated successfully');
        }
        else{
            return redirect('/linkUser')->with('error','Nothing to update');
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
