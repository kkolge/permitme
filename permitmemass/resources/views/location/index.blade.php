@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($locations) > 0)
    <font size="+2"><h1> List of registered Locations </h1></font><br/>
        <table class="table table-striped table-bordered">
            <font size="+1">
                <tr>
                    <th>Serial No. </th>
                    <th>Name</th>
                    <th>Pin code</th>
                    <th>City</th>
                    <th>Send SMS</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </font>
        @foreach($locations as $loc)
            <tr>
                <td>{{$counter++}} </td>
                <td>{{$loc->name}}</td>
                <td>{{$loc->pincode}} </td>
                <td>{{$loc->city}} </td>
                <td>
                    @if ($loc->smsnotification == true)
                       Yes
                    @else
                       No    
                    @endif   
                   </td>
                <td>
                 @if ($loc->isactive == true)
                    Active
                 @else
                    Disabled    
                 @endif   
                </td>
                <td>
                    <a href="location/{{$loc->id}}" class="btn btn-info">Show</a> 
                    
                    <a href="location/{{$loc->id}}/edit" class="btn btn-info">Edit</a>
                </td>
            </tr>
        @endforeach
        </table>
        {{$locations->links()}}
    @else
       <h1> No Locations registered yet!</h1>        
    @endif
    <p>
        <div class="flex">
            <div class="mx-auto">
        <a href="location/create" class="btn btn-primary">Add Location </a>
            </div>
            <div class="mx-auto">
        <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>
        </div>
    </p>
    <p> &nbsp; </p> <p> &nbsp; </p><p> &nbsp; </p>

@endsection