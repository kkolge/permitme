@extends('layouts.app')

@section('content')
<?php $count = 1;?>
    <h1><font size="+2">Show Role Details</font></h1><br/>
    <div class="flex">
        <strong>Role Name: </strong>{{$role->name}} 
    </div>
    <div class='flex'>
        <table class='tabel'>
            <th> Serial No. </th>
            <th> Permission Name </th>
            <th>
        </table>
    </div>
    {!! Form::open(['action' => 'RolesController@store', 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
        <div class="form-group">
            <div class="px-md-5">
                {{Form::label('name', 'Name')}}
                {{Form::text('name', '', ['class' => 'form-control', 'placeholder' => 'Name'])}}
            </div><br/>
            <div class="px-md-5">
                {{Form::label('guard', 'Guard Name (optional)')}}
                {{Form::text('guard', '', ['class' => 'form-control', 'placeholder' => 'Guard Name'])}}
            </div><br/>
        </div>
        <!-- adding list of permissions-->
        @if(count($perms) > 0)
            <div class="px-md-5">
            <table class="table table-striped table-bordered">
                <tr>
                    <th width="10%">Select </th>
                    <th>Permissions</th>
                </tr>
                @foreach($perms as $p)
                    
                    <tr>
                        <td>
                            {{Form::checkbox('permsSel[]',$p->id )}}
                        </td>
                        <td>{{$p->name}}</td>
                    </tr>
                @endforeach

            </table>
            </div>
        @endif
        <!-- permissions end -->
        <div class="flex">
            <div class="mx-auto">
                {{Form::submit('Submit', ['class'=>'btn btn-primary'])}}
            </div>
            <div class="mx-auto">
                <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>
        </div>
    {!! Form::close() !!}
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
@endsection