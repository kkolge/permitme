@extends('layouts.app')

@section('content')
<h1><font size="+2">Details of Location </font></h1><br/>
<table class="table table-striped table-bordered">
    <tr>
        <td>
            <div class="flex">
                <div class="w-1/2 px-md-2"><strong> Name:</strong></div>
                <div class="w-1/2"> {{ $location->name}}</div>
            </div>
        </td>
        <td>
            <div class="flex">
                <div class="w-1/2 px-md-2"><strong>Number of Users/Beds</strong></div>
                <div class="w-1/2">{{$location->noofresidents}}</div>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="flex">
                <div class="w-1/2 px-md-2"><strong> Address Line 1:</strong> </div>
                <div class="w-1/2"> {{ $location->address1}}</div>
            </div>
        </td>
        <td>
            <div class="flex">
                <div class="w-1/2 px-md-2"><strong> Address Line 2:</strong> </div>
                <div class="w-1/2"> {{ $location->address2}}</div>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="flex">
                <div class="w-1/2 px-md-2"><strong> Pincode: </strong> </div>
                <div class="w-1/2"> {{ $location->pincode}}</div>
            </div>
        </td>
        <td>
            <div class="flex">
                <div class="w-1/2 px-md-2"><strong> City: </strong></div>
                <div class="w-1/2"> {{ $location->city}}</div>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="flex">
                <div class="w-1/2 px-md-2"><strong> Taluka:</strong> </div>
                <div class="w-1/2"> {{ $location->taluka}}</div>
            </div>
        </td>
        <td>
            <div class="flex">
                <div class="w-1/2 px-md-2"><strong> District:</strong> </div>
                <div class="w-1/2"> {{ $location->district}}</div>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="flex">
                <div class="w-1/2 px-md-2"><strong> State: </strong></div>
                <div class="w-1/2"> {{ $location->state}}</div>
            </div>
        </td>
        <td>
            <div class="flex">
                <div class="w-1/2 px-md-2"><strong> Created at:</strong></div>
                <div class="w-1/2"> {{ $location->created_at}}</div>
            </div>
        </td>
    </tr>
    <tr>
        <td>
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
        <td>
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
</table>


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