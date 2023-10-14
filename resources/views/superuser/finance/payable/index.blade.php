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
<div class="block">
  {{--<div class="block-content">
    <a href="{{ route('superuser.finance.payable.create') }}">
      <button type="button" class="btn btn-outline-primary min-width-125">Create</button>
    </a>
  </div>
  <hr class="my-20">--}}
  <div class="block-content block-content-full">
    <table id="datatables" class="table table-striped ">
      <thead>
        <tr>
          <th class="text-center">Store</th>
          <th class="text-center">Count Invoice</th>
          <th class="text-center">Outstanding</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($customer as $index)
          @foreach($index->do as $key => $row)
            <?php
              $total_invoicing = $row->invoicing->grand_total_idr ?? 0;
              $payable = $row->invoicing->payable_detail->sum('total');
              $sisa = $total_invoicing - $payable;

              $count_invoice = $row->invoicing->where('customer_id', $index->id)->count();
              $outstanding = $row->invoicing->where('customer_id', $index->id)->sum('grand_total_idr');
            ?>
            @if($sisa > 0)
              <tr>
                <td>{{ $row->customer->name }} {{ $row->customer->text_kota }}</td>
                <td>{{ number_format($count_invoice) }}</td>
                <td>{{ number_format($outstanding) }}</td>
                <td>
                  <a class="btn btn-primary" href="{{ route('superuser.finance.payable.create', $index->id) }}" role="button">Payment</a>
                </td>
              </tr>
            @endif
          @endforeach
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    $('#datatables').DataTable( {
        "paging":   true,
        "ordering": true,
        "info":     false,
        "searching" : true,
        "columnDefs": [{
          "targets": 0,
          "orderable": false
        }]
      });
  })
</script>
@endpush