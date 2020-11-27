@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($linkBP) > 0)
    <h1> <font size="+2">Patent Linked to Beds</font> </h1>
    <br/>
        <table class="table table-striped table-bordered">
            <tr>
                <font size="+1">
                <th>Serial No </th>
                <th>Bed No</th>
                <th>Patent Name </th>
                <th>Phone No.</th>
                <th>Aadhar No.</th>
                <th>Admitted Since</th>
                <th>Actions</th>
                </font>
            </tr>
        @foreach($linkBP as $link)
            <tr>
                <td>{{$counter++}} </td>
                <td>{{$link->bedno}}</td>
                <td>{{$link->name}}</td>
                <td>{{$link->phoneno}}</td>
                <td>{{$link->AadharNo}}</td>
                <td>{{$link->created_at}}</td>
                <td>
                    <a href="/hospital/linkUserBed/{{$link->id}}+discharge/edit" class="btn btn-info">Discharge</a>
                    <a href="/hospital/linkUserBed/{{$link->id}}+edit/edit" class="btn btn-info">Edit</a>
                </td>
            </tr>
        @endforeach
        </table>
        <!-- add pagination -->
    @else
        <h1><font size="+2">No patients linked to beds!</font></h1>        
    @endif
    <p>
        <div class="flex">
            <div class="mx-auto">
        <a href="/hospital/linkUserBed/create" class="btn btn-primary">Admit Patient </a>
            </div>
            <div class="mx-auto">
        <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>
        </div>
    </p>
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>

@endsection