@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.sales_order.index_awal') }}">Sales Order Awal</a>
  <span class="breadcrumb-item active">Riwayat Archive</span>
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

<div class="block block-archive">
  <div class="block-header block-header-default">
    <h3 class="block-title">
      Riwayat SO Awal CASH/TEMPO (Archive)
      <span class="badge badge-secondary ml-5">{{ count($archives) }}</span>
    </h3>
    <a href="{{ route('superuser.penjualan.sales_order.index_awal') }}" class="btn btn-sm btn-outline-secondary">
      <i class="fa fa-arrow-left mr-5"></i> Kembali ke SO Awal
    </a>
  </div>
  <div class="block-content">
    <div class="table-responsive">
      <table id="datatable-archive" class="table table-striped">
        <thead>
          <tr>
            <th class="text-center">#</th>
            <th class="text-center">Tanggal SO</th>
            <th class="text-center">SO Code</th>
            <th class="text-center">Estimate Code</th>
            <th class="text-center">Customer</th>
            <th class="text-center">Brand</th>
            <th class="text-center">Type</th>
            <th class="text-center">Diarsipkan</th>
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach($archives as $archive)
            <tr>
              <td class="text-center">{{ $loop->iteration }}</td>
              <td class="text-center">{{ $archive->created_at ? $archive->created_at->format('d/m/Y') : '-' }}</td>
              <td><b>{{ $archive->so_code ?? '-' }}</b></td>
              <td>{{ $archive->estimate_code ?? '-' }}</td>
              <td>{{ $archive->member->name ?? '-' }} {{ $archive->member->text_kota ?? '' }}</td>
              <td>{{ $archive->brand_name ?? '-' }}</td>
              <td class="text-center">
                @if($archive->type_transaction == 'CASH')
                  <span class="badge badge-success">CASH</span>
                @elseif($archive->type_transaction == 'TEMPO')
                  <span class="badge badge-info">TEMPO</span>
                @else
                  <span class="badge badge-secondary">{{ $archive->type_transaction ?? '-' }}</span>
                @endif
              </td>
              <td class="text-center" title="{{ $archive->archived_at ? $archive->archived_at->format('d/m/Y H:i') : '' }}">
                {{ $archive->archived_at ? $archive->archived_at->format('d/m/Y H:i') : '-' }}
                @if($archive->archived_at)
                  <br><small class="text-muted">{{ $archive->archived_at->diffForHumans() }}</small>
                @endif
              </td>
              <td class="text-center text-nowrap">
                <button type="button" class="btn btn-sm btn-circle btn-alt-info" data-toggle="modal" data-target="#modalArchive{{ $archive->id }}" title="Lihat Detail">
                  <i class="fa fa-eye"></i>
                </button>
                <button type="button" class="btn btn-sm btn-circle btn-alt-warning" title="Kembalikan ke list aktif"
                  onclick="confirmRestore('{{ route('superuser.penjualan.sales_order.archive_awal_restore', $archive->id) }}', '{{ $archive->so_code }}')">
                  <i class="fa fa-undo"></i>
                </button>
                @if($archive->is_estimate)
                  @if(empty($archive->idr_rate) || (float) $archive->idr_rate <= 1)
                    <button type="button" class="btn btn-sm btn-circle btn-alt-primary" title="Print Estimate"
                      onclick="Swal.fire('Peringatan!', 'Kurs belum di setting pada data ini', 'warning');">
                      <i class="fa fa-file-pdf-o"></i>
                    </button>
                  @else
                    <a href="{{ route('superuser.penjualan.sales_order.archive_awal_print_estimate', $archive->id) }}" target="_blank" class="btn btn-sm btn-circle btn-alt-primary" title="Print Estimate">
                      <i class="fa fa-file-pdf-o"></i>
                    </a>
                  @endif
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Detail Archive -->
@foreach($archives as $archive)
<div class="modal fade" id="modalArchive{{ $archive->id }}" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detail Archive #{{ $archive->so_code }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <div class="block">
              <div class="block-header block-header-default">
                <h3 class="block-title">#Info SO</h3>
              </div>
              <div class="block-content">
                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label>SO Code</label>
                    <input type="text" class="form-control" readonly value="{{ $archive->so_code ?? '-' }}">
                  </div>
                  <div class="form-group col-md-6">
                    <label>Estimate Code</label>
                    <input type="text" class="form-control" readonly value="{{ $archive->estimate_code ?? '-' }}">
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label>Tanggal SO</label>
                    <input type="text" class="form-control" readonly value="{{ $archive->created_at ? $archive->created_at->format('d/m/Y H:i') : '-' }}">
                  </div>
                  <div class="form-group col-md-6">
                    <label>Brand</label>
                    <input type="text" class="form-control" readonly value="{{ $archive->brand_name ?? '-' }}">
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label>Type</label>
                    <input type="text" class="form-control" readonly value="{{ $archive->type_transaction ?? '-' }}">
                  </div>
                  <div class="form-group col-md-6">
                    <label>Kurs IDR</label>
                    <input type="text" class="form-control" readonly value="{{ number_format((float)$archive->idr_rate, 0, ',', '.') }}">
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label>Diarsipkan</label>
                    <input type="text" class="form-control" readonly value="{{ $archive->archived_at ? $archive->archived_at->format('d/m/Y H:i') : '-' }}">
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="block">
              <div class="block-header block-header-default">
                <h3 class="block-title">#Customer</h3>
              </div>
              <div class="block-content">
                <div class="form-row">
                  <div class="form-group col-md-12">
                    <label>Customer</label>
                    <input type="text" class="form-control" readonly value="{{ $archive->member->name ?? '-' }} {{ $archive->member->text_kota ?? '' }}">
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group col-md-12">
                    <label>Alamat</label>
                    <textarea class="form-control" rows="2" readonly>{{ $archive->member->address ?? '-' }}</textarea>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <div class="block">
              <div class="block-header block-header-default">
                <h3 class="block-title">#Produk</h3>
              </div>
              <div class="block-content">
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Product</th>
                      <th>Qty</th>
                      <th>Harga (USD)</th>
                      <th>Disc (USD)</th>
                      <th>Kemasan</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($archive->so_detail as $item)
                    <tr>
                      <td class="text-center">{{ $loop->iteration }}</td>
                      <td>{{ $item->product_pack->code ?? '' }} - {{ $item->product_pack->name ?? '' }}</td>
                      <td class="text-right">{{ $item->qty }}</td>
                      <td class="text-right">{{ number_format((float)$item->price, 2, ',', '.') }}</td>
                      <td class="text-right">{{ number_format((float)$item->disc_usd, 2, ',', '.') }}</td>
                      <td>{{ $item->product_pack->packaging->pack_name ?? '-' }}</td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-warning"
          onclick="confirmRestore('{{ route('superuser.penjualan.sales_order.archive_awal_restore', $archive->id) }}', '{{ $archive->so_code }}')">
          <i class="fa fa-undo"></i> Restore ke List Aktif
        </button>
        @if($archive->is_estimate && !empty($archive->idr_rate) && (float)$archive->idr_rate > 1)
        <a href="{{ route('superuser.penjualan.sales_order.archive_awal_print_estimate', $archive->id) }}" target="_blank" class="btn btn-primary">
          <i class="fa fa-file-pdf-o"></i> Print Estimate
        </a>
        @endif
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endforeach

@endsection

@include('superuser.asset.plugin.datatables')

@push('styles')
<style>
  /* Riwayat Archive: padat, minim ruang kosong */
  .block-archive .block-header {
    padding: 8px 14px;
  }
  .block-archive .block-title {
    font-size: 14px;
  }
  .block-archive .block-content {
    padding: 8px 12px 12px;
  }
  #datatable-archive {
    font-size: 13px;
  }
  #datatable-archive thead th {
    padding: 6px 8px;
    font-size: 12px;
    white-space: nowrap;
  }
  #datatable-archive tbody td {
    padding: 5px 8px;
    vertical-align: middle;
  }
  #datatable-archive .badge {
    font-size: 11px;
  }
  #datatable-archive .btn-circle {
    margin: 1px;
  }
  .block-archive .dataTables_wrapper .row {
    margin-bottom: 4px;
  }
  .block-archive .dataTables_wrapper .form-control-sm {
    font-size: 13px;
  }
</style>
@endpush

@push('scripts')
<script type="text/javascript">
  $(document).ready(function () {
    $('#datatable-archive').DataTable({
      order: [[7, 'desc']],
      pageLength: 10,
      lengthMenu: [[10, 20, 50], [10, 20, 50]]
    });
  });

  function confirmRestore(url, soCode) {
    Swal.fire({
      title: 'Restore SO?',
      text: 'Kembalikan ' + soCode + ' ke list SO Awal aktif?',
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Ya, kembalikan!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.value || result.isConfirmed) {
        window.location.href = url;
      }
    });
  }
</script>
@endpush
