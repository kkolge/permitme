<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use DB;

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
        $role = new Role();
        $role->name = $request->input('name');
        if($request->input('guard') != ""){
            $role->guard_name = $request->input('guard');
        }
        $role->save();
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
        $role = Role::where('id','=',$id);
        $allPerms = Permission::all();
        $perms = $role->getAllPermissions();
        $permMap = collect([]);

        //TO-DO
        /*foreach($allPerms as $p){
            if(count($perms->find($p->id)->get()) >0){
                $tmp = ['id' => $p->id, 'name' => $p->name, 'stat' =>'Assigned']
                $permMap->push($tmp);
            }
        }*/

        return view('roles.show', compact('role','perms','allPerms'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $role = Role::find($id);
        //getting all permissions 
        $allPerms = Permission::all();
        //getting permissions for the role
        $perms = $role->getAllPermissions();
        //dd($perms);

        //Testing the looping
         
        /*foreach($allPerms as $ap){
            foreach ($perms as $p){
                if($ap->id == $p->id){
                    dd("match fount");
                }
            }
        }*/
        return view('roles.edit',compact('role','perms','allPerms'));
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
