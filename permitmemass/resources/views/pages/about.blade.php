@extends('layouts.appCenter')

@section('content')
@if(!Auth::user())
    <br/><br/><br/>
@endif
<div class="mx-auto col-6 text-justify">
    <p class="h1">Ko-Aaham Technologies LLP</p>
    <br/>
    <p class="h3">Smart Sensing, Remote Monitoring </p>
        <br/><br/>
</div>

    <div class="mx-auto col-6 text-justify">
        Team Ko-Aaham believes in using Technology to identify an object and build intelligent decision-making capability in a smart device.
        <br/>
        These devices will not only help further optimize processes such as Asset Management, Supply chain tracking but also enable device to take intelligent decisions based on multiple sensor inputs in case of hazardous environments and emergency situations.
        <br/>
        Since inception, Team Ko-Aaham has worked to develop intelligent Scanners that can uniquely identify an object, understand its environment and location. Additional sensor data combined with object identification, enable intelligent decision and send relevant data to server
        <br/>
        All future solutions are based on IoT architecture thus building our devices to use best of the available features and simplicity of data collection to make a super efficient and optimized solution for real time responsive actions and decision making.
        <br/>
            Please visit <a class="btn-link" href="http://www.ko-aaham.com"> Ko-Aaham Technologies LLP </a> Website for more details.
        
    </div>

@endsection 
