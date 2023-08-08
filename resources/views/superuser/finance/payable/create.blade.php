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
    <div class="row">
      <div class="col">
        <div class="form-group">
          <label for="payable_date">Payable Date</label>
          <input type="date" class="form-control" id="payable_date" name="payable_date">
        </div>
      </div>
      <div class="col">
        <div class="form-group">
          <label for="note">Note</label>
          <input type="text" class="form-control" id="note" name="note">
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <div class="form-group">
          <label for="customer_other_address_id">Customer</label>
          <select class="form-control js-select2" name="customer_other_address_id">
            <option value="">Pilih Customer</option>
            @foreach($other_address as $key)
            <option value="{{$key->id}}">{{$key->name}}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="col">
        <div class="form-group">
          <label for="invoice_id">Invoice</label>
          <select class="form-control js-select2" name="invoice_id">
            <option value="">Pilih Invoice</option>
            @foreach($invoice as $key)
            <option value="{{$key->id}}">{{$key->code}}</option>
            @endforeach
          </select>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

<!-- Modal -->


@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')

@push('scripts')
  <script type="text/javascript">
    $(document).ready(function() {
      $('.js-select2').select2();
    })
  </script>
@endpush