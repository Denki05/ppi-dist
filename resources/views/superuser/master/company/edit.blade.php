@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.company.show') }}">Company</a>
  <span class="breadcrumb-item active">Edit</span>
</nav>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Edit Company</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.master.company.update') }}" data-type="POST" enctype="multipart/form-data">
      <input type="hidden" name="_method" value="PUT">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="name">Name <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="name" name="name" value="{{ $company->name ?? '' }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="address">Address <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <textarea class="form-control" id="address" name="address">{{ $company->address ?? '' }}</textarea>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Provinsi</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="provinsi" name="provinsi" data-placeholder="Select Provinsi" data-value="{{ $company->provinsi ?? '' }}">
            <option></option>
          </select>
          <input type="hidden" name="text_provinsi">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Kota</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="kota" name="kota" data-placeholder="Select Kota" data-value="{{ $company->kota ?? '' }}">
            <option></option>
          </select>
          <input type="hidden" name="text_kota">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Kecamatan</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="kecamatan" name="kecamatan" data-placeholder="Select Kecamatan" data-value="{{ $company->kecamatan ?? '' }}">
            <option></option>
          </select>
          <input type="hidden" name="text_kecamatan">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Kelurahan</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="kelurahan" name="kelurahan" data-placeholder="Select Kelurahan" data-value="{{ $company->kelurahan ?? '' }}">
            <option></option>
          </select>
          <input type="hidden" name="text_kelurahan">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="zipcode">Zipcode</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="zipcode" name="zipcode" value="{{ $company->zipcode ?? '' }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="phone">Phone</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="phone" name="phone" value="{{ $company->phone ?? '' }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="website">Website</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="website" name="website" value="{{ $company->website ?? '' }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="owner_name">Owner Name</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="owner_name" name="owner_name" value="{{ $company->owner_name ?? '' }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="note">note</label>
        <div class="col-md-7">
          <textarea class="form-control summernote" id="note" name="note" >{{ $company->note ?? '' }}</textarea>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Logo</label>
        <div class="col-md-7">
          <input type="file" id="logo" name="logo" data-max-file-size="2000" accept="image/png, image/jpeg" data-src="{{ $company->logo_url ?? '' }}">
        </div>
      </div>
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.master.company.show') }}">
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
<script type="text/javascript">
$(document).ready(function () {
  summernote = $('.summernote').length;
  if(summernote > 0){
    $('.summernote').summernote({
      toolbar: [
          ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
      ],
    });
  }

  $('#logo').fileinput({
    theme: 'explorer-fa',
    browseOnZoneClick: true,
    showCancel: false,
    showClose: false,
    showUpload: false,
    browseLabel: '',
    removeLabel: '',
    initialPreview: $('#logo').data('src'),
    initialPreviewAsData: true,
    fileActionSettings: {
      showDrag: false,
      showRemove: false
    },
    initialPreviewConfig: [
      {
          caption: '{{ $company->logo ?? '' }}'
      }
    ]
  });

  $('.js-select2').select2()
});
</script>
@endpush
