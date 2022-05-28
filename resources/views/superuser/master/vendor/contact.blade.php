@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.vendor.index') }}">Vendor</a>
  <a class="breadcrumb-item" href="{{ route('superuser.master.vendor.show', $vendor->id) }}">{{ $vendor->id }}</a>
  <span class="breadcrumb-item active">Contact</span>
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
<h2 class="content-heading">
  Vendor Contact
</h2>
<div class="row">
  <div class="col-md-6">
    <div class="block">
      <div class="block-content block-content-full">
        <form action="{{ route('superuser.master.vendor.contact.add', $vendor->id) }}" method="POST">
          @csrf
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right">Contact</label>
            <div class="col-md-7">
              <select class="js-select2 form-control" id="contact" name="contact" data-placeholder="Select Contact">
                <option></option>
                @foreach($contacts as $contact)
                <option value="{{ $contact->id }}">{{ $contact->name }} - {{ $contact->phone }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="mt-30">
            <a href="{{ route('superuser.master.vendor.show', $vendor->id) }}">
              <button type="button" class="btn bg-gd-cherry border-0 text-white">
                <i class="fa fa-arrow-left mr-10"></i> Back
              </button>
            </a>
            <div class="pull-right">
              <button type="submit" class="btn bg-gd-corporate border-0 text-white">
                Add <i class="fa fa-arrow-right ml-10"></i>
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="block">
      <div class="block-content block-content-full">
        <table id="datatable" class="table table-striped table-vcenter table-responsive js-table-sections table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Phone</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($vendor->contacts as $contact)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $contact->name }}</td>
              <td>{{ $contact->phone }}</td>
              <td>
                <a href="{{ route('superuser.master.contact.show', $contact->id) }}" target="_blank">
                  <button type="button" class="btn btn-sm btn-circle btn-alt-secondary" title="Show Contact">
                    <i class="fa fa-eye"></i>
                  </button>
                </a>
                <a href="{{ route('superuser.master.vendor.contact.remove', [$vendor->id, $contact->pivot->id]) }}">
                  <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete">
                    <i class="fa fa-times"></i>
                  </button>
                </a>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script>
$(document).ready(function () {
  $('#datatable').DataTable()

  $('.js-select2').select2()
})
</script>
@endpush