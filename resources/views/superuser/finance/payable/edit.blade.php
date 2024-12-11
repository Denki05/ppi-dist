@extends('superuser.app')

@section('content')
<form class="ajax" data-action="{{ route('superuser.finance.payable.update', $result->id) }}" data-type="POST" enctype="multipart/form-data">
  <input type="hidden" name="_method" value="PUT">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <div id="alert-block"></div>

  <div class="block">
    <div class="block-header block-header-default">
      <h3 class="block-title">#Edit Payment</h3>
    </div>
    <div class="block-content">
      <!-- Payment Details -->
      <div class="row">
        <div class="col-md-4">
          <div class="form-group">
            <label for="code">Code</label>
            <input type="text" id="code" name="code" class="form-control" value="{{ $result->code }}" readonly>
          </div>
          <div class="form-group">
            <label for="pay_date">Payment Date</label>
            <input type="date" id="pay_date" name="pay_date" class="form-control" value="{{ $result->pay_date }}">
          </div>
        </div>

        <div class="col-md-4">
          <div class="form-group">
            <label for="code">Store</label>
            <input type="text" class="form-control" id="store" name="store" value="{{ $result->customer->name }}" readonly>
          </div>
          <div class="form-group">
            <label for="pay_date">Alamat</label>
            <textarea class="form-control" id="address" rows="1" readonly>{{ $result->customer->address }}</textarea>
          </div>
        </div>

        <div class="col-md-4">
          <div class="form-group">
            <label for="code">Note</label>
            <textarea class="form-control" id="note" name="note" rows="5">{{ $result->note }}</textarea>
          </div>
        </div>
      </div>

      <!-- Payable Details Table -->
      <div class="table-responsive">
        <table class="table table-bordered" id="datatable">
          <thead>
            <tr>
              <th>#</th>
              <th>Ref INV</th>
              <th>Account Receivable</th>
              <th>Payable</th>
            </tr>
          </thead>
          <tbody>
            @foreach($result->payable_detail as $index => $detail)
              <tr>
                <input type="hidden" name="payable_detail[]" value="{{ $detail->id }}">
                <input type="hidden" name="invoice_id[]" value="{{ $detail->invoice_id }}">
                <td>{{ $loop->iteration }}</td>
                <td>{{ $detail->invoice->code }}</td>
                <td>
                  <input type="number" class="form-control" value="{{ $detail->invoice->grand_total_idr }}" readonly>
                </td>
                <td>
                  <input type="number" name="payable[]" class="form-control" value="{{ $detail->total }}" required>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <!-- Submit Button -->
      <div class="block-content">
        <div class="form-group row pt-30">
          <div class="col-md-6">
            <a href="{{ route('superuser.finance.payable.index') }}">
              <button type="button" class="btn bg-gd-cherry border-0 text-white">
                <i class="fa fa-arrow-left mr-10"></i> Back
              </button>
            </a>
          </div>
          <div class="col-md-6 text-right">
            <button type="submit" class="btn btn-primary">Submit</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {
    $('#datatable').DataTable( {
          "paging":   false,
          "ordering": true,
          "info":     false,
          "searching" : false,
          "columnDefs": [{
            "targets": 0,
            "orderable": false
          }]
    });
  })
</script>
@endpush