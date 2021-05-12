@extends('layouts.app')

@section('content')
    <p class="h1">Edit Bed Details</p>
    <br/>
    {!! Form::open(['action' => ['BedsController@update', $bed->id], 'method' => 'POST']) !!}
        <div class="form-group">
            <div class="px-md-5">
                {{Form::label('bedno', 'Bed No')}}
                {{Form::text('bedno', $bed->bedNo, ['class' => 'form-control', 'placeholder' => 'Bed No'])}}
            </div>
            <div class="px-md-5">
                {{Form::label('isactive', 'Status')}}
                <br/>
                @if ($bed->isactive == '1')
                    {{Form::radio('isactive', 1, true, ['placeholder' => 'Status', 'checked' => 'checked'])}} Yes &nbsp; &nbsp; &nbsp; &nbsp;
                    {{Form::radio('isactive', 0, false, ['placeholder' => 'Status'])}} No
                @else
                    {{Form::radio('isactive', 1, false, ['placeholder' => 'Status'])}} Yes &nbsp; &nbsp; &nbsp; &nbsp;
                    {{Form::radio('isactive', 0, true, ['placeholder' => 'Status', 'checked' => 'checked'])}} No
                @endif
            </div>     
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
    <p>
        &nbsp;
    </p>
@endsection