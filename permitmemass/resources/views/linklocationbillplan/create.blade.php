@extends('layouts.app')

@section('content')
    <p class="h1">Link Bill Plan to Location</p>
    <br/>
    {!! Form::open(['action' => 'LocationPlanController@store', 'method' => 'POST']) !!}
        <div class="form-group">
            <div class="d-flex">
                <div class="px-md-5 w-1/2">
                    {{Form::label('location', 'Location')}}
                    {{Form::select('location', $locations, null, ['class' => 'form-control', 'placeholder' => 'Select Location'])}}
                </div>
                <div class="px-md-5 w-1/2">
                    {{Form::label('plan', 'Plan')}}
                    {{Form::select('plan', $billPlans, null, ['class' => 'form-control', 'placeholder' => 'Select Plan'])}}
                </div>
            </div>
            <br/>
            <div class="px-md-5">
                {{Form::label('isactive', 'Status')}}
                <br>
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
    <p>
       
    </p>
@endsection