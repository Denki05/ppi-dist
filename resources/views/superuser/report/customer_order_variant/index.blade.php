@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Report</span>
  <span class="breadcrumb-item">Operasional</span>
  <span class="breadcrumb-item active">Customer History</span>
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

<form action="{{ route('superuser.report.customer_order_variant.print_report') }}" method="POST">
  @csrf
  <div class="row">
    <div class="col-10">
      <div class="block">
      <div class="block-content">
        <div class="row">
          <div class="col-md-6">
            <div class="form-group row">
              <label class="col-md-4 col-form-label text-left" for="customer">Customer:</label>
              <div class="col-md-8">
                <select class="js-select2 form-control" id="customer" name="customer[]" data-placeholder="Select Customer" multiple required>
                  <option value="all">All</option>
                  @foreach($customer as $row)
                    <option value="{{ $row->id }}">{{ $row->name }} {{ $row->text_kota }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="form-group row">
              <label class="col-md-4 col-form-label text-left" for="start">Periode From:</label>
              <div class="col-md-8">
                <input type="date" class="form-control" id="start_date" name="start" required value="{{ date('Y-m-01') }}">
              </div>
            </div>

            <div class="form-group row">
              <label class="col-md-4 col-form-label text-left" for="end">Periode To:</label>
              <div class="col-md-8">
                <input type="date" class="form-control" id="end_date" name="end" required value="{{ date('Y-m-d') }}">
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-group row">
              <label class="col-md-4 col-form-label text-left" for="brand_name">Brand:</label>
              <div class="col-md-8">
                <select class="js-select2 form-control js-select2-brand" id="brand_name" name="brand_name[]" data-placeholder="Select Brand/Merek" multiple>
                  <option value="all">All</option>
                  @foreach($brand as $row)
                    <option value="{{ $row->brand_name }}">{{ $row->brand_name }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="form-group row">
              <label class="col-md-4 col-form-label text-left" for="packaging">Kemasan:</label>
              <div class="col-md-8">
                <select class="js-select2 form-control" id="packaging" name="packaging[]" data-placeholder="Select Kemasan" multiple>
                </select>
              </div>
            </div>

            <div class="form-group row">
            <label class="col-md-4 col-form-label text-left" for="product">Product:</label>
            <div class="col-md-8">
              <select class="js-select2 form-control" id="product" name="product[]" data-placeholder="Select Product" multiple>
                <option value="all">All</option>
              </select>
            </div>
            </div>
          </div>
        </div>

        @if($superuser->division == "Management" OR $superuser->division == "Developer")
        <div class="form-group row">
          <div class="col-md-2"></div>
          <div class="col-md-4 align-self-center">
            <div class="form-check">
              <input type="checkbox" class="form-check-input" name="nominal" value="1" id="nominal_show" onclick="handleClick(this);">
              <label class="form-check-label" for="nominal_show">Show Nominal</label>
            </div>
          </div>
        </div>
        @endif
      </div>
      </div>
    </div>

    <div class="col-2">
      <div class="block">
        <div class="block-content">
          <div class="form-group row">
            <div class="col-md-12 text-center">
              <button type="submit" class="btn bg-gd-corporate border-0 text-white" aria-label="Print Report" id="submit-btn">
                Download <i class="fa fa-print ml-10"></i>
              </button>
            </div>
          </div>
          <div class="form-group row">
            <div class="col-md-12 text-center">
              <a href="#" id="btn-filter" class="btn bg-gd-corporate border-0 text-white" aria-label="Preview Report">
                Preview <i class="fa fa-search ml-10"></i>
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

  <div class="row">
    <div class="block">
      <div class="block-content block-content-full">
        <table class="datatable table" id="datatable">
          <thead class="thead-dark">
            <tr>
              <th>Customer</th>
              <th>Brand</th>
              <th>Month</th>
              <th>Variant</th>
              <th>Qty</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</form>

@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.daterangepicker')
@include('superuser.asset.plugin.datatables-button')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-rowgroup/1.5.0/rowGroup.dataTables.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-rowgroup/1.5.0/dataTables.rowGroup.min.js"></script>

<style>
  #datatable tbody td {
    vertical-align: middle;
    padding-top: 8px;
    padding-bottom: 8px;
  }
  #datatable tbody tr.dtrg-start td {
    border-bottom: none !important;
  }
</style>

@push('scripts')
<script type="text/javascript">
  var start_date = $('#start_date').val();
  var end_date = $('#end_date').val();

  $(document).ready(function() {
      $('.js-select2').select2();

      let datatableUrl = '{{ route('superuser.report.customer_order_variant.json') }}';
      let firstDatatableUrl = datatableUrl + '?start_date=' + start_date + '&end_date=' + end_date +
      '&customer=all&brand=all&product=all&packaging=all';

      var datatable = $('#datatable').DataTable({
        language: {
              processing: "<span class='fa-stack fa-lg'>\n\
                                    <i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\
                              </span>",
        },
        processing: true,
        serverSide: false,
        rowGroup: {
          dataSrc: ['combined_customer', 'invoice_brand', 'combined_month_year'],
          startRender: function (rows, group, level) {
              let config = {
                  0: { bg: '#dfe3e8', weight: '700', size: '14px', color: '#1f2937', topSpace: true },
                  1: { bg: '#eef1f4', weight: '600', size: '13px', color: '#374151', topSpace: false },
                  2: { bg: '#f6f7f9', weight: '500', size: '12.5px', color: '#4b5563', topSpace: false }
              };

              let c = config[level];

              let tr = $('<tr/>')
                  .addClass('group-row-level-' + level)
                  .css({
                      'background-color': c.bg,
                      'border-top': level === 0 ? '3px solid #9aa5b1' : '1px solid #e5e7eb',
                  });

              // 5 kolom: Customer, Brand, Month, Variant, Qty
              for (let i = 0; i < 5; i++) {
                  let cellText = (i === level) ? group : '';
                  tr.append(
                      $('<td/>')
                          .css({
                              'font-weight': c.weight,
                              'font-size': c.size,
                              'color': c.color,
                              'text-align': i === 4 ? 'right' : 'left',
                          })
                          .text(cellText)
                  );
              }

              return tr;
          }
      },
        ajax: {
          "url": firstDatatableUrl,
          "dataType": "json",
          "type": "GET",
          "data":{ _token: "{{csrf_token()}}"}
        },
        columns: [
          {
            data: 'combined_customer',
            render: function (data, type) {
                return type === 'display' ? '' : data;
            }
          },
          {
            data: 'invoice_brand',
            name: 'penjualan_so.brand_name',
            render: function (data, type) {
                return type === 'display' ? '' : data;
            }
          },
          {
            data: 'combined_month_year',
            render: function (data, type) {
                return type === 'display' ? '' : data;
            }
          },
          {data: 'combined_product'},
          {data: 'invoice_qty'},
        ],
        columnDefs: [
          { targets: 0, width: '160px' },
          { targets: 1, width: '100px' },
          { targets: 2, width: '130px' }
        ],
        order: [
          [0, 'asc'],
          [1, 'asc'],
          [2, 'asc']
        ],
        pageLength: 10,
        lengthMenu: [
          [10, 30, 100, -1],
          [10, 30, 100, 'All']
        ], 
        dom: "<'row'<'col-sm-2'l><'col-sm-7 text-left'B><'col-sm-3'f>>" +
          "<'row'<'col-sm-12'tr>>" +
          "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
          {
            extend: 'excelHtml5',
            text: '<i class="fa fa-file-excel-o"></i>',
            titleAttr: 'Excel',
            title: 'Customer-Order Variant',
            footer: true,
          },
          {
            extend: 'pdfHtml5',
            orientation: 'landscape',
            pageSize: 'A4',
            text: '<i class="fa fa-file-pdf-o"></i>',
            titleAttr: 'PDF',
            title: 'Customer-Order Variant',
            footer: true,
          }
        ],
      });

      $('#btn-filter').on('click', function(e) {
          e.preventDefault();
          var customer = $('#customer').val();
          var brand = $('#brand_name').val();
          var product = $('#product').val();
          var packaging = $('#packaging').val();   // <-- tambahan
          var start_date = $('#start_date').val();
          var end_date = $('#end_date').val();
          
          let newDatatableUrl = datatableUrl + '?start_date=' + start_date + '&end_date=' + end_date +
            '&customer=' + customer + '&brand_name=' + brand + '&product=' + product + '&packaging=' + packaging;
          datatable.ajax.url(newDatatableUrl).load();
      });

      function handleClick(cb) {
        cb.value = cb.checked ? 0 : 1;
        console.log(cb.value);
      }

      document.getElementById('nominal_show').addEventListener('change', function() {
          this.value = this.checked ? 1 : 0;
      });

      function toCommas(value) {
        return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      }

      // $("#customer").val("all").change();
      // $("#brand_name").val("all").change();
      // $("#product").val("all").change();

      function loadPackaging() {
        $.ajax({
          url: "{{ route('superuser.report.customer_order_variant.getPackaging') }}",
          type: "GET",
          success: function (data) {
            let options = '';
            data.forEach(function (p) {
              options += `<option value="${p.id}">${p.pack_name}</option>`;
            });
            $('#packaging').html(options);
          },
          error: function () {
            alert('Gagal memuat data kemasan.');
          }
        });
      }
      loadPackaging(); // load semua kemasan sekali saat halaman dibuka, tidak bergantung brand

      var productRequest = null;
      var productRequestToken = 0; // penanda request terbaru

      function loadProducts() {
          let brand_name = $('#brand_name').val();
          let packaging = $('#packaging').val();

          // Batalkan request sebelumnya kalau masih pending
          if (productRequest !== null) {
              productRequest.abort();
              productRequest = null;
          }

          // Naikkan token tiap kali loadProducts dipanggil -> jadi "nomor urut" request ini
          productRequestToken++;
          let currentToken = productRequestToken;

          if ((!brand_name || brand_name.length === 0) && (!packaging || packaging.length === 0)) {
              $('#product').html('<option value="all" selected>All</option>').trigger('change');
              return;
          }

          productRequest = $.ajax({
              url: "{{ route('superuser.report.customer_order_variant.getProductsByBrand') }}",
              type: "GET",
              cache: false,
              data: { brand_name: brand_name, packaging: packaging },
              success: function (data) {
                  // Kalau saat response ini datang sudah ada request lain yang lebih baru, abaikan
                  if (currentToken !== productRequestToken) {
                      return;
                  }

                  let productOptions = '<option value="all">All</option>';
                  data.forEach(function (product) {
                      productOptions += `<option value="${product.product_id}">
                          ${product.product_code} - ${product.product_name}
                      </option>`;
                  });
                  $('#product').html(productOptions).trigger('change');
                  productRequest = null;
              },
              error: function (jqXHR, textStatus) {
                  productRequest = null;
                  if (textStatus !== 'abort') {
                      alert('Gagal memuat data produk.');
                  }
              }
          });
      }

      $('#brand_name').on('change', function () {
          loadProducts();
      });

      $('#packaging').on('change', function () {
          loadProducts();
      });

      

        $('#btn-reset').on('click', function (e) {
          e.preventDefault();

          $('#customer').val(null).trigger('change');
          $('#brand_name').val(null).trigger('change');
          $('#product').html('<option value="all" selected>All</option>').val("all").trigger('change');
          loadPackaging(); // reload semua kemasan lagi

          let defaultStart = '{{ date('Y-m-01') }}';
          let defaultEnd = '{{ date('Y-m-d') }}';
          $('#start_date').val(defaultStart);
          $('#end_date').val(defaultEnd);

          let resetUrl = datatableUrl + '?start_date=' + defaultStart + '&end_date=' + defaultEnd;

          datatable.ajax.url(resetUrl).load();
      });
  })
</script>
@endpush