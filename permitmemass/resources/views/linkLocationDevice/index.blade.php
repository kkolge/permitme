@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($link) > 0)
    <p class="h1">List of Devices Linked to Locations</p>
    <br/>
    <div class="d-flex">
        <div>{{$link->links()}}</div>
        <div class="ml-auto"> <a href="{{ URL::previous() }}" class="btn btn-info">Back</a></div>
    </div>
    <br/>
        <table class="table table-sm table-bordered table-responsive bg-transparent text-center">
            <tr>
                
                <th >Serial No </th>
                <th >Location</th>
                <th >Device</th>
                <th> Position </th>
                <th >Status </th>
                <th >Actions</th>
                
            </tr>
        @foreach($link as $lnk)
            <tr class="text-light">
                <td>{{$counter++}} </td>
                <td>{{$lnk->name}}</td>
                <td>{{$lnk->serial_no}}</td>
                <td>{{$lnk->devName}}</td>
                <td>
                 @if ($lnk->isactive == true)
                    Active
                 @else
                    Disabled    
                 @endif   
                </td>
                <td>
                    <a href="linkLoc/{{$lnk->id}}/edit" class="btn btn-info">Edit</a> &nbsp;{{ Form::open(array('url' => 'linkLoc/' . $lnk->id, 'class' => 'pull-right')) }}
                        {{ Form::hidden('_method', 'DELETE') }}
                        {{ Form::submit('Delete', ['class' => 'btn btn-danger']) }}
                    {{ Form::close() }}
                    
                </td>
            </tr>
        @endforeach
        </table>
        {{$link->links()}}
    @else
        <p class="h1">No Devices Linked to Location!</p>  
              
    @endif
    <br/>
    <p>
        <div class="flex">
            <div class="mx-auto">
               <a href="linkLoc/create" class="btn btn-primary">Link Device to Location </a>
            </div>
            <div class="mx-auto">
                <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>
        </div>

    </p>
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>

@endsection