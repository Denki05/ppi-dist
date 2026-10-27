@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <span class="breadcrumb-item active">Invoicing</span>
</nav>

@if($errors->any())
<div class="alert alert-danger alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Error</h3>
  @foreach ($errors->all() as $error)
  <p class="mb-0">{{ $error }}</p>
  @endforeach
</div>
@endif

<div id="alert-block"></div>

@if(session('error') || session('success'))
<div class="alert alert-{{ session('error') ? 'danger' : 'success' }} alert-dismissible fade show" role="alert">
    <strong>{{ session('error') ? 'Error!' : 'Berhasil!' }}</strong> {!! session('error') ?? session('success') !!}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

@if(session()->has('message'))
<div class="alert alert-success alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">&times;</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Success</h3>
  <p class="mb-0">{{ session()->get('message') }}</p>
</div>
@endif

<div class="row">
  <div class="col-lg-9 col-md-8 col-12">
    <div class="block">
      <div class="block-content">
        <div class="form-group row">
          <label class="col-12 col-md-4 col-lg-2 col-form-label" for="start_date">Periode From :</label>
          <div class="col-12 col-md-8 col-lg-4">
            <input type="date" class="form-control" name="start_date" id="periode_from">
          </div>
          <label class="col-12 col-md-4 col-lg-2 col-form-label" for="end_date">Periode To :</label>
          <div class="col-12 col-md-8 col-lg-4">
            <input type="date" class="form-control" name="end_date" id="periode_to">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-12 col-md-4 col-lg-2 col-form-label" for="kategori">Customer :</label>
          <div class="col-12 col-md-8 col-lg-4">
            <select class="form-control js-select2" name="customer" id="customer" data-placeholder="Select Customer">
              <option value="all">All</option>
              @foreach($customer as $row)
              <option value="{{ $row->id }}">{{ $row->name }} {{ $row->text_kota }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-4">
                <a class="btn btn-primary" href="{{ route('superuser.finance.invoicing.updateInvoice') }}" role="button">Report</a>
                @role('Developer', 'superuser')
                <a href="#" class="btn btn-warning ml-2" id="btn-generate-missing" role="button">
                  <i class="fa fa-file-text-o mr-5"></i> Nota Bermasalah
                </a>
                @endrole
              </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-3 col-md-4 col-12">
    <div class="block">
      <div class="block-content text-center">
        <a href="#" id="btn-filter" class="btn bg-gd-corporate border-0 text-white px-4 py-2">
          Filter <i class="fa fa-search ml-2"></i>
        </a>
      </div>
    </div>
  </div>
</div>

<div class="block">
  <div class="block-content block-content-full">
    <div class="table-responsive">
      <table class="table table-striped" id="datatables">
        <thead>
          <tr>
            <th>Created At</th>
            <th>Reff SO</th>
            <th>Invoice Code</th>
            <th>Type</th>
            <th>Account</th>
            <th>Total</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>
  </div>
</div>

@role('Developer', 'superuser')
<div class="modal fade" id="modalMissingInvoice" tabindex="-1" role="dialog" aria-labelledby="modalMissingInvoiceLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalMissingInvoiceLabel">Generate Nota Bermasalah</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="row mb-10">
          <div class="col-md-7">
            <input type="text" class="form-control" id="missing-search" placeholder="Cari SO / DO / customer...">
          </div>
          <div class="col-md-5 text-right">
            <span class="text-muted" id="missing-count">0 kandidat</span>
          </div>
        </div>
        <div style="max-height: 55vh; overflow-y: auto;">
          <table class="table table-sm table-striped table-bordered mb-0" id="table-missing-invoice">
            <thead>
              <tr>
                <th class="text-center" style="width: 36px;">
                  <input type="checkbox" id="missing-check-all">
                </th>
                <th>Reff SO</th>
                <th>DO</th>
                <th>Customer</th>
                <th>Status DO</th>
                <th class="text-right">Kurs</th>
                <th class="text-right">Grand Total</th>
                <th>Nota Lama</th>
              </tr>
            </thead>
            <tbody id="missing-tbody">
              <tr><td colspan="8" class="text-center text-muted">Memuat data...</td></tr>
            </tbody>
          </table>
        </div>
        <small class="text-muted d-block mt-10">
          Hanya DO dengan kurs valid (&gt; 1) dan total &gt; 0 yang tampil. Nota VOID tidak akan dibuat ulang.
        </small>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-primary" id="btn-generate-selected" disabled>
          <i class="fa fa-file-text-o mr-5"></i> Generate Nota Terpilih
        </button>
      </div>
    </div>
  </div>
</div>
@endrole
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    $('.js-select2').select2();
    let datatableUrl = '{{ route('superuser.finance.invoicing.json') }}';

    var datatable = $('#datatables').DataTable({
        responsive: true,
        language: { processing: "<i class='fa fa-spinner fa-spin'></i>" },
        processing: true,
        serverSide: false,
        ajax: {
            url: datatableUrl,
            type: "GET",
            data: { _token: "{{ csrf_token() }}" }
        },
        columns: [
            { data: 'created_at', render: { _: 'display', sort: 'timestamp' } },
            { data: 'so_code', name: 'penjualan_so.so_code' },
            { data: 'invoice_code', name: 'finance_invoicing.code' },
            { data: 'transaksi', name: 'penjualan_do.type_transaction' },
            { data: 'account_customer' },
            { data: 'grand_total_idr', render: $.fn.dataTable.render.number('.', ',', 2, 'Rp. '), searchable: false },
            { data: 'action' },
        ],
        order: [[2, 'desc']],
        pageLength: 10,
    });

    $('#btn-filter').on('click', function(e) {
        e.preventDefault();
        let newDatatableUrl = `${datatableUrl}?start_date=${$('#periode_from').val()}&end_date=${$('#periode_to').val()}&customer=${$('#customer').val()}`;
        datatable.ajax.url(newDatatableUrl).load();
    });
    
    @role('Developer', 'superuser')
      var missingInvoiceListUrl = '{{ route('superuser.finance.invoicing.missing') }}';
      var missingInvoiceGenerateUrl = '{{ route('superuser.finance.invoicing.generate_missing') }}';
      var missingData = [];
      var missingSelected = {};

      function escapeHtml(value) {
          return $('<div>').text(value === null || value === undefined ? '' : value).html();
      }

      function formatNumber(value) {
          return (Number(value) || 0).toLocaleString('id-ID', {
              minimumFractionDigits: 0,
              maximumFractionDigits: 2
          });
      }

      function formatRupiah(value) {
          return 'Rp. ' + (Number(value) || 0).toLocaleString('id-ID', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2
          });
      }

      function filteredMissingRows() {
          var keyword = ($('#missing-search').val() || '').toLowerCase().trim();

          if (!keyword) {
              return missingData;
          }

          return missingData.filter(function(row) {
              return [row.so_code, row.do_code, row.customer]
                  .join(' ')
                  .toLowerCase()
                  .indexOf(keyword) !== -1;
          });
      }

      function updateMissingSelectionState() {
          var visibleRows = filteredMissingRows();
          var checkedVisible = visibleRows.filter(function(row) {
              return missingSelected[row.id];
          }).length;
          var checkedTotal = Object.keys(missingSelected).length;

          $('#missing-check-all')
              .prop('checked', visibleRows.length > 0 && checkedVisible === visibleRows.length)
              .prop('indeterminate', checkedVisible > 0 && checkedVisible < visibleRows.length);

          $('#btn-generate-selected')
              .prop('disabled', checkedTotal === 0)
              .html('<i class="fa fa-file-text-o mr-5"></i> Generate Nota Terpilih' + (checkedTotal ? ' (' + checkedTotal + ')' : ''));

          $('#missing-count').text(visibleRows.length + ' kandidat');
      }

      function renderMissingTable() {
          var rows = filteredMissingRows();
          var html = '';

          rows.forEach(function(row) {
              var invoiceText = '-';

              if (row.invoice) {
                  invoiceText = escapeHtml(row.invoice.code)
                      + ' <span class="text-muted">(' + escapeHtml(row.invoice.status)
                      + (row.invoice.deleted ? ', dihapus' : '') + ')</span>';
              }

              html += '<tr>'
                  + '<td class="text-center"><input type="checkbox" class="missing-check" value="' + row.id + '"'
                  + (missingSelected[row.id] ? ' checked' : '') + '></td>'
                  + '<td>' + escapeHtml(row.so_code || '-') + '</td>'
                  + '<td>' + escapeHtml(row.do_code || '-') + '</td>'
                  + '<td>' + escapeHtml(row.customer || '-') + '</td>'
                  + '<td>' + escapeHtml(row.do_status || '-') + '</td>'
                  + '<td class="text-right">' + formatNumber(row.idr_rate) + '</td>'
                  + '<td class="text-right">' + formatRupiah(row.grand_total_idr) + '</td>'
                  + '<td>' + invoiceText + '</td>'
                  + '</tr>';
          });

          if (!rows.length) {
              html = '<tr><td colspan="8" class="text-center text-muted">'
                  + (missingData.length ? 'Tidak ada data yang cocok.' : 'Tidak ada nota bermasalah.')
                  + '</td></tr>';
          }

          $('#missing-tbody').html(html);
          updateMissingSelectionState();
      }

      function loadMissingInvoices() {
          missingData = [];
          missingSelected = {};

          $('#missing-tbody').html('<tr><td colspan="8" class="text-center text-muted">Memuat data...</td></tr>');
          $('#missing-count').text('Memuat...');
          $('#btn-generate-selected').prop('disabled', true);

          $.ajax({
              url: missingInvoiceListUrl,
              type: 'GET',
              dataType: 'json'
          }).done(function(response) {
              missingData = response.data || [];
              renderMissingTable();
          }).fail(function(xhr) {
              var message = xhr.responseJSON && xhr.responseJSON.message
                  ? xhr.responseJSON.message
                  : 'Gagal memuat data nota bermasalah.';

              missingData = [];
              $('#missing-tbody').html('<tr><td colspan="8" class="text-center text-danger">' + escapeHtml(message) + '</td></tr>');
              $('#missing-count').text('0 kandidat');
          });
      }

      $('#btn-generate-missing').on('click', function(e) {
          e.preventDefault();
          $('#missing-search').val('');
          $('#modalMissingInvoice').modal('show');
          loadMissingInvoices();
      });

      $('#modalMissingInvoice').on('shown.bs.modal', function() {
          $('#missing-search').trigger('focus');
      });

      $('#missing-search').on('input', renderMissingTable);

      $(document).on('change', '#missing-check-all', function() {
          var checked = this.checked;

          filteredMissingRows().forEach(function(row) {
              if (checked) {
                  missingSelected[row.id] = true;
              } else {
                  delete missingSelected[row.id];
              }
          });

          renderMissingTable();
      });

      $(document).on('change', '.missing-check', function() {
          var id = parseInt($(this).val(), 10);

          if (this.checked) {
              missingSelected[id] = true;
          } else {
              delete missingSelected[id];
          }

          updateMissingSelectionState();
      });

      $('#btn-generate-selected').on('click', function() {
          var ids = Object.keys(missingSelected).map(function(id) {
              return parseInt(id, 10);
          });

          if (!ids.length) {
              return;
          }

          Swal.fire({
              title: 'Generate ' + ids.length + ' nota?',
              text: 'Nota yang belum ada akan dibuat. Nota yang terhapus karena revisi akan dipulihkan dan disinkronkan dengan total DO saat ini.',
              type: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Ya, generate',
              cancelButtonText: 'Batal'
          }).then(function(result) {
              if (!result.value) {
                  return;
              }

              Swal.fire({
                  title: 'Memproses...',
                  allowOutsideClick: false,
                  onOpen: function() {
                      Swal.showLoading();
                  }
              });

              $.ajax({
                  url: missingInvoiceGenerateUrl,
                  type: 'POST',
                  dataType: 'json',
                  data: {
                      _token: '{{ csrf_token() }}',
                      ids: ids
                  }
              }).done(function(response) {
                  var lines = [response.message];

                  if (response.generated && response.generated.length) {
                      lines.push('Berhasil: ' + response.generated.map(function(row) {
                          return escapeHtml(row.do_code || row.do_id);
                      }).join(', '));
                  }

                  if (response.failed && response.failed.length) {
                      lines.push('Gagal: ' + response.failed.map(function(row) {
                          return escapeHtml(row.do_code || row.do_id) + ' (' + escapeHtml(row.reason) + ')';
                      }).join(', '));
                  }

                  if (response.skipped && response.skipped.length) {
                      lines.push('Dilewati: ' + response.skipped.map(function(row) {
                          return escapeHtml(row.do_code || row.do_id);
                      }).join(', '));
                  }

                  var hasProblem = (response.failed && response.failed.length) || (response.skipped && response.skipped.length);

                  Swal.fire({
                      title: hasProblem ? 'Sebagian selesai' : 'Berhasil',
                      html: lines.join('<br>'),
                      type: hasProblem ? 'warning' : 'success'
                  }).then(function() {
                      $('#modalMissingInvoice').modal('hide');
                      datatable.ajax.reload(null, false);
                  });
              }).fail(function(xhr) {
                  var message = xhr.responseJSON && xhr.responseJSON.message
                      ? xhr.responseJSON.message
                      : 'Gagal generate nota.';

                  Swal.fire('Gagal', message, 'error');
              });
          });
      });
      @endrole
  });
</script>
@endpush