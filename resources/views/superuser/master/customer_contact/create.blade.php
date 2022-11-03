@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.customer_contact.index') }}">Member Contact</a>
  <span class="breadcrumb-item active">Create</span>
</nav>

<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Create Contact</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.master.customer_contact.store') }}" data-type="POST" enctype="multipart/form-data">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="customer">Store <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="customer" name="customer" data-placeholder="Select Store">
            <option></option>
            @foreach ($customers as $customer)
              <option value="{{ $customer->id }}">{{ $customer->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="other_address">Member <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="other_address" name="other_address" data-placeholder="Select Member">
            <option></option>
            @foreach ($other_address as $row)
              <option value="{{ $row->id }}">{{ $row->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="contact">Contact <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="contact" name="contact" data-placeholder="Select Contact">
            <option></option>
            @foreach ($contacts as $contact)
              <option value="{{ $contact->id }}">{{ $contact->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      
      
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.master.customer.index') }}">
            <button type="button" class="btn bg-gd-cherry border-0 text-white">
              <i class="fa fa-arrow-left mr-10"></i> Back
            </button>
          </a>
        </div>
        <div class="col-md-6 text-right">
          <button type="submit" class="btn bg-gd-corporate border-0 text-white">
            Submit <i class="fa fa-arrow-right ml-10"></i>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.flatpickr')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
    Codebase.helpers('flatpickr')

    $('.js-select2').select2()
  })
</script>
@endpush
