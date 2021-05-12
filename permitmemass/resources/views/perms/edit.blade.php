@extends('layouts.app')

@section('content')
    <p class="h1">Edit permission</p>
    {!! Form::open(['action' => ['PermissionsController@update',$perm->id], 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
        <div class="form-group">
            <div class="px-md-5">
            {{Form::label('name', 'Name')}}
            {{Form::text('name', $perm->name, ['class' => 'form-control', 'placeholder' => 'Name'])}}
            </div><br/>
            <div class="px-md-5">
            {{Form::label('guard', 'Guard Name')}}
            {{Form::text('guard', $perm->guard_name, ['class' => 'form-control', 'placeholder' => 'Guard Name'])}}
            </div><br/>
        </div>
        {{Form::hidden('_method','PUT')}}
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