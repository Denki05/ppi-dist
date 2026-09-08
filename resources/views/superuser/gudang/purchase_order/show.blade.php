@extends('superuser.app')

@push('styles')
<style>
  @include('superuser.gudang.purchase_order._po_styles')
</style>
@endpush

@section('content')
@php
  $poBrandName = optional($purchase_order->brandLokal)->brand_name ?? '';
  $isDraft = $purchase_order->status == $purchase_order::STATUS['DRAFT'];
  $allDetails = $purchase_order->purchase_order_detail;
  $orphans = $allDetails->filter(function($d) use ($fixed_pack_ids) {
    return !in_array($d->packaging_id, $fixed_pack_ids);
  });
@endphp

<nav class="breadcrumb bg-white push py-10 mb-10">
  <span class="breadcrumb-item">Gudang</span>
  <span class="breadcrumb-item">Purchase Order (PO)</span>
  <span class="breadcrumb-item active">{{ $purchase_order->code }}</span>
</nav>

<div class="po-wrap">
  {{-- ===== KIRI: info PO ===== --}}
  <div class="po-col-left">
    <div class="po-info-card">
      <p class="po-info-code">{{ $purchase_order->code }}</p>
      <span class="po-status-pill {{ $isDraft ? 'is-draft' : 'is-live' }}"><span class="dot"></span>{{ $purchase_order->status() }}</span>

      <ul class="po-info-list">
        <li>
          <span class="po-info-icon"><i class="fa fa-warehouse"></i></span>
          <span class="po-info-body"><span class="po-info-label">Warehouse</span><span class="po-info-val" title="{{ optional($purchase_order->warehouse)->name ?? '-' }}">{{ optional($purchase_order->warehouse)->name ?? '-' }}</span></span>
        </li>
        <li>
          <span class="po-info-icon"><i class="fa fa-tag"></i></span>
          <span class="po-info-body"><span class="po-info-label">Brand</span><span class="po-info-val" title="{{ $poBrandName ?: '-' }}">{{ $poBrandName ?: '-' }}</span></span>
        </li>
        <li>
          <span class="po-info-icon"><i class="fa fa-calendar"></i></span>
          <span class="po-info-body"><span class="po-info-label">ETD</span><span class="po-info-val">{{ $purchase_order->etd ? \Carbon\Carbon::parse($purchase_order->etd)->format('d-m-Y') : '-' }}</span></span>
        </li>
        <li>
          <span class="po-info-icon"><i class="fa fa-sticky-note"></i></span>
          <span class="po-info-body"><span class="po-info-label">Note</span><span class="po-info-val" title="{{ $purchase_order->note ?: '-' }}">{{ $purchase_order->note ?: '-' }}</span></span>
        </li>
      </ul>

      <div class="po-info-actions">
        <a href="{{ route('superuser.gudang.purchase_order.index') }}" class="btn btn-sm btn-back"><i class="fa fa-arrow-left"></i>Kembali ke daftar PO</a>
        <a href="{{ route('superuser.gudang.purchase_order.print_pdf', $purchase_order->id) }}" target="_blank" class="btn btn-sm btn-edit"><i class="fa fa-print"></i>Cetak PDF</a>
      </div>
    </div>
  </div>

  {{-- ===== KANAN: daftar produk per kemasan (read-only) ===== --}}
  <div class="po-col-right">
    <div class="po-main-card">
      <div class="po-main-inner">
        <div class="po-tabs-wrap">
          <ul class="nav nav-tabs" role="tablist">
            @foreach($pack_tabs as $i => $tab)
            @php $tabCount = $allDetails->whereIn('packaging_id', $tab['pack_ids'])->count(); @endphp
            <li class="nav-item">
              <a class="nav-link {{ $i == 0 && $orphans->count() == 0 ? 'active' : '' }}" data-toggle="tab" href="#tab-{{ $tab['key'] }}" role="tab" title="{{ implode(', ', $tab['pack_names']) }}">{{ implode(' / ', $tab['pack_names']) }} <span class="badge badge-primary">{{ $tabCount }}</span></a>
            </li>
            @endforeach
            @if($orphans->count() > 0)
            <li class="nav-item">
              <a class="nav-link {{ $allDetails->whereIn('packaging_id', $fixed_pack_ids)->count() == 0 ? 'active' : '' }}" data-toggle="tab" href="#tab-lainnya" role="tab">Lainnya <span class="badge badge-secondary">{{ $orphans->count() }}</span></a>
            </li>
            @endif
          </ul>

          <div class="tab-content">
            @foreach($pack_tabs as $i => $tab)
            @php $tabDetails = $allDetails->whereIn('packaging_id', $tab['pack_ids']); @endphp
            <div class="tab-pane {{ $i == 0 && $orphans->count() == 0 ? 'active' : '' }}" id="tab-{{ $tab['key'] }}" role="tabpanel">
              <div class="po-panel">
                <div class="table-responsive po-table-scroll">
                  <table class="table table-sm table-bordered table-striped mb-0">
                    <thead class="thead-light">
                      <tr>
                        <th class="text-center" style="width:45px;">#</th>
                        <th class="text-center">Kemasan</th>
                        <th class="text-center">Produk</th>
                        <th class="text-center" style="width:80px;">Qty</th>
                        <th class="text-center">Note</th>
                        <th class="text-center">Customer</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse($tabDetails as $row)
                        <tr>
                          <td class="text-center">{{ $loop->iteration }}</td>
                          <td class="text-center">{{ $row->product_pack && $row->product_pack->packaging ? $row->product_pack->packaging->pack_name : '-' }}</td>
                          <td class="text-center">{{ optional($row->product_pack)->code ?? '-' }} - {{ optional($row->product_pack)->name ?? '-' }}</td>
                          <td class="text-center">{{ $row->quantity }}</td>
                          <td class="text-center">{{ $row->note_produksi ?: '-' }}</td>
                          <td class="text-center">{{ $row->note_repack ?: '-' }}</td>
                        </tr>
                      @empty
                        <tr class="empty-state"><td colspan="6" class="text-center">
                          <div class="empty-title">Belum ada produk di halaman ini</div>
                        </td></tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            @endforeach

            @if($orphans->count() > 0)
            <div class="tab-pane {{ $allDetails->whereIn('packaging_id', $fixed_pack_ids)->count() == 0 ? 'active' : '' }}" id="tab-lainnya" role="tabpanel">
              <div class="po-panel">
                <div class="table-responsive po-table-scroll">
                  <table class="table table-sm table-bordered table-striped mb-0">
                    <thead class="thead-light">
                      <tr>
                        <th class="text-center" style="width:45px;">#</th>
                        <th class="text-center">Kode</th>
                        <th class="text-center">Nama Varian</th>
                        <th class="text-center" style="width:80px;">Qty</th>
                        <th class="text-center">Kemasan</th>
                        <th class="text-center">Note</th>
                        <th class="text-center">Customer</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($orphans as $row)
                        <tr>
                          <td class="text-center">{{ $loop->iteration }}</td>
                          <td class="text-center">{{ optional($row->product_pack)->code ?? '-' }}</td>
                          <td>{{ optional($row->product_pack)->name ?? '-' }}</td>
                          <td class="text-center">{{ $row->quantity }}</td>
                          <td class="text-center">{{ $row->product_pack && $row->product_pack->packaging ? $row->product_pack->packaging->pack_name : '-' }}</td>
                          <td class="text-center">{{ $row->note_produksi ?: '-' }}</td>
                          <td class="text-center">{{ $row->note_repack ?: '-' }}</td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
