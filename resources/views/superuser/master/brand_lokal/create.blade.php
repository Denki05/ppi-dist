@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.brand_lokal.index') }}">Brand PPI</a>
  <span class="breadcrumb-item active">Create</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Create Brand PPI</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.master.brand_lokal.store') }}" data-type="POST" enctype="multipart/form-data">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="brand_name">Brand Name <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="brand_name" name="brand_name" data-placeholder="Select Brand Name">
              <option value="">==Select Brand Name==</option>
              <option value="Senses">Senses</option>
              <option value="GCF">GCF</option>
              <option value="PPI - FF">PPI - FF</option>
              <option value="PPI - Non FF">PPI - Non FF</option>
            </select>
        </div>
      </div>
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.master.brand_lokal.index') }}">
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

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
@endpush
