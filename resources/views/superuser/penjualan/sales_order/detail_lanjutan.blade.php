@extends('superuser.app')

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

@php
  $kursView = $kurs_view ?? $result->idr_rate;
  $subTotalItems = collect($view_items ?? [])->sum('total_idr');
  $discAgenPercent = $packing_detail->discount_1 ?? $result->disc_percent ?? 0;
  $discAgenIdr = $packing_detail->discount_1_idr ?? ($data_kalkulasi['disc_agen_idr'] ?? 0);
  $discKemasanPercent = $packing_detail->discount_2 ?? $result->disc_kemasan ?? 0;
  $discKemasanIdr = $packing_detail->discount_2_idr ?? 0;
  $discTambahanIdr = $packing_detail->discount_idr ?? $result->disc_idr ?? 0;
  $voucherIdr = $packing_detail->voucher_idr ?? 0;
  $ongkirIdr = $packing_detail->delivery_cost_idr ?? 0;
  $grandTotalIdr = $packing_detail->grand_total_idr ?? ($data_kalkulasi['grand_total'] ?? 0);
  $fmt = function ($v) { return number_format((float) ($v ?? 0), 0, ',', '.'); };
@endphp

<div class="alert alert-info" role="alert">
  View only — detail SO TUTUP <b>{{ $result->code ?? $result->so_code }}</b> (DO: <b>{{ $packing_order->do_code ?? $packing_order->code ?? '-' }}</b>). Semua field readonly, tidak ada tombol Save.
</div>

<div class="row">
  <div class="col-6">
    <div class="block">
      <div class="block-header block-header-default">
        <h3 class="block-title">#Detail Nota</h3>
      </div>
      <div class="block-content">
        <div class="form-row">
          <div class="form-group col-md-6">
            <label>Tanggal Nota</label>
            <input type="text" class="form-control" value="{{ $result->so_date ?? '-' }}" readonly>
          </div>
          <div class="form-group col-md-6">
            <label>Type Transaksi</label>
            <input type="text" class="form-control" value="{{ $result->type_transaction }}" readonly>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-md-6">
            <label>Gudang</label>
            <input type="text" class="form-control" value="{{ $result->origin_warehouse->name ?? '-' }}" readonly>
          </div>
          <div class="form-group col-md-6">
            <label>Ekspedisi</label>
            <input type="text" class="form-control" value="{{ $ekspedisi_display ?? '-' }}" readonly>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-md-6">
            <label>Sales Senior</label>
            <input type="text" class="form-control" value="{{ $result->so_sales_senior() ?? '-' }}" readonly>
          </div>
          <div class="form-group col-md-6">
            <label>Sales</label>
            <input type="text" class="form-control" value="{{ $result->so_sales() ?? '-' }}" readonly>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-md-6">
            <label>Catatan</label>
            <textarea class="form-control" rows="1" readonly>{{ $result->note }}</textarea>
          </div>
          <div class="form-check-inline">
            <label class="form-check-label">
              <input type="checkbox" class="form-check-input" value="1" @if(!empty($result->shipping_cost_buyer)) checked @endif disabled>Bayar ditempat
            </label>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-6">
    <div class="row">
      <div class="col">
        <div class="block">
          <div class="block-header block-header-default">
            <h3 class="block-title">#Customer Info</h3>
          </div>
          <div class="block-content">
            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Customer</label>
                <input type="text" class="form-control" value="{{ $customer_name ?? '-' }}" readonly>
              </div>
              <div class="form-group col-md-6">
                <label>Alamat Kirim</label>
                <textarea class="form-control" rows="1" readonly>{{ $customer_address ?? '-' }}</textarea>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-6">
                <label>Kota</label>
                <input type="text" class="form-control" value="{{ $customer_kota ?? '-' }}" readonly>
              </div>
              <div class="form-group col-md-6">
                <label>Provinsi</label>
                <input type="text" class="form-control" value="{{ $customer_provinsi ?? '-' }} " readonly>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col">
        <div class="block">
          <div class="block-content">
            <div class="form-row">
              <div class="form-group col-md-4">
                <label>Rekening</label>
                <input type="text" class="form-control" value="{{ $rekening_display ?? '-' }}" readonly>
              </div>
              <div class="form-group col-md-4">
                <label>Kurs</label>
                <input type="text" class="form-control" value="{{ $fmt($kursView) }}" readonly>
              </div>
              <div class="form-group col-md-4">
                <label>Disc Cash</label>
                <input type="text" class="form-control" value="${{ (float) ($result->disc_usd ?? 0) }}" readonly>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <aside class="col-lg-9">
    <div class="card border-0">
      <div class="table-responsive">
        <table class="table table-hover" id="datatables" style="white-space:nowrap;width:100%;">
          <thead class="text-muted">
            <tr class="small text-uppercase">
              <th class="block" style="width:auto">#</th>
              <th class="block" style="width:10%">Product</th>
              <th class="block" style="width:auto">Qty</th>
              <th class="block" style="width:15%">Harga</th>
              <th class="block" style="width:auto">Free</th>
              <th class="block" style="width:20%">Kemasan</th>
              <th class="block" style="width:2%">Disc (USD)</th>
              <th class="block" style="width:30%">Total</th>
            </tr>
          </thead>
          <tbody>
            @if(count($view_items ?? []) <= 0)
              <tr>
                <td colspan="8" align="center">Data tidak ditemukan</td>
              </tr>
            @endif
            @foreach($view_items ?? [] as $index => $item)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item['code'] }} - <b>{{ $item['name'] }}</b> - {{ $item['warehouse'] }}</td>
                <td>{{ $item['qty'] }}</td>
                <td>{{ $item['price'] }}</td>
                <td>@if($item['free']) YES @else NO @endif</td>
                <td>{{ $item['kemasan'] }}</td>
                <td>{{ $item['usd_disc'] }}</td>
                <td>{{ $fmt($item['total_idr']) }}</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr class="row-footer-subtotal">
              <td colspan="7" class="text-right">
                <b>Subtotal</b>
              </td>
              <td class="text-right">
                <input type="text" class="form-control" value="{{ $fmt($subTotalItems) }}" readonly>
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </aside>
  <aside class="col-lg-3">
    <div class="card border-0">
      <div class="card-body">
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Disc %</label>
          <div class="col-sm-3">
            <input type="text" class="form-control" value="{{ $discAgenPercent }}" readonly>
          </div>
          <div class="col-sm-5">
            <input type="text" readonly class="form-control" value="{{ $fmt($discAgenIdr) }}">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Disc Kemasan</label>
          <div class="col-sm-3">
            <input type="text" class="form-control" value="{{ $discKemasanPercent }}" readonly>
          </div>
          <div class="col-sm-5">
            <input type="text" readonly class="form-control" value="{{ $fmt($discKemasanIdr) }}">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Disc IDR</label>
          <div class="col-sm-8">
            <input type="text" class="form-control" value="{{ $fmt($discTambahanIdr) }}" readonly>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Voucher</label>
          <div class="col-sm-8">
            <input type="text" class="form-control" value="{{ $fmt($voucherIdr) }}" readonly>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Ongkir</label>
          <div class="col-sm-8">
            <input type="text" class="form-control" value="{{ $fmt($ongkirIdr) }}" readonly>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Grand Total</label>
          <div class="col-sm-8">
            <input type="text" class="form-control" value="{{ $fmt($grandTotalIdr) }}" readonly>
          </div>
        </div>
      </div>
    </div>
  </aside>
</div>

<div class="row pt-30 mb-15">
  <div class="col-md-6">
    <a href="{{ route('superuser.penjualan.sales_order.index_lanjutan') }}">
      <button type="button" class="btn bg-gd-cherry border-0 text-white">
        <i class="fa fa-arrow-left mr-10"></i> Back
      </button>
    </a>
  </div>
</div>

@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
  $(document).ready(function () {
    $('.js-select2').select2();
    $('#datatables').DataTable({
      paging: false,
      searching: false,
      info: false,
      scrollY: '430px',
      scrollCollapse: true,
    });
  });
</script>
@endpush
