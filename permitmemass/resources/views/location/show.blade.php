@extends('layouts.app')

@section('content')
<p class="h1">Details of Location </p>
<br/>
<div class="row d-flex " style="font-size:x-large;">
    <div class="w-1/4 px-md-5 text-right"> <strong>Name: </strong> </div>
    <div class="w-1/4 text-left"> {{$location->name}}</div>
    <div class="w-1/4 px-md-5 text-right"> <strong>Number of Users: </strong> </div>
    <div class="w-1/4 text-left"> {{$location->noofresidents}}</div>
</div>
<div class="row d-flex" style="font-size:x-large">
    <div class="w-1/4 px-md-5 text-right"> <strong>Address Line 1: </strong> </div>
    <div class="w-1/4 text-left"> {{$location->address1}}</div>
    <div class="w-1/4 px-md-5 text-right"> <strong>Address Line 2: </strong> </div>
    <div class="w-1/4 text-left"> {{$location->address2}}</div>
</div>
<div class="row d-flex" style="font-size:x-large">
    <div class="w-1/4 px-md-5 text-right"> <strong>Address Line 1: </strong> </div>
    <div class="w-1/4 text-left"> {{$location->address1}}</div>
    <div class="w-1/4 px-md-5 text-right"> <strong>Address Line 2: </strong> </div>
    <div class="w-1/4 text-left"> {{$location->address2}}</div>
</div>
<div class="row d-flex" style="font-size:x-large">
    <div class="w-1/4 px-md-5 text-right"> <strong>Pincode: </strong> </div>
    <div class="w-1/4 text-left"> {{$location->pincode}}</div>
    <div class="w-1/4 px-md-5 text-right"> <strong>Landmark: </strong> </div>
    <div class="w-1/4 text-left"> {{$location->landmark}}</div>
</div>
<div class="row d-flex" style="font-size:x-large">
    <div class="w-1/4 px-md-5 text-right"> <strong>City: </strong> </div>
    <div class="w-1/4 text-left"> {{$location->city}}</div>
    <div class="w-1/4 px-md-5 text-right"> <strong>Taluka: </strong> </div>
    <div class="w-1/4 text-left"> {{$location->taluka}}</div>
</div>
<div class="row d-flex" style="font-size:x-large">
    <div class="w-1/4 px-md-5 text-right"> <strong>District: </strong> </div>
    <div class="w-1/4 text-left"> {{$location->district}}</div>
    <div class="w-1/4 px-md-5 text-right"> <strong>State: </strong> </div>
    <div class="w-1/4 text-left"> {{$location->state}}</div>
</div>
<div class="row d-flex" style="font-size:x-large">
    <div class="w-1/4 px-md-5 text-right"> <strong>Base Location: </strong> </div>
    <div class="w-1/4 text-left"> 
        @if($location->parent == 0)
            Yes
        @else                 
            {!! $allLocations[$location->parent]; !!}
        @endif
    </div>
    <div class="w-1/4 px-md-5 text-right"> <strong>Map: </strong> </div>
    <div class="w-1/4 text-left btn-link"> 
        <a href=
                "http://maps.google.com/maps?q={{ $location->latitude }},{{ $location->longitude }}&ll={{ $location->latitude }},{{ $location->longitude }}&z={{ $location->altitude }} " target="_blank" >
        {{ $location->latitude }} : {{ $location->longitude }} : {{ $location->altitude }} 
        </a>
    </div>
</div>
<div class="row d-flex" style="font-size:x-large">
    <div class="w-1/4 px-md-5 text-right"> <strong>Send SMS: </strong> </div>
    <div class="w-1/4 text-left">
        @if($location->smsnotification == 1)
            Yes
        @else
            No
        @endif
    </div>
    <div class="w-1/4 px-md-5 text-right"> <strong>Is Active: </strong> </div>
    <div class="w-1/4 text-left">
        @if($location->isactive == 1)
            Yes
        @else
            No
        @endif
    </div>
</div>
<div class="row d-flex" style="font-size:x-large">
    <div class="w-1/4 px-md-5 text-right"> <strong>Created At: </strong> </div>
    <div class="w-1/4 text-left"> {{$location->created_at}}</div>
</div>

<!-- Bill Plan Details -->
<p class="h2">Bill Plan Details</p>
<br/>
<div class="row d-flex " style="font-size:x-large;">
    <div class="w-1/2 px-md-5 text-right"><strong>Name</strong></div>
    <div class="w-1/2 px-md-5 text-right"><strong>Bill Plan Name</strong></div>
</div>
<div class="row d-flex " style="font-size:x-large;">
    <div class="w-1/2 px-md-5 text-right"><strong>Description</strong></div>
    <div class="w-1/2 px-md-5 text-right"><strong>Bill Plan Name</strong></div>
</div>
<div class="row d-flex " style="font-size:x-large;">
    <div class="w-1/4 px-md-5 text-right"><strong>Security Deposite</strong></div>
    <div class="w-1/4 px-md-5 text-right"><strong>Bill Plan Name</strong></div>
    <div class="w-1/4 px-md-5 text-right"><strong>Rent Per Month</strong></div>
    <div class="w-1/4 px-md-5 text-right"><strong>Bill Plan Name</strong></div>
</div>
<div class="row d-flex " style="font-size:x-large;">
    <div class="w-1/4 px-md-5 text-right"><strong>Cost per Transaction</strong></div>
    <div class="w-1/4 px-md-5 text-right"><strong>Bill Plan Name</strong></div>
</div>
<div class="row d-flex " style="font-size:x-large;">
    <div class="w-1/2 px-md-5 text-right"><strong>Bill Plan Name</strong></div>
    <div class="w-1/2 px-md-5 text-right"><strong>Bill Plan Name</strong></div>
    <div class="w-1/4 px-md-5 text-right"><strong>Cost per Transaction</strong></div>
    <div class="w-1/4 px-md-5 text-right"><strong>Bill Plan Name</strong></div>
</div>


 <!--<table class="table  table-bordered table-responsive bg-transparent text-center text-light">
   <tr>
        <td class="col-5">
            <div class="flex">
                <div class="w-1/2 px-md-2"><strong> Name:</strong></div>
                <div class="w-1/2"> {{ $location->name}}</div>
            </div>
        </td>
        <td class="col-5">
            <div class="flex">
                <div class="w-1/2 px-md-2"><strong>Number of Users</strong></div>
                <div class="w-1/2">{{$location->noofresidents}}</div>
            </div>
        </td>
    </tr>
    <tr>
        <td class="col-5">
            <div class="flex">
                <div class="w-1/2 px-md-2"><strong> Address Line 1:</strong> </div>
                <div class="w-1/2"> {{ $location->address1}}</div>
            </div>
        </td>
        <td class="col-5">
            <div class="flex">
                <div class="w-1/2 px-md-2"><strong> Address Line 2:</strong> </div>
                <div class="w-1/2"> {{ $location->address2}}</div>
            </div>
        </td>
    </tr> 
    <tr>
        <td class="col-5">
            <div class="flex">
                <div class="w-1/2 px-md-2"><strong> Pincode: </strong> </div>
                <div class="w-1/2"> {{ $location->pincode}}</div>
            </div>
        </td>
        <td class="col-5">
            <div class="flex">
                <div class="w-1/2 px-md-2"><strong> Landmakr: </strong></div>
                <div class="w-1/2"> {{ $location->landmark}}</div>
            </div>
        </td>
    </tr> 
    <tr>
        <td class="col-5">
            <div class="flex">
                <div class="w-1/2 px-md-2"><strong> City: </strong></div>
                <div class="w-1/2"> {{ $location->city}}</div>
            </div>
        </td>
        <td class="col-5">
            <div class="flex">
                <div class="w-1/2 px-md-2"><strong> Taluka:</strong> </div>
                <div class="w-1/2"> {{ $location->taluka}}</div>
            </div>
        </td>
    </tr> 
    <tr>
        <td class="col-5">
            <div class="flex">
                <div class="w-1/2 px-md-2"><strong> District:</strong> </div>
                <div class="w-1/2"> {{ $location->district}}</div>
            </div>
        </td>
        <td class="col-5">
            <div class="flex">
                <div class="w-1/2 px-md-2"><strong> State: </strong></div>
                <div class="w-1/2"> {{ $location->state}}</div>
            </div>
        </td>
    </tr> 
    <tr>
        <td class="col-5">
            <div class="flex">
                <div class="w-1/2 px-md-2"><strong> Base Location: </strong></div>
                <div class="w-1/2"> 
                    @if($location->parent == 0)
                        Yes
                    @else                 
                        {!! $allLocations[$location->parent]; !!}
                    @endif
                </div>
            </div>
        </td>
        <td class="col-5">
            <div class="flex">
                <div class="w-1/2 px-md-2"><strong> Map:</strong></div>
                <div class="w-1/2 btn-link"> <a href=
                "http://maps.google.com/maps?q={{ $location->latitude }},{{ $location->longitude }}&ll={{ $location->latitude }},{{ $location->longitude }}&z={{ $location->altitude }} " target="_blank" >
                {{ $location->latitude }} : {{ $location->longitude }} : {{ $location->altitude }} </a></div>
            </div>
        </td>
    </tr> 
    <tr>
        <td class="col-5">
            <div class="flex">
                <div class="w-1/2 px-md-2"><strong> Send SMS: </strong></div>
                <div class="w-1/2"> 
                    @if($location->smsnotification == 1)
                        Yes
                    @else
                        No
                    @endif
                </div>
            </div>
        </td>
        <td class="col-5">
            <div class="flex">
                <div class="w-1/2 px-md-2"><strong> Is Active:</strong></div>
                <div class="w-1/2"> 
                    @if ($location->isactive == 1)
                        Active
                    @else
                        Disabled    
                    @endif
                </div>
            </div>
        </td>
    </tr> 
    <tr>
        <td class="col-5">
            <div class="flex">
                <div class="w-1/2 px-md-2"><strong> Created at:</strong></div>
                <div class="w-1/2"> {{ $location->created_at}}</div>
            </div>
        </td>
        <td class="col-5">
            <div class="flex">
                &nbsp;
            </div>
        </td>
    </tr>
</table> -->


<br/><hr/><br/>

<p>
    <div class="flex">
        <div class="mx-auto">
            <a href="{{$location->id}}/edit" class="btn btn-primary">Edit</a>
        </div>
        <div class=" mx-auto">
            <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
        </div>
    </div>
</p>
<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>

@endsection