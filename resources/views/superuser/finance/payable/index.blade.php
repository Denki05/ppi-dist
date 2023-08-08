@extends('superuser.app')

@section('content')

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
    <div class="block-content block-content-full">
      <div class="block-header block-header-default">
        <h3 class="block-title">#List Payable</h3>
        <!-- <input type="button" value="Click Me" style="float: right;"> -->
        <a href="{{ route('superuser.finance.payable.create') }}" class="btn btn-primary" style="float: right;"><i class="fa fa-plus"></i></a>
      </div>
    </div>
    <div class="row mb-30">
      <div class="col-12">
        <div class="block">
          <div class="block-content">
            <table class="table table-hover table-fixed" id="listPayable">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Code</th>
                  <th>Ref Invoice</th>
                  <th>Customer</th>
                  <th>Grand Total</th>
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
  </div>

@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    let datatableUrl = '{{ route('superuser.finance.payable.json') }}';

      var datatable = $('#listPayable').DataTable({
        language: {
              processing: "<span class='fa-stack fa-lg'>\n\
                                    <i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\
                              </span>",
        },
        processing: true,
        serverSide: false,
        searching: false,
        paging: false,
        info: false,
        ajax: {
          "url": datatableUrl,
          "dataType": "json",
          "type": "GET",
          "data":{ _token: "{{csrf_token()}}"}
        },
        columns: [
          {data: 'DT_RowIndex', name: 'id'},
          {data: 'payableCode', name: 'finance_payable.code'},
          {data: 'invoiceCode', name: 'finance_invoicing.code'},
          {data: 'customer', name: 'master_customer_other_addresses.name'},
          {data: 'payableTotal', name: 'finance_payable_detail.total'},
          {data: 'status', name: 'finance_payable.status'},
          {data: 'actions'}
        ],
        order: [
          [1, 'desc']
        ],
        pageLength: 5,
        lengthMenu: [
          [5, 15, 20],
          [5, 15, 20]
        ],
      });

    $('.js-select2').select2();
  })
</script>
@endpush