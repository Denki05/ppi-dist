@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.product.index') }}">Product</a>
  <a class="breadcrumb-item" href="{{ route('superuser.master.product.show', $product->id) }}">{{ $product->id }}</a>
  <span class="breadcrumb-item active">Edit</span>
</nav>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Edit Product</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.master.product.update', $product) }}" data-type="POST" enctype="multipart/form-data">
      <input type="hidden" name="_method" value="PUT">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">Code <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)" value="{{ $product->code }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="name">Name <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="name" name="name" value="{{ $product->name }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="material_code">Material Code <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="material_code" name="material_code" value="{{ $product->material_code }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="material_name">Material Name <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="material_name" name="material_name" value="{{ $product->material_name }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="buying_price">Buying Price</label>
        <div class="col-md-7">
          <input type="number" class="form-control" id="buying_price" name="buying_price" min="0" value="{{ $product->buying_price }}" step="0.0001">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="selling_price">Selling Price</label>
        <div class="col-md-7">
          <input type="number" class="form-control" id="selling_price" name="selling_price" min="0" value="{{ $product->selling_price }}" step="0.0001">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="description">Description</label>
        <div class="col-md-7">
          <textarea class="form-control" id="description" name="description">{{ $product->description }}</textarea>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="note">Note</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="note" name="note" data-placeholder="Select Note">
            <option></option>
            @foreach($product_notes as $note)
            <option value="{{ $note }}" {{ ($note == $product->note) ? 'selected' : '' }}>{{ $note }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <hr class="my-20">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Default Quantity <span class="text-danger">*</span></label>
        <div class="col-md-4">
          <input type="number" class="form-control" id="default_quantity" name="default_quantity" min="0" value="{{ $product->default_quantity }}" step="0.0001">
        </div>
        <div class="col-md-3">
          <select class="js-select2 form-control" id="default_unit" name="default_unit" data-placeholder="Unit">
            <option></option>
            @foreach($units as $unit)
            <option value="{{ $unit->id }}" {{ ($unit->id == $product->default_unit_id ) ? 'selected' : '' }}>{{ $unit->abbreviation }} / {{ $unit->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="default_warehouse">Default Warehouse <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="default_warehouse" name="default_warehouse" data-placeholder="Select Warehouse">
            <option></option>
            @foreach($warehouses as $warehouse)
            <option value="{{ $warehouse->id }}" {{ ($warehouse->id == $product->default_warehouse_id ) ? 'selected' : '' }}>{{ $warehouse->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <hr class="my-20">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="brand_reference">Brand Reference <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="brand_reference" name="brand_reference" data-placeholder="Select Brand Reference">
            <option></option>
            @foreach($brand_references as $brand_reference)
            <option value="{{ $brand_reference->id }}" {{ ($brand_reference->id == $product->brand_reference_id ) ? 'selected' : '' }}>{{ $brand_reference->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="sub_brand_reference">Sub Brand Reference <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="sub_brand_reference" name="sub_brand_reference" data-placeholder="Select Sub Brand Reference">
            <option></option>
            @foreach($sub_brand_references as $sub_brand_reference)
            <option value="{{ $sub_brand_reference->id }}" {{ ($sub_brand_reference->id == $product->sub_brand_reference_id ) ? 'selected' : '' }}>{{ $sub_brand_reference->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="category">Category <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="category" name="category" data-placeholder="Select Category">
            <option></option>
            @foreach($product_categories as $category)
            <option value="{{ $category->id }}" {{ ($category->id == $product->category_id ) ? 'selected' : '' }} data-types-id="{{ $category->types->pluck('id') }}">{{ $category->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="type">Type <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="type" name="type" data-placeholder="Select Type">
            <option></option>
            {{-- @foreach($product_types as $type)
            <option value="{{ $type->id }}" {{ ($type->id == $product->type_id ) ? 'selected' : '' }}>{{ $type->name }}</option>
            @endforeach --}}
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">
          Image
          <i class="fa fa-question-circle" data-toggle="popover" data-placement="left" title="Image" data-content="Standard / small image quality. (For Apps, etc)"></i>
        </label>
        <div class="col-md-7">
          <input type="file" id="image" name="image" data-max-file-size="2000" accept="image/png, image/jpeg" data-src="{{ $product->image_url }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">
          Image HD
          <i class="fa fa-question-circle" data-toggle="popover" data-placement="left" title="Image HD" data-content="Better image quality. (For Printing, etc)"></i>
        </label>
        <div class="col-md-7">
          <input type="file" id="image_hd" name="image_hd" data-max-file-size="2000" accept="image/png, image/jpeg" data-src="{{ $product->image_hd_url }}">
        </div>
      </div>
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.master.product.show', $product->id) }}">
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
<div id="alert-block"></div>
@endsection

@include('superuser.asset.plugin.fileinput')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {
    $('.js-select2').select2()

    $('#image').fileinput({
      theme: 'explorer-fa',
      browseOnZoneClick: true,
      showCancel: false,
      showClose: false,
      showUpload: false,
      browseLabel: '',
      removeLabel: '',
      initialPreview: $('#image').data('src'),
      initialPreviewAsData: true,
      fileActionSettings: {
        showDrag: false,
        showRemove: false
      },
      initialPreviewConfig: [
        {
            caption: '{{ $product->image ?? '' }}'
        }
      ]
    });

    $('#image_hd').fileinput({
      theme: 'explorer-fa',
      browseOnZoneClick: true,
      showCancel: false,
      showClose: false,
      showUpload: false,
      browseLabel: '',
      removeLabel: '',
      initialPreview: $('#image_hd').data('src'),
      initialPreviewAsData: true,
      fileActionSettings: {
        showDrag: false,
        showRemove: false
      },
      initialPreviewConfig: [
        {
            caption: '{{ $product->image_hd ?? '' }}'
        }
      ]
    });

    setTimeout(function() {
      $('select#category').trigger('select2:select');
    }, 500);

    $('select#category').on('select2:select', function () {
      let types_id = $(this).find(':selected').data('types-id') || false;
      let data = ajaxGet("{{ route('superuser.repo.master.product_type') }}" + '?id=' + encodeURIComponent(JSON.stringify(types_id)))
      let select_type = $('select#type')

      addLoadSpiner(select_type)
      
      select2_clear(select_type)

      for (i = 0; i < data.length; ++i) {
        let newOption = new Option(data[i].name, data[i].id, false, false);
        select_type.append(newOption).trigger('change');
      }

      hideLoadSpinner(select_type)

      setTimeout(function() {
        $('select#type').val('{{ $product->type_id }}').trigger('change');
      }, 500);
    })
  })
</script>
@endpush
