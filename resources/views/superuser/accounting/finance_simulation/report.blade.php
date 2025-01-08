@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Laporan</span>
  <span class="breadcrumb-item">Accountng</span>
  <span class="breadcrumb-item active">Simulation UV Report</span>
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
<div class="block">
  <div class="block-content">
      {{--<button type="button" class="btn btn-outline-primary min-width-125" data-toggle="modal" data-target="#addFinanceCashback">Create</button>--}}
  </div>
  <div class="block-content block-content-full">
    <div class="form-group row">
      <label class="col-md-2 col-form-label text-left" for="period">Bulan :</label>
      <div class="col-md-4">
        <div class="input-group">
          <select id="bulan" name="bulan" class="form-control js-select2">
            <option value="">Pilih Bulan</option>
            @foreach ($availableMonths->groupBy('tahun') as $tahun => $months)
              <optgroup label="Tahun {{ $tahun }}">
                @foreach ($months as $month)
                  <option value="{{ str_pad($month->bulan, 2, '0', STR_PAD_LEFT) }}" 
                    {{ str_pad($month->bulan, 2, '0', STR_PAD_LEFT) == $selectedBulan ? 'selected' : '' }}>
                    {{ $bulan[str_pad($month->bulan, 2, '0', STR_PAD_LEFT)] }}
                  </option>
                @endforeach
              </optgroup>
            @endforeach
          </select>
        </div>
      </div>
      <div class="col-md-4">
        <div class="input-group">
          <select id="tahun" name="tahun" class="form-control js-select2">
            <option value="">Pilih Tahun</option>
            @foreach ($availableMonths->groupBy('tahun')->keys() as $tahun)
              <option value="{{ $tahun }}" {{ $tahun == $selectedTahun ? 'selected' : '' }}>
                {{ $tahun }}
              </option>
            @endforeach
          </select>
        </div>
      </div>
    </div>
    <table id="datatable" class="table table-striped">
      <thead>
        <tr>
          <th>#</th>
          <th>Invoice</th>
          <th>Product</th>
          <th>Kemasan</th>
          <th>UV Beli</th>
          <th>UV Jual</th>
          <th>Harga Real</th>
          <th>Qty</th>
          <th>Total UV Beli</th>
          <th>Total UV Jual</th>
          <th>Total Real</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($simulation as $index => $key)
          @foreach ($key->simulation_detail as $item)
            @php
                $get_price_real = $key->do->do_detail->first()->price ?? 'N/A';
                $cal_price_real = $get_price_real * $key->do->idr_rate;
                $cal_total_real = $cal_price_real * $item->qty;
            @endphp
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>{{ $key->code }}</td>
              <td>{{ $item->product_tax->name ?? 'N/A' }}</td>
              <td>{{ $item->product_tax->packaging->pack_name ?? 'N/A' }}</td>
              <td>{{ number_format($item->price_buying, 2) }}</td>
              <td>{{ number_format($item->price_selling, 2) }}</td>
              <td>{{ number_format($cal_price_real, 2) }}</td>
              <td>{{ $item->qty }}</td>
              <td>{{ number_format($item->subtotal_harga_beli, 2) }}</td>
              <td>{{ number_format($item->subtotal_harga_jual, 2) }}</td>
              <td>{{ number_format($cal_total_real, 2) }}</td>
            </tr>
          @endforeach
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
    $('.js-select2').select2();

    $('#datatable').DataTable({
      paging: true,
      searching: true,
      ordering: true,
      order: [[0, 'asc']], // Default ordering by the first column
      pageLength: 10 // Default number of rows per page
    });

    // Filter data saat bulan atau tahun berubah
    $('#bulan, #tahun').change(function() {
        const bulan = $('#bulan').val();
        const tahun = $('#tahun').val();
        const url = `?bulan=${bulan}&tahun=${tahun}`;
        window.location.href = url; // Redirect dengan parameter bulan & tahun
    });
});
</script>
@endpush