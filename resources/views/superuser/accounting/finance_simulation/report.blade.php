@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Laporan</span>
  <span class="breadcrumb-item">Accounting</span>
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
  </div>
  <div class="block-content block-content-full">
    <div class="form-group row">
      <label class="col-md-2 col-form-label text-left" for="period">Bulan :</label>
      <div class="col-md-3">
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
      <div class="col-md-3">
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
      <div class="col-md-2">
        <button id="filterButton" class="btn btn-primary">Filter</button>
      </div>
    </div>
    @if(!empty($selectedBulan) && !empty($selectedTahun))
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
      <tfoot>
        <tr>
          <th colspan="8" class="text-right">Total:</th>
          <th id="totalUVBeli" class="text-center"></th>
          <th id="totalUVJual" class="text-center"></th>
          <th id="totalReal" class="text-center"></th>
        </tr>
      </tfoot>
    </table>
    @else
    <div class="alert alert-warning">
      <p>Silakan pilih <b>Bulan</b> dan <b>Tahun</b> lalu klik tombol <b>Filter</b> untuk menampilkan data.</p>
    </div>
    @endif
  </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.datatables-button')

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
    $('.js-select2').select2();

    $('#datatable').DataTable({
      paging: true,
      searching: true,
      ordering: true,
      order: [[0, 'asc']], // Default ordering by the first column
      pageLength: 10, // Default number of rows per page
      dom: "<'row'<'col-sm-2'l><'col-sm-7 text-left'B><'col-sm-3'f>>" +
                                "<'row'<'col-sm-12'tr>>" +
                                "<'row'<'col-sm-5'i><'col-sm-7'p>>",
      buttons: [
        {
          extend: 'excelHtml5',
          text: '<i class="fa fa-file-excel-o"></i>',
          titleAttr: 'Excel',
          title: 'Simulation UV Price',
          footer: true,
        },
        {
          extend: 'pdfHtml5',
          orientation: 'landscape',
          pageSize: 'A4',
          text: '<i class="fa fa-file-pdf-o"></i>',
          titleAttr: 'PDF',
          title: 'Simulation Under Value Price',
          footer: true,
        }
      ],
      footerCallback: function(row, data, start, end, display) {
            // Calculate totals
            let totalUVBeli = 0;
            let totalUVJual = 0;
            let totalReal = 0;

            data.forEach(function(row) {
                totalUVBeli += parseFloat(row[8].replace(/,/g, '')) || 0;
                totalUVJual += parseFloat(row[9].replace(/,/g, '')) || 0;
                totalReal += parseFloat(row[10].replace(/,/g, '')) || 0;
            });

            // Update footer
            $('#totalUVBeli').html(totalUVBeli.toLocaleString('en-US', { minimumFractionDigits: 2 }));
            $('#totalUVJual').html(totalUVJual.toLocaleString('en-US', { minimumFractionDigits: 2 }));
            $('#totalReal').html(totalReal.toLocaleString('en-US', { minimumFractionDigits: 2 }));
        }
      });

    // Handle tombol Filter
    $('#filterButton').on('click', function() {
        const bulan = $('#bulan').val();
        const tahun = $('#tahun').val();

        if (!bulan || !tahun) {
            alert('Silakan pilih Bulan dan Tahun terlebih dahulu!');
            return;
        }

        const url = `?bulan=${bulan}&tahun=${tahun}`;
        window.location.href = url; // Redirect with bulan & tahun parameters
    });
});
</script>
@endpush