@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.vendor.index') }}">Vendor</a>
  <a class="breadcrumb-item" href="{{ route('superuser.master.vendor.show', $vendor->id) }}">{{ $vendor->name }}</a>
  <span class="breadcrumb-item">Transaction History</span>
  <span class="breadcrumb-item active">Create</span>
</nav>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Transaction History</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.master.vendor.detail.store', $vendor->id) }}" data-type="POST" enctype="multipart/form-data">
    <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="transaction">Transaction Name <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="transaction" name="transaction">
        </div>
      </div> 
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="quantity">Quantity </label>
        <div class="col-md-7">
          <input type="number" class="form-control" id="quantity" name="quantity">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Unit</label>
        <div class="col-md-7">
          <select class="form-control" id="satuan" name="satuan" data-placeholder="Select Unit">
              <option>Select Unit</option>
              <option value="1">Pcs</option>
              <option value="2">Lembar</option>
              <option value="3">Lusin</option>
              <option value="4">Liter</option>
              <option value="5">KG</option>
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="grand_total">Grand Total </label>
        <div class="col-md-7">
          <input type="numeric" class="form-control" id="grand_total" name="grand_total" />
        </div>
      </div>
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.master.vendor.index') }}">
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

@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {
    $('.js-select2').select2()

    // var grand_total = document.getElementById('grand_total');
    // grand_total.addEventListener('keyup', function(e)
    // {
    //   grand_total.value = formatRupiah(this.value);
    // });

    // function formatRupiah(angka, prefix)
    // {
    //     var number_string = angka.replace(/[^,\d]/g, '').toString(),
    //         split    = number_string.split(','),
    //         sisa     = split[0].length % 3,
    //         rupiah     = split[0].substr(0, sisa),
    //         ribuan     = split[0].substr(sisa).match(/\d{3}/gi);
            
    //     if (ribuan) {
    //         separator = sisa ? '.' : '';
    //         rupiah += separator + ribuan.join('.');
    //     }
        
    //     rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    //     return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
    // }
  })
</script>
@endpush
