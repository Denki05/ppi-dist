@extends('superuser.app')

@section('content')

@if($errors->any())
<div class="alert alert-danger alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Error</h3>
  @foreach ($errors->all() as $error)
  <p class="mb-0">{{ $error }}</p>
  @endforeach
</div>
@endif

<div id="alert-block"></div>

@if(session()->has('message'))
<div class="alert alert-success alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Success</h3>
  <p class="mb-0">{{ session()->get('message') }}</p>
</div>
@endif

<div class="block">  
  <div class="block-header block-header-default">
    <h2 class="block-title">#Create Product TAX</h2>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.accounting.product_finance.store') }}" data-type="POST" enctype="multipart/form-data">
    @csrf

        <div class="row mb-30">
          <div class="col-8">
            <div class="form-group row">
              <label for="example-text-input" class="col-2 col-form-label">Brand</label>
              <div class="col-8">
                <select class="form-control js-select2" name="brand">
                    <option value="">Pilih Brand</option>
                    @foreach($brand AS $item)
                    <option value="{{ $item->brand_name }}">{{ $item->brand_name }}</option>
                    @endforeach
                </select>
              </div>
            </div>
            <div class="form-group row">
              <label for="example-text-input" class="col-2 col-form-label">Kode produk</label>
              <div class="col-8">
                <input class="form-control" type="text"  name="kode_produk">
              </div>
            </div>
            <div class="form-group row">
              <label for="example-text-input" class="col-2 col-form-label">Nama produk</label>
              <div class="col-8">
                <input class="form-control" type="text" name="nama_produk">
              </div>
            </div>
            <div class="form-group row">
              <label for="example-text-input" class="col-2 col-form-label">Kemasan</label>
              <div class="col-8">
                <select class="form-control js-select2" name="kemasan">
                    <option value="">Pilih Kemasan</option>
                    @foreach($kemasan AS $item)
                    <option value="{{ $item->id }}">{{ $item->pack_name }}</option>
                    @endforeach
                </select>
              </div>
            </div>
          </div>

          <div class="col-4">
            <div class="form-group row">
              <label for="example-text-input" class="col-6 col-form-label">Mitra</label>
              <div class="col-6">
                <input class="form-control" type="text" value="{{ $mitra->name }}" readonly>
                <input type="hidden" value="{{ $mitra->id }}" name="mitra_id">
              </div>
            </div>
            <div class="form-group row">
              <label for="example-text-input" class="col-6 col-form-label">Harga Beli Satuan(IDR)</label>
              <div class="col-6">
                <input class="form-control" type="text" name="harga_beli_satuan"> 
              </div>
            </div>
            <div class="form-group row">
              <label for="example-text-input" class="col-6 col-form-label">Harga Jual Satuan(IDR)</label>
              <div class="col-6">
                <input class="form-control" type="text" name="harga_jual_satuan">
              </div>
            </div>
          </div>
          <div class="row pt-30 mb-15">
              <div class="col-md-6">
              <a href="{{ route('superuser.accounting.product_finance.show', $mitra->id) }}">
                    <button type="button" class="btn bg-gd-cherry border-0 text-white">
                        <i class="fa fa-arrow-left mr-10"></i> Back
                    </button>
                  </a>
              </div>
              
              <div class="col-md-6 text-right">
                <button type="submit" class="btn bg-gd-corporate border-0 text-white">
                    Save <i class="fa fa-check ml-10"></i>
                </button>
              </div>
          </div>
        </div>
      </form>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function () {
    $('.js-select2').select2();
  });
</script>
@endpush
