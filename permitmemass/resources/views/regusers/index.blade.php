@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($regUser) > 0)
    <p class="h1"> List of registered users for your Location </p>
        <table class="table table-striped table-bordered">
            <font size="+1">
                <tr>
                    <th>Serial No </th>
                    <th>Name</th>
                    <th>Phone No.</th>
                    <th>Aadhar Number</th>
                    <th>Residential Area</th>
                    <th>Residential Landmark</th>
                    <th>Vaccinated</th>
                    <th>Location</th>
                    <th>Status </th>
                    @if(Auth::user()->hasRole(['Super Admin']))
                        <th>Actions</th>
                    @endif
                </tr>
            </font>
        @foreach($regUser as $user)
            <tr>
                <td>{{$counter++}} </td>
                <td>{{$user->name}}</td>
                <td>{{$user->phoneno}}</td>
                <td>{{$user->AadharNo}}</td>
                <td>{{$user->resiarea}}</td>
                <td>{{$user->resilandmark}}</td>
                <td>
                    @if($user->vaccinated == true)
                        Yes
                    @else
                        No
                    @endif
                </td>
                <td>{{$user->lname}}</td>
                <td>
                 @if ($user->isactive == true)
                    Active
                 @else
                    Disabled    
                 @endif   
                </td>
                @if(Auth::user()->hasRole(['Super Admin']))
                    <td>
                        <a href="reguser/{{$user->id}}", class="btn btn-info">Details</a>
                        <a href="reguser/{{$user->id}}/edit", class="btn btn-info">Edit</a>
                    </td>
                @endif
            </tr>
        @endforeach
        </table>
        {{$regUser->links()}}
    @else
        <p class="h1">No Users registered yet!</p>        
    @endif
    
    <p>
        <div class="flex">
            @if(Auth::user()->hasRole(['Super Admin']))
                <div class="mx-auto">
                    <a href="reguser/create" class="btn btn-primary">Add User </a>
                </div>
            @endif
            <div class="mx-auto">
                <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>
            <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
    </p>
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
@endsection