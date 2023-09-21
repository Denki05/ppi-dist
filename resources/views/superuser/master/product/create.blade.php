@extends('superuser.app')

@section('content')
  <div class="block">
            <div class="block-conten" align="center">
                <div class="col-md-10 col-md-offset-1">
                	<form data-action="{{ route('superuser.master.product.store') }}" data-type="POST" enctype="multipart/form-data" class="f1 ajax">
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
                              <!-- Material Product -->
                              <div class="row">
                                <div class="col">
                                    <div class="form-group row">
                                      <label for="material_code" class="col-sm-2 col-form-label">Kode Material <span class="text-danger">*</span></label>
                                      <div class="col-sm-10">
                                        <input type="text" id="material_code" name="material_code"  class="form-control">
                                        <a href="#material_optional" class="link-primary"><i class="fa fa-plus-circle" aria-hidden="true"></i> addMore</a>
                                      </div>  
                                    </div>
                                </div>
                                <div class="col">
                                  <div class="form-group row">
                                      <label for="material_name" class="col-sm-2 col-form-label">Nama Material <span class="text-danger">*</span></label>
                                    <div class="col-sm-10">
                                      <input type="text" id="material_name" name="material_name"  class="form-control">
                                    </div>
                                  </div>
                                </div>
                                <div class="col">
                                  <div class="form-group row">
                                      <label for="factory" class="col-sm-2 col-form-label">Pabrik</span></label>
                                    <div class="col-sm-10">
                                      <select class="js-select2 form-control" id="factory" name="factory" style="width:100%;" required>
                                        <option>Pilih Pabrik</option>
                                        @foreach($factory as $factory)
                                        <option value="{{ $factory->id }}">{{ $factory->name }}</option>
                                        @endforeach
                                      </select>
                                    </div>
                                  </div>
                                </div>
                              </div>

                              <!-- Optional Material Product -->
                              <div class="row" id="material_optional" style="display: none;">
                                <div class="col">
                                    <div class="form-group row">
                                      <label for="material_code_optional" class="col-sm-2 col-form-label"></label>
                                      <div class="col-sm-10">
                                        <input type="text" id="material_code_optional" name="material_code_optional" placeholder="Kode Material Opsional" class="form-control">
                                      </div>  
                                    </div>
                                </div>
                                <div class="col">
                                  <div class="form-group row">
                                      <label for="material_name_optional" class="col-sm-2 col-form-label"></label>
                                    <div class="col-sm-10">
                                      <input type="text" id="material_name_optional" name="material_name_optional" placeholder="Nama Material Opsional" class="form-control">
                                    </div>
                                  </div>
                                </div>
                                <div class="col">
                                  <div class="form-group row">
                                    <label class="col-sm-2 col-form-label"></label>
                                    <div class="col-sm-10">
                                      <select class="js-select2 form-control" id="factory2" name="factory2" style="width:100%;" placeholder="Pabrik Opsional" required>
                                        <option>Pilih Pabrik Opsional</option>
                                        @foreach($factory_optional as $factory2)
                                        <option value="{{ $factory2->id }}">{{ $factory2->name }}</option>
                                        @endforeach
                                      </select>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                            
                            <div class="container">
                              <div class="row">
                                <div class="col">
                                  <div class="form-group">
                                    <label for="code">Kode Produk <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="code" name="code" required>
                                  </div>
                                </div>
                                <div class="col">
                                  <div class="form-group">
                                    <label for="name">Nama Produk <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                  </div>
                                </div>
                              </div>
                            </div>

                            <hr>

                            <div class="container">
                              <div class="row">
                                <div class="col">
                                  <div class="form-group">
                                    <label for="alias">Alias</label>
                                    <input type="text" class="form-control" id="alias" name="alias">
                                  </div>
                                </div>
                                <div class="col">
                                  <div class="form-group">
                                    <label for="ratio">Ratio</label>
                                    <input type="number" class="form-control" id="ratio" name="ratio" min="0" value="0" step="0.0001">
                                  </div>
                                </div>
                              </div>
                            </div>

                            <div class="container">
                              <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                      <label for="gender">Gender</label>
                                      <select class="form-control js-select2" id="gender" name="gender">
                                        <option value="">Pilih Gender</option>
                                        @foreach($gender as $gender)
                                        <option value="{{ $gender }}">{{ $gender }}</option>
                                        @endforeach
                                      </select>
                                    </div>
                                </div>
                                <div class="col">
                                  <div class="form-group">
                                    <label for="selling_price">Price List <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="selling_price" name="selling_price" min="0" value="0" step="0.0001" required>
                                  </div>
                                </div>
                              </div>
                            </div>

                            <div class="container">
                              <div class="row">
                                <div class="col">
                                  <div class="form-group">
                                    <label for="note">Note</label>
                                    <select class="js-select2 form-control" id="note" name="note" style="width:100%;" placeholder="Pilih Note">
                                      <option>Pilih Note</option>
                                      @foreach($product_notes as $note)
                                      <option value="{{ $note }}">{{ $note }}</option>
                                      @endforeach
                                    </select>
                                  </div>
                                </div>
                                  <div class="col">
                                    <div class="form-group">
                                      <label for="description">Keterangan</label>
                                        <textarea class="form-control" name="description" placeholder="Keterangan" rows="1"></textarea>
                                    </div>
                                  </div>
                                </div>
                            </div>

                            <div class="f1-buttons">
                                  <a href="javascript:history.back()" class="btn btn-danger">Go Back <i class="fa fa-arrow-left"></i></a>
                                  <button type="button" class="btn btn-primary btn-next">Next <i class="fa fa-arrow-right"></i></button>
                              </div>
                              <br>
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
                                          <option value="{{ $brand_ppi->brand_name }}">{{$brand_ppi->brand_name}}</option>
                                          @endforeach
                                      </select>
                                    </div>
                                  </div>
                                  </div>
                                  <div class="col">
                                    <div class="form-group row">
                                      <label for="searah" class="col-sm-2 col-form-label">Searah<span class="text-danger">*</span></label>
                                      <div class="col-sm-10">
                                        <select class="js-select2 form-control" id="searah"  name="searah" style="width:100%;" data-placeholder="Pilih Searah">
                                          <option value="">Pilih Searah</option>
                                          @foreach($sub_brand_references as $searah)
                                          <option value="{{ $searah->id }}">{{$searah->brand_reference->name}} - {{ $searah->name }}</option>
                                          @endforeach
                                        </select>
                                      </div>
                                    </div>   
                                </div>
                              </div>
                              <div class="row">
                                  <div class="col">
                                    <div class="form-group row">
                                      <label for="category" class="col-sm-2 col-form-label">Category<span class="text-danger">*</span></label>
                                      <div class="col-sm-10">
                                        <select class="js-select2 form-control" id="category"  name="category" style="width:100%;" data-placeholder="Pilih Kategori">
                                            <option value="">==Select Category==</option>
                                            @foreach($category as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="col">
                                    <div class="form-group row">
                                      <label for="category" class="col-sm-2 col-form-label">Type<span class="text-danger">*</span></label>
                                      <div class="col-sm-10">
                                        <select class="js-select2 form-control" id="type"  name="type" style="width:100%;" data-placeholder="Pilih Kategori">
                                            <option value="">==Select Type==</option>
                                            @foreach($type as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                      </div>
                                    </div>
                                  </div>
                              </div> 
                              <div class="row">
                                <div class="form-group">
                                  <label for="packaging">Packaging</label>
                                  <select class="form-control js-select2" id="packaging[]" name="packaging[]" style="width:100%;" data-placeholder="Pilih Kemasan" multiple="multiple">
                                    <option value="">Pilih Kemasan</option>  
                                    @foreach($pack as $pack)
                                    <option value="{{$pack->id}}">{{$pack->pack_name}}</option>
                                    @endforeach
                                  </select>
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
                            <br>
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
                                  <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
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

    $('a[href^="#"]').on('click', function(event) {

    var target = $( $(this).attr('href') );
    target.fadeToggle(100);
    /*if( target.length ) {
        event.preventDefault();
        $('html, body').animate({
            scrollTop: target.offset().top
        }, 2000);
    }*/

    });
  })
</script>
@endpush
