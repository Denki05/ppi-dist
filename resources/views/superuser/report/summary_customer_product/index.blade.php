@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Report</span>
  <span class="breadcrumb-item">Operasional</span>
  <span class="breadcrumb-item">Customer</span>
  <span class="breadcrumb-item active">Summary Customer - Produk</span>
</nav>

@if(session('error') || session('success'))
<div class="alert alert-{{ session('error') ? 'danger' : 'success' }} alert-dismissible fade show" role="alert">
    @if (session('error'))
    <strong>Error!</strong> {!! session('error') !!}
    @elseif (session('success'))
    <strong>Berhasil!</strong> {!! session('success') !!}
    @endif
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<form action="{{ route('superuser.report.summary_customer_product.print_report') }}" method="POST">
  @csrf

  <div class="row">
    <div class="col-10">
      <div class="block">
        <div class="block-content">
            <div class="form-group row">
                <label class="col-md-2 col-form-label text-left" for="customer[]">Customer:</label>
                <div class="col-md-4">
                    <select class="js-select2 form-control" id="customer" name="customer[]" data-placeholder="Select Customer" multiple required>
                        <option value="all">All</option>
                        @foreach($customers as $row)
                        <option value="{{ $row->id }}">{{ $row->name }} {{ $row->text_kota }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 align-self-center">
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" name="non_bulan" value="1" id="nominal_show" onclick="handleClick(this);">
                  <label class="form-check-label" for="nominal_show">Non Bulan</label>
                </div>
              </div>
            </div>

            <div class="form-group row">
                <label class="col-md-2 col-form-label text-left" for="brand">Brand:</label>
                <div class="col-md-4">
                    <select class="js-select2 form-control" id="brand" name="brand[]" data-placeholder="Select Product" multiple>
                        <option value="all">All</option>
                        @foreach($brand as $row)
                        <option value="{{ $row->brand_name }}">{{ $row->brand_name }}</option>
                        @endforeach
                    </select>
                </div>
                <label class="col-md-2 col-form-label text-left" for="product">Product:</label>
                <div class="col-md-4">
                    <select class="js-select2 form-control" id="product" name="product[]" data-placeholder="Select Product" multiple>
                        <option value="all">All</option>
                        @foreach($product as $row)
                        <option value="{{ $row->id }}">{{ $row->code }} - {{ $row->name }} / {{ $row->packaging->pack_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-md-2 col-form-label text-left" for="start">Periode From:</label>
                <div class="col-md-4">
                    <input type="date" class="form-control" id="start_date" name="start" required value="{{ date('Y-m-01') }}">
                </div>

                <label class="col-md-2 col-form-label text-left" for="end">Periode To:</label>
                <div class="col-md-4">
                    <input type="date" class="form-control" id="end_date" name="end" required value="{{ date('Y-m-d') }}">
                </div>
            </div>
        </div>
      </div>
    </div>

    <div class="col-2">
      <div class="block">
        <div class="block-content">
          <div class="form-group row">
            <div class="col-md-12 text-center">
              <button type="submit" class="btn bg-gd-corporate border-0 text-white" aria-label="Print Report" id="submit-btn">
                Download <i class="fa fa-print ml-10"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>



@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.daterangepicker')
@include('superuser.asset.plugin.datatables-button')

@push('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        $('.js-select2').select2();

        $("#customer").val("all").change();
        $("#product").val("all").change();
        $("#brand").val("all").change();

        document.getElementById('non_bulan').addEventListener('change', function() {
          this.value = this.checked ? 1 : 0;
        });
    })
</script>
@endpush