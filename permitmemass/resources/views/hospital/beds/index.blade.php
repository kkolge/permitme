@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($beds) > 0)
    <p class="h1">Beds created for your Location</p>
    <br/>
        <table class="table table-striped table-bordered">
            <tr>
                <font size="+1">
                <th>Serial No </th>
                <th>Bed No</th>
                <th>Status </th>
                <th>Actions</th>
                </font>
            </tr>
        @foreach($beds as $bed)
            <tr>
                <td>{{$counter++}} </td>
                <td>{{$bed->bedNo}}</td>
                <td>
                 @if ($bed->isactive == true)
                    Active
                 @else
                    Disabled    
                 @endif   
                </td>
                <td>
                    <a href="/hospital/beds/{{$bed->id}}/edit" class="btn btn-info">Edit</a>
                </td>
            </tr>
        @endforeach
        </table>
        {{$beds->links()}}
    @else
        <p class="h1">No Beds defined for your location yet!</p>        
    @endif
    <p>
        <div class="flex">
            <div class="mx-auto">
        <a href="/hospital/beds/create" class="btn btn-primary">Add Bed </a>
            </div>
            <div class="mx-auto">
        <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>
        </div>
    </p>
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>

@endsection