@extends('layouts.app')
<script>
$(document).ready(function(){
  $("#tableSearch").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#myTable tr").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});
</script>
@section('content')
<?php use Illuminate\Pagination\LengthAwarePaginator; ?>
<?php $counter = 1; ?>
    
    @if (count($link) > 0)
    <p class="h1">List of Devices Linked to Locations</p>
    <br/>
        <table class="table table-striped table-bordered table-sm">
            <tr>
                <font size="+1">
                <th >Serial No </th>
                <th >Location</th>
                <th >Device</th>
                <th> Position </th>
                <th >Status </th>
                <th >Actions</th>
                </font>
            </tr>
        @foreach($link as $lnk)
            <tr>
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