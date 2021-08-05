@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($locationplanlinks) > 0)
        <p class="h1">Bill Plans Linked to Location</p>
        <br/>
        <div class="d-flex">
            <div>{{$locationplanlinks->links()}}</div>
            <div class="ml-auto"><a href="{{ URL::previous() }}" class="btn btn-info">Back</a></div>
        </div>
        <br/>
        <table class="table table-sm table-bordered table-responsive bg-transparent text-center" style="height:100%">
            <tr>
               
                <th>Serial No </th>
                <th>Location</th>
                <th>Plan</th>
                @if(Auth::user()->hasRole(['Super Admin']))
                <th>Start Date</th>
                <th>End Date</th>
                <th>Actions</th>
                @endif
               
            </tr>
        @foreach($locationplanlinks as $plan)
            <tr class="text-light">
                <td>{{$counter++}} </td>
                <td>{{$plan->lname}}</td>
                <td>{{$plan->pname}}</td>
                <td>{{$plan->planstartdate}}</td>
                <td>{{$plan->planenddate}}</td>
                
                @if(Auth::user()->hasRole(['Super Admin']))
                <td>
                    <a href="linklocationbillplan/{{$plan->id}}/edit" class="btn btn-info">Edit</a>
                </td>
                @endif
            </tr>
        @endforeach
        </table>
        {{$locationplanlinks->links()}}
    @else
        <p class="h1">No Locations Linked to Bill Plans yet!</p>        
    @endif
    <br/>
    <p>
        <div class="flex">
            @if(Auth::user()->hasRole(['Super Admin']))
            <div class="mx-auto">
                <a href="linklocationbillplan/create" class="btn btn-primary">Add Plan </a>
            </div>
            @endif
            <div class="mx-auto">
        <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>
        </div>
        <br/><br/>
    
    </p>
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>

@endsection