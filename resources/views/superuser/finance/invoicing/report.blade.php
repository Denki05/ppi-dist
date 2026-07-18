@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Laporan</span>
  <span class="breadcrumb-item">Finance</span>
  <span class="breadcrumb-item active">Piutang Faktur</span>
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
              <label class="col-md-2 col-form-label text-left" for="period">Period From :</label>
              <div class="col-md-4">
                <div class="input-group">
                  <input type="date" class="form-control form-control" name="periode_from" id="periode_from" value="{{ date('Y-m-01') }}">
                </div>
              </div>
              <label class="col-md-2 col-form-label text-left" for="product">Period To :</label>
              <div class="col-md-4">
                <div class="input-group">
                  <input type="date" class="form-control form-control" name="periode_to" id="periode_to" value="{{ date('Y-m-d') }}">
                </div>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-left" for="customer">Customer :</label>
              <div class="col-md-4">
                <select class="js-select2 form-control" id="customer" name="customer" data-placeholder="Select Customer" multiple="multiple">
                  <option value="all">All</option>
                  @foreach ($customer as $value)
                    <option value="{{ $value->id }}">{{ $value->name }} {{ $value->text_kota }}</option>
                  @endforeach
                </select>
              </div>

              <!-- <label class="col-md-2 col-form-label text-left" for="status_payment">Status :</label>
              <div class="col-md-4">
                <select class="js-select2 form-control" id="status_payment" name="status_payment" data-placeholder="Select Status">
                  <option value="all">All</option>
                  <option value="UNPAID">UNPAID</option>
                  <option value="PAID">PAID</option>
                </select>
              </div> -->
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="block">
          <div class="block-content">
            <div class="form-group row">
              <div class="col-md-12 text-center">
                <a href="#" id="btn-filter" class="btn bg-gd-corporate border-0 text-white pl-50 pr-50">
                  Filter <i class="fa fa-search ml-10"></i>
                </a>
              </div>
            </div>

            <!-- <div class="form-group row">
              <div class="col-md-12 text-center">
                <a href="#" id="btn-print" class="btn bg-gd-sea border-0 text-white pl-50 pr-50">
                  Print <i class="fa fa-print ml-10"></i>
                </a>
              </div>
            </div> -->
          </div>
        </div>
      </div>

      <div class="block">
        <div class="block-content block-content-full">
            <div class="row mb-30">
              <div class="col-12">
                <table class="table" id="datatables">
                  <thead>
                    <tr>
                      <th>Customer</th>
                      <th>No. Faktur</th>
                      <th>Tanggal Faktur</th>
                      <th>Jatuh Tempo</th>
                      <th>Nilai faktur</th>
                      <th>Hutang(Asing)</th>
                      <th>Tempo</th>
                      <th>Status Faktur</th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                  <tfoot>
                    
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

        let startDate = $('#periode_from').val();
        let endDate = $('#periode_to').val();

        let datatableUrl = '{{ route('superuser.finance.invoicing.json2') }}';
        let firstDatatableUrl = datatableUrl + '?startDate=' + startDate + '&endDate=' + endDate +
          '&customer=all';

        const rupiah = $.fn.dataTable.render.number('.', ',', 2, 'Rp. ').display;

        var datatable = $('#datatables').DataTable({
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
                { data: 'account_customer' },
                { data: 'no_faktur' },
                {
                    data: 'tanggal_faktur',
                    render: {
                        _: 'display',
                        sort: 'timestamp'
                    }
                },
                { data: 'jatuh_tempo' },
                {
                  data: 'nilai_faktur',
                  searchable: false,
                  render: {
                    display: function (data) {
                      return $.fn.dataTable.render
                        .number('.', ',', 2, 'Rp. ')
                        .display(data);
                    },
                    export: function (data) {
                      return parseFloat(data) || 0;
                    },
                    sort: function (data) {
                      return parseFloat(data) || 0;
                    }
                  }
                },
                {
                  data: 'hutang_asing',
                  searchable: false,
                  render: {
                    display: function (data) {
                      return $.fn.dataTable.render
                        .number('.', ',', 2, 'Rp. ')
                        .display(data);
                    },
                    export: function (data) {
                      return parseFloat(data) || 0;
                    },
                    sort: function (data) {
                      return parseFloat(data) || 0;
                    }
                  }
                },
                { data: 'diff_days' },
                { data: 'status_faktur' },
            ],
            order: [[0, 'asc'], [1, 'asc']],
            rowGroup: {
                dataSrc: 'account_customer',
                startRender: function(rows, group) {
                    var totalNilaiFaktur = rows
                        .data()
                        .pluck('nilai_faktur')
                        .reduce(function(a, b) { 
                            return a + (parseFloat(b) || 0); 
                        }, 0);
                        
                    var totalHutangAsing = rows
                        .data()
                        .pluck('hutang_asing')
                        .reduce(function(a, b) { 
                            return a + (parseFloat(b) || 0); 
                        }, 0);

                    return $('<tr/>')
                      .append('<td style="font-weight:bold; background-color: #bfbfbf;">' + group + '</td>')
                      .append('<td colspan="2" style="background-color: #bfbfbf;"></td>')
                      .append(`<td style="font-weight:bold; background:#bfbfbf;">${rupiah(totalNilaiFaktur)}</td>`)
                      .append(`<td style="font-weight:bold; background:#bfbfbf;">${rupiah(totalHutangAsing)}</td>`)
                      .append('<td colspan="3" style="background-color: #bfbfbf;"></td>');
                }
            },
            columnDefs: [
                {
                    targets: 0,
                    visible: false
                }
            ],
            dom: "<'row'<'col-sm-2'l><'col-sm-7 text-left'B><'col-sm-3'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            buttons: [
                {
                  extend: 'excel',
                  text: '<i class="fa fa-file-excel-o"></i>',
                  title: 'Piutang Faktur',
                  exportOptions: {
                    orthogonal: 'export', // ⭐ INI KUNCINYA
                    modifier: {
                      page: 'all'
                    }
                  },
                  customize: function (xlsx) {
                    var sheet  = xlsx.xl.worksheets['sheet1.xml'];
                    var styles = xlsx.xl['styles.xml'];

                    // 1. Hilangkan underline
                    $('fonts font u', styles).remove();

                    // 2. Header BOLD
                    var fonts = $('fonts', styles);
                    fonts.append('<font><b/></font>');
                    var boldFontId = $('font', fonts).length - 1;

                    var cellXfs = $('cellXfs', styles);
                    cellXfs.append(`<xf fontId="${boldFontId}" xfId="0" applyFont="1"/>`);
                    var headerStyleIndex = $('xf', cellXfs).length - 1;

                    $('row[r="2"] c', sheet).attr('s', headerStyleIndex);

                    // 3. Format Rupiah numeric
                    var numFmtId = 164;
                    $('numFmts', styles).append(
                      `<numFmt numFmtId="${numFmtId}" formatCode="[$Rp-421] #,##0.00"/>`
                    );

                    $('cellXfs', styles).append(
                      `<xf numFmtId="${numFmtId}" fontId="0" xfId="0" applyNumberFormat="1"/>`
                    );

                    var rupiahStyleIndex = $('cellXfs xf', styles).length - 1;
                    $('row c[r^="E"], row c[r^="F"]', sheet).attr('s', rupiahStyleIndex);
                  }
                },
                {
    extend: 'pdf',
    text: '<i class="fa fa-file-pdf-o"></i>',
    orientation: 'landscape',
    title: 'Piutang Faktur',
    exportOptions: {
        modifier: { page: 'all' }
    },
    customize: function(doc) {
        var tableBody = doc.content[1].table.body;

        // Ambil jumlah kolom dari baris header (row index 0) secara dinamis
        var colCount = tableBody[0].length;

        // Set widths sesuai colCount yang sebenarnya
        var widths = [];
        for (var i = 0; i < colCount; i++) {
            widths.push('*');
        }
        doc.content[1].table.widths = widths;

        doc.pageMargins = [20, 20, 20, 20];

        doc.styles.tableHeader = {
            bold: true,
            fontSize: 10,
            color: 'white',
            fillColor: '#343a40',
            alignment: 'center'
        };

        doc.content[1].table.headerRows = 1;

        // --- Helper parse & format Rupiah ---
        function parseRupiah(str) {
            if (!str) return 0;
            return parseFloat(
                String(str)
                    .replace(/Rp\.\s*/g, '')
                    .replace(/\./g, '')
                    .replace(',', '.')
            ) || 0;
        }

        function formatRupiah(num) {
            return 'Rp. ' + num.toLocaleString('id-ID', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // --- Hitung total, skip baris header (i=0) dan baris rowGroup ---
        var totalNilaiFaktur = 0;
        var totalHutangAsing = 0;

        // Index kolom nilai_faktur & hutang_asing di hasil export (col-0 hidden)
        // No.Faktur=0, Tgl=1, Jatuh Tempo=2, Nilai Faktur=3, Hutang=4, Tempo=5, Status=6
        var idxNilai  = 4;
        var idxHutang = 5;

        for (var i = 1; i < tableBody.length; i++) {
            var row = tableBody[i];

            // Lewati baris rowGroup (jumlah sel < colCount atau sel pertama ada colspan)
            if (!row || row.length < colCount || (row[0] && row[0].colSpan > 1)) {
                continue;
            }

            totalNilaiFaktur += parseRupiah(row[idxNilai] ? row[idxNilai].text : 0);
            totalHutangAsing += parseRupiah(row[idxHutang] ? row[idxHutang].text : 0);

            // Center-align setiap sel data
            for (var j = 0; j < row.length; j++) {
                row[j].alignment = (j >= idxNilai) ? 'right' : 'left';
                row[j].fontSize = 9;
            }
        }

        // --- Baris TOTAL ---
        var totalRow = [];

        for (var k = 0; k < colCount; k++) {
            if (k === 0) {
                totalRow.push({
                    text: 'TOTAL',
                    bold: true,
                    colSpan: idxNilai,
                    alignment: 'right',
                    fillColor: '#bfbfbf',
                    fontSize: 9
                });
            } else if (k < idxNilai) {
                totalRow.push({}); // placeholder colSpan
            } else if (k === idxNilai) {
                totalRow.push({
                    text: formatRupiah(totalNilaiFaktur),
                    bold: true,
                    alignment: 'right',
                    fillColor: '#bfbfbf',
                    fontSize: 9
                });
            } else if (k === idxHutang) {
                totalRow.push({
                    text: formatRupiah(totalHutangAsing),
                    bold: true,
                    alignment: 'right',
                    fillColor: '#bfbfbf',
                    fontSize: 9
                });
            } else {
                totalRow.push({ text: '', fillColor: '#bfbfbf' });
            }
        }

        tableBody.push(totalRow);
    }
}
            ]
        });

        $('#btn-filter').on('click', function(e) {
          e.preventDefault();

          startDate = $('#periode_from').val();
          endDate = $('#periode_to').val();
          var customer = $('#customer').val();

          let newDatatableUrl = datatableUrl + '?startDate=' + startDate + '&endDate=' + endDate +
            '&customer=' + customer;

          datatable.ajax.url(newDatatableUrl).load();
        });
        
        $("#customer").val("all").change();
    })
</script>
@endpush