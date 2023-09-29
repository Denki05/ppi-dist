@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Purchasing</span>
  <span class="breadcrumb-item">Receiving</span>
  <span class="breadcrumb-item">{{ $receiving->code }}</span>
  <span class="breadcrumb-item">{{ $receiving_detail->id }}</span>
  <span class="breadcrumb-item active">Add Qty Colly</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Add Qty Colly</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.gudang.receiving.detail.colly.store', [$receiving->id, $receiving_detail->id]) }}" data-type="POST" enctype="multipart/form-data">
      {{-- <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">Barcode</label>
        <div class="col-md-4">
          <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)" value="{{ App\Repositories\ReceivingDetailCollyRepo::generateCode() }}" readonly>
        </div>
      </div> --}}
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="colly">Colly</label>
        <div class="col-md-4">
          <input type="number" class="form-control" id="colly" name="colly">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="ri">Qty</label>
        <div class="col-md-4">
          <input type="number" class="form-control" id="ri" name="ri" step="any">
        </div>
      </div>
      
      <hr>
      {{--<div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="is_reject">Is Rejected?</label>
        <div class="col-md-7">
          <div class="form-check">
            <input class="form-check-input" style="margin-top: 10px" type="checkbox" id="is_reject" name="is_reject">
          </div>
        </div>
      </div>--}}
      
      
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.gudang.receiving.detail.show', [$receiving->id, $receiving_detail->id]) }}">
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

@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {

    $('.js-select2').select2()
  })
</script>
@endpush
