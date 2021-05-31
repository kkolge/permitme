@extends('layouts.app')

@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($locations) > 0)
    <p class="h1">List of registered Locations </p>
        <div class="d-flex">
            <div >{{$locations->links()}}</div>
            <div class="ml-auto"><a href="{{ URL::previous() }}" class="btn btn-info">Back</a></div>
        </div>
        <br/>
        <table class="table table-sm table-bordered table-responsive bg-transparent text-center">
          
                <tr>
                    <th>Serial No. </th>
                    <th>Name</th>
                    <th>Pin code</th>
                    <th>Landmark</th>
                    <th>City</th>
                    <th>Base Location</th>
                    <th>Send SMS</th>
                    <th>Status</th>
                    @if(Auth::user()->hasRole(['Super Admin']))
                    <th>Action</th>
                    @endif
                </tr>
          
        @foreach($locations as $loc)
            <tr class="text-light">
                <td>{{$counter++}} </td>
                <td>{{$loc->name}}</td>
                <td>{{$loc->pincode}} </td>
                <td>{{$loc->landmark}}</td>
                <td>{{$loc->city}} </td>
                <td>
                    @if($loc->parent == 0)
                        Yes
                    @else                        
                        {!! ($loc->where('id','=',$loc->parent)->pluck('name'))[0]; !!}
                    @endif
                </td>
                <td>
                    @if ($loc->smsnotification == true)
                       Yes
                    @else
                       No    
                    @endif   
                   </td>
                <td>
                 @if ($loc->isactive == true)
                    Active
                 @else
                    Disabled    
                 @endif   
                </td>
                @if(Auth::user()->hasRole(['Super Admin']))
                <td>
                    <a href="location/{{$loc->id}}" class="btn btn-info">Show</a> 
                    
                    <a href="location/{{$loc->id}}/edit" class="btn btn-info">Edit</a>
                </td>
                @endif
            </tr>
        @endforeach
        </table>
        {{$locations->links()}}
    @else
       <p class="h1"> No Locations registered yet!</p>        
    @endif
    <br/>
    <p>
        <div class="flex">
            @if(Auth::user()->hasRole(['Super Admin']))
                <div class="mx-auto">
                    <a href="location/create" class="btn btn-primary">Add Location </a>
                </div>
            @endif
            <div class="mx-auto">
        <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>
        </div>
    </p>
    <p> &nbsp; </p> <p> &nbsp; </p><p> &nbsp; </p>

@endsection