@extends('layouts.app')

@section('content')
    <!-- creating form for capturing the inputs -->
    {!! Form::open(['url' =>'/adminReports/sUserReport', 'method' => 'GET']) !!}
        <div class="form-group">
            <table class="table table-sm max-width">
                <tr>
                    <td class="px-md-5" style="width:30%"> <font size="+1">{!! Form::label('identifier','Identifier') !!}</font></td>
                    <td style="width:40%">{!! Form::text('identifier','',['class'=>'form-control', 'placeholder'=>'Identifier']) !!}</td>
                    <td class="px-md-5" style="width:30%">{{Form::submit('Get Data', ['class'=>'btn btn-primary'])}}</td>
                </tr>
            </table>
            <hr/>
        </div>
    {!! Form::close() !!}
    <!-- end form User-->
    

    {!! Form::open(['url' => '/adminReports/sStateReport', 'method'=>'GET'])!!}
        <div class="form-group">
            <table class="table table-sm max-width ">
                <tr>
                    <td class="px-md-5" style="width:30%">
                        <font size="+1">
                            {!! Form::label('state','Select State')!!}
                        </font>
                    </td>
                    <td style="width:40%">{{Form::select('state',$state,null, ['class'=>'form-control', 'placeholder'=>'Select State'])}}</td>
                    <td class="px-md-5" style="width:30%">{{Form::submit('Get Data', ['class'=>'btn btn-primary'])}}</td>
                </tr>
            </table>
            <hr/>
        </div>
    {!! Form::close() !!}
    <!-- end form State-->

    {!! Form::open(['url' => '/adminReports/sPincodeReport', 'method'=>'GET'])!!}
        <div class="form-group">
            <table class="table table-sm max-width ">
                <tr>
                    <td class="px-md-5" style="width:30%">
                        <font size="+1">
                            {!! Form::label('district','Select District')!!}
                        </font>
                    </td>
                    <td style="width:40%">{{Form::select('district',$district,null, ['class'=>'form-control', 'placeholder'=>'Select District'])}}</td>
                    <td class="px-md-5" style="width:30%">{{Form::submit('Get Data', ['class'=>'btn btn-primary'])}}</td>
                </tr>
            </table>
            <hr/>
        </div>
    {!! Form::close() !!}
    <!-- end form District-->

    {!! Form::open(['url' => '/adminReports/sPincodeReport', 'method'=>'GET'])!!}
        <div class="form-group">
            <table class="table table-sm max-width">
                <tr>
                    <td class="px-md-5" style="width:30%">
                        <font size="+1">
                            {!! Form::label('taluka','Select Taluka')!!}
                        </font>
                    </td>
                    <td style="width:40%">{{Form::select('taluka',$taluka,null, ['class'=>'form-control', 'placeholder'=>'Select Taluka'])}}</td>
                    <td class="px-md-5" style="width:30%">{{Form::submit('Get Data', ['class'=>'btn btn-primary'])}}</td>
                </tr>
            </table>
            <hr/>
        </div>
    {!! Form::close() !!}
    <!-- end form Taluka-->

    {!! Form::open(['url' => '/adminReports/sCityReport', 'method'=>'GET'])!!}
        <div class="form-group">
            <table class="table table-sm max-width">
                <tr>
                    <td class="px-md-5" style="width:30%">
                        <font size="+1">
                            {!! Form::label('source','Select City')!!}
                        </font>
                    </td>
                    <td style="width:40%">{{Form::select('source',$city,null, ['class'=>'form-control', 'placeholder'=>'Select City'])}}</td>
                    <td class="px-md-5" style="width:30%">{{Form::submit('Get Data', ['class'=>'btn btn-primary'])}}</td>
                </tr>
            </table>
            <hr/>
        </div>
    {!! Form::close() !!}
    <!-- end form City-->

    {!! Form::open(['url' => '/adminReports/sPincodeReport', 'method'=>'GET'])!!}
        <div class="form-group">
            <table class="table table-sm max-width">
                <tr>
                    <td class="px-md-5" style="width:30%">
                        <font size="+1">
                            {!! Form::label('source','Select Pincode')!!}
                        </font>
                    </td>
                    <td style="width:40%">{{Form::select('source',$pin,null, ['class'=>'form-control', 'placeholder'=>'Select Pincode'])}}</td>
                    <td class="px-md-5" style="width:30%">{{Form::submit('Get Data', ['class'=>'btn btn-primary'])}}</td>
                </tr>
            </table>
            <hr/>
        </div>
    {!! Form::close() !!}
    <!-- end form Pincode-->


    {!! Form::open(['url' => '/adminReports/sLocationReport', 'method'=>'GET'])!!}
        <div class="form-group">
            <table class="table table-sm max-width">
                <tr>
                    <td class="px-md-5" style="width:30%">
                        <font size="+1">
                            {!! Form::label('surce','Select Location')!!}
                        </font>
                    </td>
                    <td style="width:40%">{{Form::select('source',$loc,null, ['class'=>'form-control', 'placeholder'=>'Select Location'])}}</td>
                    <td class="px-md-5" style="width:30%">{{Form::submit('Get Data', ['class'=>'btn btn-primary'])}}</td>
                </tr>
            </table>
            <hr/>
        </div>
    {!! Form::close() !!}
    <!-- end form Location-->
    <p>
        <div class="flex">
            <div class="mx-auto">
                <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
            </div>
        </div>
    </p>
    <p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
@endsection