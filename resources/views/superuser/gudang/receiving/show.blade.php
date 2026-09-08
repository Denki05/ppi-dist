@extends('superuser.app')

@push('styles')
<style>
  @include('superuser.gudang.shared._doc_styles')
</style>
@endpush

@section('content')
@php
  use App\Entities\Gudang\Receiving as RI;
  $role = $superuser->division;
  $isDraft = $receiving->status == RI::STATUS['ACTIVE'];
@endphp

<nav class="breadcrumb bg-white py-10" style="margin-bottom:8px;">
  <span class="breadcrumb-item">Gudang</span>
  <span class="breadcrumb-item">Receiving</span>
  <span class="breadcrumb-item active">{{ $receiving->code }}</span>
</nav>

<div class="po-wrap">
  {{-- ===== KIRI: info receiving ===== --}}
  <div class="po-col-left">
    <div class="po-info-card">
      <p class="po-info-code">{{ $receiving->code }}</p>
      <span class="po-status-pill {{ $isDraft ? 'is-draft' : 'is-live' }}"><span class="dot"></span>{{ $receiving->status() }}</span>

      <ul class="po-info-list">
        <li>
          <span class="po-info-icon"><i class="fa fa-warehouse"></i></span>
          <span class="po-info-body"><span class="po-info-label">Warehouse</span><span class="po-info-val" title="{{ optional($receiving->warehouse)->name ?? '-' }}">{{ optional($receiving->warehouse)->name ?? '-' }}</span></span>
        </li>
        <li>
          <span class="po-info-icon"><i class="fa fa-exchange"></i></span>
          <span class="po-info-body"><span class="po-info-label">Tipe</span><span class="po-info-val">{{ $receiving->type() }}</span></span>
        </li>
        <li>
          <span class="po-info-icon"><i class="fa fa-calendar"></i></span>
          <span class="po-info-body"><span class="po-info-label">PBM Date</span><span class="po-info-val">{{ $receiving->pbm_date ? date('d-m-Y', strtotime($receiving->pbm_date)) : '-' }}</span></span>
        </li>
        <li>
          <span class="po-info-icon"><i class="fa fa-sticky-note"></i></span>
          <span class="po-info-body"><span class="po-info-label">Note</span><span class="po-info-val" title="{{ $receiving->note ?: '-' }}">{{ $receiving->note ?: '-' }}</span></span>
        </li>
      </ul>

      <div class="po-info-actions">
        <a href="{{ route('superuser.gudang.receiving.index') }}" class="btn btn-sm btn-back"><i class="fa fa-arrow-left"></i>Kembali ke daftar</a>

        @if($receiving->status == RI::STATUS['ACTIVE'] && in_array($role, ['Admin','Developer']))
          <a href="{{ route('superuser.gudang.receiving.edit', $receiving->id) }}" class="btn btn-sm btn-edit"><i class="fa fa-pencil"></i>Edit</a>
          <a href="{{ route('superuser.gudang.receiving.publish', $receiving->id) }}" class="btn btn-sm btn-publish"><i class="fa fa-check"></i>Publish to QC</a>
          <a href="javascript:deleteConfirmation('{{ route('superuser.gudang.receiving.destroy', $receiving->id) }}', true)" class="btn btn-sm btn-danger-ghost"><i class="fa fa-trash"></i>Delete</a>
        @elseif($receiving->status == RI::STATUS['QC'] && in_array($role, ['Warehouse','Developer']))
          <a href="{{ route('superuser.gudang.receiving.publish', $receiving->id) }}" class="btn btn-sm btn-publish"><i class="fa fa-check"></i>Publish to Ready</a>
        @elseif($receiving->status == RI::STATUS['READY'] && in_array($role, ['Admin','Developer']))
          <a href="javascript:saveConfirmation2('{{ route('superuser.gudang.receiving.acc_ri', $receiving->id) }}')" class="btn btn-sm btn-publish" title="ACC"><i class="fa fa-check"></i>ACC</a>
        @endif
      </div>
    </div>
  </div>

  {{-- ===== KANAN: daftar produk (read-only) ===== --}}
  <div class="po-col-right">
    <div class="po-main-card">
      <div class="po-main-inner">
        <div class="po-panel">
          <div class="po-section-label" style="margin:10px 0 0 12px;">Daftar produk ({{ $receiving->details->count() }})</div>
          <div class="table-responsive po-table-scroll mt-5">
            <table id="datatable" class="table table-sm table-bordered table-striped mb-0">
              <thead class="thead-light">
                <tr>
                  <th class="text-center" style="width:45px;">#</th>
                  <th class="text-center">Produk</th>
                  <th class="text-center" style="width:90px;">Qty PO</th>
                  <th class="text-center" style="width:90px;">Qty RI</th>
                  <th class="text-center" style="width:90px;">Kurang</th>
                  <th class="text-center">No Batch</th>
                  <th class="text-center">Note</th>
                </tr>
              </thead>
              <tbody>
                @forelse($receiving->details as $detail)
                <tr>
                  <td class="text-center">{{ $loop->iteration }}</td>
                  <td>{{ optional($detail->product_pack)->code ?? '-' }} - <b>{{ optional($detail->product_pack)->name ?? '-' }}</b>{{ $detail->product_pack && $detail->product_pack->packaging ? ' - ' . $detail->product_pack->packaging->pack_name : '' }}</td>
                  <td class="text-center">{{ $detail->quantity_po }}</td>
                  <td class="text-center">{{ $detail->quantity_ri ?? '-' }}</td>
                  <td class="text-center">{{ $detail->selisih ?? '-' }}</td>
                  <td class="text-center">{{ $detail->no_batch ?? '-' }}</td>
                  <td class="text-center">{{ $detail->note ?: '-' }}</td>
                </tr>
                @empty
                <tr class="empty-state"><td colspan="7" class="text-center">
                  <i class="fa fa-inbox"></i>
                  <div class="empty-title">Belum ada produk di receiving ini</div>
                </td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    $('#datatable').DataTable({});
  })
</script>
@endpush
