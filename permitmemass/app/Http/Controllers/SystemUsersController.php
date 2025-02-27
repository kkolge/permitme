<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Expr\Cast;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\ExportHelpers;

class SystemUsersController extends Controller
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
        //dd($_GET['type']);
        if(isset($_GET['type']) && !empty($_GET['type'])){
            if($_GET['type']== 'download'){
                $users = User::select('name','email',DB::raw('DATE_FORMAT(created_at,"%d-%b-%Y") as created_at'))
                ->get();
                //dd($users);
                $colHeaders = array('Name','Email ID', 'Added On');
                $listOfFields = array('name','email','created_at');
                $fileName = "SystemUsers.csv";
                //dd('sending data to export controller');
                $this->generateCSV($fileName, $colHeaders, $users, 3, $listOfFields);
            }
        }

        $users = User::orderBy('name')->paginate(10);
        //dd($users);
        return view('usr.index', compact('users'));
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
        return view('usr.create');
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
            'name' => 'required|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => ['required','min:6',
            'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/'],
            'cpassword' => 'required|same:password'
        ]);

        $user = new User();
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = bcrypt($request->input('password'));
        $user->save();

        return redirect('/usr')->with('success', 'User created successfully');
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


        //only thing that can be done it to reset the password
        $user = User::find($id);
        return view('usr.edit',compact('user'));
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
            'password' => ['required','min:6',
            'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/'],
            'cpassword' => 'required|same:password'
        ]);

        $user = User::find($id);
        $user->password = bcrypt($request->input('password'));
        $user->update();

        return redirect('/usr')->with('success', 'User updated successfully');
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

    /**
     * Function to download all system users
     */
    public function downloadAll(){
        
    }
}
