@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.customer_other_address.index') }}">Store</a>
  <span class="breadcrumb-item active">Create</span>
</nav>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Create Store</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.master.customer_other_address.store') }}" data-type="POST" enctype="multipart/form-data">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="name">Name <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="name" name="name">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">Code <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)" value="{{ App\Repositories\CodeRepo::generateStore() }}" readonly>
        </div>
      </div>
      <!-- <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="contact_person">Contact Person</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="contact_person" name="contact_person">
        </div>
      </div> -->
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="npwp">NPWP</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="npwp" name="npwp">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="phone">Phone</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="phone" name="phone">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="address">Address <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <textarea class="form-control" id="address" name="address"></textarea>
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
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="javascript:history.back()">
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

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {
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
@endpush
