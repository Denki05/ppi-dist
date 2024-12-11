@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Finance</span>
  <span class="breadcrumb-item active">Payable</span>
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

@if(session()->has('message'))
<div class="alert alert-success alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Success</h3>
  <p class="mb-0">{{ session()->get('message') }}</p>
</div>
@endif

<div class="block">
  <hr class="my-20">
  <div class="block-content block-content-full">
      <div class="row mb-30">
        <div class="col">
          <a href="#" class="btn btn-outline-primary ml-10 btn-add"><i class="fa fa-plus"></i> Add Payable</a>

          @if(auth()->user()->is_superuser)
          <button type="button" class="btn btn-outline-info ml-10" data-toggle="modal" data-target="#modal-manage">Manage</button>
          @endif
        </div>
      </div>
      <div class="row mb-30">
        <div class="form-group row">
          <div class="col-md-9">
            <div class="block">
              <div class="block-content">
                <div class="form-group row">
                  <label class="col-md-2 col-form-label text-left" for="customer_name">Customer :</label>
                  <div class="col-md-4">
                    <select class="form-control js-select2" name="customer_name" id="customer_name">
                      <option value="all">All</option>
                      @foreach($customer AS $row)
                      <option value="{{ $row->id }}">{{ $row->name }} {{ $row->text_kota }}</option>
                      @endforeach
                    </select>
                  </div>
                  <label class="col-md-2 col-form-label text-left" for="status">Name :</label>
                  <div class="col-md-4">
                    <select class="form-control js-select2" name="status" id="status">
                      <option value="all">All</option>
                      <option value="0">DELETED</option>
                      <option value="1">ACTIVE</option>
                      <option value="2">ACC</option>
                      <option value="3">REVISI</option>
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
                    <a href="#" id="btn-filter" class="btn bg-gd-corporate border-0 text-white pl-50 pr-50">
                      Filter <i class="fa fa-search ml-10"></i>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-12">
        <table class="datatable table table-striped" id="datatable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Payable Code</th>
                    <th>Invoice Code</th>
                    <th>Store</th>
                    <th>Total</th>
                    <th>Payable Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>

        </div>
      </div>
  </div>
</div>

@include('superuser.finance.payable.modal')

@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')

@push('scripts')

<script type="text/javascript">
    $(document).ready(function () {
      $('.js-select2').select2({});

      let datatableUrl = '{{ route('superuser.finance.payable.json') }}';
      let firstDatatableUrl = datatableUrl + '?customer_name=all&status=all';
      var datatable = $('#datatable').DataTable({
        language: {
          processing: "<span class='fa-stack fa-lg'>\n\
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
            {data: 'DT_RowIndex', name: 'id'},
            {data: 'code', name: 'code'},
            {data: 'invoice_code', name: 'invoice_code'},
            {data: 'customer', name: 'customer'},
            {
              data: 'total_pay',
              render: function(data, type, row) {
                  return formatRupiah(data);
              }
            },
            {
              data: 'created_at',
              render: {
                _: 'display',
                sort: 'timestamp'
              }
            },
            {data: 'status'},
            {data: 'action', orderable: false, searcable: false}
          ],
          order: [
            [1, 'desc']
          ],
          pageLength: 10,
            lengthMenu: [
              [10, 15, 20, 50, 100],
              [10, 15, 20, 50, 100]
          ],
          dom: "<'row'<'col-sm-2'l><'col-sm-7 text-left'B><'col-sm-3'f>>" +
          "<'row'<'col-sm-12'tr>>" +
          "<'row'<'col-sm-5'i><'col-sm-7'p>>",
      });

      $('#btn-filter').on('click', function(e) {
        e.preventDefault();
        var customer_search = $('#customer_name').val();
        var status_search = $('#status').val();
        let newDatatableUrl = datatableUrl + '?customer_name=' + customer_search + '&status=' + status_search;
        datatable.ajax.url(newDatatableUrl).load();
      });

      function formatRupiah(amount) {
          var number_string = amount.toString().replace(/[^,\d]/g, ''),
              split = number_string.split(','),
              remainder = split[0].length % 3,
              rupiah = split[0].substr(0, remainder),
              thousands = split[0].substr(remainder).match(/\d{3}/gi);

          if (thousands) {
              separator = remainder ? '.' : '';
              rupiah += separator + thousands.join('.');
          }

          return 'Rp ' + rupiah + (split[1] ? ',' + split[1] : '');
      }

      $(document).on('click', '.btn-add', function () {
        $('#modalSelectCustomer').modal('show');
      });

      $(document).on('click', '.btn-close-modal', function () {
        $('#modalSelectCustomer').modal('hide');
      });

      // Initialize Select2 after the modal is shown
      $('#modalSelectCustomer').on('shown.bs.modal', function () {
          $("#select_customer").select2({
              dropdownParent: $('#modalSelectCustomer .modal-content')
          });
      });
    });
</script>
@endpush