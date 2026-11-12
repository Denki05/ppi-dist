@extends('superuser.app')
@php
  $salesColumns = \App\Entities\Master\CustomerOtherAddress::salesColumnMap();
  $smLabel = ($salesColumns['member_sm'] ?? null) === 'am' ? 'AM' : 'SM';
  $asmLabel = ($salesColumns['member_asm'] ?? null) === 'ams' ? 'AMS' : 'ASM';
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('superuser_assets/css/customer-form.css') }}?v=20261110b">
@endpush

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.customer.index') }}">Member</a>
  <span class="breadcrumb-item active">Create</span>
</nav>

<div class="block">
  <div class="block-content-block">
    <form data-action="{{ route('superuser.master.customer.other_address.store', $customer->id) }}" data-type="POST" enctype="multipart/form-data" class="cust-form ajax" novalidate>
      <div class="cust-hero">
        <div class="cust-hero-icon"><i class="mdi mdi-account-plus"></i></div>
        <div>
          <h4>Tambah Member</h4>
          <p>Member baru untuk store <strong>{{ $customer->name }}</strong>.</p>
        </div>
        <span class="cust-hero-badge">Member</span>
      </div>

      <div class="cust-stepper">
        <div class="cust-progress-line" style="width: 0;"></div>
        <div class="cust-step active" data-step="0">
          <div class="cust-step-icon"><i class="mdi mdi-account"></i></div>
          <p>Profil Member</p>
        </div>
        <div class="cust-step" data-step="1">
          <div class="cust-step-icon"><i class="mdi mdi-map-marker"></i></div>
          <p>Alamat & Wilayah</p>
        </div>
        <div class="cust-step" data-step="2">
          <div class="cust-step-icon"><i class="mdi mdi-card-account-details"></i></div>
          <p>Identitas Pajak</p>
        </div>
        <div class="cust-step" data-step="3">
          <div class="cust-step-icon"><i class="mdi mdi-file-document-box"></i></div>
          <p>Dokumen</p>
        </div>
      </div>
        <section class="cust-fieldset active" data-step="0">
        <div class="cust-card">
          <div class="cust-card-head">
            <div class="cust-card-icon"><i class="mdi mdi-account"></i></div>
            <div>
              <h5>Profil Member</h5>
              <p>Identitas dan kontak member.</p>
            </div>
          </div>
          <div class="row">
            <div class="col-md-8">
              <div class="form-group">
                <label class="cust-label" for="name">Nama <span class="req">*</span></label>
                <input type="text" id="name" name="name" placeholder="Nama member" class="form-control">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label class="cust-label" for="category">Kategori</label>
                <input type="text" id="category" name="category" placeholder="Kategori member" class="form-control" value="{{ $customer->category->name }}" readonly>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label class="cust-label" for="contact_person">Contact Person <span class="req">*</span></label>
                <input type="text" id="contact_person" name="contact_person" placeholder="Nama contact person" class="form-control">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="cust-label" for="phone">Telp <span class="req">*</span></label>
                <input type="text" id="phone" name="phone" placeholder="08xxxxxxxxxx" class="form-control">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label class="cust-label" for="sm_am">{{ $smLabel }}</label>
                <input type="text" id="sm_am" name="sm_am" placeholder="Nama {{ $smLabel }}" class="form-control">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label class="cust-label" for="asm">{{ $asmLabel }}</label>
                <input type="text" id="asm" name="asm" placeholder="Nama {{ $asmLabel }}" class="form-control">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label class="cust-label" for="pic">PIC</label>
                <input type="text" id="pic" name="pic" placeholder="Nama PIC" class="form-control">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label class="cust-label" for="officer">Officer</label>
                <input type="text" id="officer" name="officer" placeholder="Nama officer" class="form-control">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label class="cust-label" for="target_income">Target Incomes</label>
                <input type="number" id="target_income" name="target_income" placeholder="0" class="form-control">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <div class="form-group">
                <label class="cust-label" for="address">Alamat <span class="req">*</span></label>
                <textarea class="form-control" id="address" name="address" rows="2" placeholder="Alamat member"></textarea>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label class="cust-label" for="ar1">AR1</label>
                <input type="text" id="ar1" name="ar1" placeholder="Account Representative 1" class="form-control">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label class="cust-label" for="ar2">AR2</label>
                <input type="text" id="ar2" name="ar2" placeholder="Account Representative 2" class="form-control">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label class="cust-label" for="ar3">AR3</label>
                <input type="text" id="ar3" name="ar3" placeholder="Account Representative 3" class="form-control">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="cust-check">
                <input type="checkbox" value="1" id="free_shipping" name="free_shipping">
                <label for="free_shipping">Free Shipping</label>
              </div>
            </div>
          </div>
        </div>

        </section>
        <section class="cust-fieldset" data-step="1">
        <div class="cust-card">
          <div class="cust-card-head">
            <div class="cust-card-icon"><i class="mdi mdi-map-marker"></i></div>
            <div>
              <h5>Alamat &amp; Wilayah</h5>
              <p>Lokasi pengiriman member.</p>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label class="cust-label" for="provinsi">Provinsi</label>
                <select class="js-select2 form-control" id="provinsi" name="provinsi" placeholder="Pilih provinsi">
                  <option value="">Pilih provinsi</option>
                  @foreach ($provinces as $provinsi)
                  <option value="{{ $provinsi->prov_id }}">{{ $provinsi->prov_name }}</option>
                  @endforeach
                </select>
                <input type="hidden" name="text_provinsi">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label class="cust-label" for="kota">Kota / Kabupaten</label>
                <select class="js-select2 form-control" id="kota" name="kota" placeholder="Pilih kota">
                  <option value="">Pilih kota</option>
                </select>
                <input type="hidden" name="text_kota">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label class="cust-label" for="kecamatan">Kecamatan</label>
                <select class="js-select2 form-control" id="kecamatan" name="kecamatan" placeholder="Pilih kecamatan">
                  <option value="">Pilih kecamatan</option>
                </select>
                <input type="hidden" name="text_kecamatan">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label class="cust-label" for="kelurahan">Kelurahan</label>
                <select class="js-select2 form-control" id="kelurahan" name="kelurahan" placeholder="Pilih kelurahan">
                  <option value="">Pilih kelurahan</option>
                </select>
                <input type="hidden" name="text_kelurahan">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label class="cust-label" for="zipcode">Kode Pos</label>
                <select class="js-select2 form-control" id="zipcode" name="zipcode" placeholder="Pilih kode pos">
                  <option value="">Pilih kode pos</option>
                </select>
                <input type="hidden" name="text_zipcode">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label class="cust-label" for="zone">Zona</label>
                <select class="form-control js-select2" id="zone" name="zone">
                  <option value="">Pilih zona</option>
                  @foreach(\App\Entities\Master\CustomerOtherAddress::ZONING AS $zoning)
                  <option value="{{ $zoning }}">{{ $zoning }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label class="cust-label" for="gps_latitude">GPS Latitude</label>
                <input type="text" name="gps_latitude" id="gps_latitude" placeholder="Latitude" class="form-control">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="cust-label" for="gps_longitude">GPS Longitude</label>
                <input type="text" name="gps_longitude" id="gps_longitude" placeholder="Longitude" class="form-control">
              </div>
            </div>
          </div>
        </div>

        </section>
        <section class="cust-fieldset" data-step="2">
        <div class="cust-card">
          <div class="cust-card-head">
            <div class="cust-card-icon"><i class="mdi mdi-currency-usd"></i></div>
            <div>
              <h5>Identitas Pajak</h5>
              <p>Data KTP dan NPWP member.</p>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label class="cust-label" for="name_card_ktp">KTP</label>
                <div class="form-row">
                  <div class="col">
                    <input type="text" class="form-control" id="name_card_ktp" name="name_card_ktp" placeholder="Nama pada KTP">
                  </div>
                  <div class="col">
                    <input type="text" class="form-control" name="ktp" placeholder="Nomor KTP">
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="cust-label" for="name_card_npwp">NPWP</label>
                <div class="form-row">
                  <div class="col">
                    <input type="text" class="form-control" id="name_card_npwp" name="name_card_npwp" placeholder="Nama pada NPWP">
                  </div>
                  <div class="col">
                    <input type="text" class="form-control" name="npwp" placeholder="Nomor NPWP">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        </section>
        <section class="cust-fieldset" data-step="3">
        <div class="cust-card">
          <div class="cust-card-head">
            <div class="cust-card-icon"><i class="mdi mdi-file-document-box"></i></div>
            <div>
              <h5>Dokumen</h5>
              <p>Unggah foto KTP dan NPWP bila tersedia.</p>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label class="cust-label" for="image_ktp">Foto KTP</label>
                <input type="file" id="image_ktp" name="image_ktp" data-max-file-size="2000" accept="image/png, image/jpeg">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="cust-label" for="image_npwp">Foto NPWP</label>
                <input type="file" id="image_npwp" name="image_npwp" data-max-file-size="2000" accept="image/png, image/jpeg">
              </div>
            </div>
          </div>
        </div>

        </section>
        <div class="cust-actions">
          <span class="cust-actions-note">Field bertanda <span class="text-danger">*</span> wajib diisi.</span>
          <a href="{{ route('superuser.master.customer.index') }}" class="btn btn-warning text-white"><i class="fa fa-arrow-left"></i> Batal</a>
          <button type="button" class="btn btn-light cust-prev" style="display:none;"><i class="fa fa-arrow-left"></i> Sebelumnya</button>
          <button type="button" class="btn btn-primary cust-next">Lanjut <i class="fa fa-arrow-right"></i></button>
          <button type="submit" class="btn btn-primary btn-submit cust-submit"><i class="fa fa-save"></i> Simpan Member</button>
        </div>
    </form>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.fileinput')
@include('superuser.asset.plugin.select2')

@push('scripts')
  <script src="{{ asset('superuser_assets/js/customer-form.js') }}?v=20261110b"></script>
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
@endpush
