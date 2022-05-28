@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.product.index') }}">Product</a>
  <span class="breadcrumb-item active">{{ $product->id }}</span>
</nav>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Show Product</h3>
  </div>
  <div class="block-content">
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Code</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $product->code }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Name</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $product->name }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Material Code</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $product->material_code }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Material Name</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $product->material_name }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Buying Price</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $product->buying_price }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Selling Price</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $product->selling_price }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Description</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $product->description }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Note</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $product->note }}</div>
      </div>
    </div>
    <hr>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Default Quantity</label>
      <div class="col-md-2">
        <div class="form-control-plaintext">{{ $product->default_quantity }}</div>
      </div>
      <label class="col-md-2 col-form-label text-right">Default Unit</label>
      <div class="col-md-2">
        <div class="form-control-plaintext">
          <a href="{{ route('superuser.master.unit.show', $product->default_unit_id) }}">
            {{ $product->default_unit->name }}
          </a>
        </div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Default Warehouse</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">
          <a href="{{ route('superuser.master.warehouse.show', $product->default_warehouse_id) }}">
            {{ $product->default_warehouse->name }}
          </a>
        </div>
      </div>
    </div>
    <hr>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Brand Reference</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">
          <a href="{{ route('superuser.master.brand_reference.show', $product->brand_reference_id) }}">
            {{ $product->brand_reference->name }}
          </a>
        </div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Sub Brand Reference</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">
          <a href="{{ route('superuser.master.sub_brand_reference.show', $product->sub_brand_reference_id) }}">
            {{ $product->sub_brand_reference->name }}
          </a>
        </div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Category</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">
          <a href="{{ route('superuser.master.product_category.show', $product->category_id) }}">
            {{ $product->category->name }}
          </a>
        </div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Type</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">
          <a href="{{ route('superuser.master.product_type.show', $product->type_id) }}">
            {{ $product->type->name }}
          </a>
        </div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">image</label>
      <div class="col-md-7">
        <a href="{{ $product->image_url ?? img_holder() }}" class="img-link img-link-zoom-in img-thumb img-lightbox">
          <img src="{{ $product->image_url ?? img_holder() }}" class="img-fluid img-show-small">
        </a>
      </div>
    </div>

    <div class="row">
      <label class="col-md-3 col-form-label text-right">image HD</label>
      <div class="col-md-7">
        <a href="{{ $product->image_hd_url ?? img_holder() }}" class="img-link img-link-zoom-in img-thumb img-lightbox">
          <img src="{{ $product->image_hd_url ?? img_holder() }}" class="img-fluid img-show-small">
        </a>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Status</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $product->status() }}</div>
      </div>
    </div>
    <div class="row pt-30 mb-15">
      <div class="col-md-6">
        <a href="{{ route('superuser.master.product.index') }}">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
      </div>
      @if($product->status != $product::STATUS['DELETED'])
      <div class="col-md-6 text-right">
        @if($product->status == $product::STATUS['ACTIVE'])
        <a href="{{ route('superuser.master.product.disable', $product->id) }}">
          <button type="button" class="btn bg-warning border-0 text-white">
            Disable
          </button>
        </a>
        @elseif($product->status == $product::STATUS['INACTIVE'])
        <a href="{{ route('superuser.master.product.enable', $product->id) }}">
          <button type="button" class="btn bg-info border-0 text-white">
            Enable
          </button>
        </a>
        @endif
        <a href="javascript:deleteConfirmation('{{ route('superuser.master.product.destroy', $product->id) }}', true)">
          <button type="button" class="btn bg-gd-pulse border-0 text-white">
            Delete <i class="fa fa-trash ml-10"></i>
          </button>
        </a>
        <a href="{{ route('superuser.master.product.edit', $product->id) }}">
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
        <h3 class="block-title">Min Stock</h3>
        
        @if($product->status != $product::STATUS['DELETED'])
        <a href="{{ route('superuser.master.product.min_stock.create', [$product->id]) }}">
          <button type="button" class="btn btn-outline-primary min-width-125 pull-right">Create</button>
        </a>
        @endif
      </div>
      <div class="block-content block-content-full">
        <table id="datatable" class="table table-striped table-vcenter table-responsive">
          <thead>
            <tr>
              <th>#</th>
              <th>Warehouse</th>
              <th>Quantity</th>
              <th>Unit</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($product->min_stocks as $min_stock)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $min_stock->warehouse->name }}</td>
              <td>{{ $min_stock->quantity }}</td>
              <td>{{ $min_stock->unit->name }}</td>
              <td>
                <a href="{{ route('superuser.master.product.min_stock.edit', [$product->id, $min_stock->id]) }}">
                  <button type="button" class="btn btn-sm btn-circle btn-alt-warning" title="Edit">
                    <i class="fa fa-pencil"></i>
                  </button>
                </a>
                <a href="javascript:deleteConfirmation('{{ route('superuser.master.product.min_stock.destroy', [$product->id, $min_stock->id]) }}')">
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
@include('superuser.asset.plugin.magnific-popup')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    $('#datatable').DataTable()

    $('a.img-lightbox').magnificPopup({
    type: 'image',
    closeOnContentClick: true,
  });
  })
</script>
@endpush
