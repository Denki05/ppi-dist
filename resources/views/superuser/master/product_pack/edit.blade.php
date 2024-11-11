@extends('superuser.app')

@section('content')
  <div class="block">
            <div class="block-conten" align="center">
                <div class="col-md-10 col-md-offset-1">
                <form class="f1 ajax" data-action="{{ route('superuser.master.product.product_pack.update', [$product->id, $product_pack->id]) }}" data-type="POST" enctype="multipart/form-data">
                  <input type="hidden" name="_method" value="PUT">
                		<div class="f1-steps">
                			<div class="f1-progress">
                			    <div class="f1-progress-line" data-now-value="50" data-number-of-steps="2"></div>
                			</div>
                      <div class="f1-step active">
                        <div class="f1-step-icon"><i class="mdi mdi-account-card-details"></i></div>
                        <p>Details</p>
                			</div>
                      <div class="f1-step">
                        <div class="f1-step-icon"><i class="mdi mdi-source-branch"></i></div>
                				<p>Brand</p>
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
                                        <input type="text" id="material_code" name="material_code" class="form-control" value="{{ $product->material_code }}" readonly>
                                      </div>  
                                    </div>
                                </div>
                                <div class="col">
                                  <div class="form-group row">
                                      <label for="material_name" class="col-sm-2 col-form-label">Nama Material <span class="text-danger">*</span></label>
                                    <div class="col-sm-10">
                                      <input type="text" id="material_name" name="material_name"  class="form-control" value="{{ $product->material_name }}" readonly>
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
                                    <input type="text" class="form-control" id="code" name="code" value="{{ $product->code }}" readonly>
                                  </div>
                                </div>
                                <div class="col">
                                  <div class="form-group">
                                    <label for="name">Nama Produk <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ $product->name }}" readonly>
                                  </div>
                                </div>
                              </div>
                            </div>

                            <hr>

                            <div class="container">
                              <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                      <label for="gender">Gender</label>
                                      <input type="text" class="form-control" name="gender" value="{{ $product->gender }}" readonly>
                                    </div>
                                </div>
                                <div class="col">
                                  <div class="form-group">
                                    <label for="selling_price">Price List <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="selling_price" name="selling_price" min="0" value="{{ $product_pack->price }}" step="0.0001" required>
                                  </div>
                                </div>
                              </div>
                            </div>

                            <div class="f1-buttons">
                                  <a href="javascript:history.back()" class="btn btn-danger">Back <i class="fa fa-arrow-left"></i></a>
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
                                      <input type="text" class="form-control" value="{{ $product->brand_name }}" readonly>
                                    </div>
                                  </div>
                                  </div>
                                  <div class="col">
                                    <div class="form-group row">
                                      <label for="searah" class="col-sm-2 col-form-label">Searah<span class="text-danger">*</span></label>
                                      <div class="col-sm-10">
                                        <input type="text" class="form-control" value="{{ $product->sub_brand_reference->name }}" readonly>
                                      </div>
                                    </div>   
                                </div>
                              </div>
                              <div class="row">
                                  <div class="col">
                                    <div class="form-group row">
                                      <label for="category" class="col-sm-2 col-form-label">Category<span class="text-danger">*</span></label>
                                      <div class="col-sm-10">
                                        <input type="text" class="form-control" value="{{ $product->category->name }}" readonly>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="col">
                                    <div class="form-group row">
                                      <label for="category" class="col-sm-2 col-form-label">Type<span class="text-danger">*</span></label>
                                      <div class="col-sm-10">
                                        <select class="form-control js-select2" id="type" name="type" style="width:100%;" data-placeholder="Pilih Kemasan">
                                          <option value="">Pilih Kemasan</option>  
                                          @foreach($type as $row)
                                          <option value="{{$row->id}}" {{ ($row->id == $product_pack->type_id ) ? 'selected' : '' }}>{{$row->name}}</option>
                                          @endforeach
                                        </select>
                                      </div>
                                    </div>
                                  </div>
                              </div> 
                              <div class="row">
                                <div class="form-group">
                                  <label for="packaging">Packaging</label>
                                  <select class="form-control js-select2" id="packaging" name="packaging" style="width:100%;" data-placeholder="Pilih Kemasan">
                                    <option value="">Pilih Kemasan</option>  
                                    @foreach($packaging as $pack)
                                    <option value="{{$pack->id}}" {{ ($pack->id == $product_pack->packaging_id ) ? 'selected' : '' }}>{{$pack->pack_name}}</option>
                                    @endforeach
                                  </select>
                                </div>
                              </div>
                            </div>
                            <div class="f1-buttons">
                              <button type="button" class="btn btn-warning btn-previous"><i class="fa fa-arrow-left"></i> Previous</button>
                              <button type="submit" class="btn btn-primary btn-submit"><i class="fa fa-save"></i> Submit</button>
                            </div>
                            <br>
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
    });

    $('#brand_name').on('change', function(){
      let brand_lokal_id = $('#brand_name').val();

      $.ajax({
        type : 'POST',
        url : '{{route('superuser.master.product.get_category')}}',
        data : {brand_lokal_id:brand_lokal_id},
        cache : false,

        success: function(msg){
          $('#category').html(msg);
        },
        error : function(data){
          console.log('error:',data)
        },
      })
    })
  })
</script>
@endpush
