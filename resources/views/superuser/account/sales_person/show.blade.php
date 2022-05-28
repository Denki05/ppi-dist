@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Account</span>
  <a class="breadcrumb-item" href="{{ route('superuser.account.sales_person.index') }}">Sales Person</a>
  <span class="breadcrumb-item active">{{ $sales_person->id }}</span>
</nav>
<h2 class="content-heading">Sales Person</h2>
<div class="block">
  <div class="block-content">
    <div class="row">
      <label class="col-lg-3 col-form-label text-right">Username</label>
      <div class="col-lg-7">
        <div class="form-control-plaintext">{{ $sales_person->username }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-lg-3 col-form-label text-right">Email</label>
      <div class="col-lg-7">
        <div class="form-control-plaintext">{{ $sales_person->email }}</div>
      </div>
    </div>
    <hr>
    <div class="row">
      <label class="col-lg-3 col-form-label text-right">Name</label>
      <div class="col-lg-7">
        <div class="form-control-plaintext">{{ $sales_person->name }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-lg-3 col-form-label text-right">Phone</label>
      <div class="col-lg-7">
        <div class="form-control-plaintext">{{ $sales_person->phone }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-lg-3 col-form-label text-right">Address</label>
      <div class="col-lg-7">
        <div class="form-control-plaintext">{{ $sales_person->address }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Provinsi</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $sales_person->text_provinsi }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Kota</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $sales_person->text_kota }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Kecamatan</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $sales_person->text_kecamatan }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Kelurahan</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $sales_person->text_kelurahan }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Zipcode</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $sales_person->zipcode }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-lg-3 col-form-label text-right">Status</label>
      <div class="col-lg-7">
        <div class="form-control-plaintext">
          @if($sales_person->is_active)
          <i class="fa fa-lg fa-check text-success"></i>
          @else
          <i class="fa fa-lg fa-close text-danger"></i>
          @endif
        </div>
      </div>
    </div>
    {{-- <div class="row">
      <label class="col-lg-3 col-form-label text-right">Created By</label>
      <div class="col-lg-7">
        <div class="form-control-plaintext">{{ $sales_person->createdBySuperuser() }}</div>
      </div>
    </div> --}}
    <div class="row pt-30 mb-15">
      <div class="col-md-6">
        <a href="{{ route('superuser.account.sales_person.index') }}">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
      </div>
      @if($sales_person->is_active)
      <div class="col-md-6 text-right">
        <a href="javascript:deleteConfirmation('{{ route('superuser.account.sales_person.destroy', $sales_person->id) }}', true)">
          <button type="button" class="btn bg-gd-pulse border-0 text-white">
            Delete <i class="fa fa-trash ml-10"></i>
          </button>
        </a>
        <a href="{{ route('superuser.account.sales_person.edit', $sales_person->id) }}">
          <button type="button" class="btn bg-gd-leaf border-0 text-white">
            Edit <i class="fa fa-pencil ml-10"></i>
          </button>
        </a>
      </div>
      @endif
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-6">
    <h2 class="content-heading">
      Zone
      @if($sales_person->is_active)
      <a href="{{ route('superuser.account.sales_person.zone.manage', $sales_person->id) }}">
        <button type="button" class="pull-right btn btn-warning btn-noborder btn-sm">
          Manage
        </button>
      </a>
      @endif
    </h2>
    <div class="block">
      <div class="block-content block-content-full">
        <table id="datatable-zone" class="table table-striped table-vcenter table-responsive js-table-sections table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>Provinsi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($sales_person->zones as $zone)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $zone->text_provinsi }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <h2 class="content-heading">
      Warehouse
      @if($sales_person->is_active)
      <a href="{{ route('superuser.account.sales_person.warehouse.manage', $sales_person->id) }}">
        <button type="button" class="pull-right btn btn-warning btn-noborder btn-sm">
          Manage
        </button>
      </a>
      @endif
    </h2>
    <div class="block">
      <div class="block-content block-content-full">
        <table id="datatable-warehouse" class="table table-striped table-vcenter table-responsive js-table-sections table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>Warehouse</th>
            </tr>
          </thead>
          <tbody>
            @foreach($sales_person->warehouses as $warehouse)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $warehouse->name }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-6">
    <h2 class="content-heading">
      Branch Office
      @if($sales_person->is_active)
      <a href="{{ route('superuser.account.sales_person.branch_office.manage', $sales_person->id) }}">
        <button type="button" class="pull-right btn btn-warning btn-noborder btn-sm">
          Manage
        </button>
      </a>
      @endif
    </h2>
    <div class="block">
      <div class="block-content block-content-full">
        <table id="datatable-branch_office" class="table table-striped table-vcenter table-responsive js-table-sections table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>Branch Office</th>
            </tr>
          </thead>
          <tbody>
            @foreach($sales_person->branch_offices as $branch_office)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $branch_office->name }}</td>
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
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script>
$(document).ready(function () {
  $('#datatable-zone').DataTable()
  $('#datatable-warehouse').DataTable()
  $('#datatable-branch_office').DataTable()
})
</script>
@endpush