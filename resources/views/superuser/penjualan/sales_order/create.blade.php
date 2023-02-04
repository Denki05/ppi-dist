@extends('superuser.app')

@section('content')

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Create Sales Order Awal</h3>
  </div>
            <div class="block-conten" align="center">
                <div class="col-md-10 col-md-offset-1">
                	<form id="frmCreate" data-type="POST" enctype="multipart/form-data" class="f1 ajax">
                   @csrf
                    <input type="hidden" name="ajukankelanjutan" value="0">
                		<div class="f1-steps2">
                			<div class="f1-progress2">
                			    <div class="f1-progress-line2" data-now-value="50" data-number-of-steps="2"></div>
                			</div>
                      <div class="f1-step active">
                          <div class="f1-step-icon"><i class="fa fa-store"></i></div>
                        <p>Order Detail</p>
                      </div>
                			<div class="f1-step">
                				<div class="f1-step-icon"><i class="fa fa-list"></i></div>
                				<p>Product Order</p>
                			</div>
                		</div>
                   
                    
                		<!-- step 1 -->
                		<fieldset>
                        <br>
                          <h4 align="left">Data Order</h4>
                          <div class="container">
                            <div class="row">
                                @if($step == 1 || $step == 2 || $step == 9)
                                <div class="form-group row">
                                  <label class="col-md-2 col-form-label text-right" for="name">Sales Senior<span class="text-danger">*</span></label>
                                  <div class="col-md-8">
                                    <select class="form-control js-select2" name="sales_senior_id" <?php echo $step == 2 ? 'disabled' : '' ?>>
                                      <option value="">Pilih Sales Senior</option>
                                      @foreach($sales as $index => $row)
                                        <option value="{{$row->id}}">{{$row->name}}</option>
                                      @endforeach
                                    </select>
                                  </div>
                                </div>
                                @endif
                            @if($step == 1 || $step == 2 || $step == 9)
                            <div class="form-group row">
                              <label class="col-md-2 col-form-label text-right" for="name">Sales <span class="text-danger">*</span></label>
                              <div class="col-md-8">
                                <select class="form-control js-select2" name="sales_id" <?php echo $step == 2 ? 'disabled' : '' ?>>
                                  <option value="">Pilih Sales</option>
                                  @foreach($sales as $index => $row)
                                    <option value="{{$row->id}}">{{$row->name}}</option>
                                  @endforeach
                                </select>
                              </div>
                            </div>
                            @endif
                            @if($step == 9)
                            <div class="form-group row">
                              <label class="col-md-2 col-form-label text-right">Origin warehouse<span class="text-danger">*</span></label>
                              <div class="col-md-8">
                                <select class="form-control js-select2" name="origin_warehouse_id">
                                  <option value="">==Select origin warehouse==</option>
                                  @foreach($warehouse as $index => $row)
                                    <option value="{{$row->id}}">{{$row->name}}</option>
                                  @endforeach
                                </select>
                              </div>
                            </div>
                            @endif
                            @if($step == 9)
                            <div class="form-group row">
                              <label class="col-md-2 col-form-label text-right">Destination warehouse</label>
                              <div class="col-md-1 d-none">
                                <input type="checkbox" name="checkbox_destination_warehouse" value="1" class="form-control select-checkbox mx-auto" style="width: 20px;">
                              </div>
                              <div class="col-md-8">
                                <select class="form-control js-select2 select-warehouse" name="destination_warehouse_id" disabled>
                                  <option value="">==Select destination warehouse==</option>
                                  @foreach($warehouse as $index => $row)
                                    <option value="{{$row->id}}">{{$row->name}}</option>
                                  @endforeach
                                </select>
                              </div>
                            </div>
                            @endif
                            @if($step == 1)
                            <div class="form-group row">
                              <label class="col-md-2 col-form-label text-right">Transaction<span class="text-danger">*</span></label>
                              <div class="col-md-8">
                                <select class="form-control js-select2" name="type_transaction">
                                  <option value="">Pilih Transaksi Type</option>
                                  <option value="1">Cash</option>
                                  <option value="2">Tempo</option>
                                  <option value="3">Marketplace</option>
                                </select>
                              </div>
                            </div>
                            @endif
                            @if($step == 1)
                              <input type="hidden" class="form-control" name="brand_type" value="{{ $brand }}">
                            @endif
                            @if($step == 2)
                            <div class="form-group row">
                              <label class="col-md-2 col-form-label text-right">Ekspedisi</label>
                              <div class="col-md-8">
                                <select class="form-control js-select2" name="ekspedisi_id">
                                  <option value="">==Select ekspedisi==</option>
                                  @foreach($ekspedisi as $index => $row)
                                  <option value="{{$row->id}}">{{$row->name}}</option>
                                  @endforeach
                                </select>
                              </div>
                            </div>
                            @endif
                            @if($step == 1 || $step == 2)
                            <div class="form-group row">
                              <label class="col-md-2 col-form-label text-right">Note</label>
                              <div class="col-md-8">
                                <textarea class="form-control" name="note" rows="3"></textarea>
                              </div>
                            </div>
                            @endif
                          </div>
                            <div class="f1-buttons">
                              <a href="{{route('superuser.penjualan.sales_order.index_' . strtolower($step_txt))}}" class="btn btn-warning  btn-md text-white"><i class="fa fa-arrow-left"></i> Back</a>
                              <button type="button" class="btn btn-primary btn-next">Next <i class="fa fa-arrow-right"></i></button>
                            </div>
                        </fieldset>
                        <!-- step 2 -->
                        <fieldset>
                            <h4>Product List</h4>
                              <div class="card">
                                  <div class="card-header">
                                      Select Product
                                  </div>
                                  <div class="card-body">
                                    <div class="row">
                                      <div class="col-12 product-list">
                                        <div class="row">
                                          <div class="col-2">Brand</div>
                                          <div class="col-2">Category</div>
                                          <div class="col-3">Product</div>
                                          <div class="col-1">Qty</div>
                                          <div class="col-2">Packaging</div>
                                        </div>

                                        <div class="row mt-10 product-row">
                                          <div class="col-2">
                                            <select class="form-control js-select2 select-brand" data-index="0">
                                              <option value="">Pilih Brand</option>
                                              @foreach($brand_ppi as $index => $row)
                                                <option value="{{$row->id}}">{{$row->brand_name}}</option>
                                              @endforeach
                                            </select>
                                          </div>
                                          <div class="col-2">
                                            <select class="form-control js-select2 select-category" data-index="0">
                                              <option value="">Pilih Category</option>
                                            </select>
                                          </div>
                                          <div class="col-3">
                                            <select class="form-control js-select2 select-product" name="product_id[]" data-index="0">
                                              <option value="">Pilih Product</option>
                                            </select>
                                          </div>
                                          <div class="col-1">
                                            <input type="number" name="qty[]" class="form-control input-qty" data-index="0" step="any">
                                          </div>
                                          <div class="col-2">
                                            <select name="packaging[]" class="form-control js-select2 select-packaging" data-index="0">
                                              <option value="">Pilih Kemasan</option>
                                              <option value="1">100gr (0.1)</option>
                                              <option value="2">500gr (0.5)</option>
                                              <option value="3">Jerigen 5kg (5)</option>
                                              <option value="4">Alumunium 5kg (5)</option>
                                              <option value="5">Jerigen 25kg (25)</option>
                                              <option value="6">Drum 25kg (25)</option>
                                              <option value="7">Free</option>
                                            </select>
                                          </div>
                                          <div class="col-1"><button type="button" id="buttonAddProduct" class="btn btn-primary"><em class="fa fa-plus"></em></button></div>
                                        </div>
                                        <hr />
                                      </div>
                                    </div>
                                  </div>
                                </div>
                                <div class="f1-buttons">
                                  <button type="button" class="btn btn-warning btn-previous"><i class="fa fa-arrow-left"></i> Previous</button>
                                  <button class="btn btn-primary btn-md btn-simpan" type="button"><i class="fa fa-save"></i> Simpan</button>
                                  <button class="btn btn-primary btn-md btn-simpan-dan-ajukan-ke-lanjutan" type="button"><i class="fa fa-save"></i> Simpan dan ajukan ke Lanjutan</button>
                                </div>
                        </fieldset>
                	</form>
                </div>
            </div>
        </div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script>
  var productCount = 1;

  $(function(){
    $('button[type="submit"]').removeAttr('disabled');

    $('.js-select2').select2();

    $(document).on('click','.select-checkbox',function(){
      if($(this).is(':checked')){
        $('select[name="destination_warehouse_id"]').attr('disabled',false);

        $('select[name="customer_id"]').val(null).trigger('change')
        $('select[name="customer_id"]').attr('disabled',true);
        $('textarea[name="address"]').val("");
      }
      else{
       $('select[name="customer_id"]').attr('disabled',false);

       $('select[name="destination_warehouse_id"]').val(null).trigger('change')
       $('select[name="destination_warehouse_id"]').attr('disabled',true);
       $('textarea[name="address"]').val("");

      }
    })

    if (1 == {{ $step == 9 ? 1 : 2}}) {
      $('.select-checkbox').click();
    }

    $(document).on('change','.select-customer',function(){
      let val = $(this).val();
      if(val != ""){
        customer_address(val);
      }else{
        $('textarea[name="address"]').val("");
      }
    })

    $(document).on('change','.select-warehouse',function(){
      let val = $(this).val();
      if(val != ""){
        warehouse_address(val);
      }else{
        $('textarea[name="address"]').val("");
      }
    })

    $(document).on('click','#buttonAddProduct',function(){
      const brandId = $('.select-brand[data-index=0]').val();
      const brandText = $('.select-brand[data-index=0] option:selected').text();
      const categoryId = $('.select-category[data-index=0]').val();
      const categoryText = $('.select-category[data-index=0] option:selected').text();
      const productId = $('.select-product[data-index=0]').val();
      const productText = $('.select-product[data-index=0] option:selected').text();
      const qty = $('.input-qty[data-index=0]').val();
      const packagingId = $('.select-packaging[data-index=0]').val();
      const packagingText = $('.select-packaging[data-index=0] option:selected').text();
      if (brandId === null || brandId === '' || categoryId === null || categoryId === '' || productId === null || productId === '' || qty === null || qty === '' || packagingId == null || packagingId === '') {
        Swal.fire(
          'Error!',
          'Please input all the data',
          'error'
        );
        return;
      }

      let html = "<div class='row mt-10 product-row brand-" + brandId + "'>";
      html += "  <div class='col-2'>";
      html += "    <input type='hidden' class='form-control' value='" + brandId + "'>";
      html += brandText;
      html += "  </div>";
      html += "  <div class='col-2'>";
      html += "    <input type='hidden' class='form-control' value='" + categoryId + "'>";
      html += categoryText;
      html += "  </div>";
      html += "  <div class='col-2'>";
      html += "    <input type='hidden' name='product_id[]' class='form-control' value='" + productId + "'>";
      html += productText;
      html += "  </div>";
      html += "  <div class='col-2 text-right'>";
      html += "    <input type='hidden' name='qty[]' class='form-control' value='" + qty + "'>";
      html += qty;
      html += "  </div>";
      html += "  <div class='col-2'>";
      html += "    <input type='hidden' name='packaging[]' class='form-control' value='" + packagingId + "'>";
      html += packagingText;
      html += "  </div>";
      html += "  <div class='col-1'>";
      html += "    <button type='button' id='buttonDeleteProduct' class='btn btn-danger'><em class='fa fa-minus'></em></button>";
      html += "  </div>";
      html += "</div>";
      
      if ($('.product-row.brand-' + brandId).length > 0) {
        $('body').find('.product-row.brand-' + brandId + ':last').after(html);
      } else {
        $('body').find('.product-list').append(html);
      }

      //let option = '<option value="">==Select product==</option>';
      //$('.select-product[data-index=0]').html(option);

      $('.select-brand[data-index=0]').val('').change();
      $('.select-category[data-index=0]').val('').change();
      $('.select-product[data-index=0]').val('').change();
      $('.input-qty[data-index=0]').val('');
      $('.select-packaging[data-index=0]').val('').change();

      $('.select-brand[data-index=0]').select2('focus');

      productCount++;
    })

    $(document).on('click','#buttonDeleteProduct',function(){
      $(this).parents(".product-row").remove();
    })

    $(document).on('click','.btn-simpan',function(){
      $('#frmCreate').find('input[name="ajukankelanjutan"]').val(0);
      $('#frmCreate').submit();
    })

    $(document).on('click','.btn-simpan-dan-ajukan-ke-lanjutan',function(){
      $('#frmCreate').find('input[name="ajukankelanjutan"]').val(1);
      $('#frmCreate').submit();
    })

    $(document).on('submit','#frmCreate',function(e){
      e.preventDefault();
      if(confirm("Apakah anda yakin ingin menambakan sales order ini ?")){
        let _form = $('#frmCreate');
        $.ajax({
          url : '{{route('superuser.penjualan.sales_order.store', [$customer->id, $member->id])}}',
          method : "POST",
          data : $('#frmCreate').serializeArray(),
          dataType : "JSON",
          beforeSend : function(){
            $('button[type="submit"]').html('Loading...');
          },
          success : function(resp){
            if(resp.IsError == true){
              showToast('danger',resp.Message);
            }
            else{
              Swal.fire(
                'Success!',
                resp.Message,
                'success'
              ).then((result) => {
                  document.location.href = '{{ route('superuser.penjualan.sales_order.index_' . strtolower($step_txt)) }}';
              })
              
            }
          },
          error : function(){
            alert("Cek Koneksi Internet")
          },
          complete : function(){
            $('button[type="submit"]').html('<i class="fa fa-save"> Save</i>');
          }
        })
      }
    })
  })

  function customer_address(id){
    ajaxcsrfscript();
    $.ajax({
      url : '{{route('superuser.penjualan.sales_order.ajax_customer_detail')}}',
      method : "POST",
      data : {id:id},
      dataType : "JSON",
      success : function(resp){
        if(resp.IsError == true){
          showToast('danger',resp.Message);
        }
        else{
          $('textarea[name="address"]').val(resp.Data.address);
        }
      },
      error : function(){
        alert('Cek Koneksi Internet');
      },
    })
  }
  function warehouse_address(id){
    ajaxcsrfscript();
    $.ajax({
      url : '{{route('superuser.penjualan.sales_order.ajax_warehouse_detail')}}',
      method : "POST",
      data : {id:id},
      dataType : "JSON",
      success : function(resp){
        if(resp.IsError == true){
          showToast('danger',resp.Message);
        }
        else{
          $('textarea[name="address"]').val(resp.Data.address);
        }
      },
      error : function(){
        alert('Cek Koneksi Internet');
      },
    })
  }

  var param = [];
  param["brand_lokal_id"] = "";

  loadCategory({});

  $(document).on('change','.select-brand',function(){
    if ($(this).val() === '') return;

    param["brand_lokal_id"] = $(this).val();
    loadCategory({
      brand_lokal_id:param["brand_lokal_id"],
      index: $(this).data("index")
    })
  })

  function loadCategory(param){
    $.ajax({
      url : '{{route('superuser.penjualan.sales_order.get_category')}}',
      method : "GET",
      data : param,
      dataType : "JSON",
      success : function(resp){
        let option = "";
        option = '<option value="">Pilih Category</option>';
        $.each(resp.Data,function(i,e){
          option += '<option value="'+e.id+'">'+e.name+' - '+e.type+'</option>';
        })
        //$(".select-product[data-index=0]").length
        $('.select-category[data-index=' + param.index + ']').html(option);
      },
      error : function(){
        alert("Cek Koneksi Internet");
      }
    })
  }

  var param = [];
  param["category_id"] = "";

  loadProduct({});

  $(document).on('change','.select-category',function(){
    if ($(this).val() === '') return;

    param["category_id"] = $(this).val();
    loadProduct({
      category_id:param["category_id"],
      index: $(this).data("index")
    })
  })

  function loadProduct(param){
    $.ajax({
      url : '{{route('superuser.penjualan.sales_order.get_product')}}',
      method : "GET",
      data : param,
      dataType : "JSON",
      success : function(resp){
        let option = "";
        option = '<option value="">Pilih Product</option>';
        $.each(resp.Data,function(i,e){
          option += '<option value="'+e.id+'">'+e.product_code+' - '+e.product_name+' - '+e.packaging+'</option>';
        })
        //$(".select-product[data-index=0]").length
        $('.select-product[data-index=' + param.index + ']').html(option);
      },
      error : function(){
        alert("Cek Koneksi Internet");
      }
    })
  }

  $(function () {
      $('.select-customer').on('change', function(){
          let customer_id = $('.select-customer').val();

          $.ajax({
            type : 'POST',
            url : '{{route('superuser.penjualan.sales_order.getmember')}}',
            data : {customer_id:customer_id},
            cache : false,

            success: function(msg){
              $('.other_address').html(msg);
            },
            error : function(data){
              console.log('error:',data)
            },
          })
        })
    });
</script>
@endpush




