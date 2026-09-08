@extends('superuser.app')

@push('styles')
<style>
  @include('superuser.gudang.shared._doc_styles')
</style>
@endpush

@section('content')
<nav class="breadcrumb bg-white py-10" style="margin-bottom:8px;">
  <span class="breadcrumb-item">Gudang</span>
  <a class="breadcrumb-item" href="{{ route('superuser.gudang.receiving.index') }}">Receiving</a>
  <span class="breadcrumb-item active">New</span>
</nav>
<div id="alert-block"></div>
<div class="block po-main-card">
  <div class="block-content po-main-inner">
    <div class="po-section-label" style="margin-top:10px;">Receiving Baru</div>
    <form class="ajax po-form" data-action="{{ route('superuser.gudang.receiving.store') }}" data-type="POST" enctype="multipart/form-data">
      <div class="form-group">
        <label class="field-label" for="code">Code<span class="req">*</span></label>
        <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)" placeholder="Kode receiving...">
      </div>
      <div class="form-group">
        <label class="field-label" for="warehouse">Warehouse<span class="req">*</span></label>
        <select class="js-select2 form-control" id="warehouse" name="warehouse" data-placeholder="— Pilih warehouse —" style="width:100%;">
          <option></option>
          @foreach($warehouses as $warehouse)
          <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-row">
        <div class="form-group col-md-6">
          <label class="field-label" for="pbm_date">PBM Date</label>
          <input type="date" class="form-control" id="pbm_date" name="pbm_date">
        </div>
        <div class="form-group col-md-6">
          <label class="field-label" for="note">Note</label>
          <textarea class="form-control" id="note" name="note" rows="1" placeholder="Catatan..."></textarea>
        </div>
      </div>
      <div class="form-row pt-10">
        <div class="col-md-6">
          <a href="{{ route('superuser.gudang.receiving.index') }}" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left mr-5"></i>Back</a>
        </div>
        <div class="col-md-6 text-right">
          <button type="submit" class="btn btn-sm btn-primary">Next<i class="fa fa-arrow-right ml-5"></i></button>
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
