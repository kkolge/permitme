<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use DB;
use Illuminate\Support\Facades\Auth;

class RolesController extends Controller
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

        $roles = Role::paginate(10);

        return view('roles.index',compact('roles'));
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

        $perms = Permission::all();
        //dd($perms);
        return view ('roles.create', compact('perms')); 
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

        $role = new Role();
        $role->name = $request->input('name');
        if($request->input('guard') != ""){
            $role->guard_name = $request->input('guard');
        }
        else{
            $role->guard_name = 'web';
        }
        $role->save();
        /*
        //getting the new role id 
        $roleid = $role->id;

        //now processing the permissions 
        $perms = $request->input('permsSel');
        //dd($perms);
        if(count($perms) == 0){
            //nothing to be done
        }
        else if (count($perms) == 1){
            $role->givePermissionTo($perms[0]);
        }
        else {
            $role->syncPermissions($perms);
        }
        */

        return redirect('/roles')->with('success', 'Role created successfully');
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

        $role = Role::where('id','=',$id);
        //$allPerms = Permission::all();
        //$perms = $role->getAllPermissions();
        //$permMap = collect([]);

        //TO-DO
        /*foreach($allPerms as $p){
            if(count($perms->find($p->id)->get()) >0){
                $tmp = ['id' => $p->id, 'name' => $p->name, 'stat' =>'Assigned']
                $permMap->push($tmp);
            }
        }*/

        return view('roles.show', compact('role'));
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

        $role = Role::find($id);
        //dd($role);
        //getting all permissions 
       
        return view('roles.edit',compact('role'));
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

        if(!Auth::user()->hasRole(['Super Admin'])){
            abort(403);
        }
        
        $role = Role::find($id);
        $role->delete();

        $roles = Role::paginate(10);

        return view('roles.index',compact('roles'));
    }

    
}
