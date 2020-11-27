@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($regUser) > 0)
    <h1><font size="+2"> List of registered users for your Location </font></h1><br/>
        <table class="table table-striped table-bordered">
            <font size="+1">
                <tr>
                    <th>Serial No </th>
                    <th>Name</th>
                    <th>Phone No.</th>
                    <th>Status </th>
                    <th>Actions</th>
                </tr>
            </font>
        @foreach($regUser as $user)
            <tr>
                <td>{{$counter++}} </td>
                <td>{{$user->name}}</td>
                <td>{{$user->phoneno}}</td>
                <td>
                 @if ($user->isactive == true)
                    Active
                 @else
                    Disabled    
                 @endif   
                </td>
                <td>
                    <a href="reguser/{{$user->id}}", class="btn btn-info">Details</a>
                    <a href="reguser/{{$user->id}}/edit", class="btn btn-info">Edit</a>
                </td>
            </tr>
        @endforeach
        </table>
        {{$regUser->links()}}
    @else
        <h1><font size="+2">No Users registered yet!</font></h1>        
    @endif
    
    <p>
        <div class="flex">
            <div class="mx-auto">
                <a href="reguser/create" class="btn btn-primary">Add User </a>
            </div>
            <div class="mx-auto">
                <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>

    </p>
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
@endsection