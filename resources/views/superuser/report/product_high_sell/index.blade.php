@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Laporan</span>
  <span class="breadcrumb-item">Operasional</span>
  <span class="breadcrumb-item">Produk</span>
  <span class="breadcrumb-item active">Produk Penjualan Tertinggi</span>
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

<form action="{{ route('superuser.report.product_high_sell.print_report') }}" method="POST">
  @csrf
  <div class="form-group row">
    <div class="col-md-9">
      <div class="block">
        <div class="block-content">
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-left" for="periode_from">Period From :</label>
            <div class="col-md-4">
              <input type="date" class="form-control" name="periode_from" id="periode_from" required value="{{ date('Y-m-01') }}">
            </div>
            <label class="col-md-2 col-form-label text-left" for="brand_name">Brand / Merek :</label>
            <div class="col-md-4">
              <select class="js-select2 form-control" id="brand_name" name="brand_name[]"  data-placeholder="Pilih Brand" multiple required>
                <option value="all">All</option>
                @foreach ($brand as $value)
                  <option value="{{ $value->brand_name }}">{{ $value->brand_name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-left" for="periode_to">Period To :</label>
            <div class="col-md-4">
              <input type="date" class="form-control" name="periode_to" id="periode_to" required value="{{ date('Y-m-d') }}">
            </div>
            <label class="col-md-2 col-form-label text-left" for="kemasan">Kemasan :</label>
            <div class="col-md-4">
              <select class="js-select2 form-control" id="kemasan" name="kemasan[]" data-placeholder="Pilih Kemasan" multiple required>
                <option value="all">All</option>
                @foreach ($kemasan as $value)
                  <option value="{{ $value->pack_name }}">{{ $value->pack_name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group row align-items-center">
            <label class="col-md-2 col-form-label text-left">Type Report :</label>
            <div class="col-md-4">
              <label class="form-check form-check-inline mb-0">
                <input class="form-check-input" type="radio" name="type" value="1" required>
                <span class="ml-1">Semester</span>
              </label>
              <label class="form-check form-check-inline mb-0">
                <input class="form-check-input" type="radio" name="type" value="2" required>
                <span class="ml-1">Zone</span>
              </label>
            </div>
            <label class="col-md-2 col-form-label text-left" for="product">Produk / Variant :</label>
            <div class="col-md-4">
              <select class="js-select2 form-control" id="product" name="product[]" data-placeholder="Pilih Produk (mengikuti Brand & Kemasan)" multiple required>
                <option value="all">All</option>
                @foreach ($product as $value)
                  <option value="{{ $value->id }}" data-brand="{{ $value->brand_name }}" data-pack="{{ optional($value->packaging)->pack_name }}">{{ $value->code }} - {{ $value->name }} / {{ optional($value->packaging)->pack_name }} ({{ $value->brand_name }})</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="block">
        <div class="block-content">
          <div class="form-group row">
            <div class="col-md-12 text-center">
              <button type="submit" class="btn bg-gd-corporate border-0 text-white pl-50 pr-50">
                Download <i class="fa fa-print ml-10"></i>
              </button>
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-12 text-center">
              <a href="#" id="btn-filter" class="btn bg-gd-sea border-0 text-white pl-50 pr-50">
                Filter <i class="fa fa-search ml-10"></i>
              </a>
            </div>
          </div>

          <div class="form-group row">
            <div class="col-md-12 text-center">
              <button type="button" id="btn-reset" class="btn btn-warning">
                Reset <i class="fa fa-refresh ml-5"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group row">
    <div class="block">
      <div class="block-content">
        <div class="row mb-2 align-items-center">
          <div class="col-md-6" id="grouping-wrap">
            <label class="form-check mb-0">
              <input class="form-check-input" type="checkbox" id="grouping-toggle" checked>
              <span class="ml-1">Grouping per Produk (1 produk banyak kemasan + total)</span>
            </label>
          </div>
          <div class="col-md-6 text-right">
            <small class="text-muted" id="mode-info">Mode: Ringkas per Produk. Pilih Type Report + Filter untuk grouping ala Crystal (Merek &gt; Semester/Zona &gt; Variant &gt; Customer).</small>
          </div>
        </div>
        <table id="datatables" class="table table-striped table-vcenter" style="width:100%">
          <thead>
            <tr>
              <th class="text-center">Merek</th>
              <th class="text-center">Variant</th>
              <th class="text-center">Kemasan</th>
              <th class="text-center">Qty</th>
            </tr>
          </thead>
            <tbody>
            </tbody>
          <tfoot>
            <tr>
              <th colspan="3" class="text-right">Grand Total Qty :</th>
              <th class="text-right" id="grand-total">0</th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</form>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.datatables-button')

@push('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        var start_date = $('#periode_from').val();
        var end_date = $('#periode_to').val();

        $('.js-select2').select2();

        let datatableUrl = '{{ route('superuser.report.product_high_sell.json') }}';
        let firstDatatableUrl = datatableUrl + '?start_date=' + start_date + '&end_date=' + end_date;

        var datatable = null;
        var currentMode = 'aggregate'; // aggregate | semester | zona

        function fmtQty(data) {
          var n = parseFloat(data) || 0;
          return n.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        function sumQty(data) {
          var total = 0;
          data.forEach(function(r) { total += parseFloat(r.total_qty) || 0; });
          return total;
        }

        function headAggregate() {
          $('#datatables thead tr').html(
            '<th class="text-center">Merek</th>' +
            '<th class="text-center">Variant</th>' +
            '<th class="text-center">Kemasan</th>' +
            '<th class="text-center">Qty</th>'
          );
          $('#datatables tfoot tr').html(
            '<th colspan="3" class="text-right">Grand Total Qty :</th>' +
            '<th class="text-right" id="grand-total">0</th>'
          );
        }

        function headDetail(grupLabel) {
          $('#datatables thead tr').html(
            '<th class="text-center">Merek</th>' +
            '<th class="text-center">' + grupLabel + '</th>' +
            '<th class="text-center">Variant</th>' +
            '<th class="text-center">Customer</th>' +
            '<th class="text-center">Qty</th>' +
            '<th class="text-center">Total Variant</th>' +
            '<th class="text-center">Urut</th>'
          );
          $('#datatables tfoot tr').html(
            '<th class="text-right">Grand Total Qty :</th>' +
            '<th class="text-right" id="grand-total">0</th>'
          );
        }

        function destroyTable() {
          if (datatable) {
            datatable.destroy();
            datatable = null;
          }
          $('#datatables tbody').empty();
        }

        var spinnerLang = {
          processing: "<span class='fa-stack fa-lg'>\n\
                                    <i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\
                            </span>",
        };
        var domButtons = "<'row'<'col-sm-2'l><'col-sm-7 text-left'B><'col-sm-3'f>>" +
          "<'row'<'col-sm-12'tr>>" +
          "<'row'<'col-sm-5'i><'col-sm-7'p>>";

        function initAggregate(url) {
          destroyTable();
          headAggregate();
          $('#grouping-wrap').show();
          $('#mode-info').text('Mode: Ringkas per Produk. Pilih Type Report + Filter untuk grouping ala Crystal (Merek > Semester/Zona > Variant > Customer).');
          currentMode = 'aggregate';
          datatable = $('#datatables').DataTable({
            language: spinnerLang,
            processing: true,
            serverSide: false,
            ajax: {
              "url": url,
              "dataType": "json",
              "type": "GET",
              "data": { _token: "{{ csrf_token() }}" }
            },
            columns: [
              { data: 'brand', name: 'master_products.brand_name' },
              { data: 'variant' },
              { data: 'kemasan', name: 'master_packaging.pack_name' },
              { data: 'total_qty', className: 'text-right', render: function(data) { return fmtQty(data); } },
              { data: 'product_total', visible: false, searchable: false },
            ],
            order: [[4, 'desc'], [1, 'asc'], [3, 'desc']],
            rowGroup: {
              dataSrc: 1,
              startRender: function(rows, group) {
                if (!$('#grouping-toggle').is(':checked')) { return null; }
                var total = rows.data().pluck('total_qty').reduce(function(a, b) {
                  return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                }, 0);
                var brand = rows.data()[0] ? rows.data()[0].brand : '';
                var jml = rows.count();
                return $('<tr/>')
                  .append('<td><strong>' + brand + '</strong></td>')
                  .append('<td><strong>' + group + ' — Total: ' + fmtQty(total) + ' (' + jml + ' kemasan)</strong></td>')
                  .append('<td class="text-center"><strong>' + jml + ' kemasan</strong></td>')
                  .append('<td class="text-right"><strong>' + fmtQty(total) + '</strong></td>');
              }
            },
            footerCallback: function(row, data, start, end, display) {
              $('#grand-total').html(fmtQty(sumQty(data)));
            },
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            dom: domButtons,
            buttons: [
              { extend: 'excelHtml5', text: '<i class="fa fa-file-excel-o"></i>', titleAttr: 'Excel', title: 'Product-High Sell', exportOptions: { columns: [0, 1, 2, 3] } },
              { extend: 'pdfHtml5', orientation: 'portrait', pageSize: 'A5', text: '<i class="fa fa-file-pdf-o"></i>', titleAttr: 'PDF', title: 'Product-High Sell', exportOptions: { columns: [0, 1, 2, 3] } }
            ],
          });
        }

        // Preview detail ala Crystal Report: Merek > Semester/Zona > Variant > Customer + TOTAL per variant.
        function initDetail(url, grupLabel) {
          destroyTable();
          headDetail(grupLabel);
          $('#grouping-wrap').hide();
          $('#mode-info').text('Mode: Detail ' + grupLabel + ' ala Crystal Report (Merek > ' + grupLabel + ' > Variant > Customer).');
          currentMode = 'detail';
          datatable = $('#datatables').DataTable({
            language: spinnerLang,
            processing: true,
            serverSide: false,
            ajax: {
              "url": url,
              "dataType": "json",
              "type": "GET",
              "data": { _token: "{{ csrf_token() }}" }
            },
            columns: [
              { data: 'brand', visible: false },
              { data: 'grup', visible: false },
              { data: 'variant_full', visible: false },
              { data: 'customer' },
              { data: 'total_qty', className: 'text-right', render: function(data) { return fmtQty(data); } },
              { data: 'variant_total', visible: false, searchable: false },
              { data: 'grup_order', visible: false, searchable: false },
            ],
            order: [[0, 'asc'], [6, 'asc'], [5, 'desc'], [4, 'desc']],
            paging: false,
            rowGroup: {
              dataSrc: [0, 1, 2],
              startRender: function(rows, group, level) {
                if (level === 0) {
                  return $('<tr/>').append('<td colspan="2" style="background:#d6d8db;"><strong>MEREK : ' + group + '</strong></td>');
                }
                if (level === 1) {
                  return $('<tr/>').append('<td colspan="2" style="background:#e9ecef;padding-left:20px;"><strong>' + group + '</strong></td>');
                }
                var t = rows.data()[0] ? (parseFloat(rows.data()[0].variant_total) || 0) : 0;
                var jml = rows.count();
                return $('<tr/>').append('<td colspan="2" style="background:#f8f9fa;padding-left:40px;"><strong>' + group + ' — Total: ' + fmtQty(t) + ' (' + jml + ' customer)</strong></td>');
              },
              endRender: function(rows, group, level) {
                if (level !== 2) { return null; }
                var total = rows.data().pluck('total_qty').reduce(function(a, b) {
                  return (parseFloat(a) || 0) + (parseFloat(b) || 0);
                }, 0);
                return $('<tr/>')
                  .append('<td class="text-right">TOTAL :</td>')
                  .append('<td class="text-right"><strong>' + fmtQty(total) + '</strong></td>');
              }
            },
            footerCallback: function(row, data, start, end, display) {
              $('#grand-total').html(fmtQty(sumQty(data)));
            },
            dom: "<'row'<'col-sm-7 text-left'B><'col-sm-5'f>>" +
              "<'row'<'col-sm-12'tr>>" +
              "<'row'<'col-sm-12'i>>",
            buttons: [
              { extend: 'excelHtml5', text: '<i class="fa fa-file-excel-o"></i>', titleAttr: 'Excel', title: 'Product-High Sell-Detail', exportOptions: { columns: [0, 1, 2, 3, 4] } },
              { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'A4', text: '<i class="fa fa-file-pdf-o"></i>', titleAttr: 'PDF', title: 'Product-High Sell-Detail', exportOptions: { columns: [0, 1, 2, 3, 4] } }
            ],
          });
        }

        initAggregate(firstDatatableUrl);

        $('#grouping-toggle').on('change', function() {
          if (currentMode !== 'aggregate' || !datatable) { return; }
          if ($(this).is(':checked')) {
            datatable.order([[4, 'desc'], [1, 'asc'], [3, 'desc']]).draw();
          } else {
            datatable.order([[3, 'desc']]).draw();
          }
        });

        $('#btn-filter').on('click', function(e) {
          e.preventDefault();
          var brand = $('#brand_name').val();
          var kemasan = $('#kemasan').val();
          var product = $('#product').val();
          let periode_from = $("#periode_from").val();
          let periode_to = $("#periode_to").val();
          var type = $('input[name="type"]:checked').val();

          let newDatatableUrl = datatableUrl + '?start_date=' + periode_from + '&end_date=' + periode_to +
            '&brand=' + brand + '&kemasan=' + kemasan + '&product=' + product + (type ? '&type=' + type : '');
          if (type === '1') {
            initDetail(newDatatableUrl, 'Semester');
          } else if (type === '2') {
            initDetail(newDatatableUrl, 'Zona');
          } else {
            initAggregate(newDatatableUrl);
          }
        })

        // Urutan cascading: Brand -> Kemasan -> Produk (mengikuti Brand & Kemasan)
        function selectedOrAll(val) {
          if (!val || val.length === 0) return [];
          return val;
        }

        function refreshKemasanOptions() {
          var brands = selectedOrAll($('#brand_name').val());
          var useAllBrand = brands.length === 0 || brands.includes('all');
          // Kumpulkan kemasan yang tersedia untuk brand terpilih dari opsi produk
          var available = {};
          $('#product option').each(function() {
            var b = $(this).data('brand');
            var p = $(this).data('pack');
            if (!p) return;
            if (useAllBrand || (b && brands.includes(b.toString()))) {
              available[p] = true;
            }
          });
          $('#kemasan option').each(function() {
            var v = $(this).val();
            if (v === 'all') return;
            $(this).prop('disabled', !(v in available));
          });
        }

        function refreshProductOptions() {
          var brands = selectedOrAll($('#brand_name').val());
          var packs = selectedOrAll($('#kemasan').val());
          var useAllBrand = brands.length === 0 || brands.includes('all');
          var useAllPack = packs.length === 0 || packs.includes('all');
          $('#product option').each(function() {
            var v = $(this).val();
            if (v === 'all') return;
            var b = $(this).data('brand');
            var p = $(this).data('pack');
            var showBrand = useAllBrand || (b && brands.includes(b.toString()));
            var showPack = useAllPack || (p && packs.includes(p.toString()));
            $(this).prop('disabled', !(showBrand && showPack));
          });
        }

        $('#brand_name').on('change', function() {
          refreshKemasanOptions();
          refreshProductOptions();
        });
        $('#kemasan').on('change', function() {
          refreshProductOptions();
        });
        refreshKemasanOptions();
        refreshProductOptions();

        $('#btn-reset').on('click', function (e) {
          e.preventDefault();

          // Kosongkan pilihan select2 (brand, kemasan, product) + type report
          $('#brand_name').val(null).trigger('change');
          $('#kemasan').val(null).trigger('change');
          $('#product').val(null).trigger('change');
          $('input[name="type"]').prop('checked', false);
          refreshKemasanOptions();
          refreshProductOptions();

          // Reset tanggal ke default (bulan berjalan)
          let defaultStart = '{{ date('Y-m-01') }}';
          let defaultEnd = '{{ date('Y-m-d') }}';
          $('#periode_from').val(defaultStart);
          $('#periode_to').val(defaultEnd);

          // Kembali ke mode ringkas
          let resetUrl = datatableUrl + '?start_date=' + defaultStart + '&end_date=' + defaultEnd;

          initAggregate(resetUrl);
        });
    })
</script>
@endpush