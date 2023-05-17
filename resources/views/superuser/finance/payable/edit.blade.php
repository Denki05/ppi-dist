@extends('superuser.app')

@section('content')
<div id="alert-block"></div>
<form class="ajax" data-action="{{ route('superuser.finance.payable.update', $result->id) }}" data-type="POST" enctype="multipart/form-data">
  <div class="block">
    <div class="block-header block-header-default">
      <h3 class="block-title">#Edit Payment</h3>
    </div>
    <div class="block-content">
      <div class="row">
        <div class="col-sm">
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="code">Code</label>
            <div class="col-md-4">
              <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)" value="{{ $result->code }}" readonly>
            </div>
          </div>
        </div>
        <div class="col-sm">
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="select_date">Store <span class="text-danger">*</span></label>
            <div class="col-md-8">
              <input type="text" class="form-control" id="store" name="store" value="{{ $result->customer->name }}" readonly>
            </div>
          </div>
        </div>
        <div class="col-sm">
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="select_date">Alamat <span class="text-danger">*</span></label>
            <div class="col-md-8">
              <textarea class="form-control" id="address" rows="1" readonly>{{ $result->customer->address }}</textarea>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-sm">
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="select_date">Tanggal Bayar</label>
            <div class="col-md-4">
              <input type="date" class="form-control" id="created_at" name="created_at" value="{{ $result->created_at ? date('Y-m-d', strtotime($result->created_at)) : '' }}" readonly>
            </div>
          </div>
        </div>
        <div class="col-sm">
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="select_date">Telp</label>
            <div class="col-md-8">
              <input type="text" class="form-control" id="store" name="store" value="{{ $result->customer->phone }}" readonly>
            </div>
          </div>
        </div>
        <div class="col-sm">
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="select_date">Note </label>
            <div class="col-md-8">
              <textarea class="form-control" id="note" rows="1" readonly>{{ $result->note }}</textarea>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="form-group row">
      <div class="col-md-12">
        <div class="block-header block-header-default">
          <h3 class="block-title">#List Payment</h3>
        </div>
        <hr>
        <div class="block-content">
          <table id="datatables" class="table table-striped">
            <thead>
              <tr>
                <th class="text-center" >#</th>
                <th class="text-center">Ref INV</th>
                <th class="text-center">Account Receivable</th>
                <th class="text-center">Payabel</th>
              </tr>
            </thead>
            <tbody>
             @foreach($result->payable_detail as $index => $key)
                  <tr>
                    <input type="hidden" class="form-control" name="payable_detail[]" value="{{$key->id}}">
                    <input type="hidden" class="form-control" name="invoice_id[]" value="{{$key->invoice_id}}">
                    <td width="5%">{{ $loop->iteration }}</td>
                    <td width="25%">
                      <span>{{ $key->invoice->code }}</span>
                    </td>
                    <td width="25%">
                      <input style="text-align: center;" type="number" class="form-control count" name="account_receivable[]" value="{{ $key->invoice->grand_total_idr }}" readonly>
                    </td>
                    <td width="25%">
                      <input style="text-align: center;" type="number" class="form-control count" name="payable[]" value="{{ $key->total }}" required>
                    </td>
                  </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      
    </div>
    
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
          <button type="submit" class="btn bg-gd-corporate border-0 text-white" id="submit-table">
            Submit <i class="fa fa-arrow-right ml-10"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</form>
@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {
    $('#datatables').DataTable( {
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
