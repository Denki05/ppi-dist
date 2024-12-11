@extends('superuser.app')

@section('content')

<div class="row">
  <div class="col-6">
    <div class="block">
      <div class="block-header block-header-default">
        <h3 class="block-title">#Detail Nota</h3>
      </div>
      <div class="block-content">
        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="so_date">Tanggal Nota</label>
            <input type="date" name="so_date" class="form-control" value="{{ $result->so_date }}" readonly>
          </div>
          <div class="form-group col-md-6">
            <label for="invoice_code">Nomer Nota</label>
            <input type="text" class="form-control" id="invoice_code" value="{{ $result->code }}" readonly>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="so_date">Transaksi</label>
            <input type="text" name="type_transaksi" class="form-control" value="{{ $result->type_transaction }}" readonly>
          </div>
          <div class="form-group col-md-6">
            <label for="customer_area">Provinsi</label>
            <input type="text" name="customer_area" class="form-control" value="{{ $result->member->text_provinsi }} " readonly>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-md-12">
            <label for="warehouse_id">Catatan <span class="text-danger">*</span></label>
            <textarea class="form-control" name="note" rows="1" readonly>{{ $result->note }}</textarea>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-6">
    <div class="block">
      <div class="block-header block-header-default">
        <h3 class="block-title">#Customer</h3>
      </div>
      <div class="block-content">
        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="so_date">Customer</label>
            <input type="text" name="customer" class="form-control" value="{{ $result->member->name }} {{$result->member->text_kota}}" readonly>
          </div>
          <div class="form-group col-md-6">
            <label for="invoice_code">Alamat Kirim</label>
            <textarea class="form-control" rows="1" readonly>{{ $result->member->address }}</textarea>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="so_date">Kota</label>
            <input type="text" name="customer_city" class="form-control" value="{{$result->member->text_kota}}" readonly>
          </div>
          <div class="form-group col-md-6">
            <label for="invoice_code">No. Dokumen</label>
            <input type="text" class="form-control" id="invoice_code" value="{{ $result->no_ducument_ppn ?? '-' }}" readonly>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $('.js-select2').select2()

    function delay(fn, ms) {
      let timer = 0
      return function(...args) {
        clearTimeout(timer)
        timer = setTimeout(fn.bind(this, ...args), ms || 0)
      }
    }
  });
</script>
@endpush
