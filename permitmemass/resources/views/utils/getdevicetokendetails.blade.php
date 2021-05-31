@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($devRecs) > 0)
    <p class="h1">Tokens issues to device: {{$deviceid}}</p>
    <br/>
    <div class="d-flex">
            <div >{{$devRecs->links()}}</div>
            <div class="ml-auto"> <a href="{{ URL::previous() }}" class="btn btn-info">Back</a> </div>
        </div>
        <br/>
        <table class="table table-sm table-bordered table-responsive bg-transparent text-center">
            <tr>
                
                <th>Serial No </th>
                <th>Token</th>
                <th>Saved?</th>
                <th>Token Active</th>
                <th> Generated at</th>
                <th> Last Updated at</th>
              
            </tr>
        @foreach($devRecs as $l)
            <tr class="text-light">
                <td>{{$counter++}} </td>
                <td>{{$l->token}}</td>
                @if ($l->devupdated == true)
                    <td>Yes</td>
                @else
                    <td>No</td>
                @endif
                @if($l->isactive == true)
                    <td>Yes</td>
                @else
                    <td>No</td>
                @endif
                <td>{{$l->created_at}}</td>
                <td>{{$l->updated_at}}</td>
                
            </tr>
        @endforeach
        </table>
        {{$devRecs->links()}}
    @else
        <p class="h1">No Tokens generated for device {{$deviceid}}!</p>        
    @endif
    <br/>
    <p>
        <div class="flex">
            <div class="mx-auto">
                <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>
        </div>
    </p>


@endsection