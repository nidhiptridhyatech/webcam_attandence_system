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

                        
                        <div class="form-group row">
                            <label for="snap" class="col-md-4 col-form-label text-md-right">{{ __('Take Snapshot') }}</label>

                            <div class="col-md-6">
                            @error('image')
                                    <span class="text-danger" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
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
@endsection


