@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($users) > 0)
    <p class="h1"> List of System Users </p>
        <table class="table table-striped table-bordered">
            <font size="+1">
                <tr>
                    <th>Serial No </th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Created at</th>
                    <th>Actions</th>
                </tr>
            </font>
        @foreach($users as $user)
            <tr>
                <td>{{$counter++}} </td>
                <td>{{$user->name}}</td>
                <td>{{$user->email}}</td>
                <td>{{$user->created_at}}</td>
                <td>
                    <a href='/usr/{{$user->id}}/edit' class='btn btn-info'>Edit </a>
                </td>
            </tr>
        @endforeach
        </table>
        {{$users->links()}}
    @else
        <p class="h1">No Users added!</p>      
    @endif
    
    <p>
        <div class="flex">
            <div class="mx-auto">
                <a href="usr/create" class="btn btn-primary">Add User </a>
            </div>
            <div class="mx-auto">
                <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>

    </p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
@endsection