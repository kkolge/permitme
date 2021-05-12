@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($lur) > 0)
    <p class="h1">Users Linked to Roles</p>
    <br/>
        <table class="table table-striped table-bordered">
            <tr>
                <font size="+1">
                <th>Serial No </th>
                <th>Role </th>
                <th>User Name</th>
                <th>Actions</th>
                </font>
            </tr>
        @foreach($lur as $l)
            <tr>
                <td>{{$counter++}} </td>
                <td> {{$l->rn}} </td>
                <td>{{$l->un}}</td>
                <td>
                    <a href="aur/{{$l->ui.",".$l->ri}}/edit" class="btn btn-info">Edit</a>
                </td>
            </tr>
        @endforeach
        </table>
        {{$lur->links()}}
    @else
        <p class="h1">No Users linked to Role!</p>        
    @endif
    <p>
        <div class="flex">
            <div class="mx-auto">
        <a href="aur/create" class="btn btn-primary">Link User to Role </a>
            </div>
            <div class="mx-auto">
        <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>
        </div>
    </p>
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>

@endsection