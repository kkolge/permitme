@extends('layouts.app')

@section('content')
<p class="h1">User Details and Data </p>
<div class="flex">
        <div class="w-1/2">
            <table class="table table-striped table-bordered">
                <tr>
                    <td>Name</td><td>{{ $stf->name }}</td>
                </tr>
                <tr>
                    <td>Phone No.</td><td>{{$stf->phoneno }}</td>
                </tr>
                <tr>
                    <td>Tag ID</td><td>{{$stf->tagid}} </td>
                </tr>
                <tr>
                    <td>Aadhar No</td><td>{{$stf->AadharNo}} </td>
                </tr>
                <tr>
                    <td>Residential Area</td><td>{{$stf->resiarea}}</td>
                </tr>
                <tr>
                    <td>Residential Landmark</td> <td>{{$stf->resilandmark}}</td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="flex">
                            <div class="w-1/3 mx-auto">Vaccinated</div>
                            <div class="w-1/3 mx-auto">First Vaccin</div>
                            <div class="w-1/3 mx-auto">Second Vaccin</div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="flex">
                            @if($stf->vaccinated == true)
                                <div class="w-1/3 mx-auto">Yes</div>
                                <div class="w-1/3 mx-auto">{{ (new \Carbon\Carbon($stf->firstvaccin))->rawFormat('d-M-Y') }}</div>
                                <div class="w-1/3 mx-auto">{{ (new \Carbon\Carbon($stf->secondvaccin)) -> rawFormat('d-M-Y')}}</div>
                            @else
                                <div class="w-1/3 mx-auto">No</div>
                                <div class="w-1/3 mx-auto">Not Available</div>
                                <div class="w-1/3 mx-auto">Not Available</div>
                            @endif
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>Status</td><td>
                        @if ($stf->isactive == 1)
                            Active
                        @else
                            Disabled    
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        <div class="w-1/2" align="center">
            <img style="width:50%" src="/storage/coverimages/{{$stf->coverimage}}">
        </div>
</div>
<br/><hr/><br/>
<div class="flex">
    <div class="w-1/2">
        {{ $chart1->container() }}
        {{ $chart1->script() }}
    </div>
    <div class="w-1/2">
        {{ $chart2->container() }}
        {{ $chart2->script() }}
    </div>
</div>
<br/>
<p class="small">
                Normal Range: &nbsp; &nbsp; &nbsp; &nbsp;
                Pulse Rate < {{env('CUTOFF_PULSE')}} per min &nbsp; &nbsp; &nbsp; &nbsp;
                SPO2 > {{env('CUTOFF_SPO2')}}% &nbsp; &nbsp; &nbsp; &nbsp;
                Temperature < {{env('CUTOFF_TEMP')}} &#8457; (Wrist temperature is 3.3&#8451; / 5.9&#8457; lower than core body temperature)
            </p> <br/><br/>
<p>
    <div class="flex">
        <div class="mx-auto">
            <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
        </div>
    </div>
</p>
<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>

@endsection