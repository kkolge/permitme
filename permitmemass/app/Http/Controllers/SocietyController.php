<?php

namespace App\Http\Controllers;

use App\LinkLocUser;
use Illuminate\Http\Request;
use App\Society;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\ExportHelpers;

class SocietyController extends Controller
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
        if(isset($_GET['type']) && !empty($_GET['type'])){
            if($_GET['type']== 'download'){
                if(Auth::user()->hasRole(['Super Admin'])){
                    $locationsDownload = DB::select("select l.name, l.noofresidents, concat(l.address1, ' ', l.address2) as address, l.pincode, l.city, l.taluka, l.district, l.state, ifnull(m.name,'Parent Location') as parent 
                    from location l 
                    left outer join location m 
                        on m.id = l.parent
                    order BY 
                        parent asc, l.state ASC, l.district asc, l.taluka asc, l.city asc, l.pincode asc, l.name asc");

                
                }
                /*else if(Auth::user()->hasRole('Location Admin')){
                    $locationsDownload = DB::select("select l.name, l.noofresidents, concat(l.address1, ' ', l.address2) as address, l.pincode, l.city, l.taluka, l.district, l.state, ifnull(m.name,'Parent Location') as parent 
                    from location l 
                    left outer join location m 
                        on m.id = l.parent
                    where id = ? or parent = ? 
                    order BY 
                        parent asc, l.state ASC, l.district asc, l.taluka asc, l.city asc, l.pincode asc, l.name asc", session('GlocationId'), session('GlocationId'));
                }*/
                //dd($locationsDownload);
                $colHeaders = array('Name','No Of Users', 'Address 1', 'Pin Code', 'City', 'Taluka', 'District', 'State', 'Parent Location');
                $listOfFields = array('name','noofresidents','address', 'pincode', 'city', 'taluka', 'district', 'state' ,'parent');
                $fileName = "Locations.csv";
                //dd('sending data to export controller');
                $this->generateCSV($fileName, $colHeaders, $locationsDownload, 9, $listOfFields);
            }
        }

        if(Auth::user()->hasRole(['Super Admin'])){
            $locations = Society::orderBy('state')
            ->orderBy('landmark')
            ->orderBy('city')
            ->orderBy('pincode')
            ->orderBy('name')
            ->paginate(20);
        }
        else if(Auth::user()->hasRole('Location Admin')){
           
            $locations = Society::where('id',session('GlocationId'))->orWhere('parent',session('GlocationId'))
            ->orderBy('state')
            ->orderBy('landmark')
            ->orderBy('city')
            ->orderBy('pincode')
            ->orderBy('name')
            ->paginate(20);

            //dd($locations);
        }else if(Auth::user()->hasRole('Site Admin')){
            $locations = Society::where('id','=', session('GlocationId'))          
            ->orderBy('state')
            ->orderBy('landmark')
            ->orderBy('city')
            ->orderBy('pincode')
            ->orderBy('name')
            ->paginate(20);
            
        }

        return view('location.index', compact("locations"));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //Only Super Admin can do this
        if(!Auth::user()->hasRole(['Super Admin'])){
            abort(403);
        }

        $locations = Society::where('isactive','=',true)
            ->select (DB::raw("id,CONCAT(name,':',pincode,':',city,':',taluka,':',district,':',state) AS name"))
            ->orderBy('name','asc')
            ->pluck('name','id')
            ->toArray();
        $locations[0] = 'Parent Location';
        //dd($locations);
        /*$locations = Society::where('parent','=',0)
        ->orderBy('state')
        ->orderBy('city')
        ->orderBy('pincode')
        ->orderBy('name')
        ->pluck('name');
        */
        return view('location.create', compact("locations"));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Only Super Admin can do this
        if(!Auth::user()->hasRole(['Super Admin'])){
            abort(403);
        }

        //dd($request);
        $this -> validate($request, [
            'name' => 'required',
            'noofresidents' => 'required',
            'address1'=>'required',
            'pincode' => 'required',
            'city' => 'required',
            'state' => 'required',
            'location' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'altitude' => 'required',
        ]);

        $loc = new Society();
        $loc->name = $request->input('name');
        $loc->noofresidents = $request->input('noofresidents');
        $loc->address1 = $request->input('address1');
        if($request->input('address2') != ""){
            $loc->address2 = $request->input('address2');
        }
        $loc->pincode = $request->input('pincode');
        $loc->city = $request->input('city');
        if($request->input('taluka') != ""){
            $loc->taluka = $request->input('taluka');
        }
        if($request->input('district') != ""){
            $loc->district = $request->input('district');
        }
        $loc->state = $request->input('state');
        $loc->isactive = $request->input('isactive');
        $loc->smsnotification = $request->input('sms');
        $loc->parent = $request->input('location');
        $loc->latitude = $request->input('latitude');
        $loc->longitude = $request->input('longitude');
        $loc->altitude = $request->input('altitude');
        $loc->save();

        return redirect('/location')->with('success', 'Location added successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //Only Super Admin can do this
        if(!Auth::user()->hasRole(['Super Admin'])){
            abort(403);
        }
        //dd('in controller');
        $location = Society::find($id);
        $allLocations = Society::where('isactive','=',true)
            ->select (DB::raw("id,CONCAT(name,':',pincode,':',city,':',taluka,':',district,':',state) AS name"))
            ->orderBy('name','asc')
            ->pluck('name','id')
            ->toArray();
        $allLocations[0] = 'Parent Location';
        return view('location.show', compact('location','allLocations'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //Only Super Admin can do this
        if(!Auth::user()->hasRole(['Super Admin'])){
            abort(403);
        }
        $location = Society::find($id);
        $allLocations = Society::where('isactive','=',true)
            ->select (DB::raw("id,CONCAT(name,':',pincode,':',city,':',taluka,':',district,':',state) AS name"))
            ->orderBy('name','asc')
            ->pluck('name','id')
            ->toArray();
        $allLocations[0] = 'Parent Location';
        return view('location.edit', compact('location','allLocations'));
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
        //Only Super Admin can do this
        if(!Auth::user()->hasRole(['Super Admin'])){
            abort(403);
        }
        $this -> validate($request, [
            'name' => 'required',
            'noofresidents' => 'required',
            'address1'=>'required',
            'pincode' => 'required',
            'city' => 'required',
            'state' => 'required',
            'location' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'altitude' => 'required',
        ]);

        $loc = Society::find($id);
        $loc->name = $request->input('name');
        $loc->noofresidents = $request->input('noofresidents');
        $loc->address1 = $request->input('address1');
        if($request->input('address2') != ""){
            $loc->address2 = $request->input('address2');
        }
        $loc->pincode = $request->input('pincode');
        $loc->city = $request->input('city');
        if($request->input('taluka') != ""){
            $loc->taluka = $request->input('taluka');
        }
        if($request->input('district') != ""){
            $loc->district = $request->input('district');
        }
        $loc->state = $request->input('state');
        $loc->isactive = $request->input('isactive');
        $loc->smsnotification = $request->input('sms');
        $loc->parent = $request->input('location');
        $loc->latitude = $request->input('latitude');
        $loc->longitude = $request->input('longitude');
        $loc->altitude = $request->input('altitude');

        if($loc->isDirty()){
            $loc->update();
            return redirect('/location')->with('success', 'Location updated successfully');
        }
        else{
            return redirect('/location')->with('error', 'Data not updated. Same as before');
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
