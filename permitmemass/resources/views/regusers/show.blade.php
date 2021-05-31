@extends('layouts.app')

@section('content')
<p class="h1">User Details and Data </p>
<br/>
<div class="row d-flex text-light" style="font-size:x-large">
    <div class="w-1/2 ">
        <div class="row d-flex border">
            <div class="w-1/2">Name:</div>
            <div class="w-1/2 px-md-5">{{$stf->name}} </div>
        </div>
        <div class="row d-flex border">
            <div class="w-1/2">Phone No.:</div>
            <div class="w-1/2 px-md-5">{{$stf->phoneno}} </div>
        </div>
        <div class="row d-flex border">
            <div class="w-1/2">Tag ID:</div>
            <div class="w-1/2 px-md-5">{{$stf->tagid}} </div>
        </div>
        <div class="row d-flex border">
            <div class="w-1/2">Aadhar No.:</div>
            <div class="w-1/2 px-md-5">{{$stf->AadharNo}} </div>
        </div>
        <div class="row d-flex border">
            <div class="w-1/2">Residential Area:</div>
            <div class="w-1/2 px-md-5">{{$stf->resiarea}} </div>
        </div>
        <div class="row d-flex border">
            <div class="w-1/2">Residential Landmark:</div>
            <div class="w-1/2 px-md-5">{{$stf->resilandmark}} </div>
        </div>
        <div class="row d-flex border">
            <div class="w-1/2">IS Active:</div>
            <div class="w-1/2 px-md-5">
                @if ($stf->isactive == 1)
                    Active
                @else
                    Disabled    
                @endif 
            </div>
        </div>
        <div class="row d-flex border">
            <div class="w-1/3">Vaccinated:</div>
            <div class="w-1/3 px-md-5">First Vaccine: </div>
            <div class="w-1/3 px-md-5">Second Vaccine:</div>
        </div>
        <div class="row d-flex border">
            @if($stf->vaccinated == true)
                <div class="w-1/3">Yes</div>
                <div class="w-1/3 px-md-5">{{ (new \Carbon\Carbon($stf->firstvaccin))->rawFormat('d-M-Y') }}</div>
                <div class="w-1/3 px-md-5">{{ (new \Carbon\Carbon($stf->secondvaccin)) -> rawFormat('d-M-Y')}}</div>
            @else
                <div class="w-1/3">No</div>
                <div class="w-1/3 px-md-5">Not Available </div>
                <div class="w-1/3 px-md-5">Not Available</div>
            @endif
        </div>
       
    </div>
    <div class="w=1/2 mx-auto"> <img class="img-fluid"  src="/storage/coverimages/{{$stf->coverimage}}"> </div>
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
@include('inc.parameters')
    <div class="flex">
        <div class="mx-auto">
            <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
        </div>
    </div>
</p>
<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>

@endsection