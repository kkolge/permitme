@extends('layouts.app')

@section('content')
    <p class="h1">Edit Location Device Link</p>
    {!! Form::open(['action' => ['LinkLocDevController@update', $lnk], 'method' => 'POST']) !!}
        <div class="form-group">
            <div class="px-md-5">
                {{Form::select('Location',$loc,$lnk->locationid, ['class'=>'form-control'])}}
            </div>
            <br/>
            <div class="px-md-5">
                {{Form::select('Device',$dev,$lnk->deviceid,['class'=>'form-control'])}}
            </div>
            <br/>
            <div class="px-md-5">
                {{Form::label('position', 'Position')}}
                {{Form::text('position', $lnk->name, ['class' => 'form-control'])}}
            </div>
            <br/>
            <div class="px-md-5">
                {{Form::label('isactive', 'Status')}}
                <br/>
                @if ($lnk->isactive == '1')
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
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
@endsection