<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use DB;
use Illuminate\Support\Facades\Auth;

class PermissionsController extends Controller
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

        $perms = Permission::paginate(10);
        return view('perms.index',compact('perms'));
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

        return view ('perms.create');
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


        $perm = new Permission();
        $perm->name = $request->input('name');
        if($request->input('guard') != ""){
            $perm->guard = $request->input('guard');
        }
        $perm->save();
        return redirect('/perms')->with('success', 'Permission saves successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // nothing required here
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

        $perm = Permission::find($id)->first();
        return view ('perms.edit',compact('perm'));
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

        $perm = Permission::find($id);
        /*$checkName = Permission::where('name','=',$request->input('name'))
            ->select(DB::raw('count(*) as cnt'))
            ->get()
            ->first();
        if ($checkName->cnt > 0){
            if($checkName->id != $id){
                return view ('perms.edit', compact('perm'))->with('error', 'Role Name already exists');
            }
        }*/

        $perm->name = $request->input('name');
        $perm->guard_name = $request->input('guard');
        $perm->save();
        return redirect('/perms')->with('success', 'Permission edited successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(!Auth::user()->hasRole(['Super Admin'])){
            abort(403);
        }
        
        $perm = Permission::find($id);
        $perm->delete();

        return redirect('/perms')->with('success', 'Permission deleted successfully');
    }
}
