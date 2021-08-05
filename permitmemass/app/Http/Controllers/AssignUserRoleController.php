<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\ExportHelpers;

class AssignUserRoleController extends Controller
{
    use ExportHelpers;

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

        if(isset($_GET['type']) && !empty($_GET['type'])){
            if($_GET['type']== 'download'){
                $lur = DB::table('model_has_roles')
                -> join('users','users.id','=','model_has_roles.model_id')
                -> join('roles','roles.id','=','model_has_roles.role_id')
                -> select('users.name as un', 'roles.name as rn')
                ->orderBy('rn')
                ->orderBy('un')
                ->get()->toArray();
                //dd($users);
                $colHeaders = array('Name','Role');
                $listOfFields = array('un','rn');
                $fileName = "UserRoles.csv";
                //dd('sending data to export controller');
                $this->generateCSV($fileName, $colHeaders, $lur, 2, $listOfFields);
            }
        }

        $lur = DB::table('model_has_roles')
            -> join('users','users.id','=','model_has_roles.model_id')
            -> join('roles','roles.id','=','model_has_roles.role_id')
            -> select('users.name as un', 'users.id as ui', 
                    'roles.name as rn', 'roles.id as ri')
            ->orderBy('rn')
            ->orderBy('un')
            ->paginate(10);
        //dd($lur);

        return view('aur.index',compact('lur'));
        
        
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
        //this function is used to assign role to a user 
        $users = User::orderBy('name')->pluck('name','id');
        $roles = Role::orderBy('name')->pluck('name','id');

        return view('aur.create', compact('users','roles'));
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
            'user' => 'required|numeric|gt:0',
            'role' => 'required|numeric|gt:0',
        ]);

    $user = User::find($request->input('user'));
        $role = Role::find($request->input('role'));
        $user->assignRole($role->name);

        return redirect('/aur')->with('success','Role asigned successfully');
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
