@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($roles) > 0)
    <h1><font size="+2"> List of Roles </font></h1><br/>
        <table class="table table-striped table-bordered">
            <font size="+1">
                <tr>
                    <th>Serial No </th>
                    <th>Name</th>
                    <th>Guard name</th>
                    <th>Created at</th>
                    <th>Actions</th>
                </tr>
            </font>
        @foreach($roles as $role)
            <tr>
                <td>{{$counter++}} </td>
                <td>{{$role->name}}</td>
                <td>{{$role->guard_name}}</td>
                <td>{{$role->created_at}}</td>
                <td>
                    <a href='/roles/{{$role->id}}/show' class="btn btn-link">Show</a>
                    <a href='/roles/{{$role->id}}/show' class="btn btn-link">Edit</a>
                    
                </td>
            </tr>
        @endforeach
        </table>
        {{$roles->links()}}
    @else
        <h1><font size="+2">No Roles added!</font></h1>  <br/>      
    @endif
    
    <p>
        <div class="flex">
            <div class="mx-auto">
                <a href="roles/create" class="btn btn-primary">Add Role </a>
            </div>
            <div class="mx-auto">
                <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>

    </p>
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
@endsection