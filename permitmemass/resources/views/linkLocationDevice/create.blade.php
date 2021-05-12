@extends('layouts.app')

@section('content')
    <p class="h1">Link Device to Location</p>
    {!! Form::open(['action' => 'LinkLocDevController@store', 'method' => 'POST']) !!}
        <div class="form-group">
            <div class="px-md-5">
                {{Form::select('Location',$loc,null, ['class'=>'form-control', 'placeholder'=>'Select'])}}
            </div><br/>
            <div class="px-md-5">
                {{Form::select('Device',$dev,null,['class'=>'form-control', 'placeholder'=>'Select'])}}
            </div><br/>
            <div class="px-md-5">
                {{Form::label('position', 'Position')}}
                {{Form::text('position', '', ['class' => 'form-control', 'placeholder' => 'Installation position'])}}
            </div>
            <br/>
            <div class="px-md-5">
                {{Form::label('isactive', 'Status')}}
            <br/>
                {{Form::radio('isactive',1, false, ['placeholder' => 'Status'])}} Yes &nbsp; &nbsp; &nbsp; &nbsp;
                {{Form::radio('isactive',0, true, ['placeholder' => 'Status', 'checked' => 'true'])}} No
            </div>
        </div>
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