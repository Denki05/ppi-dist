@extends('superuser.app')

@section('content')
@php
  $grandTotal = $summary->sum('total_quantity');
  $allPo = [];
  foreach ($summary as $s) {
    foreach (explode(',', $s->kode_po ?? '') as $c) {
      $c = trim($c);
      if ($c !== '') $allPo[] = $c;
    }
  }
  $poCount = count(array_unique($allPo));
@endphp

<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Gudang</span>
  <span class="breadcrumb-item">Purchase Order (PO)</span>
  <span class="breadcrumb-item active">Summary Outstanding</span>
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

<div class="row">
  <div class="col-md-4">
    <div class="block block-rounded block-bordered text-center">
      <div class="block-content py-15">
        <div class="font-size-h3 font-w700">{{ number_format($grandTotal, 2) }}</div>
        <div class="text-muted font-size-sm">Total sisa (KG)</div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="block block-rounded block-bordered text-center">
      <div class="block-content py-15">
        <div class="font-size-h3 font-w700">{{ $summary->count() }}</div>
        <div class="text-muted font-size-sm">Varian produk outstanding</div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="block block-rounded block-bordered text-center">
      <div class="block-content py-15">
        <div class="font-size-h3 font-w700">{{ $poCount }}</div>
        <div class="text-muted font-size-sm">PO belum selesai receiving</div>
      </div>
    </div>
  </div>
</div>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title"><i class="fa fa-bar-chart mr-5"></i>Sisa PO per Produk</h3>
    <div class="block-options">
      <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#modalExportPO">
        <i class="fa fa-file-excel-o mr-5"></i> Export Detail
      </button>
    </div>
  </div>
  <div class="block-content">
    <p class="text-muted mb-15" style="font-size:12px;">
      <i class="fa fa-info-circle mr-5"></i>Sisa quantity PO <strong>terkirim</strong> yang <strong>belum di-receiving</strong>, dikelompokkan per produk &amp; kemasan. Berkurang otomatis setiap ada penerimaan barang.
    </p>
    <div class="row mb-30">
      <div class="col-12">
      <table id="datatables" class="table table-bordred table-striped" style="width:100%">
        <thead>
          <tr>
            <td class="text-center">#</td>
            <td class="text-center">Produk</td>
            <td class="text-center">Kemasan</td>
            <td class="text-center">Sisa Qty (KG)</td>
            <td class="text-center">Jml PO</td>
            <td class="text-center">Kode PO</td>
          </tr>
        </thead>
        <tbody>
          @foreach($summary as $item)
          @php $codes = array_filter(array_map('trim', explode(',', $item->kode_po ?? ''))); @endphp
          <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td>{{ $item->produk_code }} - <b>{{ $item->produk_name }}</b></td>
            <td class="text-center">{{ $item->kemasan }}</td>
            <td class="text-center"><b>{{ number_format((float) $item->total_quantity, 2) }}</b></td>
            <td class="text-center"><span class="badge badge-primary">{{ count($codes) }}</span></td>
            <td class="text-center"><small>{{ $item->kode_po }}</small></td>
          </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3" class="text-right"><b>TOTAL</b></td>
            <td class="text-center"><b>{{ number_format($grandTotal, 2) }}</b></td>
            <td colspan="2"></td>
          </tr>
        </tfoot>
      </table>
      </div>
    </div>
  </div>

  <div class="row pt-10 mb-15">
    <div class="col-md-6">
      <a href="{{ route('superuser.gudang.purchase_order.index') }}">
        <button type="button" class="btn bg-gd-cherry border-0 text-white">
          <i class="fa fa-arrow-left mr-10"></i> Back
        </button>
      </a>
    </div>
  </div>
</div>

<!-- Modal Export PO (filter tanggal) -->
<div class="modal fade" id="modalExportPO" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-file-excel-o mr-5"></i>Export PO</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Dari tanggal <small class="text-muted">(Tgl Pembuatan)</small></label>
          <input type="date" class="form-control" id="exp-start">
        </div>
        <div class="form-group mb-0">
          <label>Sampai tanggal</label>
          <input type="date" class="form-control" id="exp-end">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-success" id="btnDoExport">
          <i class="fa fa-download mr-5"></i> Download
        </button>
      </div>
    </div>
  </div>
</div>

@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    $('#datatables').DataTable({
      pageLength: 10,
      order: [[3, 'desc']]
    });

    $(document).on('click', '#btnDoExport', function() {
      var start = $('#exp-start').val();
      var end = $('#exp-end').val();
      if ((start && !end) || (!start && end)) {
        showToast('warning', 'Isi kedua tanggal, atau kosongkan keduanya untuk semua data.');
        return;
      }
      if (start && end && start > end) {
        showToast('warning', 'Tanggal awal tidak boleh lebih dari tanggal akhir.');
        return;
      }
      var url = '{{ route("superuser.gudang.purchase_order.export") }}';
      var params = [];
      if (start) params.push('start_date=' + encodeURIComponent(start));
      if (end) params.push('end_date=' + encodeURIComponent(end));
      if (params.length > 0) url += '?' + params.join('&');
      $('#modalExportPO').modal('hide');
      window.location.href = url;
    });
  });
</script>
@endpush
