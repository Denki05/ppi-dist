@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.product_category.index') }}">Product Category</a>
  <span class="breadcrumb-item active">{{ $product_category->id }}</span>
</nav>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Show Product Category :</h3>
  </div>
  <div class="block-content">
    <div class="container">
      <div class="row">
        <label class="col-md-3 col-form-label text-right">Brand Name :</label>
        <div class="col-md-7">
          <div class="form-control-plaintext">{{ $product_category->brand_name }}</div>
        </div>
      </div>
      <div class="row">
        <label class="col-md-3 col-form-label text-right">Code :</label>
        <div class="col-md-7">
          <div class="form-control-plaintext">{{ $product_category->code }}</div>
        </div>
      </div>
      <div class="row">
        <label class="col-md-3 col-form-label text-right">Detail </label>
        <div class="col-md-3">
          <div class="form-control-plaintext">Category: {{ $product_category->name ?? '-' }}</div>
        </div>
        <div class="col-md-3">
          <div class="form-control-plaintext">Type:  {{ $product_category-> ?? '-' }}</div>
        </div>
      </div>
      <div class="row">
        <label class="col-md-3 col-form-label text-right">Status :</label>
        <div class="col-md-7">
          <div class="form-control-plaintext">{{ $product_category->status() }}</div>
        </div>
      </div>
    </div>
    <div class="row pt-30 mb-15">
      <div class="col-md-6">
        <a href="{{ route('superuser.master.product_category.index') }}">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
      </div>
      @if($product_category->status != $product_category::STATUS['DELETED'])
      <div class="col-md-6 text-right">
        <a href="javascript:deleteConfirmation('{{ route('superuser.master.product_category.destroy', $product_category->id) }}', true)">
          <button type="button" class="btn bg-gd-pulse border-0 text-white">
            Delete <i class="fa fa-trash ml-10"></i>
          </button>
        </a>
        <a href="{{ route('superuser.master.product_category.edit', $product_category->id) }}">
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
   <div class="col-md-12">
    <div class="block">
      <div class="block-header block-header-default">
        <h3 class="block-title">Product</h3>
      </div>
      <div class="block-content block-content-full">
        <table id="datatable-product" class="table table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Code</th>
              <th>Name</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($product_category->products as $product)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $product->code }}</td>
              <td>{{ $product->name }}</td>
              <td>
                <a href="{{ route('superuser.master.product.show', $product->id) }}">
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
    $('#datatable-product').DataTable()
  })
</script>
@endpush
