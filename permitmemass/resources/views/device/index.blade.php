@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($dev) > 0)
        <p class="h1">List of registered devices</p>
        <br/>
        <div class="d-flex">
            <div>{{$dev->links()}}</div>
            <div class="ml-auto"><a href="{{ URL::previous() }}" class="btn btn-info">Back</a></div>
        </div>
        <br/>
        <table class="table table-sm table-bordered table-responsive bg-transparent text-center">
            <tr>
               
                <th>Serial No </th>
                <th>Device ID</th>
                <th>Device Type </th>
                <th>Status </th>
                @if(Auth::user()->hasRole(['Super Admin']))
                <th>Actions</th>
                @endif
               
            </tr>
        @foreach($dev as $device)
            <tr class="text-light">
                <td>{{$counter++}} </td>
                <td>{{$device->serial_no}}</td>
                <td>{{$device->devtype}}</td>
                <td>
                 @if ($device->isactive == true)
                    Active
                 @else
                    Disabled    
                 @endif   
                </td>
                @if(Auth::user()->hasRole(['Super Admin']))
                <td>
                    <a href="device/{{$device->id}}/edit" class="btn btn-info">Edit</a>
                </td>
                @endif
            </tr>
        @endforeach
        </table>
        {{$dev->links()}}
    @else
        <p class="h1">No Devices registered yet!</p>        
    @endif
    <br/>
    <p>
        <div class="flex">
            @if(Auth::user()->hasRole(['Super Admin']))
            <div class="mx-auto">
                <a href="device/create" class="btn btn-primary">Add Device </a>
            </div>
            @endif
            <div class="mx-auto">
        <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>
        </div>
    </p>
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>

@endsection