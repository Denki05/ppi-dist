@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Purchasing</span>
  <a class="breadcrumb-item" href="{{ route('superuser.gudang.receiving.index') }}">Receiving</a>
  <span class="breadcrumb-item active">Edit</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Edit Receiving</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.gudang.receiving.update', $receiving->id) }}" data-type="POST" enctype="multipart/form-data">
      <input type="hidden" name="_method" value="PUT">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">Code <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)" value="{{ $receiving->code }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="warehouse">Warehouse <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="warehouse" name="warehouse" data-placeholder="Select Warehouse" disabled>
            <option value="{{ $receiving->warehouse_id }}">{{ $receiving->warehouse->name }}</option>
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="warehouse_disp">Warehouse <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="warehouse_disp" name="warehouse_disp" data-placeholder="Select Warehouse" disabled>
            <option value="{{ $receiving->warehouse_disp }}">{{ $receiving->warehouse->name }}</option>
          </select>
        </div>
      </div>
      
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="pbm_date">PBM Date</span></label>
        <div class="col-md-7">
          <input type="date" class="form-control" id="pbm_date" name="pbm_date" value="{{ $receiving->pbm_date ? date('Y-m-d', strtotime($receiving->pbm_date)) : '' }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="note">Note</span></label>
        <div class="col-md-7">
          <textarea class="form-control" id="note" name="note">{{ $receiving->note }}</textarea>
        </div>
      </div>
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.gudang.receiving.step', ['id' => $receiving->id]) }}">
            <button type="button" class="btn bg-gd-cherry border-0 text-white">
              <i class="fa fa-arrow-left mr-10"></i> Back
            </button>
          </a>
        </div>
        <div class="col-md-6 text-right">
          <button type="submit" class="btn bg-gd-corporate border-0 text-white">
            Next <i class="fa fa-arrow-right ml-10"></i>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {
    $('.js-select2').select2()
  })
</script>
@endpush