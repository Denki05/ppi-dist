@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.customer.index') }}">Store</a>
  <a class="breadcrumb-item" href="{{ route('superuser.master.customer.show', $customer->id) }}">{{ $customer->name }}</a>
  <span class="breadcrumb-item">Member</span>
  <span class="breadcrumb-item active">Create</span>
</nav>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Create Member</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.master.customer.other_address.store', $customer->id) }}" data-type="POST" enctype="multipart/form-data">
    <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="name">Name <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="name" name="name">
        </div>
      </div>
      
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="contact_person">Contact Person</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="contact_person" name="contact_person">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="npwp">NPWP</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="npwp" name="npwp">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="ktp">KTP</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="ktp" name="ktp">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="phone">Phone</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="phone" name="phone">
        </div>
      </div>
      <!-- <div style="width: 100%" class="form-group row">
        <iframe width="100%" height="600" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?width=100%25&amp;height=600&amp;hl=en&amp;q=1%20Grafton%20Street,%20Dublin,%20Ireland+(My%20Business%20Name)&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed"><a href="https://www.gps.ie/farm-gps/">gps for tractors</a></iframe>
      </div> -->
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="address">Address <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <textarea class="form-control" id="address" name="address"></textarea>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Map <span class="text-danger">*</span></label>
        <div class="col-md-3" id="map" style="height:200px; width: 400px;"  >
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">GPS Coordinate</label>
        <div class="col-md-3">
          <input type="text" class="form-control" id="gps_latitude" name="gps_latitude" placeholder="Latitude">
        </div>
        <div class="col-md-1"></div>
        <div class="col-md-3">
          <input type="text" class="form-control" id="gps_longitude" name="gps_longitude" placeholder="Longitude">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Provinsi</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="provinsi" name="provinsi" data-placeholder="Select Provinsi">
            <option></option>
            @foreach ($provinces as $provinsi)
              <option value="{{ $provinsi->prov_id }}">{{ $provinsi->prov_name }}</option>
            @endforeach
          </select>
          <input type="hidden" name="text_provinsi">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Kota</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="kota" name="kota" data-placeholder="Select Kota">
            <option></option>
          </select>
          <input type="hidden" name="text_kota">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Kecamatan</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="kecamatan" name="kecamatan" data-placeholder="Select Kecamatan">
            <option></option>
          </select>
          <input type="hidden" name="text_kecamatan">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Kelurahan</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="kelurahan" name="kelurahan" data-placeholder="Select Kelurahan">
            <option></option>
          </select>
          <input type="hidden" name="text_kelurahan">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="zipcode">Zipcode</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="zipcode" name="zipcode">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Image NPWP</label>
        <div class="col-md-7">
          <input type="file" id="image_npwp" name="image_ktp" data-max-file-size="2000" accept="image/png, image/jpeg">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Image KTP</label>
        <div class="col-md-7">
          <input type="file" id="image_ktp" name="image_ktp" data-max-file-size="2000" accept="image/png, image/jpeg">
        </div>
      </div>
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.master.customer.index') }}">
            <button type="button" class="btn bg-gd-cherry border-0 text-white">
              <i class="fa fa-arrow-left mr-10"></i> Back
            </button>
          </a>
        </div>
        <div class="col-md-6 text-right">
          <button type="submit" class="btn bg-gd-corporate border-0 text-white">
            Submit <i class="fa fa-arrow-right ml-10"></i>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
<div id="alert-block"></div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.fileinput')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {
    $('#image_npwp').fileinput({
      theme: 'explorer-fa',
      browseOnZoneClick: true,
      showCancel: false,
      showClose: false,
      showUpload: false,
      browseLabel: '',
      removeLabel: '',
      fileActionSettings: {
        showDrag: false,
        showRemove: false
      },
    });

    $('#image_ktp').fileinput({
      theme: 'explorer-fa',
      browseOnZoneClick: true,
      showCancel: false,
      showClose: false,
      showUpload: false,
      browseLabel: '',
      removeLabel: '',
      fileActionSettings: {
        showDrag: false,
        showRemove: false
      },
    });

    $('.js-select2').select2()

    $(function () {
    $.ajaxSetup({
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}
    });

    $(function(){

        $('#provinsi').on('change', function(){
          let prov_id = $('#provinsi').val();

          let text_provinsi = (objHasProp($('#provinsi').select2('data')[0], 'text')) ? $('#provinsi').select2('data')[0].text : '';
          $('input[name=text_provinsi]').val(text_provinsi);
          $('input[name=text_kota]').val('');
          $('input[name=text_kecamatan]').val('');
          $('input[name=text_kelurahan]').val('');
          

          $.ajax({
            type : 'POST',
            url : '{{route('superuser.master.customer.getkabupaten')}}',
            data : {prov_id:prov_id},
            cache : false,

            success: function(msg){
              $('#kota').html(msg);
            },
            error : function(data){
              console.log('error:',data)
            },
          })
        })

        $('#kota').on('change', function(){
          let city_id = $('#kota').val();

          let text_kota = (objHasProp($('#kota').select2('data')[0], 'text')) ? $('#kota').select2('data')[0].text : '';
          $('input[name=text_kota]').val(text_kota);
          $('input[name=text_kecamatan]').val('');
          $('input[name=text_kelurahan]').val('');
          

          $.ajax({
            type : 'POST',
            url : '{{route('superuser.master.customer.getkecamatan')}}',
            data : {city_id:city_id},
            cache : false,

            success: function(msg){
              $('#kecamatan').html(msg);
            },
            error : function(data){
              console.log('error:',data)
            },
          })
        })

        $('#kecamatan').on('change', function(){
          let dis_id = $('#kecamatan').val();

          let text_kecamatan = (objHasProp($('#kecamatan').select2('data')[0], 'text')) ? $('#kecamatan').select2('data')[0].text : '';
          $('input[name=text_kecamatan]').val(text_kecamatan);
          $('input[name=text_kelurahan]').val('');
          

          $.ajax({
            type : 'POST',
            url : '{{route('superuser.master.customer.getkelurahan')}}',
            data : {dis_id:dis_id},
            cache : false,

            success: function(msg){
              $('#kelurahan').html(msg);
            },
            error : function(data){
              console.log('error:',data)
            },
          })
        })

        $('#kelurahan').on('change', function(){
          let subdis_id = $('#kelurahan').val();

          let text_kelurahan = (objHasProp($('#kelurahan').select2('data')[0], 'text')) ? $('#kelurahan').select2('data')[0].text : '';
          $('input[name=text_kelurahan]').val(text_kelurahan);

          $.ajax({
            type : 'POST',
            url : '{{route('superuser.master.customer.getzipcode')}}',
            data : {subdis_id:subdis_id},
            cache : false,

            success: function(msg){
              $('#zipcode').html(msg);
            },
            error : function(data){
              console.log('error:',data)
            },
          })
        })
      })
    })
  })
</script>
<script>
                    let map;
                    function initMap() {
                        map = new google.maps.Map(document.getElementById("map"), {
                            center: { lat: -7.3020583, lng: 112.7851902 },
                            zoom: 8,
                            scrollwheel: true,
                        });
                        const uluru = { lat: -7.3020583, lng: 112.7851902 };
                        let marker = new google.maps.Marker({
                            position: uluru,
                            map: map,
                            draggable: true
                        });
                        google.maps.event.addListener(marker,'position_changed',
                            function (){
                                let lat = marker.position.lat()
                                let lng = marker.position.lng()
                                $('#gps_latitude').val(lat)
                                $('#gps_longitude').val(lng)
                            })
                        google.maps.event.addListener(map,'click',
                        function (event){
                            pos = event.latLng
                            marker.setPosition(pos)
                        })
                        
                    }
                </script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=&callback=initMap" type="text/javascript"></script>
@endpush
