@extends('superuser.app')

@section('content')
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Create Product</h3>
  </div>
  <div class="block">
            <div class="block-conten" align="center">
                <div class="col-md-10 col-md-offset-1">
                	<form data-action="{{ route('superuser.master.product.store') }}" data-type="POST" enctype="multipart/form-data" class="f1 ajax">
                		<div class="f1-steps">
                			<div class="f1-progress">
                			    <div class="f1-progress-line" data-now-value="25" data-number-of-steps="4" style="width: 25%;"></div>
                			</div>
                      <div class="f1-step active">
                        <div class="f1-step-icon"><i class="mdi mdi-account-card-details"></i></div>
                        <p>Details</p>
                      </div>
                			<div class="f1-step">
                				<div class="f1-step-icon"><i class="mdi mdi-file-account"></i></div>
                				<p>Data</p>
                			</div>
                			<div class="f1-step">
                				<div class="f1-step-icon"><i class="mdi mdi-shopping"></i></div>
                				<p>Brand</p>
                			</div>
                      <div class="f1-step">
                				<div class="f1-step-icon"><i class="mdi mdi-image"></i></div>
                				<p>Document Image</p>
                			</div>
                		</div>
                		<!-- step 1 -->
                        <fieldset>
                          <h4>Product Details</h4>
                          <div class="form-group">
                            <label for="code">Code <span class="text-danger">*</span></label>
                            <input type="text" id="code" name="code" placeholder="Product Code" class="form-control" onkeyup="nospaces(this)>
                          </div>
                          <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" placeholder="Product Name" class="form-control">
                          </div>
                          <div class="form-group">
                            <label for="material_code">Material Code <span class="text-danger">*</span></label>
                            <input type="text" id="material_code" name="material_code" placeholder="Material Code" class="form-control">
                          </div>
                          <div class="form-group">
                            <label for="material_name">Material Name <span class="text-danger">*</label>
                            <input type="text" id="material_name" name="material_name" placeholder="Material Name" class="form-control">
                          </div>
                          <div class="form-group">
                            <label for="alias">Alias</label>
                            <input type="text" id="alias" name="alias" placeholder="Alias" class="form-control">
                          </div>
                          <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" id="description" name="description" placeholder="Description"></textarea>
                          </div>
                          <div class="form-group">
                            <label>Notes</label>
                            <select class="form-control" id="note" name="note" data-placeholder="Select Note">
                              <option></option>
                              @foreach($product_notes as $note)
                              <option value="{{ $note }}">{{ $note }}</option>
                              @endforeach
                            </select>
                          </div>
                          <div class="f1-buttons">
                            <button type="button" class="btn btn-primary btn-next">Next <i class="fa fa-arrow-right"></i></button>
                          </div>
                        </fieldset>
                        <!-- step 2 -->
                        <fieldset>
                            <h4>product Data</h4>
                            <div class="form-group">
                              <label for="buying_price">Buying Price</label>
                              <input type="number" class="form-control" id="buying_price" name="buying_price" min="0" value="0" step="0.0001">
                            </div>
                            <div class="form-group">
                              <label>Selling Price</label>
                              <input type="number" class="form-control" id="selling_price" name="selling_price" min="0" value="0" step="0.0001">
                            </div>
                            <div class="form-group">
                              <label>Default Quantity <span class="text-danger">*</span></label>
                              <input type="number" class="form-control" id="default_quantity" name="default_quantity" min="0" value="0" step="0.0001">
                            </div>
                            <div class="form-group">
                              <label>Ratio</label>
                              <input type="number" class="form-control" id="ratio" name="ratio" min="0" value="0" step="0.0001">
                            </div>
                            <div class="form-group">
                              <label>Unit <span class="text-danger">*</span></label>
                              <select class="js-select2 form-control" id="default_unit" name="default_unit" data-placeholder="Select Unit" required>
                                <option></option>
                                @foreach($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->abbreviation }} / {{ $unit->name }}</option>
                                @endforeach
                              </select>
                              &ensp;
                              <label>Default Warehouse <span class="text-danger">*</span></label>
                              <select class="js-select2 form-control" id="default_warehouse" name="default_warehouse" data-placeholder="Select Warehouse" required>
                                <option></option>
                                @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                              </select>
                            </div>
                            <div class="f1-buttons">
                                <button type="button" class="btn btn-warning btn-previous"><i class="fa fa-arrow-left"></i> Previous</button>
                                <button type="button" class="btn btn-primary btn-next">Next <i class="fa fa-arrow-right"></i></button>
                            </div>
                        </fieldset>
                        <!-- step 3 -->
                        <fieldset>
                            <h4>Product Brand</h4>
                            <div class="form-group">
                              <label>Brand Reference <span class="text-danger">*</span></label>
                              <select class="form-control" id="brand_reference" name="brand_reference" data-placeholder="Select Brand">
                                <option></option>
                                @foreach($brand_references as $brand_reference)
                                <option value="{{ $brand_reference->id }}">{{ $brand_reference->name }}</option>
                                @endforeach
                              </select>
                            </div>
                            <div class="form-group">
                              <label>Sub Brand Reference <span class="text-danger">*</span></label>
                              <select class="form-control" id="sub_brand_reference" name="sub_brand_reference" data-placeholder="Select Brand">
                                <option></option>
                                @foreach($sub_brand_references as $sub_brand_reference)
                                <option value="{{ $sub_brand_reference->id }}">{{ $sub_brand_reference->name }}</option>
                                @endforeach
                              </select>
                            </div>
                            <div class="form-group">
                              <label>Category <span class="text-danger">*</span></label>
                              <select class="form-control" id="category" name="category" data-placeholder="Select Category">
                                <option></option>
                                @foreach($product_categories as $category)
                                <option value="{{ $category->id }}" data-types-id="{{ $category->types->pluck('id') }}">{{ $category->name }}</option>
                                @endforeach
                              </select>
                            </div>
                            <div class="form-group">
                              <label for="type">Type <span class="text-danger">*</span></label>
                              <select class="form-control" id="type" name="type" data-placeholder="Select Type">
                                <option></option>
                                @foreach($product_types as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                              </select>
                            </div>
                            <div class="f1-buttons">
                                <button type="button" class="btn btn-warning btn-previous"><i class="fa fa-arrow-left"></i> Previous</button>
                                <button type="button" class="btn btn-primary btn-next">Next <i class="fa fa-arrow-right"></i></button>
                            </div>
                        </fieldset>
                        <!-- step 4 -->
                        <fieldset>
                            <h4>Product Image</h4>
                            <div class="form-group">
                                <label>Image NPWP
                                  <i class="mdi mdi-comment-question-outline" data-toggle="popover" data-placement="left" title="Image" data-content="Standard / small image quality. (For Apps, etc)"></i>
                                </label>
                                <input type="file" id="image" name="image" data-max-file-size="2000" accept="image/png, image/jpeg">
                            </div>
                            <div class="form-group">
                                <label>Image HD
                                  <i class="mdi mdi-comment-question-outline" data-toggle="popover" data-placement="left" title="Image HD" data-content="Better image quality. (For Printing, etc)"></i>
                                </label>
                                <input type="file" id="image_hd" name="image_hd" data-max-file-size="2000" accept="image/png, image/jpeg">
                            </div>
                            <div class="f1-buttons">
                                <button type="button" class="btn btn-warning btn-previous"><i class="fa fa-arrow-left"></i> Previous</button>
                                <button type="submit" class="btn btn-primary btn-submit"><i class="fa fa-save"></i> Submit</button>
                            </div>
                        </fieldset>
                	</form>
                </div>
            </div>
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
      fileActionSettings: {
        showDrag: false,
        showRemove: false
      },
    });

    $('#image_hd').fileinput({
      theme: 'explorer-fa',
      browseOnZoneClick: true,
      showCancel: false,
      showClose: false,
      showUpload: false,
      browseLabel: '',
      removeLabel: '',
      fileActionSettings: {
        showDrag: false,
        showRemove: false
      },
    });

    $('.js-select2').select2()
    
    // $('select#category').on('select2:select', function () {
    //   let types_id = $(this).find(':selected').data('types-id') || false;
    //   let data = ajaxGet("{{ route('superuser.repo.master.product_type') }}" + '?id=' + encodeURIComponent(JSON.stringify(types_id)))
    //   let select_type = $('select#type')

    //   addLoadSpiner(select_type)
      
    //   select2_clear(select_type)

    //   for (i = 0; i < data.length; ++i) {
    //     let newOption = new Option(data[i].name, data[i].id, false, false);
    //     select_type.append(newOption).trigger('change');
    //   }

    //   hideLoadSpinner(select_type)
    // })
  })
</script>
@endpush
