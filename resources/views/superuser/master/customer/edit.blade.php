@extends('superuser.app')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.customer.index') }}">Store</a>
  <a class="breadcrumb-item" href="{{ route('superuser.master.customer.show', $customer->id) }}">{{ $customer->name }}</a>
  <span class="breadcrumb-item active">Edit</span>
</nav>
<div id="alert-block"></div>
@section('content')
<div class="block">
            <div class="block-conten" align="center">
                <div class="col-md-10 col-md-offset-1">
                	<form data-action="{{ route('superuser.master.customer.update', $customer->id) }}" data-type="POST" enctype="multipart/form-data" class="f1 ajax">
                    <input type="hidden" name="_method" value="PUT">
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
                		
                    <!-- Data Profile -->
                		<fieldset>
                		    <h5>Data Profile</h5>
                          <div class="container">
                            <div class="row">
                              <div class="col">
                                <div class="form-group row">
                                  <label for="name" class="col-sm-2 col-form-label">Nama <span class="text-danger">*</span></label>
                                  <div class="col-sm-10">
                                    <input type="text" id="name" name="name" placeholder="Name Store" class="form-control" value="{{$customer->name}}">
                                  </div>
                                </div>
                              </div>
                              <div class="col">
                                <div class="form-group row">
                                  <label for="category" class="col-sm-2 col-form-label">Category<span class="text-danger">*</span></label>
                                  <div class="col-sm-10">
                                    <select class="js-select2 form-control" id="category" name="category" style="width:100%;" placeholder="Pilih Kategori Store">
                                        <option>Select Category</option>
                                        @foreach($category as $category)
                                        <option value="{{ $category->id }}" {{ ($category->id == $customer->category_id ) ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="row">
                              <div class="col">
                                <div class="form-group row">
                                  <label for="owner_name" class="col-sm-2 col-form-label">Owner <span class="text-danger">*</span></label>
                                  <div class="col-sm-10">
                                    <input type="text" id="owner_name" name="owner_name" placeholder="Owner Store" class="form-control" value="{{ $customer->owner_name }}">
                                  </div>
                                </div>
                              </div>
                              <div class="col">
                                <?php 
                                  $phone = explode(",", $customer->phone);
                                ?>
                                <div class="form-group row">
                                    <label for="ktp" class="col-sm-2 col-form-label">Telepone <span class="text-danger">*</span></label>
                                    <div class="col-sm-10">
                                      <!-- <input type="number" class="form-control" id="ktp" name="ktp" min="0" value="0"> -->
                                      <div class="form-row">
                                        <div class="col">
                                          <input type="text" class="form-control" name="phone1" min="0" value="{{$phone[0]}}" placeholder="Telephone 1">
                                        </div>
                                        <div class="col">
                                          <input type="text" class="form-control" name="phone2" min="0" value="{{$phone[1] ?? '' }}" placeholder="Telephone 2">
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                              </div>
                            </div>
                            <div class="row">
                              <div class="col">
                                <div class="form-group row">
                                  <label for="email" class="col-sm-2 col-form-label">email</label>
                                  <div class="col-sm-10">
                                    <input type="text" name="email" placeholder="Email" class="form-control" value="{{ $customer->email }}">
                                  </div>
                                </div>
                              </div>
                              <div class="col">
                                <div class="form-group row">
                                  <label for="website" class="col-sm-2 col-form-label">Web / Sosmed</label>
                                  <div class="col-sm-10">
                                    <input type="text" id="website" name="website" placeholder="Website Store" class="form-control" value="{{ $customer->website }}">
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="row">
                              <div class="mb-3">
                                <label for="image_store" class="form-label">Image Store</label>
                                <input class="form-control form-control-lg" id="image_store" name="image_store" type="file">
                              </div>
                            </div>
                          </div>
                            <div class="f1-buttons">
                              <a href="{{route('superuser.master.customer.index')}}" class="btn btn-warning  btn-md text-white"><i class="fa fa-arrow-left"></i> Back</a>
                              <button type="button" class="btn btn-primary btn-next">Next <i class="fa fa-arrow-right"></i></button>
                            </div>
                        </fieldset>
                        
                        <!-- Geo Tag -->
                        <fieldset>
                            <h5>Data Geo Tag</h5>
                            <div class="container">
                              <div class="row">
                              <div class="form-row">
                                <div class="form-group col-md-4">
                                  <label for="provinsi">Provinsi</label>
                                  <select class="js-select2 form-control" id="provinsi" name="provinsi" style="width:100%;" placeholder="Pilih Provinsi">
                                    <option>Pilih provinsi</option>
                                    @foreach ($provinces as $provinsi)
                                    <option value="{{ $provinsi->prov_id }}" {{ ($provinsi->prov_id == $customer->provinsi ) ? 'selected' : '' }}>{{ $provinsi->prov_name }}</option>
                                    @endforeach
                                  </select>
                                  <input type="hidden" name="text_provinsi" value="{{$customer->text_provinsi}}">
                                </div>
                                <div class="form-group col-md-2">
                                  <label for="kota">Kota</label>
                                  <select class="js-select2 form-control" id="kota" name="kota" style="width:100%;" placeholder="Pilih Kota">
                                    <option value="{{$customer->kota}}">{{$customer->text_kota}}</option>
                                  </select>
                                  <input type="hidden" name="text_kota" value="{{$customer->text_kota}}">
                                </div>
                                <div class="form-group col-md-2">
                                  <label for="kecamatan">Kecamatan</label>
                                  <select class="js-select2 form-control" id="kecamatan" name="kecamatan" style="width:100%;" placeholder="Pilih Kecamatan">
                                    <option value="{{$customer->kecamatan}}">{{$customer->text_kecamatan}}</option>
                                  </select>
                                  <input type="hidden" name="text_kecamatan" value="{{$customer->text_kecamatan}}">
                                </div>
                                <div class="form-group col-md-2">
                                  <label for="kelurahan">Kelurahan</label>
                                  <select class="js-select2 form-control" id="kelurahan" name="kelurahan" style="width:100%;" placeholder="Pilih Kelurahan">
                                    <option value="{{$customer->kelurahan}}">{{$customer->text_kelurahan}}</option>
                                  </select>
                                  <input type="hidden" name="text_kelurahan" value="{{$customer->text_kelurahan}}">
                                </div>
                                <div class="form-group col-md-2">
                                  <label for="zipcode">Zipcode</label>
                                  <select class="js-select2 form-control" id="zipcode" name="zipcode" style="width:100%;" placeholder="Pilih Kode Pos">
                                    <option value="{{$customer->zipcode}}">{{$customer->zipcode}}</option>
                                  </select>
                                  <input type="hidden" name="text_zipcode" value="{{$customer->zipcode}}">
                                </div>
                              </div>
                              <div class="row">
                                <div class="col">
                                  <div class="form-group row">
                                    <label for="address" class="col-sm-2 col-form-label">Alamat <span class="text-danger">*</span></label>
                                    <div class="col-sm-10">
                                      <!-- <input type="text" name="address" id="address" placeholder="Store Address" class="form-control"> -->
                                      <textarea class="form-control" id="address" name="address" rows="4">{{$customer->address}}</textarea>
                                    </div>
                                  </div>
                                </div>
                                <div class="col">
                                  <div class="form-group row">
                                      <label for="gps_latitude" class="col-sm-2 col-form-label">GPS Latitude<span class="text-danger">*</span></label>
                                      <div class="col-sm-10">
                                        <input type="text" name="gps_latitude" id="gps_latitude" placeholder="Latitude" class="form-control" value="{{$customer->gps_latitude}}">
                                      </div>
                                      <label for="gps_longitude" class="col-sm-2 col-form-label">GPS Longitude<span class="text-danger">*</span></label>
                                      <div class="col-sm-10">
                                        <input type="text" name="gps_longitude" id="gps_longitude" placeholder="Longitude" class="form-control" value="{{$customer->gps_longitude}}">
                                      </div>
                                  </div>
                                </div>
                              </div>
                              <div class="row">
                                <div class="mb-3">
                                  <label for="image_store" class="form-label">Zoning</label>
                                  <input type="text" name="zone" id="zone" placeholder="Zone Area" class="form-control" value="{{$customer->zone}}">
                                </div>
                              </div>
                              <div class="f1-buttons">
                                <button type="button" class="btn btn-warning btn-previous"><i class="fa fa-arrow-left"></i> Previous</button>
                                <button type="button" class="btn btn-primary btn-next">Next <i class="fa fa-arrow-right"></i></button>
                              </div>
                            </div>
                        </fieldset>
                        
                        <!-- Finance -->
                        <fieldset>
                            <h5>Data Finance</h5>
                            <div class="container">
                              <div class="row">
                                <div class="col">
                                  <div class="form-group row">
                                    <label for="plafon_piutang" class="col-sm-2 col-form-label">Plafon Piutang <span class="text-danger">*</span></label>
                                    <div class="col-sm-10">
                                    <input type="number" class="form-control" id="plafon_piutang" name="plafon_piutang" min="0" value="0" step="0.0001" value="{{$customer->plafon_piutang}}">
                                    </div>
                                  </div>
                                </div>
                                <div class="col">
                                  <div class="form-group row">
                                    <div class="col-sm-10">
                                      <div class="form-row">
                                        <div class="col">
                                          <input class="form-check-input" type="checkbox" value="1" id="has_ppn" name="has_ppn">
                                          <span class="text-danger"><b>*Customers ppn</b></span></label><br>
                                        </div>
                                        <div class="col">
                                          <!-- <input class="form-check-input" type="checkbox" value="1" id="has_tempo" name="has_tempo"> -->
                                          <input type="checkbox" value="1" name="has_tempo" @if($customer->has_tempo == '1') checked="checked" @endif />
                                          <span class="text-danger"><b>*Customers tempo</b></span></label><br>
                                        </div>
                                        <div class="col">
                                          <input class="form-control" id="tempo_limit" value="{{$customer->tempo_limit}}" name="tempo_limit" placeholder="Tempo limit">
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <div class="row">
                                <div class="col">
                                  <div class="form-group row">
                                    <label for="ktp" class="col-sm-2 col-form-label">KTP <span class="text-danger">*</span></label>
                                    <div class="col-sm-10">
                                      <!-- <input type="number" class="form-control" id="ktp" name="ktp" min="0" value="0"> -->
                                      <div class="form-row">
                                       <?php 
                                        $ktp = explode("/", $customer->ktp);
                                       ?>
                                        <div class="col">
                                          <input type="text" class="form-control" name="name_card_ktp" placeholder="Name Card" value="{{$ktp[0]}}">
                                        </div>
                                        <div class="col">
                                          <input type="number" class="form-control" name="ktp"  placeholder="Number Card" value="{{$ktp[1] ?? ''}}">
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                <div class="col">
                                  <div class="form-group row">
                                    <label for="npwp" class="col-sm-2 col-form-label">NPWP <span class="text-danger">*</span></label>
                                    <div class="col-sm-10">
                                    <div class="form-row">
                                      <?php 
                                        $npwp = explode("/", $customer->npwp);
                                       ?>
                                        <div class="col">
                                          <input type="text" class="form-control" name="name_card_npwp" placeholder="Name Card" value="{{$npwp[0]}}">
                                        </div>
                                        <div class="col">
                                          <input type="number" class="form-control" name="npwp" placeholder="Number Card" value="{{$npwp[1] ?? ''}}">
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <div class="f1-buttons">
                                  <button type="button" class="btn btn-warning btn-previous"><i class="fa fa-arrow-left"></i> Previous</button>
                                  <button type="button" class="btn btn-primary btn-next">Next <i class="fa fa-arrow-right"></i></button>
                              </div>
                            </div>
                        </fieldset>
                        
                        <!-- Document Image -->
                        <fieldset>
                            <h5>Data Document</h5>
                              <div class="container">
                                <div class="row">
                                  <div class="col">
                                    <div class="form-group">
                                      <label>Image NPWP</label>
                                      <input type="file" id="image_npwp" name="image_npwp" data-max-file-size="2000" accept="image/png, image/jpeg">
                                    </div>
                                  </div>
                                  <div class="col">
                                    <div class="form-group">
                                      <label>Image KTP</label>
                                      <input type="file" id="image_ktp" name="image_ktp" data-max-file-size="2000" accept="image/png, image/jpeg">
                                    </div>
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

    // $('#image_store').fileinput({
    //   theme: 'explorer-fa',
    //   browseOnZoneClick: true,
    //   showCancel: false,
    //   showClose: false,
    //   showUpload: false,
    //   browseLabel: '',
    //   removeLabel: '',
    //   fileActionSettings: {
    //     showDrag: false,
    //     showRemove: false
    //   },
    // });

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

    $('#has_tempo').click(function () {
        //check if checkbox is checked
        if ($(this).is(':checked')) {

            $('#tempo_limit').removeAttr('disabled'); //enable input

        } else {
            $('#tempo_limit').attr('disabled', true); //disable input
        }
    });
  })
</script>
@endpush


