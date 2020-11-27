@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($dev) > 0)
    <h1> <font size="+2">List of registered devices</font> </h1>
    <br/>
        <table class="table table-striped table-bordered">
            <tr>
                <font size="+1">
                <th>Serial No </th>
                <th>Device ID</th>
                <th>Status </th>
                <th>Actions</th>
                </font>
            </tr>
        @foreach($dev as $device)
            <tr>
                <td>{{$counter++}} </td>
                <td>{{$device->serial_no}}</td>
                <td>
                 @if ($device->isactive == true)
                    Active
                 @else
                    Disabled    
                 @endif   
                </td>
                <td>
                    <a href="device/{{$device->id}}/edit" class="btn btn-info">Edit</a>
                </td>
            </tr>
        @endforeach
        </table>
        {{$dev->links()}}
    @else
        <h1><font size="+2">No Devices registered yet!</font></h1>        
    @endif
    <p>
        <div class="flex">
            <div class="mx-auto">
        <a href="device/create" class="btn btn-primary">Add Device </a>
            </div>
            <div class="mx-auto">
        <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>
        </div>
    </p>
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>

@endsection