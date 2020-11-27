<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Bed;

class BedsController extends Controller
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
        $beds = Bed::where('locationId','=',session("GlocationId"))->paginate(50);
        //dd($beds);
        return view('hospital.beds.index',compact('beds'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $locName = session('GlocationName');
        return view('hospital.beds.create',compact('locName'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Validate - bed no not empty
        $this -> validate($request, [
            'bedno' => 'required|min:3|max:10',
        ]);
        //dd(session('GlocationId'));
        $bed = new Bed();
        $bed->bedno = $request->input('bedno');
        $bed->locationId = session('GlocationId');
        $bed->isactive = $request->input('isactive');
        $bed->save();

        return redirect('/hospital/beds')->with("Success: Bed added successfullt!");

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
        $bed = Bed::find($id);
        //dd($bed);
        return view('hospital.beds.edit', compact('bed'));
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
            'bedno' => 'required|min:3|max:10',
        ]);
        
        $bed = Bed::find($id);
        $bed->bedNo = $request->input('bedno');
        $bed->isactive = $request->input('isactive');

        $bed->save();

        return redirect('/hospital/beds') ->with ('success', 'Bed details updated successfully.');
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
