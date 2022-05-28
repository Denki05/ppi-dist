@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Account</span>
  <a class="breadcrumb-item" href="{{ route('superuser.account.sales_person.index') }}">Sales Person</a>
  <a class="breadcrumb-item" href="{{ route('superuser.account.sales_person.show', $sales_person->id) }}">{{ $sales_person->id }}</a>
  <span class="breadcrumb-item active">Warehouse</span>
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
  Sales Person Warehouse
</h2>
<div class="row">
  <div class="col-md-6">
    <div class="block">
      <div class="block-content block-content-full">
        <form action="{{ route('superuser.account.sales_person.warehouse.add', $sales_person->id) }}" method="POST">
          @csrf
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right">Warehouse</label>
            <div class="col-md-7">
              <select class="js-select2 form-control" id="warehouse" name="warehouse" data-placeholder="Select Warehouse">
                <option></option>
                @foreach($warehouses as $wh)
                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="mt-30">
            <a href="{{ route('superuser.account.sales_person.show', $sales_person->id) }}">
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
              <th>Warehouse</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($sales_person->warehouses as $warehouse)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $warehouse->name }}</td>
              <td>
                <a href="{{ route('superuser.account.sales_person.warehouse.remove', [$sales_person->id, $warehouse->pivot->id]) }}">
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