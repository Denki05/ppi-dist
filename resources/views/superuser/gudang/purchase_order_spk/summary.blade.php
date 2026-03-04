@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Gudang</span>
  <span class="breadcrumb-item ">Purchase Order (PO)</span>
  <span class="breadcrumb-item active">Summary List PO</span>
</nav>

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

<div class="block">
  <div class="block-content">
      <div class="row mb-30">
        <div class="col-12">
        <table id="datatables" class="table table-bordred table-striped" style="width:100%">
          <thead>
            <tr>
              <td class="text-center">#</td>
              <td class="text-center">CREATED AT</td>
              <td class="text-center">VARIANT</td>
              <td class="text-center">KEMASAN</td>
              <td class="text-center">QUANTITY</td>
              <td class="text-center">KODE PO</td>
            </tr>
          </thead>
          <tbody>
            @foreach($summary as $item)
            <tr>
              <td class="text-center">{{ $loop->iteration }}</td>
              <td class="text-center">{{ $item->created_at ? date('d M Y', strtotime($item->created_at)) : '-' }}</td>
              <td class="text-center">{{ $item->produk_code }} - <b>{{ $item->produk_name }}</b></td>
              <td class="text-center">{{ $item->kemasan }}</td>
              <td class="text-center">{{ $item->total_quantity }}</td>
              <td class="text-center">{{ $item->kode_po }}</td>
            </tr>
            @endforeach
        </table>
        </div>
      </div>
    </div>

    <div class="row pt-30 mb-15">
      <div class="col-md-6">
        <a href="{{ route('superuser.gudang.purchase_order.index') }}">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
      </div>
    </div>
</div>

@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    $('#datatables').DataTable({});
  });
</script>
@endpush