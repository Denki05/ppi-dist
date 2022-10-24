@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.customer_category.index') }}">Customer Category</a>
  <span class="breadcrumb-item active">{{ $customer_category->id }}</span>
</nav>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Show Customer Category</h3>
  </div>
  <div class="block-content">
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Code</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer_category->code }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Name</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer_category->name }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Score</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer_category->score }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Description</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer_category->description }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Status</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer_category->status() }}</div>
      </div>
    </div>
    <div class="row pt-30 mb-15">
      <div class="col-md-6">
        <a href="{{ route('superuser.master.customer_category.index') }}">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
      </div>
      @if($customer_category->status != $customer_category::STATUS['DELETED'])
      <div class="col-md-6 text-right">
        <a href="javascript:deleteConfirmation('{{ route('superuser.master.customer_category.destroy', $customer_category->id) }}', true)">
          <button type="button" class="btn bg-gd-pulse border-0 text-white">
            Delete <i class="fa fa-trash ml-10"></i>
          </button>
        </a>
        <a href="{{ route('superuser.master.customer_category.edit', $customer_category->id) }}">
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
    <div class="block">
      <div class="block-header block-header-default">
        <h3 class="block-title">Customer</h3>
      </div>
      <div class="block-content block-content-full">
        <table id="datatable-customer" class="table table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>Code</th>
              <th>Name</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($customer_category->customer as $customer)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $customer->code }}</td>
              <td>{{ $customer->name }}</td>
              <td>
                <a href="{{ route('superuser.master.customer.show', $customer->id) }}">
                  <button type="button" class="btn btn-sm btn-circle btn-alt-secondary" title="View">
                    <i class="fa fa-eye"></i>
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
  <div class="col-md-6">
    <div class="block">
      <div class="block-header block-header-default">
        <h3 class="block-title">Type</h3>

        @if($customer_category->status != $customer_category::STATUS['DELETED'])
        <!-- <a href="{{ route('superuser.master.customer_category.type.manage', [$customer_category->id]) }}">
          <button type="button" class="btn btn-outline-warning min-width-125 pull-right">Manage</button>
        </a> -->
        @endif
      </div>
      <div class="block-content block-content-full">
        <table id="datatable-type" class="table table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>Code</th>
              <th>Name</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($customer_category->types as $type)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $type->code }}</td>
              <td>{{ $type->name }}</td>
              <td>
                <a href="{{ route('superuser.master.customer_type.show', $type->id) }}">
                  <button type="button" class="btn btn-sm btn-circle btn-alt-secondary" title="View">
                    <i class="fa fa-eye"></i>
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

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    $('#datatable-customer').DataTable()
    $('#datatable-type').DataTable()
  })
</script>
@endpush
