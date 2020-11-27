@extends('layouts.app')

@section('content')
<h1><font size="+2">Details of permanent user </font></h1><br/>
<div class="flex">
        <div class="w-1/2">
            <table class="table table-striped table-bordered">
                <tr>
                    <td>Name</td><td>{{ $stf->name}}</td>
                </tr>
                <tr>
                    <td>Phone No.</td><td>{{$stf->phoneno}}</td>
                </tr>
                <!--tr>
                    <td>Photo</td><td>Not available right now</td>
                </tr-->
                <tr>
                    <td>Tag ID</td><td>{{$stf->tagid}} </td>
                </tr>
                <tr>
                    <td>Aadhar No</td><td>{{$stf->AadharNo}} </td>
                </tr>
                <tr>
                    <td>Status</td><td>
                        @if ($stf->isactive == 1)
                            Active
                        @else
                            Disabled    
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        <div class="w-1/2" align="center">
            <img style="width:50%" src="/storage/coverimages/{{$stf->coverimage}}">
        </div>
</div>
<br/><hr/><br/>
<div class="flex">
    <div class="w-1/2">
        {{ $chart1->container() }}
        {{ $chart1->script() }}
    </div>
    <div class="w-1/2">
        {{ $chart2->container() }}
        {{ $chart2->script() }}
    </div>
</div>
<br/>
<p>
    <div class="flex">
        <div class="mx-auto">
            <a href="{{ URL::previous() }}" class="btn btn-info">Back</a>
        </div>
    </div>
</p>
<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>

@endsection