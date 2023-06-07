@extends('superuser.app')

@section('content')

  <div class="block">
            <div class="block-conten" align="center">
                <div class="col-md-10 col-md-offset-1">
                	<form data-action="{{ route('superuser.master.product.update', [$product->id]) }}" data-type="POST" enctype="multipart/form-data" class="f1 ajax">
                  <input type="hidden" name="_method" value="PUT">
                		<div class="f1-steps">
                			<div class="f1-progress">
                			    <div class="f1-progress-line" data-now-value="25" data-number-of-steps="4"></div>
                			</div>
                      <div class="f1-step active">
                        <div class="f1-step-icon"><i class="mdi mdi-account-card-details"></i></div>
                        <p>Details</p>
                			</div>
                      <div class="f1-step">
                        <div class="f1-step-icon"><i class="mdi mdi-source-branch"></i></div>
                				<p>Brand</p>
                      </div>
                			<div class="f1-step">
                				<div class="f1-step-icon"><i class="mdi mdi-home"></i></div>
                				<p>Warehouse</p>
                			</div>
                      <div class="f1-step">
                				<div class="f1-step-icon"><i class="mdi mdi-cash-usd"></i></div>
                				<p>Cost</p>
                			</div>
                		</div>
                		
                    <!-- Detail Product -->
                    <fieldset>
                            <h4>#Product Detail's</h4>
                            <div class="container">
                                <div class="row">
                                  <div class="col">
                                    <div class="form-group row">
                                      <label for="inputPassword" class="col-sm-2 col-form-label">Code <span class="text-danger">*</span></label>
                                      <div class="col-sm-10">
                                        <input type="text" id="code" name="code" placeholder="Product Code" class="form-control" value="{{$product->code}}">
                                      </div>
                                    </div>
                                  </div>
                                  <div class="col">
                                    <div class="form-group row">
                                      <label for="inputPassword" class="col-sm-2 col-form-label">Name <span class="text-danger">*</span></label>
                                      <div class="col-sm-10">
                                        <input type="text" id="name" name="name" placeholder="Product Name" class="form-control" value="{{$product->name}}">
                                      </div>
                                    </div>  
                                  </div>
                                </div>
                              <div class="row">
                                  <div class="col">
                                    <div class="form-group row">
                                      <label for="material_code" class="col-sm-2 col-form-label">Material Code <span class="text-danger">*</span></label>
                                      <div class="col-sm-10">
                                        <input type="text" id="material_code" name="material_code" placeholder="Material Code" class="form-control" value="{{$product->material_code}}">
                                      </div>  
                                    </div>
                                  </div>
                                    <div class="col">
                                      <div class="form-group row">
                                        <label for="material_name" class="col-sm-2 col-form-label">Material Name <span class="text-danger">*</span></label>
                                        <div class="col-sm-10">
                                          <input type="text" id="material_name" name="material_name" placeholder="Material Name" class="form-control" value="{{$product->material_name}}">
                                        </div>
                                    </div>
                                  </div>
                              </div>
                              <div class="row">
                                <div class="col">
                                    <div class="form-group row">
                                      <label for="alias" class="col-sm-2 col-form-label">Alias <span class="text-danger">*</span></label>
                                      <div class="col-sm-10">
                                        <input type="text" id="alias" name="alias" placeholder="Alias Name" class="form-control" value="{{$product->alias}}">
                                      </div>
                                    </div>
                                  </div>
                                  <div class="col">
                                    <div class="form-group row">
                                      <label for="ratio" class="col-sm-2 col-form-label">Ratio <span class="text-danger">*</span></label>
                                      <div class="col-sm-10">
                                        <input type="number" class="form-control" id="ratio" name="ratio" min="0" value="0" step="0.0001" value="{{$product->ratio}}">
                                      </div>
                                    </div>
                                  </div>
                              </div>

                              <hr>

                                <div class="row">
                                  <div class="col">
                                    <div class="form-group row">
                                      <label for="factory" class="col-sm-2 col-form-label">Factory <span class="text-danger">*</span></label>
                                      <div class="col-sm-10">
                                        <select class="js-select2 form-control" id="factory" name="factory" style="width:100%;" placeholder="Pilih Pabrik" required>
                                          <option>Pilih Pabrik</option>
                                          @foreach($vendor as $vendor)
                                          <option value="{{ $vendor->id }}" {{ ($vendor->id == $product->vendor_id ) ? 'selected' : '' }}>{{ $vendor->name }}</option>
                                          @endforeach
                                        </select>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="col">
                                    <div class="form-group row">
                                      <label for="selling_price" class="col-sm-2 col-form-label">Price List <span class="text-danger">*</span></label>
                                      <div class="col-sm-6">
                                        <input type="number" class="form-control" id="selling_price" name="selling_price" min="0" value="0" value="{{$product->selling_price}}">
                                        <span class="text-danger">*Harga dalam USD</span>
                                      </div>
                                    </div>  
                                  </div>
                                </div>
                                <div class="row">
                                  <div class="col">
                                    <div class="form-group row">
                                      <label for="inputPassword" class="col-sm-2 col-form-label">Catatan</label>
                                      <div class="col-sm-10">
                                        <select class="js-select2 form-control" id="note" name="note" style="width:100%;" placeholder="Pilih Note">
                                          <option>Pilih Note</option>
                                          @foreach($product_notes as $note)
                                          <option value="{{ $note }}" {{ ($note == $product->note ) ? 'selected' : '' }}>{{ $note }}</option>
                                          @endforeach
                                        </select>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="col">
                                    <div class="form-group row">
                                      <label for="gender" class="col-sm-2 col-form-label">Gender</label>
                                      <div class="col-sm-10">
                                        <select class="js-select2 form-control" id="gender" name="gender" style="width:100%;" placeholder="Pilih Gender">
                                          <option>Pilih Gender</option>
                                          @foreach($gender as $gender)
                                          <option value="{{ $gender }}" {{ ($gender == $product->gender ) ? 'selected' : '' }}>{{ $gender }}</option>
                                          @endforeach
                                        </select>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                <div class="row">
                                  <div class="col">
                                    <div class="form-group row">
                                      <div class="col-sm-12">
                                        <textarea class="form-control" name="description" placeholder="Keterangan" rows="1" value="{{$product->description}}"></textarea>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <div class="f1-buttons">
                                  <button type="button" class="btn btn-primary btn-next">Next <i class="fa fa-arrow-right"></i></button>
                              </div>
                        </fieldset>
                        
                        <!-- Detail Brand -->
                        <fieldset>
                          <h4>#Product Brand's</h4>
                            <div class="container">
                              <div class="row">
                                <div class="col">
                                  <div class="form-group row">
                                      <label for="brand_name" class="col-sm-2 col-form-label">Brand <span class="text-danger">*</span></label>
                                      <div class="col-sm-10">
                                        <select class="js-select2 form-control" id="brand_name"  name="brand_name" style="width:100%;" data-placeholder="Pilih Brand">
                                          <option value=""></option>
                                          @foreach($brand_ppi as $brand_ppi)
                                          <option value="{{ $brand_ppi->brand_name }}" {{ ($brand_ppi->brand_name == $product->brand_name ) ? 'selected' : '' }}>{{$brand_ppi->brand_name}}</option>
                                          @endforeach
                                      </select>
                                    </div>
                                  </div>
                                  </div>
                                  <div class="col">
                                    <div class="form-group row">
                                      <label for="category" class="col-sm-2 col-form-label">Category<span class="text-danger">*</span></label>
                                      <div class="col-sm-10">
                                        <select class="js-select2 form-control" id="category"  name="category" style="width:100%;" data-placeholder="Pilih Kategori">
                                            <option value="">==Select Category==</option>
                                            @foreach($product_categories as $cat)
                                            <option value="{{ $cat->id }}" {{ ($cat->id == $product->category_id ) ? 'selected' : '' }}>{{ $cat->name }} - {{ $cat->packaging->pack_name }}</option>
                                            @endforeach
                                        </select>
                                      </div>
                                    </div>
                                  </div>
                              </div>
                              <div class="row">
                                <div class="col">
                                  <div class="form-group row">
                                    <label for="searah" class="col-sm-1 col-form-label">Searah<span class="text-danger">*</span></label>
                                    <div class="col-sm-10">
                                      <select class="js-select2 form-control" id="searah"  name="searah" style="width:100%;" data-placeholder="Pilih Searah">
                                        <option value="">Pilih Searah</option>
                                        @foreach($sub_brand_references as $searah)
                                        <option value="{{ $searah->id }}" {{ ($searah->id == $product->sub_brand_reference_id ) ? 'selected' : '' }}>{{$searah->brand_reference->name}} - {{ $searah->name }}</option>
                                        @endforeach
                                      </select>
                                    </div>
                                  </div>   
                                </div>
                              </div> 
                              <div class="row">
                                <div class="col">
                                  <div class="form-group">
                                        <label>Image
                                          <i class="mdi mdi-comment-question-outline" data-toggle="popover" data-placement="left" title="Image" data-content="Standard / small image quality. (For Apps, etc)"></i>
                                        </label>
                                        <input type="file" id="image" name="image" data-max-file-size="2000" accept="image/png, image/jpeg">
                                    </div>
                                  </div>
                                <div class="col">
                                  <div class="form-group">
                                      <label>Image HD
                                        <i class="mdi mdi-comment-question-outline" data-toggle="popover" data-placement="left" title="Image HD" data-content="Better image quality. (For Printing, etc)"></i>
                                      </label>
                                      <input type="file" id="image_hd" name="image_hd" data-max-file-size="2000" accept="image/png, image/jpeg">
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="f1-buttons">
                              <button type="button" class="btn btn-warning btn-previous"><i class="fa fa-arrow-left"></i> Previous</button>
                              <button type="submit" class="btn btn-primary btn-submit"><i class="fa fa-save"></i> Submit</button>
                            </div>
                        </fieldset>
                        <!-- step 2 -->
                        <fieldset>
                            <h4>#Warehouse</h4>
                            <div class="row">
                              {{--<div class="form-group">
                                <label>Factory <span class="text-danger">*</span></label>
                                <select class="js-select2 form-control" id="factory" name="factory" style="width:100%;" placeholder="Pilih Pabrik" required>
                                  <option>Pilih Pabrik</option>
                                  @foreach($factory as $factory)
                                  <option value="{{ $factory->id }}">{{ $factory->name }}</option>
                                  @endforeach
                                </select>
                              </div>--}}
                              <div class="form-group">
                                <label>Default Warehouse <span class="text-danger">*</span></label>
                                <select class="js-select2 form-control" id="default_warehouse" name="default_warehouse" style="width:100%;" placeholder="Pilih Gudang">
                                  <option>Pilih Gudang</option>
                                  @foreach($warehouses as $warehouse)
                                  <option value="{{ $warehouse->id }}" {{ ($warehouse->id == $product->default_warehouse_id ) ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                  @endforeach
                                </select>
                              </div>
                            </div>
                            <div class="row">
                              <div class="col">
                                <div class="form-group">
                                  <label>Default Unit <span class="text-danger">*</span></label>
                                  <select class="js-select2 form-control" id="default_unit" name="default_unit" style="width:100%;" placeholder="Pilih Unit">
                                    <option>Pilih Unit Satuan</option>
                                    @foreach($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->abbreviation }} / {{ $unit->name }}</option>
                                    @endforeach
                                  </select>
                                </div>
                              </div>
                              <div class="col">
                                <div class="form-group row col-3">
                                  <label>Default Quantity <span class="text-danger">*</span></label>
                                  <input type="number" class="form-control" id="default_quantity" name="default_quantity" min="0" value="0" step="0.0001">
                                </div>
                              </div>
                            </div>
                            <div class="f1-buttons">
                                <button type="button" class="btn btn-warning btn-previous"><i class="fa fa-arrow-left"></i> Previous</button>
                                <button type="button" class="btn btn-primary btn-next">Next <i class="fa fa-arrow-right"></i></button>
                            </div>
                        </fieldset>
                        <fieldset>
                            <h4>#Product Cost</h4>
                            <div class="container">
                              {{--<div class="row">
                                <div class="col">
                                  <div class="form-group">
                                    <label for="buying_price">Harga Beli </label>
                                    <input type="number" class="form-control" id="buying_price" name="buying_price" min="0" value="0" step="0.0001">
                                  </div>
                                </div>
                                <div class="col">
                                  <div class="form-group">
                                    <label for="buying_price">Harga Jual </label>
                                    <input type="number" class="form-control" id="selling_price" name="selling_price" min="0" value="0" step="0.0001">
                                  </div>
                                </div>
                              </div>--}}
                            </div>
                            <span class="text-danger">*Harga dalam kurs USD</span>
                            <div class="f1-buttons">
                              <button type="button" class="btn btn-warning btn-previous"><i class="fa fa-arrow-left"></i> Previous</button>
                              
                            </div>
                        </fieldset>
                	</form>
                </div>
            </div>
        </div>
<div id="alert-block"></div>
@endsection

@include('superuser.asset.plugin.fileinput')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
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
  })
</script>

<script type="text/javascript">
  $(document).ready(function() {
    var table = $('#datatable').DataTable({
        paging: false,
        bInfo : false,
        searching: false,
        columns: [
          {name: 'counter', "visible": false},
          {name: 'parfume_scent', orderable: false, width: "25%"},
          {name: 'scent_range', orderable: false, searcable: false},
          {name: 'color_scent', orderable: false, searcable: false},
          {name: 'action', orderable: false, searcable: false, width: "5%"}
        ],
        'order' : [[0,'desc']]
    })

    var counter = 1;

    $('a.row-add').on( 'click', function (e) {
      e.preventDefault();
      
      table.row.add([
                    counter,
                    '<input class="form-control" id="parfume_scent['+counter+']" name="parfume_scent[]" data-placeholder="" style="width:100%" required>',
                    
                    '<input type="range" class="form-control-range" min="1" max="100" value="50" name="scent_range[]">',
                    '<input type="color" class="form-control" name="color_scent[]">',
                    '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
                  ]).draw( false );
      counter++;
    });

    $('#datatable tbody').on( 'click', '.row-delete', function (e) {
      e.preventDefault();
      
      table.row( $(this).parents('tr') ).remove().draw();

    })
  });
</script>
@endpush
