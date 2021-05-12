@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($perms) > 0)
    <p class="h1"> List of Permissions </p>
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
        @foreach($perms as $perm)
            <tr>
                <td>{{$counter++}} </td>
                <td>{{$perm->name}}</td>
                <td>{{$perm->guard_name}}</td>
                <td>{{$perm->created_at}}</td>
                <td>
                    <a href="perms/{{$perm->id}}/edit", class="btn btn-info">Edit</a>
                    <!--a href="perms/{{$perm->id}}", class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a-->
                </td>
            </tr>
        @endforeach
        </table>
        {{$perms->links()}}
        
    @else
        <p class="h1">No Permissions added!</p>      
    @endif
    
    <p>
        <div class="flex">
            <div class="mx-auto">
                <a href="perms/create" class="btn btn-primary">Add Permission </a>
            </div>
            <div class="mx-auto">
                <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>
            <div>
            <p> &nbsp; </p><p> &nbsp; </p><p> &nbsp; </p>
            </div>
    </p>
    
@endsection