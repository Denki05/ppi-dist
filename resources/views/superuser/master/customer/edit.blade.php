@extends('superuser.app')
@php
  $salesColumns = $sales_columns ?? [];
  $customerSmColumn = $salesColumns['customer_sm'] ?? null;
  $customerAsmColumn = $salesColumns['customer_asm'] ?? null;
  $customerPicColumn = $salesColumns['customer_pic'] ?? null;
  $memberSmColumn = $salesColumns['member_sm'] ?? null;
  $memberAsmColumn = $salesColumns['member_asm'] ?? null;
  $memberPicColumn = $salesColumns['member_pic'] ?? null;
  $memberOfficerColumn = $salesColumns['member_officer'] ?? null;
  $memberAr1Column = $salesColumns['member_ar1'] ?? null;
  $memberAr2Column = $salesColumns['member_ar2'] ?? null;
  $memberAr3Column = $salesColumns['member_ar3'] ?? null;
  $smLabel = $customerSmColumn === 'am' ? 'AM' : 'SM';
  $asmLabel = $customerAsmColumn === 'ams' ? 'AMS' : 'ASM';
  $smValue = $customerSmColumn ? $customer->{$customerSmColumn} : null;
  $asmValue = $customerAsmColumn ? $customer->{$customerAsmColumn} : null;
  $picValue = $customerPicColumn ? $customer->{$customerPicColumn} : null;
  $officerValue = ($default_member && $memberOfficerColumn) ? $default_member->{$memberOfficerColumn} : null;
  $ar1Value = ($default_member && $memberAr1Column) ? $default_member->{$memberAr1Column} : null;
  $ar2Value = ($default_member && $memberAr2Column) ? $default_member->{$memberAr2Column} : null;
  $ar3Value = ($default_member && $memberAr3Column) ? $default_member->{$memberAr3Column} : null;
  if ($smValue === null && $default_member && $memberSmColumn) {
      $smValue = $default_member->{$memberSmColumn};
  }
  if ($asmValue === null && $default_member && $memberAsmColumn) {
      $asmValue = $default_member->{$memberAsmColumn};
  }
  if ($picValue === null && $default_member && $memberPicColumn) {
      $picValue = $default_member->{$memberPicColumn};
  }
  $phone = explode(',', (string) $customer->phone);
  $ktp = explode('/', (string) $customer->ktp);
  $npwp = explode('/', (string) $customer->npwp);
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('superuser_assets/css/customer-form.css') }}?v=20261110d">
@endpush

@section('content')
<div id="alert-block"></div>
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.customer.index') }}">Store</a>
  <a class="breadcrumb-item" href="{{ route('superuser.master.customer.show', $customer->id) }}">{{ $customer->name }}</a>
  <span class="breadcrumb-item active">Edit</span>
</nav>

<div class="block">
  <div class="block-content-block">
    <form data-action="{{ route('superuser.master.customer.update', $customer->id) }}" data-type="POST" enctype="multipart/form-data" class="cust-form ajax">
      <input type="hidden" name="_method" value="PUT">
      <div class="cust-hero">
        <div class="cust-hero-icon"><i class="mdi mdi-store"></i></div>
        <div>
          <h4>Edit Store &mdash; {{ $customer->name }}</h4>
          <p>Perubahan pada sales &amp; AR ikut memperbarui member default.</p>
        </div>
        <span class="cust-hero-badge">Kode {{ $customer->code }}</span>
      </div>

      <div class="cust-stepper">
        <div class="cust-progress-line" style="width: 0;"></div>
        <div class="cust-step active" data-step="0">
          <div class="cust-step-icon"><i class="mdi mdi-store"></i></div>
          <p>Identitas Store</p>
        </div>
        <div class="cust-step" data-step="1">
          <div class="cust-step-icon"><i class="mdi mdi-map-marker"></i></div>
          <p>Alamat & Wilayah</p>
        </div>
        <div class="cust-step" data-step="2">
          <div class="cust-step-icon"><i class="mdi mdi-account-multiple"></i></div>
          <p>Sales &amp; AR</p>
        </div>
        <div class="cust-step" data-step="3">
          <div class="cust-step-icon"><i class="mdi mdi-currency-usd"></i></div>
          <p>Keuangan</p>
        </div>
        <div class="cust-step" data-step="4">
          <div class="cust-step-icon"><i class="mdi mdi-file-document-box"></i></div>
          <p>Dokumen</p>
        </div>
      </div>
        <section class="cust-fieldset active" data-step="0">
        <div class="cust-card">
          <div class="cust-card-head">
            <div class="cust-card-icon"><i class="mdi mdi-account"></i></div>
            <div>
              <h5>Identitas Store</h5>
              <p>Nama, kategori, dan kontak utama store.</p>
            </div>
          </div>
          <div class="row">
            <div class="col-md-8">
              <div class="form-group">
                <label class="cust-label" for="name">Nama Store <span class="req">*</span></label>
                <input type="text" id="name" name="name" placeholder="Nama Store" class="form-control" value="{{ $customer->name }}">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label class="cust-label" for="category">Kategori <span class="req">*</span></label>
                <select class="js-select2 form-control" id="category" name="category" placeholder="Pilih kategori store">
                  <option value="">Pilih kategori</option>
                  @foreach($category as $rowCategory)
                  <option value="{{ $rowCategory->id }}" {{ ($rowCategory->id == $customer->category_id) ? 'selected' : '' }}>{{ $rowCategory->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label class="cust-label" for="owner_name">Owner</label>
                <input type="text" id="owner_name" name="owner_name" placeholder="Nama Owner" class="form-control" value="{{ $customer->owner_name }}">
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label class="cust-label" for="phone1">Telepon 1</label>
                <input type="text" class="form-control" id="phone1" name="phone1" value="{{ $phone[0] ?? '' }}" placeholder="08xxxxxxxxxx">
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label class="cust-label" for="phone2">Telepon 2</label>
                <input type="text" class="form-control" id="phone2" name="phone2" value="{{ $phone[1] ?? '' }}" placeholder="Opsional">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label class="cust-label" for="email">Email</label>
                <input type="text" id="email" name="email" placeholder="Email" class="form-control" value="{{ $customer->email }}">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="cust-label" for="website">Web / Sosmed</label>
                <input type="text" id="website" name="website" placeholder="Website / Sosmed" class="form-control" value="{{ $customer->website }}">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label class="cust-label" for="image_store">Foto Store</label>
                <input class="form-control" id="image_store" name="image_store" type="file" accept="image/png, image/jpeg">
                <small class="cust-help">Biarkan kosong jika tidak ingin mengganti gambar.</small>
                @if($customer->image_store)
                <img src="{{ $customer->img_store }}" alt="Foto store" class="cust-current-img">
                @endif
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
              <p>Alamat pengiriman dan titik lokasi store.</p>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label class="cust-label" for="provinsi">Provinsi</label>
                <select class="js-select2 form-control" id="provinsi" name="provinsi" placeholder="Pilih provinsi">
                  <option value="">Pilih provinsi</option>
                  @foreach ($provinces as $provinsi)
                  <option value="{{ $provinsi->prov_id }}" {{ ($provinsi->prov_id == $customer->provinsi) ? 'selected' : '' }}>{{ $provinsi->prov_name }}</option>
                  @endforeach
                </select>
                <input type="hidden" name="text_provinsi" value="{{ $customer->text_provinsi }}">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label class="cust-label" for="kota">Kota / Kabupaten</label>
                <select class="js-select2 form-control" id="kota" name="kota" placeholder="Pilih kota">
                  <option value="{{ $customer->kota }}">{{ $customer->text_kota }}</option>
                </select>
                <input type="hidden" name="text_kota" value="{{ $customer->text_kota }}">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label class="cust-label" for="kecamatan">Kecamatan</label>
                <select class="js-select2 form-control" id="kecamatan" name="kecamatan" placeholder="Pilih kecamatan">
                  <option value="{{ $customer->kecamatan }}">{{ $customer->text_kecamatan }}</option>
                </select>
                <input type="hidden" name="text_kecamatan" value="{{ $customer->text_kecamatan }}">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label class="cust-label" for="kelurahan">Kelurahan</label>
                <select class="js-select2 form-control" id="kelurahan" name="kelurahan" placeholder="Pilih kelurahan">
                  <option value="{{ $customer->kelurahan }}">{{ $customer->text_kelurahan }}</option>
                </select>
                <input type="hidden" name="text_kelurahan" value="{{ $customer->text_kelurahan }}">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label class="cust-label" for="zipcode">Kode Pos</label>
                <select class="js-select2 form-control" id="zipcode" name="zipcode" placeholder="Pilih kode pos">
                  <option value="{{ $customer->zipcode }}">{{ $customer->zipcode }}</option>
                </select>
                <input type="hidden" name="text_zipcode" value="{{ $customer->zipcode }}">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label class="cust-label" for="zone">Zona</label>
                <select class="form-control js-select2" id="zone" name="zone">
                  <option value="">Pilih zona</option>
                  @foreach(\App\Entities\Master\CustomerOtherAddress::ZONING AS $zoning)
                  <option value="{{ $zoning }}" {{ $customer->zone == $zoning ? 'selected' : '' }}>{{ $zoning }}</option>
                  @endforeach
                  @if($customer->zone && !in_array($customer->zone, \App\Entities\Master\CustomerOtherAddress::ZONING))
                  <option value="{{ $customer->zone }}" selected>{{ $customer->zone }}</option>
                  @endif
                </select>
                <small class="cust-help">Dipakai untuk pengelompokan rute / gudang.</small>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <div class="form-group">
                <label class="cust-label" for="address">Alamat Lengkap <span class="req">*</span></label>
                <textarea class="form-control" id="address" name="address" rows="3">{{ $customer->address }}</textarea>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label class="cust-label" for="gps_latitude">GPS Latitude <span class="req">*</span></label>
                <input type="text" name="gps_latitude" id="gps_latitude" placeholder="Latitude" class="form-control" value="{{ $customer->gps_latitude }}">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="cust-label" for="gps_longitude">GPS Longitude <span class="req">*</span></label>
                <input type="text" name="gps_longitude" id="gps_longitude" placeholder="Longitude" class="form-control" value="{{ $customer->gps_longitude }}">
              </div>
            </div>
          </div>
        </div>

        </section>
        <section class="cust-fieldset" data-step="2">
        <div class="cust-card">
          <div class="cust-card-head">
            <div class="cust-card-icon"><i class="mdi mdi-account-multiple"></i></div>
            <div>
              <h5>Sales &amp; Account Representative</h5>
              <p>
                Disinkronkan ke member default
                @if($default_member)
                <span class="badge badge-success">{{ $default_member->id }}</span>
                @else
                <span class="badge badge-warning">belum ada member default</span>
                @endif
                &middot; member non-default tidak ikut berubah.
              </p>
            </div>
          </div>
          <div class="cust-subhead">Tim Sales</div>
          <div class="row">
            <div class="col-md-3">
              <div class="form-group">
                <label class="cust-label" for="sm_am">{{ $smLabel }}</label>
                <input type="text" id="sm_am" name="sm_am" placeholder="Nama {{ $smLabel }}" class="form-control" value="{{ $smValue }}">
                <small class="cust-help">Kolom aktif: {{ $customerSmColumn ?: 'tidak tersedia' }}</small>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label class="cust-label" for="asm">{{ $asmLabel }}</label>
                <input type="text" id="asm" name="asm" placeholder="Nama {{ $asmLabel }}" class="form-control" value="{{ $asmValue }}">
                <small class="cust-help">Kolom aktif: {{ $customerAsmColumn ?: 'tidak tersedia' }}</small>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label class="cust-label" for="pic">PIC</label>
                <input type="text" id="pic" name="pic" placeholder="Nama PIC" class="form-control" value="{{ $picValue }}">
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label class="cust-label" for="officer">Officer</label>
                <input type="text" id="officer" name="officer" placeholder="Nama Officer" class="form-control" value="{{ $officerValue }}">
              </div>
            </div>
          </div>
          <div class="cust-subhead">Account Representative</div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label class="cust-label" for="ar1">AR1</label>
                <input type="text" id="ar1" name="ar1" placeholder="Account Representative 1" class="form-control" value="{{ $ar1Value }}">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label class="cust-label" for="ar2">AR2</label>
                <input type="text" id="ar2" name="ar2" placeholder="Account Representative 2" class="form-control" value="{{ $ar2Value }}">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label class="cust-label" for="ar3">AR3</label>
                <input type="text" id="ar3" name="ar3" placeholder="Account Representative 3" class="form-control" value="{{ $ar3Value }}">
              </div>
            </div>
          </div>
        </div>

        </section>
        <section class="cust-fieldset" data-step="3">
        <div class="cust-card">
          <div class="cust-card-head">
            <div class="cust-card-icon"><i class="mdi mdi-currency-usd"></i></div>
            <div>
              <h5>Keuangan</h5>
              <p>Plafon piutang, pajak, tempo, dan data kartu identitas.</p>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label class="cust-label" for="plafon_piutang">Plafon Piutang <span class="req">*</span></label>
                <input type="number" class="form-control" id="plafon_piutang" name="plafon_piutang" min="0" step="0.0001" value="{{ $customer->plafon_piutang }}">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label class="cust-label" for="tempo_limit">Tempo Limit (hari)</label>
                <input class="form-control" id="tempo_limit" value="{{ $customer->tempo_limit }}" name="tempo_limit" placeholder="Jumlah hari" @if($customer->has_tempo != '1') disabled @endif>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label class="cust-label">Status Pajak &amp; Tempo</label>
                <div class="cust-check-row">
                  <div class="cust-check">
                    <input type="checkbox" value="1" id="has_ppn" name="has_ppn" @if($customer->has_ppn == '1') checked @endif>
                    <label for="has_ppn">Customer PPN</label>
                  </div>
                  <div class="cust-check">
                    <input type="checkbox" value="1" id="has_tempo" name="has_tempo" @if($customer->has_tempo == '1') checked @endif>
                    <label for="has_tempo">Customer Tempo</label>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label class="cust-label" for="name_card_ktp">KTP</label>
                <div class="form-row">
                  <div class="col">
                    <input type="text" class="form-control" id="name_card_ktp" name="name_card_ktp" placeholder="Nama pada KTP" value="{{ $ktp[0] ?? '' }}">
                  </div>
                  <div class="col">
                    <input type="text" class="form-control" name="ktp" placeholder="Nomor KTP" value="{{ $ktp[1] ?? '' }}">
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="cust-label" for="name_card_npwp">NPWP</label>
                <div class="form-row">
                  <div class="col">
                    <input type="text" class="form-control" id="name_card_npwp" name="name_card_npwp" placeholder="Nama pada NPWP" value="{{ $npwp[0] ?? '' }}">
                  </div>
                  <div class="col">
                    <input type="text" class="form-control" name="npwp" placeholder="Nomor NPWP" value="{{ $npwp[1] ?? '' }}">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        </section>
        <section class="cust-fieldset" data-step="4">
        <div class="cust-card">
          <div class="cust-card-head">
            <div class="cust-card-icon"><i class="mdi mdi-file-document-box"></i></div>
            <div>
              <h5>Dokumen</h5>
              <p>Unggah ulang hanya jika ingin mengganti dokumen.</p>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label class="cust-label" for="image_ktp">Foto KTP</label>
                <input type="file" id="image_ktp" name="image_ktp" data-max-file-size="2000" accept="image/png, image/jpeg">
                @if($customer->image_ktp)
                <img src="{{ $customer->img_ktp }}" alt="Foto KTP" class="cust-current-img">
                @endif
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label class="cust-label" for="image_npwp">Foto NPWP</label>
                <input type="file" id="image_npwp" name="image_npwp" data-max-file-size="2000" accept="image/png, image/jpeg">
                @if($customer->image_npwp)
                <img src="{{ $customer->img_npwp }}" alt="Foto NPWP" class="cust-current-img">
                @endif
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
          <button type="submit" class="btn btn-primary btn-submit cust-submit"><i class="fa fa-save"></i> Simpan Perubahan</button>
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

    $('#has_tempo').on('change', function () {
        $('#tempo_limit').prop('disabled', !this.checked);
    });
  })
</script>
@endpush
