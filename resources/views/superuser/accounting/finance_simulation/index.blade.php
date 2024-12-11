@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Accounting</span>
  <span class="breadcrumb-item active">Finance Simulation Price</span>
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

<div id="alert-block"></div>

<div class="block">
    <div class="block-content">
        <form class="ajax" data-action="{{ route('superuser.accounting.finance_simulation.store') }}" data-type="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="month">Bulan</label>
                    <select id="month" name="month" class="form-control js-select2" style="width: 100%;">
                        <option value="" selected disabled>Pilih Bulan</option>
                        @foreach($months as $month)
                            <option value="{{ $month['id'] }}">{{ $month['monthName'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <a class="btn btn-danger" href="{{ route('superuser.accounting.finance_simulation.removeData') }}" role="button"><i class="fa-solid fa-trash"></i> Delete</a>
                    <button type="submit" class="btn btn-success"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                </div>
            </div>
        </form>
    </div>
    <hr>
    <div class="block-content block-content-full">
        <div class="row mb-30">
            <div class="col-12">
                <table class="table table-striped" id="invoice_tax">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Invoice</th>
                            <th>Product</th>
                            <th>Kemasan</th>
                            <th>Harga Beli</th>
                            <th>Harga Jual</th>
                            <th>Qty</th>
                            <th>Total Beli</th>
                            <th>Total Jual</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($simulations as $simulation)
                            @foreach($simulation->simulation_detail as $item)
                                <tr>
                                    <td>{{ $loop->parent->iteration }}</td>
                                    <td>{{ $simulation->code }}</td>
                                    <td>{{ $item->product_tax->name ?? 'N/A' }}</td>
                                    <td>{{ $item->product_tax->packaging->pack_name ?? 'N/A' }}</td>
                                    <td>{{ number_format($item->price_buying, 2) }}</td>
                                    <td>{{ number_format($item->price_selling, 2) }}</td>
                                    <td>{{ $item->qty }}</td>
                                    <td>{{ number_format($item->subtotal_harga_beli, 2) }}</td>
                                    <td>{{ number_format($item->subtotal_harga_jual, 2) }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="7" class="text-center">Total:</th>
                            <th id="totalBeli" class="text-center">0</th>
                            <th id="totalJual" class="text-center">0</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
      </div>
    </div>
</div>

@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.datatables-button')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('.js-select2').select2();

        $('#invoice_tax').DataTable({
            processing: true,
            serverSide: false,
            order: [
                [1, 'asc']
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
                    title: 'Simulation Under Value Price',
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
                    customize: function(doc) {
                        // Set table header style
                        doc.content[1].table.widths = [
                            '5%', '5%', '15%', '10%', '10%', '10%', '5%', '20%', '20%'
                        ];

                        doc.pageMargins = [20, 20, 20, 20]; // Set margins [left, top, right, bottom]

                        doc.styles.tableHeader = {
                            bold: true,
                            fontSize: 12,
                            color: 'white',
                            fillColor: 'black',
                            alignment: 'center'
                        };

                        // Center-align the body rows
                        var tableBody = doc.content[1].table.body;
                        for (var i = 1; i < tableBody.length; i++) {
                            for (var j = 0; j < tableBody[i].length; j++) {
                                tableBody[i][j].alignment = 'center';
                            }
                        }

                        doc.styles.tableBody = {
                            fontSize: 10,
                            alignment: 'center'
                        };

                        // Make the first row the header
                        doc.content[1].table.headerRows = 1;
                    }
                }
            ],
            footerCallback: function (row, data, start, end, display) {
                var api = this.api();

                // Helper function to parse numeric values
                var parseValue = function (value) {
                    return typeof value === 'string' ?
                        parseFloat(value.replace(/[^0-9.-]+/g, '')) : value;
                };

                // Calculate the total for "Total Beli"
                var totalBeli = api
                    .column(7, { search: 'applied' }) // Column index for "Total Beli"
                    .data()
                    .reduce(function (a, b) {
                        return parseValue(a) + parseValue(b);
                    }, 0);

                // Calculate the total for "Total Jual"
                var totalJual = api
                    .column(8, { search: 'applied' }) // Column index for "Total Jual"
                    .data()
                    .reduce(function (a, b) {
                        return parseValue(a) + parseValue(b);
                    }, 0);

                // Update footer cells
                $(api.column(7).footer()).html(
                    $.fn.dataTable.render.number('.', ',', 2, '').display(totalBeli)
                );
                $(api.column(8).footer()).html(
                    $.fn.dataTable.render.number('.', ',', 2, '').display(totalJual)
                );
            }
        });
    });
</script>
@endpush