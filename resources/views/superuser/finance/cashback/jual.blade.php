@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Laporan</span>
  <span class="breadcrumb-item">Finance</span>
  <span class="breadcrumb-item">Araya Report</span>
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
                        <select id="filter-month" class="form-control js-select2">
                            <option value="">Pilih Bulan</option>
                            @foreach(range(1, 12) as $month)
                                <option value="{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}" 
                                    {{ (request('month') == str_pad($month, 2, '0', STR_PAD_LEFT) || (request('month') == null && date('m') == $month)) ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $month, 10)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <label class="col-md-2 col-form-label text-left" for="year">Tahun :</label>
                <div class="col-md-4">
                    <select id="filter-year" class="form-control js-select2">
                        <option value="">Pilih Tahun</option>
                        @foreach(range(date('Y') - 5, date('Y')) as $year)
                            <option value="{{ $year }}" 
                                {{ (request('year') == $year || (request('year') == null && date('Y') == $year)) ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
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
                <a href="#" id="filter-button" class="btn bg-gd-sea border-0 text-white pl-50 pr-50">
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
                      <th>Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($cashback as $key)
                        <tr>
                            <td>{{ $key->date }}</td>
                            <td>{{ $key->customer_name }} {{ $key->customer_kota }}</td>
                            <td>{{ $key->code }}</td>
                            <td>{{ $key->total }}</td>
                        </tr>
                    @endforeach
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
        // Initialize DataTable with Buttons
        let datatable = $('#datatables').DataTable({
            dom: "<'row'<'col-sm-2'l><'col-sm-7 text-left'B><'col-sm-3'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i>',
                    footer: true, // Include footer in export
                    title: 'Cashback Report - JUAL'
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fa fa-file-pdf-o"></i>',
                    footer: true, // Include footer in export
                    title: 'Cashback Report - JUAL',
                    customize: function(doc) {
                        // Center the table content and align totals
                        doc.styles.tableHeader.alignment = 'center';
                        doc.styles.tableFooter = { alignment: 'center' };
                    }
                }
            ],
            footerCallback: function (row, data, start, end, display) {
                let api = this.api();

                // Helper function to parse numbers
                let intVal = function (i) {
                    return typeof i === 'string'
                        ? i.replace(/[\$,]/g, '') * 1
                        : typeof i === 'number'
                        ? i
                        : 0;
                };

                // Calculate grand total and page total
                let total = api
                    .column(3) // Index of Total column
                    .data()
                    .reduce((a, b) => intVal(a) + intVal(b), 0);

                let pageTotal = api
                    .column(3, { page: 'current' })
                    .data()
                    .reduce((a, b) => intVal(a) + intVal(b), 0);

                // Format numbers for display
                let formatter = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0, // Typically no decimal for IDR
                });

                // Update the footer
                $(api.column(3).footer()).html(
                    `${formatter.format(total)}`
                );

                // Optionally update the total displayed in each row
                api.column(3).nodes().each(function (cell, i) {
                    let rawData = api.cell(cell).data();
                    $(cell).html(formatter.format(intVal(rawData)));
                });
            }
        });

        $('#filter-button').click(function() {
            let month = $('#filter-month').val();
            let year = $('#filter-year').val();
            let query = '';

            if (month) {
                query += 'month=' + month + '&';
            }
            if (year) {
                query += 'year=' + year;
            }

            window.location.href = "{{ route('superuser.finance.cashback.pageReportJual') }}?" + query;
        });
    });
</script>
@endpush
