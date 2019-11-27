@extends('layouts.app')

<style>
    #my_camera{
    width: 320px;
    height: 240px;
    border: 1px solid black;
    }
    p{ border: 2px dashed #009755; width: auto; padding: 10px; font-size: 18px; border-radius: 5px; color: #FF7361;} 

    span.label{ font-weight: bold; color: #000;} 
</style>
<link rel="stylesheet" type="text/css" href="{{asset('css/recordstyle.css')}}">
<script type="text/javascript" src="{{asset('js/webcamjs/webcam.min.js')}}"></script>    
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script> 

 <!-- Code to handle taking the snapshot and displaying it locally -->
 <script language="JavaScript">
 $(document).ready(function(){ 

if (navigator.geolocation) { 

    navigator.geolocation.getCurrentPosition(showLocation); 

} else { 

    $('#location').html('Geolocation is not supported by this browser.'); 

} 

}); 
function showLocation(position) { 

var latitude = position.coords.latitude; 

var longitude = position.coords.longitude; 

$("#login_lati").val(latitude);
$("#login_long").val(longitude);


} 

 // Configure a few settings and attach camera
 function configure(){
  Webcam.set({
   width: 320,
   height: 240,
   image_format: 'jpeg',
   jpeg_quality: 90
  });
  Webcam.attach('#my_camera');
 }
 // A button for taking snaps
 function take_snapshot() {

  // take snapshot and get image data
  Webcam.snap( function(data_uri) {
    $(".image-tag").val(data_uri);
  // display results in page
  document.getElementById('results').innerHTML = 
   '<img id="imageprev" src="'+data_uri+'"/>';
  } );

  Webcam.reset();
 }

function saveSnap(){
 // Get base64 value from <img id='imageprev'> source
 var base64image = document.getElementById("imageprev").src;

 Webcam.upload( base64image, 'upload.php', function(code, text) {
  console.log('Save successfully');
  //console.log(text);
 });

}
</script>

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Make Attendance') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('make-attendance') }}" enctype="multipart/form-data">
                        @csrf
                        @if(session()->has('info'))
                            <div class="alert alert-warning">
                            <strong>Info!</strong> {{ session()->get('info') }}
                            </div>
                        @endif
                        @if(session('success'))
                            <div class="alert alert-success">
                            <strong>Success!</strong> {{session('success')}}
                            </div>
                        @endif
                        @error('image')
                            <div class="alert alert-danger">
                            <strong>Sorry!</strong> {{ $message }}
                            </div>
                        @enderror
                        <div class="form-group row">
                            <label for="snap" class="col-md-4 col-form-label text-md-right">{{ __('Mobile No.') }}</label>

                            <div class="col-md-6">
                                <input type="number" name="phone_number" id="phone_number" required>
                            </div>    
                        </div>    
                        <div class="form-group row">
                            <label for="snap" class="col-md-4 col-form-label text-md-right">{{ __('Take Snapshot') }}</label>

                            <div class="col-md-6">
                            <!-- @error('image')
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                            @if(session('success'))
    <h1>{{session('success')}}</h1>
@endif -->
                            <div id="my_camera"></div>
 <input type=button value="Start Camera" onClick="configure()">
 <input type=button value="Take Snapshot" name="snapshot" onClick="take_snapshot()">
 <input type="hidden" name="image" class="image-tag">
 <input id="login_lati" type="hidden" name="login_lati" value="">
 <input id="login_long" type="hidden" name="login_long" value="">
 <!-- <input type=button value="Save Snapshot" onClick="saveSnap()"> -->
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right"></label>

                            <div class="col-md-6">
                            <div id="results" ></div>
                            </div>
                         </div>   
                         
<div class="form-group row">
                            <label for="snap" class="col-md-4 col-form-label text-md-right">{{ __('Record Voice') }}</label>

                            <div class="col-md-6">
<div id="controls">
  	 <button onclick="startRecording()" type="button" id="recordButton">Record</button>
  	 <button onclick="pauseRecording()" type="button" id="pauseButton" style="display:none" disabled>Pause</button>
  	 <button onclick="stopRecording()" type="button" id="stopButton" disabled>Stop</button>
    </div>
    <!-- <div id="formats">Format: start recording to see sample rate</div> -->
  	<span>(*The audio file should be at least 1-second-long and no longer than 15 seconds)</span>
  	<ol id="recordingsList"></ol>
</div>
</div>
                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                            
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Make Attendance') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- <script src="https://cdn.rawgit.com/mattdiamond/Recorderjs/08e7abd9/dist/recorder.js"></script> -->
<script type="text/javascript" src="{{asset('js/recorder.js')}}"></script>
<script type="text/javascript" src="{{asset('js/recordapp.js')}}"></script>    

@endsection

