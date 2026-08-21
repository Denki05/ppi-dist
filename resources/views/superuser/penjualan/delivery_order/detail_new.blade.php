@php
    // Checker murni (bukan SPV, bukan superuser) pakai layout tablet portrait.
    // Superuser tetap lihat versi CRM penuh biar gampang dicek/debug.
    $useTabletLayout = ($isChecker ?? false) && !($isSpvGudang ?? false) && Auth::user()->is_superuser == 0;
@endphp
@extends($useTabletLayout ? 'superuser.app_tablet' : 'superuser.app')
@push('styles')
  <link rel="stylesheet" href="{{ asset('superuser_assets/css/page/delivery-order.css') }}">
  <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
<style>
  .do-page-wrap {
    max-width: 1180px;
    margin: 20px auto 32px;
  }
  .do-canvas {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 1px 6px rgba(0,0,0,.06);
    overflow: hidden;
  }
  .do-canvas-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    padding: 14px 20px;
    border-bottom: 1px solid #f1f3f5;
  }
  .do-canvas-title {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .do-canvas-title h4 {
    margin: 0;
    font-size: 17px;
    font-weight: 700;
    color: #212529;
  }
  .do-canvas-title small {
    display: block;
    font-size: 12px;
    color: #adb5bd;
    margin-top: 1px;
  }
  .do-status-badge {
    font-size: 11.5px;
    font-weight: 700;
    padding: 5px 14px;
    border-radius: 20px;
    letter-spacing: .02em;
    white-space: nowrap;
  }
  .do-status-ready      { background: #f1f3f5; color: #495057; }
  .do-status-packed     { background: #fff3bf; color: #995c00; }
  .do-status-delivering { background: #edf2ff; color: #3b5bdb; }
  .do-status-delivered  { background: #ebfbee; color: #2b8a3e; }
  .do-status-cancel     { background: #fff5f5; color: #c92a2a; }

  .do-canvas-body {
    padding: 18px 20px;
  }

  .do-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 14px 32px;
  }
  .do-info-item {
    min-width: 0;
  }
  .do-info-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #adb5bd;
    font-weight: 700;
    margin-bottom: 3px;
  }
  .do-info-value {
    font-size: 14px;
    color: #343a40;
    font-weight: 500;
    word-break: break-word;
  }

  .do-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    padding: 12px 20px;
    background: #f8f9fb;
    border-top: 1px solid #f1f3f5;
    border-bottom: 1px solid #f1f3f5;
  }
  .do-toolbar-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }
  .do-toolbar .btn {
    font-size: 13px;
    padding: 7px 14px;
    border-radius: 8px;
  }

  .do-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    padding: 14px 20px;
  }
  .do-footer .btn {
    font-size: 13.5px;
    padding: 9px 16px;
    border-radius: 9px;
    font-weight: 600;
  }
  .do-footer .btn-primary,
  .do-footer .btn-delivery,
  .do-footer .btn-delivered {
    padding: 9px 24px;
  }

  .do-form-section {
    margin-bottom: 14px;
  }
  .do-form-section-title {
    font-size: 11.5px;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #868e96;
    font-weight: 700;
    margin-bottom: 8px;
    padding-bottom: 5px;
    border-bottom: 1px solid #f1f3f5;
  }
  .do-form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px 24px;
  }
  .do-form-field label {
    font-size: 12.5px;
    color: #495057;
    font-weight: 600;
    margin-bottom: 4px;
    display: block;
  }
  .do-upload-box {
    background: #f8f9fb;
    border: 1px dashed #dee2e6;
    border-radius: 10px;
    padding: 12px;
  }
  .do-upload-preview img {
    border-radius: 8px;
    border: 1px solid #eef0f2;
  }
  .do-upload-preview-img {
    border-radius: 8px;
    border: 1px solid #eef0f2;
  }

  /* Tabel konfirmasi barang (dipakai di status 3) - versi canvas, compact */
  .do-confirm-table {
    width: 100%;
    border-collapse: collapse;
  }
  .do-confirm-table th {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #adb5bd;
    font-weight: 700;
    text-align: left;
    padding: 8px 6px;
    border-bottom: 2px solid #f1f3f5;
  }
  .do-confirm-table td {
    font-size: 13.5px;
    padding: 8px 6px;
    border-bottom: 1px solid #f4f5f6;
    vertical-align: middle;
  }
  .do-confirm-table input[type="checkbox"] {
    width: 20px;
    height: 20px;
  }

  @media (max-width: 768px) {
    .do-page-wrap { margin: 12px auto 20px; }
    .do-info-grid, .do-form-grid { grid-template-columns: 1fr; }
    .do-canvas-header, .do-toolbar, .do-footer { padding: 12px 14px; }
    .do-canvas-body { padding: 14px; }
  }

@if($useTabletLayout)
  /* Tablet portrait: tabel konfirmasi barang jadi list bertumpuk, bukan tabel sempit */
  @media (max-width: 768px) {
    .do-confirm-table thead { display: none; }
    .do-confirm-table, .do-confirm-table tbody, .do-confirm-table tr, .do-confirm-table td {
      display: block;
      width: 100%;
    }
    .do-confirm-table tr {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 1px 4px rgba(0,0,0,.08);
      margin-bottom: 10px;
      padding: 12px;
      border: none !important;
    }
    .do-confirm-table td {
      border: none !important;
      padding: 4px 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .do-confirm-table td::before {
      content: attr(data-label);
      font-weight: 600;
      color: #6c757d;
      margin-right: 12px;
    }
    .do-confirm-table td:last-child {
      justify-content: flex-end;
    }
    .do-confirm-table input[type="checkbox"] {
      width: 28px;
      height: 28px;
    }
    .btn-cancel-step, #btnCancelToDraft, [onclick="konfirmasiBarang()"] {
      width: 100%;
      font-size: 18px;
      padding: 14px;
    }
  }
@endif
</style>
@endpush

@section('content')
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

{{-- ===================================================================
     STATUS 3 - DO PROSES (Checker / fallback SPV). Direstyle canvas,
     ruang kosong dipadetin, sama bahasa desain dengan status 4/5/6.
     =================================================================== --}}
@if($result->status == 3 && (($isChecker ?? false) || ($isSpvGudang ?? false) || Auth::user()->is_superuser == 1))
<div class="do-page-wrap">
<div class="do-canvas">

  <div class="do-canvas-header">
    <div class="do-canvas-title">
      {{--<a href="{{ route('superuser.penjualan.delivery_order.index') }}" class="btn btn-light btn-sm" style="border-radius:8px;">
        <i class="fa fa-arrow-left"></i>
      </a>--}}
      <div>
        <h4>{{ $result->do_code ?: $result->code }}</h4>
        <small>DO Proses &middot; fallback checker/picker</small>
      </div>
    </div>
    <span class="do-status-badge do-status-ready">PROSES</span>
  </div>

  <div class="do-canvas-body">
    <div class="do-info-grid">
      <div class="do-info-item">
        <div class="do-info-label">Warehouse</div>
        <div class="do-info-value">{{ $result->warehouse->name ?? '-' }}</div>
      </div>
      <div class="do-info-item">
        <div class="do-info-label">Customer</div>
        <div class="do-info-value">{{ $result->member->name ?? '-' }} {{ $result->member->text_kota ?? '' }}</div>
      </div>
      <div class="do-info-item">
        <div class="do-info-label">Ekspedisi</div>
        <div class="do-info-value">{{ $result->vendor->name ?? '-' }}</div>
      </div>
      <div class="do-info-item">
        <div class="do-info-label">Referensi SO</div>
        <div class="do-info-value">{{ $result->so->code ?? '-' }}</div>
      </div>
    </div>
  </div>

  <div class="do-canvas-body" style="padding-top:14px;">
    <table class="do-confirm-table">
      <thead>
        <tr>
          <th class="text-center">No</th>
          <th class="text-center">Nama Barang</th>
          <th class="text-center">Jumlah</th>
          <th class="text-center">Packaging</th>
          <th class="text-center">
            Cek <input type="checkbox" class="check-all-confirm-item" onclick="$('.confirm-item').prop('checked', $(this).prop('checked'))" />
          </th>
        </tr>
      </thead>
      <tbody>
        @if(count($result->do_detail) == 0)
          <tr><td colspan="5" align="center">Data tidak ditemukan</td></tr>
        @endif
        @foreach($result->do_detail as $index => $row)
          <tr>
            <td data-label="No">{{$index+1}}</td>
            <td data-label="Nama Barang">{{ $row->product_pack->code }} - {{$row->product_pack->name}}</td>
            <td data-label="Jumlah">{{$row->qty}}</td>
            <td data-label="Packaging">{{$row->product_pack->packaging->pack_name}}</td>
            <td data-label="Konfirmasi">
              <input type="checkbox"
                class="confirm-item"
                name="confirmed_items[]"
                value="{{$row->id}}" />
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="do-footer">
    <a href="{{ route('superuser.penjualan.delivery_order.index') }}" class="btn btn-danger btn-sm" style="border-radius:8px;">
      <i class="fa fa-arrow-left"></i> Back
    </a>
    <button type="button" class="btn btn-primary" onclick="konfirmasiBarang()">
      <i class="fa fa-save"></i> Save
    </button>
  </div>

</div>
</div>
@elseif($result->status == 3)
<div class="alert alert-info">
  DO ini sedang menunggu proses <strong>Checker</strong>. Anda tidak memiliki akses untuk memproses tahap ini.
</div>
@endif

{{-- ===================================================================
     STATUS 4 - DO SIAP KIRIM (SPV Gudang). Revamp canvas CRM-style.
     =================================================================== --}}
@if($result->status == 4 && (($isSpvGudang ?? false) || Auth::user()->is_superuser == 1))
<div class="do-page-wrap">
<div class="do-canvas">

  <div class="do-canvas-header">
    <div class="do-canvas-title">
      {{--<a href="{{ route('superuser.penjualan.delivery_order.index') }}" class="btn btn-light btn-sm" style="border-radius:8px;">
        <i class="fa fa-arrow-left"></i>
      </a>--}}
      <div>
        <h4>{{ $result->do_code ?: $result->code }}</h4>
        <small>DO Siap Kirim</small>
      </div>
    </div>
    <span class="do-status-badge do-status-packed">SIAP KIRIM</span>
  </div>

  <div class="do-canvas-body">
    <div class="do-info-grid">
      <div class="do-info-item">
        <div class="do-info-label">Warehouse</div>
        <div class="do-info-value">{{ $result->warehouse->name ?? '-' }}</div>
      </div>
      <div class="do-info-item">
        <div class="do-info-label">Customer</div>
        <div class="do-info-value">{{ $result->member->name ?? '-' }} &middot; {{ $result->member->address ?? '-' }}</div>
      </div>
      <div class="do-info-item">
        <div class="do-info-label">Ekspedisi</div>
        <div class="do-info-value">{{ $result->vendor->name ?? '-' }}</div>
      </div>
      <div class="do-info-item">
        <div class="do-info-label">Referensi SO</div>
        <div class="do-info-value">{{ $result->so->code ?? '-' }}</div>
      </div>
    </div>
  </div>

  <div class="do-toolbar">
    <span style="font-size:12.5px; color:#868e96; font-weight:600;">
      <i class="fa fa-print"></i> Dokumen
    </span>
    <div class="do-toolbar-actions">
      @if($result->count_cancel == 0)
        <a href="{{ route('superuser.penjualan.delivery_order.print', $result->id) }}"
          class="btn btn-outline-info" target="_blank">
            <i class="fa fa-file-o"></i> Print DO
        </a>
        {{--@if(isset($result->so) && isset($result->so->showroom_mutation))
          <a href="{{ route('superuser.gudang.mutasi_showroom.print_pdf', $result->so->showroom_mutation->id) }}"
            class="btn btn-outline-secondary" target="_blank">
              <i class="fa fa-file-o"></i> Print SJ Internal
          </a>
        @endif--}}
      @elseif($result->count_cancel == 1)
        <a href="{{ route('superuser.penjualan.delivery_order.print', $result->id) }}"
          class="btn btn-outline-info" target="_blank">
            <i class="fa fa-print"></i> Print DO Revisi
        </a>
      @endif
    </div>
  </div>

  <div class="do-footer">
    <a href="{{ route('superuser.penjualan.delivery_order.index') }}" class="btn btn-danger btn-sm" style="border-radius:8px;">
      <i class="fa fa-arrow-left"></i> Back
    </a>
    <button type="button" class="btn btn-primary btn-delivery">
      <i class="fas fa-shipping-fast"></i> DELIVERING / BERANGKAT
    </button>
  </div>

</div>
</div>
@elseif($result->status == 4)
<div class="alert alert-info">
  DO ini sudah <strong>Packed</strong>, menunggu diproses oleh <strong>SPV Gudang</strong>.
</div>
@endif

{{-- ===================================================================
     STATUS 5 - UPDATE RESI (SPV Gudang). Revamp canvas CRM-style.
     =================================================================== --}}
@if($result->status == 5 && (($isSpvGudang ?? false) || Auth::user()->is_superuser == 1))
<div class="do-page-wrap">
<div class="do-canvas">

  <div class="do-canvas-header">
    <div class="do-canvas-title">
      {{--<a href="{{ route('superuser.penjualan.delivery_order.index') }}" class="btn btn-light btn-sm" style="border-radius:8px;">
        <i class="fa fa-arrow-left"></i>
      </a>--}}
      <div>
        <h4>{{ $result->do_code }}</h4>
        <small>Update Resi</small>
      </div>
    </div>
    <span class="do-status-badge do-status-delivering">DELIVERING</span>
  </div>

  <div class="do-canvas-body">
    <form id="frmSent" action="{{route('superuser.penjualan.delivery_order.sent')}}" method="post" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="do_id" value="{{$result->id}}">

      <div class="do-form-section">
        <div class="do-form-section-title">Customer</div>
        <div class="do-form-grid">
          <div class="do-form-field">
            <label>Nama</label>
            <input class="form-control" value="{{$result->member->name}}" readonly>
          </div>
          <div class="do-form-field">
            <label>Kota</label>
            <input class="form-control" value="{{$result->member->text_kota}}" readonly>
          </div>
        </div>
      </div>

      @if($result->status == 5 && $result->image == null)
      <div class="do-form-section">
        <div class="do-form-section-title">Upload Bukti Kirim</div>
        <div class="do-form-grid">
          <div class="do-form-field">
            <label>Foto 1</label>
            <div class="do-upload-box">
              <input type="file" id="image" name="image" data-max-file-size="2000" accept="image/png, image/jpeg">
            </div>
          </div>
          <div class="do-form-field">
            <label>Foto 2</label>
            <div class="do-upload-box">
              <input type="file" id="image2" name="image2" data-max-file-size="2000" accept="image/png, image/jpeg">
            </div>
          </div>
        </div>
      </div>
      @endif

      @if(!empty($result->image))
      <div class="do-form-section do-upload-preview">
        <div class="do-form-section-title">Bukti Kirim</div>
        <a href="<?= asset($result->image) ?>" target="_blank">
          <img src="<?= asset($result->image) ?>" style="max-width: 220px; max-height: 220px" />
        </a>
      </div>
      @endif

      <div class="do-form-section">
        <div class="do-form-section-title">Biaya</div>
        <div class="do-form-grid">
          <div class="do-form-field">
            <label>Ongkir (IDR) - Note</label>
            <input type="text" class="form-control" placeholder="Input Note" value="{{ $result->vendor->name ?? '-' }}" name="delivery_cost_note" {{$result->status == 6 ? 'readonly' : ''}} readonly>
          </div>
          <div class="do-form-field">
            <label>Ongkir (IDR) - Nominal</label>
            <input type="text" class="form-control" value="{{ $result->do_detail_cost[0]->delivery_cost_idr ?? 0 }}" name="delivery_cost_idr" step="any" {{$result->status == 5  || $result->status == 6 ? 'readonly' : ''}}>
          </div>
          <div class="do-form-field">
            <label>Resi - Ekspedisi</label>
            <select class="form-control js-select2" name="other_cost_note" id="other_cost_note">
              <option value="">Pilih Ekspedisi</option>
              @foreach($ekspedisi as $row)
               <option value="{{$row->name}}">{{ $row->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="do-form-field">
            <label>Resi (IDR) - Nominal</label>
            <input type="number" class="form-control" value="{{$result->do_detail_cost->first()->other_cost_idr ?? 0}}" name="other_cost_idr" step="any" {{$result->status == 6 ? 'readonly' : ''}}>
          </div>
        </div>
      </div>
    </form>
  </div>

  <div class="do-footer">
    <div style="display:flex; gap:8px;">
      <a href="{{route('superuser.penjualan.delivery_order.index')}}" class="btn btn-outline-danger">
        <i class="fa fa-arrow-left"></i> Back
      </a>
    </div>
    @if($result->status==5)
    <button type="button" class="btn btn-primary btn-delivered">
      <i class="fa fa-save"></i> Selesaikan
    </button>
    @endif
  </div>

</div>
</div>
@elseif($result->status == 5)
<div class="alert alert-info">
  DO ini sedang dalam pengiriman, menunggu update resi oleh <strong>SPV Gudang</strong>.
</div>
@endif

{{-- ===================================================================
     STATUS 6 - DELIVERED / HISTORY RESI. Read-only.
     =================================================================== --}}
@if($result->status == 6 && (($isSpvGudang ?? false) || Auth::user()->is_superuser == 1))
<div class="do-page-wrap">
<div class="do-canvas">

  <div class="do-canvas-header">
    <div class="do-canvas-title">
      {{--<a href="{{ route('superuser.penjualan.delivery_order.index') }}" class="btn btn-light btn-sm" style="border-radius:8px;">
        <i class="fa fa-arrow-left"></i>
      </a>--}}
      <div>
        <h4>{{ $result->do_code }}</h4>
        <small>History Update Resi</small>
      </div>
    </div>
    <span class="do-status-badge do-status-delivered">DELIVERED</span>
  </div>

  <div class="do-canvas-body">
    <div class="do-info-grid">
      <div class="do-info-item">
        <div class="do-info-label">Customer</div>
        <div class="do-info-value">{{ $result->member->name ?? '-' }} &middot; {{ $result->member->text_kota ?? '-' }}</div>
      </div>
      <div class="do-info-item">
        <div class="do-info-label">Tanggal Dikirim</div>
        <div class="do-info-value">
          {{ $result->date_sent ? \Carbon\Carbon::parse($result->date_sent)->format('d F Y') : '-' }}
        </div>
      </div>
      <div class="do-info-item">
        <div class="do-info-label">Ongkir (Note)</div>
        <div class="do-info-value">{{ $result->do_detail_cost[0]->delivery_cost_note ?? '-' }}</div>
      </div>
      <div class="do-info-item">
        <div class="do-info-label">Ongkir (IDR)</div>
        <div class="do-info-value">Rp {{ number_format($result->do_detail_cost[0]->delivery_cost_idr ?? 0, 0, ',', '.') }}</div>
      </div>
      <div class="do-info-item">
        <div class="do-info-label">Resi (Ekspedisi)</div>
        <div class="do-info-value">{{ $result->do_detail_cost[0]->other_cost_note ?? '-' }}</div>
      </div>
      <div class="do-info-item">
        <div class="do-info-label">Resi (IDR)</div>
        <div class="do-info-value">Rp {{ number_format($result->do_detail_cost[0]->other_cost_idr ?? 0, 0, ',', '.') }}</div>
      </div>
      <div class="do-info-item">
        <div class="do-info-label">Grand Total</div>
        <div class="do-info-value">Rp {{ number_format($result->do_detail_cost[0]->grand_total_idr ?? 0, 0, ',', '.') }}</div>
      </div>
    </div>

    @if(!empty($result->image) || !empty($result->image2))
    <div class="do-form-section" style="margin-top:16px;">
      <div class="do-form-section-title">Bukti Kirim</div>
      <div style="display:flex; gap:12px; flex-wrap:wrap;">
        @if(!empty($result->image))
        <a href="<?= asset($result->image) ?>" target="_blank">
          <img src="<?= asset($result->image) ?>" style="max-width: 200px; max-height: 200px" class="do-upload-preview-img">
        </a>
        @endif
        @if(!empty($result->image2))
        <a href="<?= asset($result->image2) ?>" target="_blank">
          <img src="<?= asset($result->image2) ?>" style="max-width: 200px; max-height: 200px" class="do-upload-preview-img">
        </a>
        @endif
      </div>
    </div>
    @endif
  </div>

  <div class="do-footer">
    <a href="{{ route('superuser.penjualan.delivery_order.index') }}" class="btn btn-outline-warning">
      <i class="fa fa-arrow-left"></i> Kembali ke List
    </a>
    {{--<a href="{{ route('superuser.penjualan.delivery_order.print', $result->id) }}" class="btn btn-outline-info" target="_blank">
      <i class="fa fa-file-o"></i> Print DO
    </a>--}}
  </div>

</div>
</div>
@elseif($result->status == 6)
<div class="alert alert-info">
  DO ini belum <strong>Delivered</strong>.
</div>
@endif

<form method="post" action="{{route('superuser.penjualan.delivery_order.packed')}}" id="frmUpdateStatusPacked">
    @csrf
    <input type="hidden" name="id" value="{{$result->id}}">
</form>
<form method="post" action="{{route('superuser.penjualan.delivery_order.sending')}}" id="frmUpdateStatus">
    @csrf
    <input type="hidden" name="id" value="{{$result->id}}">
</form>

<form method="post" action="{{ route('superuser.penjualan.delivery_order.multi_cancel') }}" id="frmCancelStep">
    @csrf
    <input type="hidden" name="ids[]" value="{{ $result->id }}">
</form>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.fileinput')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript">
  $('.js-select2').select2();

    $('#image').fileinput({
      theme: 'explorer-fa',
      browseOnZoneClick: true,
      showCancel: false,
      showClose: false,
      showUpload: false,
      browseLabel: '',
      removeLabel: '',
      fileActionSettings: {
        showDrag: false,
        showRemove: false
      },
    });

    $('#image2').fileinput({
      theme: 'explorer-fa',
      browseOnZoneClick: true,
      showCancel: false,
      showClose: false,
      showUpload: false,
      browseLabel: '',
      removeLabel: '',
      fileActionSettings: {
        showDrag: false,
        showRemove: false
      },
    });

  let idx = 0;
  $(function(){
    $(document).on('click','.btn-delivery',function(){
      if(confirm("Apakah anda yakin ingin mengubah status orderan ini menjadi delivery? ")){
        $('#frmUpdateStatus').submit();
      }
    })

    $(document).on('click','.btn-delivered',function(){
      if(confirm("Apakah anda yakin ingin mengubah status orderan ini menjadi delivered? ")){
        $('#frmSent').submit();
      }
    })
    
  })

  function konfirmasiBarang() {

    let total = $(".confirm-item").length;
    let checked = $(".confirm-item:checked").length;

    if (total === 0) {
        Swal.fire('Warning!', 'Tidak ada item untuk dikonfirmasi.', 'warning');
        return;
    }

    if (checked !== total) {
        Swal.fire(
            'Warning!',
            'Seluruh item harus dikonfirmasi terlebih dahulu.',
            'warning'
        );
        return;
    }

    $('#frmUpdateStatusPacked input[name="confirmed_items[]"]').remove();

    $(".confirm-item:checked").each(function() {
        $('#frmUpdateStatusPacked').append(
            '<input type="hidden" name="confirmed_items[]" value="'+$(this).val()+'">'
        );
    });

    if (confirm("Apakah anda yakin ingin mengubah status orderan ini menjadi packed?")) {
        $('#frmUpdateStatusPacked').submit();
    }
  }

  // ============================================================
  // Kembali ke Packing Order (status 3 -> 2 / Draft).
  // KHUSUS aksi ini kita JANGAN andalkan redirect()->back() dari
  // backend, karena detail_new.blade.php TIDAK punya blok tampilan
  // buat status 2 (Draft) - kalau balik ke halaman ini lagi hasilnya
  // blank/error. Makanya submit-nya dipaksa lewat AJAX, dan sesudah
  // selesai kita redirect MANUAL ke index, apapun respon backend-nya.
  // ============================================================
  $(document).on('click', '#btnCancelToDraft', function () {
      Swal.fire({
          title: 'Yakin ingin mengembalikan ke Packing Order?',
          text: "Status akan diturunkan ke Draft.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Ya, Kembalikan',
          cancelButtonText: 'Batal'
      }).then((result) => {
          if (result.isConfirmed) {
              $.ajax({
                  url: $('#frmCancelStep').attr('action'),
                  method: 'POST',
                  data: $('#frmCancelStep').serialize(),
                  complete: function () {
                      // Apapun hasilnya (sukses/gagal), jangan diam di halaman ini.
                      window.location.href = "{{ route('superuser.penjualan.delivery_order.index') }}";
                  }
              });
          }
      });
  });

  function changeStep(stepNumber) {
    $(".wizard .step").removeClass('active');
    $(".wizard .step-container").removeClass('active');

    $("#step" + stepNumber).addClass('active');
    $("#step" + stepNumber + "Container").addClass('active');
  }

  function previewImages(event) {
    var preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    
    var files = event.target.files;
    for (var i = 0; i < files.length; i++) {
        var file = files[i];
        var reader = new FileReader();
        
        reader.onload = function(e) {
            var img = document.createElement('img');
            img.src = e.target.result;
            img.style.width = '150px';
            preview.appendChild(img);
        }
        
        reader.readAsDataURL(file);
    }
  }

  $(document).on('click', '.btn-cancel-step', function () {

      Swal.fire({
          title: 'Yakin ingin membatalkan step ini?',
          text: "Status akan diturunkan satu level.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Ya, Turunkan',
          cancelButtonText: 'Batal'
      }).then((result) => {
          if (result.isConfirmed) {
              $('#frmCancelStep').submit();
          }
      });

  });
</script>
@endpush