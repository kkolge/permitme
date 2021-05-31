@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($link) > 0)
        <p class="h1"> List of Users Linked to Locations</p>
        <div class="d-flex">
            <div>{{$link->links()}}</div>
            <div class="ml-auto"><a href="{{ URL::previous() }}" class="btn btn-info">Back</a></div>
        </div>
        <br/>
        <table class="table table-sm table-bordered table-responsive bg-transparent text-center">
            <font size="+1">
                <tr>
                    <th>Serial No </th>
                    <th>Location</th>
                    <th>Name</th>
                    <th>Designation</th>
                    <th>Phone No</th>
                    <th>Status </th>
                    <th>Actions</th>
                </tr>
            </font>
        @foreach($link as $lnk)
            <tr class="text-light">
                <td>{{$counter++}} </td>
                <td>{{$lnk->lname}}</td>
                <td>{{$lnk->uname}}</td>
                <td>{{$lnk->designation}}</td>
                <td>{{$lnk->phoneno1}}</td>
                <td>
                 @if ($lnk->isactive == true)
                    Active
                 @else
                    Disabled    
                 @endif   
                </td>
                <td>
                    <a href="linkUser/{{$lnk->id}}/edit" class="btn btn-info" >Edit</a>
                </td>
            </tr>
        @endforeach
        </table>
        {{$link->links()}}
    @else
        <p class="h1">No Users Linked to Location!</p>        
    @endif
    <br/>
    <p>
        <div class="flex">
            <div class="mx-auto">
                <a href="linkUser/create" class="btn btn-primary">Link User to Location </a>
            </div>
            <div class="mx-auto">
                <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>
        </div>
    </p>

@endsection