@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.contact.index') }}">Contact</a>
  <span class="breadcrumb-item active">Create</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Create Contact</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.master.contact.store') }}" data-type="POST" enctype="multipart/form-data">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="name">Name <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="name" name="name">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="member">Member <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="member" name="member" data-placeholder="Select Member">
            <option></option>
            @foreach($contacts as $contact)
            <option value="{{ $contact->id }}">{{ $contact->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="phone">Phone <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="phone" name="phone">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="email">Email </label>
        <div class="col-md-7">
          <input type="email" class="form-control" id="email" name="email">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="position">Position </label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="position" name="position">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="dob">DOB </label>
        <div class="col-md-7">
          <input type="text" class="js-flatpickr form-control bg-white" placeholder="d-m-Y" data-date-format="d-m-Y" id="dob" name="dob">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="npwp">NPWP </label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="npwp" name="npwp">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="ktp">KTP </label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="ktp" name="ktp">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="address">Address</label>
        <div class="col-md-7">
          <textarea class="form-control" id="address" name="address"></textarea>
        </div>
      </div>
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.master.contact.index') }}">
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
@endsection

@include('superuser.asset.plugin.flatpickr')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
    Codebase.helpers('flatpickr')

  $('.js-select2').select2()
  })
</script>
@endpush
