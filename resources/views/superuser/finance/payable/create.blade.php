@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Finance</span>
  <span class="breadcrumb-item">Payable</span>
  <span class="breadcrumb-item active">Create</span>
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
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">#Payable Create</h3>
  </div>
  <div class="block-content block-content-full">
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="pay_date">Payable Date <span class="text-danger">*</span></label>
      <div class="col-md-7">
        <input type="date" class="form-control" id="pay_date" name="pay_date">
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="member">Customer <span class="text-danger">*</span></label>
      <div class="col-md-7">
        <select class="js-select2 form-control" name="member" id="member">
          <option value="all">Pilih Customer</option>
          @foreach($other_address as $key)
          <option value="{{$key->id}}">{{$key->name}}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="form-group row">
      <label class="col-md-3 col-form-label text-right" for="note">Note</label>
      <div class="col-md-7">
        <input type="text" class="form-control" id="note" name="note">
      </div>
    </div>
  </div>
</div>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">#Detail Invoice</h3>
  </div>
  <div class="block-content block-content-full">
    <table class="table table-striped" id="datatables">
      <thead>
        <tr>
          <th>#</th>
          <th>Invoice</th>
          <th>Grand Total</th>
          <th>Total Bayar</th>
          <th>Sisa</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
@endsection

@push('plugin-styles')
<link rel="stylesheet" href="{{ url('https://cdn.datatables.net/select/1.3.1/css/select.dataTables.min.css') }}">
@endpush

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')

@push('scripts')
  <script src="{{ url('https://cdn.datatables.net/select/1.3.1/js/dataTables.select.min.js') }}"></script>
  <script src="{{ asset('utility/superuser/js/form.js') }}"></script>
  <script type="text/javascript">
    $(document).ready(function() {
      $('.js-select2').select2();

      let datatableUrl = '{{ route('superuser.finance.invoicing.json') }}';
      let firstDatatableUrl = datatableUrl +
        '?member=all';

      var datatable = $('#datatables').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
          "url": datatableUrl,
          "dataType": "json",
          "type": "GET",
          "data":{ 
            _token: "{{csrf_token()}}"
          }
        },
        columns: [
          {data: 'id', width: '3%'},
          {data: 'invoice_code', name: 'finance_invoicing.code'},
          {
            data: 'invoice_total',
            // name: 'finance_invoicing.grand_total_idr',
            // render: $.fn.dataTable.render.number('.', ',', 2, 'Rp. '),
            // searchable: false
            render: function (data, type, row, meta) {
              return '<input class="form-control grand_total_idr" id="grand_total_idr" name="grand_total_idr[]" type="text" value="'+ parseFloat(row.invoice_total) +'" readonly>';
            }
          },
          {
            data: null,
            render: function (data, type, row) {
              return '<input class="form-control total_bayar" id="total_bayar" name="total_bayar[]" type="text" value="0">';
            }
          },
          {
            data: null,
            render: function (data, type, row) {
              return '<input class="form-control sisa" id="sisa" name="sisa[]" type="text" value="0">';
            }
          },
        ],
        columnDefs: [ {
          orderable: false,
          searcable: false,
          // data: null,
          defaultContent: '',
          className: 'select-checkbox',
          targets:   0
        }],
        select: {
            style: 'os',
            selector: 'td:not(:last-child)',
        },
        order: [
          [1, 'asc']
        ],
        pageLength: 5,
        lengthMenu: [
          [5, 15, 20],
          [5, 15, 20]
        ],
        "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>> <"row"<"col-sm-12 col-md-12"p>> <"row"<"col-sm-12"rt>> <"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
      });

      $('#member').on('change', function(e) {
        e.preventDefault();
        var member = $('#member').val();
        let newDatatableUrl = datatableUrl + '?member=' + member;
        datatable.ajax.url(newDatatableUrl).load();
      });

      $('#datatables tbody').on( 'keyup', 'input[name="total_bayar[]"]', function (e) {
        var grand_total = parseFloat($(this).parents('tr').find('input[name="grand_total_idr[]"]').val());
        var total_bayar = parseFloat($(this).val());
        var sisa = grand_total - total_bayar;

        $(this).parents('tr').find('input[name="sisa[]"]').val(sisa);
      });
    })
  </script>
@endpush