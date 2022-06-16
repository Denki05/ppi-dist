@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.customer.index') }}">Member</a>
  <a class="breadcrumb-item" href="{{ route('superuser.master.customer.show', $customer->id) }}">{{ $customer->id }}</a>
  <span class="breadcrumb-item active">Edit</span>
</nav>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Edit Member</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.master.customer.update', $customer) }}" data-type="POST" enctype="multipart/form-data">
      <input type="hidden" name="_method" value="PUT">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">Code <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)" value="{{ $customer->code }}" disabled>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="name">Name <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="name" name="name" value="{{ $customer->name }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="category">Category <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="category" name="category" data-placeholder="Select Category">
            <option></option>
            @foreach($customer_categories as $category)
            <option value="{{ $category->id }}" {{ ($category->id == $customer->category_id) ? 'selected' : '' }}>{{ $category->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="type">Type <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="type" name="type[]" data-placeholder="Select Type" multiple>
            <option></option>
            @foreach($customer_types as $type)
            <option value="{{ $type->id }}">{{ $type->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="email">Email</label>
        <div class="col-md-6">
          <input type="email" class="form-control" id="email" name="email" value="{{ $customer->email }}">
        </div>
        <div class="col-md-1 text-center">
          <label class="css-control css-control-primary css-checkbox">
            <input type="checkbox" class="css-control-input" name="notification_email" {{ ($customer->notification_email) ? 'checked' : '' }}>
            <span class="css-control-indicator"></span>
          </label>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="phone">Phone</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="phone" name="phone" value="{{ $customer->phone }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="ktp">KTP</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="ktp" name="ktp" value="{{ $customer->ktp }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="npwp">NPWP</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="npwp" name="npwp" value="{{ $customer->npwp }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="address">Address <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <textarea class="form-control" id="address" name="address">{{ $customer->address }}</textarea>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="website">Website</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="website" name="website" value="{{ $customer->website }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="plafon_piutang">Plafon Piutang <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="number" class="form-control" id="plafon_piutang" name="plafon_piutang" min="0" value="{{ $customer->plafon_piutang }}" step="0.0001">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">GPS Coordinate</label>
        <div class="col-md-3">
          <input type="text" class="form-control" id="gps_latitude" name="gps_latitude" placeholder="Latitude" value="{{ $customer->gps_latitude }}">
        </div>
        <div class="col-md-1"></div>
        <div class="col-md-3">
          <input type="text" class="form-control" id="gps_longitude" name="gps_longitude" placeholder="Longitude" value="{{ $customer->gps_longitude }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Provinsi</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="provinsi" name="provinsi" data-placeholder="Select Provinsi" data-value="{{ $customer->provinsi }}">
            <option></option>
          </select>
          <input type="hidden" name="text_provinsi">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Kota</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="kota" name="kota" data-placeholder="Select Kota" data-value="{{ $customer->kota }}">
            <option></option>
          </select>
          <input type="hidden" name="text_kota">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Kecamatan</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="kecamatan" name="kecamatan" data-placeholder="Select Kecamatan" data-value="{{ $customer->kecamatan }}">
            <option></option>
          </select>
          <input type="hidden" name="text_kecamatan">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Kelurahan</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="kelurahan" name="kelurahan" data-placeholder="Select Kelurahan" data-value="{{ $customer->kelurahan }}">
            <option></option>
          </select>
          <input type="hidden" name="text_kelurahan">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="zipcode">Zipcode</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="zipcode" name="zipcode" value="{{ $customer->zipcode }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Store</label>
        <div class="col-md-7">
          <input type="file" id="image_store" name="image_store" data-max-file-size="2000" accept="image/png, image/jpeg" data-src="{{ $customer->img_store }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">KTP</label>
        <div class="col-md-7">
          <input type="file" id="image_ktp" name="image_ktp" data-max-file-size="2000" accept="image/png, image/jpeg" data-src="{{ $customer->img_ktp }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">NPWP</label>
        <div class="col-md-7">
          <input type="file" id="image_npwp" name="image_npwp" data-max-file-size="2000" accept="image/png, image/jpeg" data-src="{{ $customer->img_ktp }}">
        </div>
      </div>
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.master.customer.show', $customer->id) }}">
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

@include('superuser.asset.plugin.fileinput')
@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.select2-chain-indonesian-teritory')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {
    $('#image_store').fileinput({
      theme: 'explorer-fa',
      browseOnZoneClick: true,
      showCancel: false,
      showClose: false,
      showUpload: false,
      browseLabel: '',
      removeLabel: '',
      initialPreview: $('#image_store').data('src'),
      initialPreviewAsData: true,
      fileActionSettings: {
        showDrag: false,
        showRemove: false
      },
      initialPreviewConfig: [
      {
          caption: '{{ $customer->image_store }}'
      }
    ]
    });

    $('#image_ktp').fileinput({
      theme: 'explorer-fa',
      browseOnZoneClick: true,
      showCancel: false,
      showClose: false,
      showUpload: false,
      browseLabel: '',
      removeLabel: '',
      initialPreview: $('#image_ktp').data('src'),
      initialPreviewAsData: true,
      fileActionSettings: {
        showDrag: false,
        showRemove: false
      },
      initialPreviewConfig: [
      {
          caption: '{{ $customer->image_ktp }}'
      }
    ]
    });

    $('#image_npwp').fileinput({
      theme: 'explorer-fa',
      browseOnZoneClick: true,
      showCancel: false,
      showClose: false,
      showUpload: false,
      browseLabel: '',
      removeLabel: '',
      initialPreview: $('#image_npwp').data('src'),
      initialPreviewAsData: true,
      fileActionSettings: {
        showDrag: false,
        showRemove: false
      },
      initialPreviewConfig: [
      {
          caption: '{{ $customer->image_npwp }}'
      }
    ]
    });

    $('.js-select2').select2()

    $('.js-select2#type').val({{ json_encode($customer->types->pluck('id')->toArray()) }}).change()
  })
</script>
@endpush
