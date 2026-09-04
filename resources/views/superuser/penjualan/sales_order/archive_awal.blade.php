@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sale</span>
  <span class="breadcrumb-item">Sales Order</span>
  <span class="breadcrumb-item">Awal</span>
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

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Riwayat SO Awal CASH/TEMPO (Archive)</h3>
    <a href="{{ route('superuser.penjualan.sales_order.index_awal') }}" class="btn btn-sm btn-outline-secondary">
      <i class="fa fa-arrow-left mr-5"></i> Kembali ke SO Awal
    </a>
  </div>
  <div class="block-content">
    <!-- <div class="row mb-15">
      <div class="col-md-4">
        <input type="text" id="searchArchive" class="form-control" placeholder="Cari kode SO, nama customer, atau kode estimate...">
      </div>
    </div> -->
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
              <td>{{ $loop->iteration }}</td>
              <td>{{ $archive->created_at ? $archive->created_at->format('d/m/Y') : '-' }}</td>
              <td>{{ $archive->so_code ?? '-' }}</td>
              <td>{{ $archive->estimate_code ?? '-' }}</td>
              <td>{{ $archive->member->name ?? '-' }} {{ $archive->member->text_kota ?? '' }}</td>
              <td>{{ $archive->brand_name ?? '-' }}</td>
              <td>{{ $archive->type_transaction == 'CASH' ? 'CASH' : 'TEMPO' }}</td>
              <td>{{ $archive->archived_at ? $archive->archived_at->format('d/m/Y H:i') : '-' }}</td>
              <td>
                <button type="button" class="btn btn-info btn-sm btn-flat" data-toggle="modal" data-target="#modalArchive{{ $archive->id }}">
                  <i class="fa fa-eye"></i> View
                </button>
                <a href="{{ route('superuser.penjualan.sales_order.archive_awal_restore', $archive->id) }}" class="btn btn-warning btn-sm btn-flat" onclick="return confirm('Kembalikan SO ini ke list aktif?')">
                  <i class="fa fa-undo"></i> Restore
                </a>
                @if($archive->is_estimate)
                  @if(empty($archive->idr_rate) || (float) $archive->idr_rate <= 1)
                    <a href="javascript:void(0);" onclick="Swal.fire('Peringatan!', 'Kurs belum di setting pada data ini', 'warning');" class="btn btn-sm btn-flat btn-alt-primary">
                      <i class="fa fa-file-pdf-o"></i> Estimate
                    </a>
                  @else
                    <a href="{{ route('superuser.penjualan.sales_order.archive_awal_print_estimate', $archive->id) }}" target="_blank" class="btn btn-sm btn-flat btn-alt-primary">
                      <i class="fa fa-file-pdf-o"></i> Estimate
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
<div class="modal fade bd-example-modal-lg" id="modalArchive{{ $archive->id }}" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl">
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
                    <input type="text" class="form-control" readonly value="{{ $archive->type_transaction == 1 ? 'CASH' : 'TEMPO' }}">
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
                      <td>{{ $loop->iteration }}</td>
                      <td>{{ $item->product_pack->code ?? '' }} - {{ $item->product_pack->name ?? '' }}</td>
                      <td>{{ $item->qty }}</td>
                      <td>{{ number_format((float)$item->price, 2, ',', '.') }}</td>
                      <td>{{ number_format((float)$item->disc_usd, 2, ',', '.') }}</td>
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
        <a href="{{ route('superuser.penjualan.sales_order.archive_awal_restore', $archive->id) }}" class="btn btn-warning" onclick="return confirm('Kembalikan SO ini ke list aktif?')">
          <i class="fa fa-undo"></i> Restore ke List Aktif
        </a>
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

@push('scripts')
<script type="text/javascript">
  $(document).ready(function () {
    var table = $('#datatable-archive').DataTable({
      order: [[7, 'desc']]
    });

    // Search custom
    $('#searchArchive').on('keyup', function () {
      table.search(this.value).draw();
    });
  });
</script>
@endpush
