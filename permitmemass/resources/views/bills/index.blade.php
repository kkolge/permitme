@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($lur) > 0)
    <p class="h1">Bills </p>
    <br/>
    <div class="d-flex">
            <div >{{$locationsWithBills->links()}}</div>
            <div class="ml-auto"> <a href="{{ URL::previous() }}" class="btn btn-info">Back</a> </div>
        </div>
        <br/>
        <table class="table table-sm table-bordered table-responsive bg-transparent text-center">
            <tr>
                
                <th>Serial No </th>
                <th>Location </th>
                <th>Bill Plan</th>
                <th>Actions</th>
              
            </tr>
        @foreach($locationsWithBills as $l)
            <tr class="text-light">
                <td>{{$counter++}} </td>
                <td> {{$l->lname}} </td>
                <td>{{$l->bname}}</td>
                <td>
                    <a href="bills/{{$l->id}}/show" class="btn btn-info">Edit</a>
                </td>
            </tr>
        @endforeach
        </table>
        {{$lur->links()}}
    @else
        <p class="h1">No Users linked to Role!</p>        
    @endif
    <br/>
    <p>
        <div class="flex">
            <div class="mx-auto">
                <a href="aur/create" class="btn btn-primary">Link User to Role </a>
            </div>
            <div class="mx-auto">
                <a href="aur?type=download" class="btn btn-primary">Download </a>
            </div>
            <div class="mx-auto">
                <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>
        </div>
    </p>


@endsection