@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Laporan</span>
  <span class="breadcrumb-item">Accounting</span>
  <span class="breadcrumb-item">Unifra Report</span>
  <span class="breadcrumb-item active">Jual</span>
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
    <div class="form-group row">
      <div class="col-md-9">
        <div class="block">
          <div class="block-content">
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-left" for="period">Bulan :</label>
              <div class="col-md-4">
                <div class="input-group">
                    <select id="bulan" name="bulan" class="form-control js-select2">
                        @foreach ($bulan as $key => $month)
                            <option value="{{ $key }}" {{ $key == $selectedBulan ? 'selected' : '' }}>
                                {{ $month }}
                            </option>
                        @endforeach
                    </select>
                </div>
              </div>
              <label class="col-md-2 col-form-label text-left" for="customer">Customer :</label>
              <div class="col-md-4">
                <select class="js-select2 form-control" id="customer" name="customer" data-placeholder="Select Customer" multiple="multiple">
                  <option value="all">All</option>
                  @foreach ($customer as $value)
                    <option value="{{ $value->id }}">{{ $value->name }} {{ $value->text_kota }}</option>
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
                <a href="#" id="btn-filter" class="btn bg-gd-sea border-0 text-white pl-50 pr-50">
                  Filter <i class="fa fa-search ml-10"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="block">
        <div class="block-content block-content-full">
            <div class="row mb-30">
              <div class="col-12">
                <table class="table table-striped" id="datatables">
                  <thead>
                    <tr>
                      <th>Tanggal</th>
                      <th>Customer</th>
                      <th>Code</th>
                      <th>Nominal</th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                  <tfoot>
                    <tr>
                      <th colspan="3" style="text-align:right">Total:</th>
                      <th style="text-align:center"></th> <!-- Kolom untuk total nominal_sub_total -->
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
        </div>
      </div>
    </div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.daterangepicker')
@include('superuser.asset.plugin.datatables-button')

@push('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        $('.js-select2').select2();

        // let bulan = $('#bulan').val();

        let datatableUrl = '{{ route('superuser.accounting.invoice_tax.json2') }}';
        let firstDatatableUrl = datatableUrl + '?bulan=' + bulan + '&customer=all';

        var datatable = $('#datatables').DataTable({
          "language": {
              "processing": "<span class='fa-stack fa-lg'>\n\
                                <i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\
                            </span>",
          },
          processing: true,
          serverSide: false,
          ajax: {
              "url": firstDatatableUrl,
              "dataType": "json",
              "type": "GET",
              "data": {
                  _token: "{{ csrf_token() }}"
              }
          },
          columns: [
              { data: 'tanggal_buat' },
              { data: 'account_customer' },
              { data: 'kode' },
              { 
                  data: 'nominal_sub_total',
                  render: $.fn.dataTable.render.number('.', ',', 2, 'Rp. '),
                  searchable: false
              },
          ],
          order: [
              [1, 'asc']
          ],
          pageLength: 10,
          lengthMenu: [
              [10, 25, 50, 100],
              [10, 25, 50, 100]
          ],
          dom: "<'row'<'col-sm-2'l><'col-sm-7 text-left'B><'col-sm-3'f>>" +
              "<'row'<'col-sm-12'tr>>" +
              "<'row'<'col-sm-5'i><'col-sm-7'p>>",
          buttons: [
              {
                  extend: 'excel',
                  text: '<i class="fa fa-file-excel-o"></i>',
                  title: 'UNIFRA-Report-Jual',
                  exportOptions: {
                      modifier: {
                          page: 'all'
                      }
                  }
              },
              {
                  extend: 'pdf',
                  text: '<i class="fa fa-file-pdf-o"></i>',
                  title: 'UNIFRA-Report-Jual',
                  orientation: 'landscape',
                  pageSize: 'A4',
                  exportOptions: {
                      columns: ':visible'
                  },
                  customize: function(doc) {
                      doc.content[1].table.widths = ['20%', '30%', '20%', '30%']; // Lebar kolom
                      doc.styles.tableHeader.alignment = 'center'; // Header kolom rata tengah
                      doc.styles.tableHeader.fontSize = 10;

                      // Rata tengah setiap kolom pada PDF
                      doc.content[1].table.body.forEach(function(row, index) {
                          if (index > 0) {
                              row.forEach(function(cell) {
                                  cell.alignment = 'center';
                              });
                          }
                      });

                      // Menambahkan total di bawah tabel pada PDF
                      var total = datatable.column(3).data().reduce(function(a, b) {
                          return parseFloat(a) + parseFloat(b);
                      }, 0);

                      // Format total nominal
                      var formattedTotal = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(total);

                      doc.content[1].table.body.push([
                          { text: 'Total Jumlah :', alignment: 'right', colSpan: 3, bold: true },
                          {}, {}, // Kosongkan kolom agar sel total ada di kolom ke-4
                          { text: formattedTotal, alignment: 'center', bold: true }
                      ]);
                  }
              }
          ],
          footerCallback: function(row, data, start, end, display) {
            var api = this.api();
            var total = api.column(3).data().reduce(function(a, b) {
                return parseFloat(a) + parseFloat(b);
            }, 0);

            // Format total nominal untuk tampilan footer
            var formattedTotal = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(total);

            // Update kolom terakhir di footer (kolom nominal_sub_total) dengan total yang diformat
            $(api.column(3).footer()).html(formattedTotal).css('text-align', 'center');
          }
        });

        $('#btn-filter').on('click', function(e) {
            e.preventDefault();

        // Get values for startDate, endDate, and customer from input fields
        let bulan = $('#bulan').val();
        let customer = $('#customer').val();

        // Capture the selected bulan from the dropdown
        bulan = $('#bulan').val();

        // Construct the new URL with the selected bulan, customer, and date filters
        let newDatatableUrl = datatableUrl + '?bulan=' + bulan  + '&customer=' + customer;

        // Reload the DataTable with the updated URL
        datatable.ajax.url(newDatatableUrl).load();
        });
    })
</script>
@endpush