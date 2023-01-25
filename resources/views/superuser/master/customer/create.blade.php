@extends('superuser.app')

@section('content')
<!-- <nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.customer.index') }}">Store</a>
  <span class="breadcrumb-item active">Create</span>
</nav> -->
<div class="block">
            <div class="block-conten" align="center">
                <div class="col-md-10 col-md-offset-1">
                	<form data-action="{{ route('superuser.master.customer.store') }}" data-type="POST" enctype="multipart/form-data" class="f1 ajax">
                		<div class="f1-steps">
                			<div class="f1-progress">
                			    <div class="f1-progress-line" data-now-value="25" data-number-of-steps="4" style="width: 25%;"></div>
                			</div>
                            <div class="f1-step active">
                                <div class="f1-step-icon"><i class="mdi mdi-account"></i></div>
                                <p>Profile</p>
                            </div>
                			<div class="f1-step">
                				<div class="f1-step-icon"><i class="mdi mdi-crosshairs-gps"></i></div>
                				<p>Geo Tag</p>
                			</div>
                			<div class="f1-step">
                				<div class="f1-step-icon"><i class="mdi mdi-currency-usd"></i></div>
                				<p>Finance</p>
                			</div>
                      <div class="f1-step">
                				<div class="f1-step-icon"><i class="mdi mdi-file-document-box"></i></div>
                				<p>Document</p>
                			</div>
                		</div>
                		<!-- step 1 -->
                		<fieldset>
                		    <h4>Data Profile</h4>
                          <div class="container">
                            <div class="row">
                              <div class="col">
                                <div class="form-group">
                                    <label for="name">Nama<span class="text-danger">*</span></label>
                                    <input type="text" id="name" name="name" placeholder="Name Store" class="form-control">
                                </div>
                              </div>
                              <div class="col">
                                <div class="form-group">
                                  <label for="has_ppn">Customer PPN <span style="font-style: italic;" class="text-danger">*check for customers ppn</span></label><br>
                                  <input type="checkbox" id="has_ppn" name="has_ppn" value="1">
                                </div>
                              </div>
                              <div class="form-group">
                                <label>Category</label>
                                  <select class="js-select2 form-control" id="category" name="category" style="width:100%;" placeholder="Pilih Kategori Store">
                                      <option>Select Category</option>
                                      @foreach($category as $category)
                                      <option value="{{ $category->id }}">{{ $category->name }}</option>
                                      @endforeach
                                  </select>
                              </div>
                              <div class="form-group">
                                <label for="zone">Zoning</label>
                                <input type="text" class="form-control" id="zone" name="zone" placeholder="Zoning Wilayah">
                              </div>
                            </div>
                            <div class="row">
                              <div class="form-group">
                                  <label for="email">Email</label>
                                  <input type="email" id="email" name="email" placeholder="Email" class="form-control">
                              </div>
                              <div class="form-group">
                                  <label for="phone">Telepone</label>
                                  <input type="number" id="phone" name="phone" placeholder="Phone" class="form-control">
                              </div>
                              <div class="form-group">
                                  <label for="address">Alamat<span class="text-danger">*</span></label>
                                  <input type="text" name="address" id="address" placeholder="Store Address" class="form-control">
                              </div>
                              <div class="form-group">
                                  <label for="owner_name">Owner</label>
                                  <input type="text" id="owner_name" name="owner_name" placeholder="Owner Store" class="form-control">
                              </div>
                              <div class="form-group">
                                  <label for="website">Website</label>
                                  <input type="text" id="website" name="website" placeholder="Website Store" class="form-control">
                              </div>
                              <div class="form-group">
                                  <label>Foto Store</label>
                                  <input type="file" id="image_store" name="image_store" data-max-file-size="2000" accept="image/png, image/jpeg">
                              </div>
                            </div>
                          </div>
                            <div class="f1-buttons">
                              <a href="{{route('superuser.master.customer.index')}}" class="btn btn-warning  btn-md text-white"><i class="fa fa-arrow-left"></i> Back</a>
                              <button type="button" class="btn btn-primary btn-next">Next <i class="fa fa-arrow-right"></i></button>
                            </div>
                        </fieldset>
                        <!-- step 2 -->
                        <fieldset>
                            <h4>Data Geo Tag</h4>
                            <div class="container">
                              <div class="row">
                                <div class="form-group">
                                  <label>Area Detail</label>
                                  
                                    <select class="js-select2 form-control" id="provinsi" name="provinsi" style="width:100%;" placeholder="Pilih Provinsi">
                                        <option>Pilih provinsi</option>
                                        @foreach ($provinces as $provinsi)
                                        <option value="{{ $provinsi->prov_id }}">{{ $provinsi->prov_name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="text_provinsi">
                                </div>
                                <div class="form-group">
                                    <select class="js-select2 form-control" id="kota" name="kota" style="width:100%;" placeholder="Pilih Kota">
                                        <option>Pilih Kota</option>
                                    </select>
                                    <input type="hidden" name="text_kota">
                                </div>
                                <div class="form-group">
                                    <select class="js-select2 form-control" id="kecamatan" name="kecamatan" style="width:100%;" placeholder="Pilih Kecamatan">
                                        <option>Pilih Kecamatan</option>
                                    </select>
                                    <input type="hidden" name="text_kecamatan">
                                </div>
                                <div class="form-group">
                                    <select class="js-select2 form-control" id="kelurahan" name="kelurahan" style="width:100%;" placeholder="Pilih Kelurahan">
                                        <option>Pilih Kelurahan</option>
                                    </select>
                                    <input type="hidden" name="text_kelurahan">
                                </div>
                                <div class="form-group">
                                    <select class="js-select2 form-control" id="zipcode" name="zipcode" style="width:100%;" placeholder="Pilih Kode Pos">
                                        <option>Pilih Kode Pos</option>
                                    </select>
                                    <input type="hidden" name="text_zipcode">
                                </div>
                              </div>
                              <div class="row">
                                <div class="col">
                                  <div class="form-group">
                                      <label for="gps_latitude">GPS Latitude</label>
                                      <input type="text" name="gps_latitude" id="gps_latitude" placeholder="Latitude" class="form-control">
                                  </div>
                                </div>
                                <div class="col">
                                  <div class="form-group">
                                      <label for="gps_longitude">GPS Logitude</label>
                                      <input type="text" name="gps_longitude" id="gps_longitude" placeholder="Longitude" class="form-control">
                                  </div>
                                </div>
                              </div>
                              <div class="f1-buttons">
                                <button type="button" class="btn btn-warning btn-previous"><i class="fa fa-arrow-left"></i> Previous</button>
                                <button type="button" class="btn btn-primary btn-next">Next <i class="fa fa-arrow-right"></i></button>
                              </div>
                            </div>
                        </fieldset>
                        <!-- step 3 -->
                        <fieldset>
                            <h4>Data Finance</h4>
                            <div class="container">
                              <div class="form-group">
                                  <label>Plafon Piutang</label>
                                  <input type="number" class="form-control" id="plafon_piutang" name="plafon_piutang" min="0" value="0" step="0.0001">
                              </div>
                              <div class="form-group">
                                  <label>NPWP</label>
                                  <input type="number" class="form-control" id="npwp" name="npwp" min="0" value="0">
                              </div>
                              <div class="form-group">
                                  <label>KTP</label>
                                  <input type="number" class="form-control" id="ktp" name="ktp" min="0" value="0">
                              </div>
                              <div class="f1-buttons">
                                  <button type="button" class="btn btn-warning btn-previous"><i class="fa fa-arrow-left"></i> Previous</button>
                                  <button type="button" class="btn btn-primary btn-next">Next <i class="fa fa-arrow-right"></i></button>
                              </div>
                            </div>
                        </fieldset>
                        <!-- step 4 -->
                        <fieldset>
                            <h4>Data Document</h4>
                              <div class="container">
                                <div class="row">
                                  <div class="col">
                                  <div class="form-group">
                                    <label>Image NPWP</label>
                                    <input type="file" id="image_npwp" name="image_npwp" data-max-file-size="2000" accept="image/png, image/jpeg">
                                  </div>
                                  <div class="form-group">
                                    <label>Image KTP</label>
                                    <input type="file" id="image_ktp" name="image_ktp" data-max-file-size="2000" accept="image/png, image/jpeg">
                                  </div>
                                </div>
                              </div>
                              <div class="f1-buttons">
                                  <button type="button" class="btn btn-warning btn-previous"><i class="fa fa-arrow-left"></i> Previous</button>
                                  <button type="submit" class="btn btn-primary btn-submit"><i class="fa fa-save"></i> Submit</button>
                              </div>
                            </div>
                        </fieldset>
                	</form>
                </div>
            </div>
        </div>
@endsection

@include('superuser.asset.plugin.fileinput')
@include('superuser.asset.plugin.select2')

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

    $('#image_store').fileinput({
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
@endpush


