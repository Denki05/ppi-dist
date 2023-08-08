@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.sales_order.index_' . strtolower($step_txt)) }}">Sales Order {{ $step_txt }}</a>
  <span class="breadcrumb-item active">Create Sales Order</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-content">
    <form id="frmCreate" action="#" data-type="POST" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="ajukankelanjutan" value="0">
      <div class="block">
          <div class="block-header block-header-default">
            <h3 class="block-title">#SO Detail</h3>
          </div>
          <div class="block-content">
            <div class="row">
              <div class="col-6">
                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-6">
                        @if($step == 1)
                          <div class="form-group row">
                            <label class="col-md-4 col-form-label text-right">Sales Senior<span class="text-danger">*</span></label>
                            <div class="col-md-8">
                              <select class="form-control js-select2" name="sales_senior_id">
                                <option value="">Pilih Sales senior</option>
                                @foreach($sales as $index => $row)
                                  <option value="{{$row->id}}">{{$row->name}}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                          @endif
                      </div>
                      <div class="col-md-6">
                        @if($step == 1)
                          <div class="form-group row">
                            <label class="col-md-4 col-form-label text-right">Sales<span class="text-danger">*</span></label>
                            <div class="col-md-8">
                              <select class="form-control js-select2" name="sales_id">
                                <option value="">Pilih Sales</option>
                                @foreach($sales as $index => $row)
                                  <option value="{{$row->id}}">{{$row->name}}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                          @endif
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        @if($step == 1)
                          <div class="form-group row">
                            <label class="col-md-4 col-form-label text-right">Order Date<span class="text-danger">*</span></label>
                            <div class="col-md-8">
                              <input type="date" class="form-control so_date" name="so_date">
                            </div>
                          </div>
                          @endif
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-6">
                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-6">
                        @if($step == 1)
                          <div class="form-group row">
                            <label class="col-md-4 col-form-label text-right">Transaksi<span class="text-danger">*</span></label>
                            <div class="col-md-6">
                              @if ($customers->has_tempo == 0)
                                <input type="text" class="form-control input-type-transaction" name="input-type-transaction" placeholder="CASH" readonly>
                                <input type="hidden" class="form-control type_transaction" name="type_transaction" id="type_transaction" value="CASH">
                              @elseif($customers->has_tempo == 1)
                                <input type="text" class="form-control input-type-transaction" name="input-type-transaction" placeholder="TEMPO" readonly>
                                <input type="hidden" class="form-control type_transaction" name="type_transaction" id="type_transaction" value="TEMPO">
                              @endif
                            </div>
                          </div>
                          @endif
                      </div>
                      <div class="col-md-6">
                        @if($step == 1)
                          <div class="form-group row">
                            <label class="col-md-4 col-form-label text-right">Note</label>
                            <div class="col-8">
                              <textarea class="form-control" name="note" rows="1"></textarea>
                            </div>
                          </div>
                          @endif
                      </div>
                      <div class="col-md-6">
                        @if($step == 1)
                          <div class="form-group row">
                            <label class="col-md-4 col-form-label text-right">Kurs</label>
                            <div class="col-md-6">
                              <input type="text" name="idr_rate" class="form-control" value="1">
                            </div>
                          </div>
                          @endif
                      </div>
                      <div class="col-md-6">
                        @if($step == 1)
                          <div class="form-group row">
                            <label class="col-md-4 col-form-label text-right">Brand</label>
                            <div class="col-md-6">
                              <select class="form-control js-select2 select-brand" data-index="0">
                                <option value="">Pilih Merek</option>
                                @foreach($brand as $index => $row)
                                <option value="{{$row->id}}">{{$row->brand_name}}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                          @endif
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

      <hr />
        <div class="block">
          <div class="block-header block-header-default">
            <h3 class="block-title">#Add Product</h3>
            
          </div>
          <div class="block-content">
            <div class="container">
              <div class="row">
                <div class="col-12 product-list">
                  <div class="row">
                    <div class="col-2">Category</div>
                    <div class="col-3">Product</div>
                    <div class="col-1">Qty</div>
                    <div class="col-1">Free</div>
                    <div class="col">Action</div>
                  </div>

                  <div class="row mt-10 product-row">
                    <div class="col-2">
                      <select class="form-control js-select2 select-category" name="category[]" data-index="0">
                        <option value="">Select Category</option>
                      </select>
                    </div>
                    <div class="col-3">
                      <select class="form-control js-select2 select-product" name="product_id[]" data-index="0">
                        <option value="">Select product</option>
                      </select>
                    </div>
                    <div class="col-1">
                      <input type="number" name="qty[]" class="form-control input-qty" data-index="0" step="any">
                    </div>
                    <div class="col-1">
                      <input type="checkbox" class="form-check-input input-gift" id="gift" name="gift">
                      <input class="form-control input-free" type="hidden" id="free_product" name="free_product[]" data-index="0" step="any">
                    </div>
                    <div class="col"><button type="button" id="buttonAddProduct" class="btn btn-primary"><em class="fa fa-plus"></em></button></div>
                  </div>
                  <hr />

                </div>
              </div>
            </div>
          </div>
        </div>
      <hr />
      
      <div class="row pt-30 mb-15">
        <div class="col-md-6">
          <a href="{{route('superuser.penjualan.sales_order.index_' . strtolower($step_txt))}}">
            <button type="button" class="btn bg-gd-cherry border-0 text-white">
              <i class="fa fa-arrow-left mr-10"></i> Back
            </button>
          </a>
        </div>
        <div class="col-md-6 text-right">
          <button class="btn btn-primary btn-md btn-simpan" type="button"><i class="fa fa-save"></i> Simpan</button>
          
          <button class="btn btn-primary btn-md btn-simpan-dan-ajukan-ke-lanjutan" type="button"><i class="fa fa-save"></i> Simpan dan ajukan ke Lanjutan</button>
        </div>
      </div>
    </form>
  </div>
</div>


@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script>
  var productCount = 1;

  $(function(){
    $('button[type="submit"]').removeAttr('disabled');

    $('.js-select2').select2();

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
          url : '{{route('superuser.penjualan.sales_order.store', [$other_address->id, $customers->id])}}',
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

    // add product
    $(document).on('click','#buttonAddProduct',function(){
      const categoryId = $('.select-category[data-index=0]').val();
      const categoryText = $('.select-category[data-index=0] option:selected').text();
      const productId = $('.select-product[data-index=0]').val();
      const productText = $('.select-product[data-index=0] option:selected').text();
      const qty = $('.input-qty[data-index=0]').val();
      const free = $('.input-free[data-index=0]').val();

      if (categoryId === null || categoryId === '' || productId === null || productId === '' || qty === null || qty === '' ) {
        Swal.fire(
          'Error!',
          'Please input all the data',
          'error'
        );
        return;
      }

      let html = "<div class='row mt-10 product-row brand-" + categoryId + "'>";
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
      html += "  <div class='col-1'>";
      html += "    <input type='hidden' name='free_product[]' class='form-control free' value='" + free + "'>";
      html += free;
      html += "  </div>";
      html += "  <div class='col-1'>";
      html += "    <button type='button' id='buttonDeleteProduct' class='btn btn-danger'><em class='fa fa-minus'></em></button>";
      html += "  </div>";
      html += "</div>";
      
      if ($('.product-row.category-' + categoryId).length > 0) {
        $('body').find('.product-row.category-' + categoryId + ':last').after(html);
      } else {
        $('body').find('.product-list').append(html);
      }

      $('.select-category[data-index=0]').val('').change();
      $('.select-product[data-index=0]').val('').change();
      $('.input-qty[data-index=0]').val('');
      $('.input-free[data-index=0]').val();

      $('.select-category[data-index=0]').select2('focus');

      productCount++;
    });

    $(document).on('click','#buttonDeleteProduct',function(){
      $(this).parents(".product-row").remove();
    });

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
          option = '<option value="">Select Category</option>';
          $.each(resp.Data,function(i,e){
            option += '<option value="'+e.catId+'">'+e.categoryName+'</option>';
          })
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
          option = '<option value="">Select Product</option>';
          $.each(resp.Data,function(i,e){
            option += '<option value="'+e.id+'">'+e.productCode+' - '+e.productName+' - '+e.packName+'</option>';
          })
          $('.select-product[data-index=' + param.index + ']').html(option);
        },
        error : function(){
          alert("Cek Koneksi Internet");
        }
      })
    }

    $('.input-gift').click(function(){
      if($(this).is(':checked')){
          $('.input-free[data-index=0]').val('Yes');
      } else {
          $('.input-free[data-index=0]').val('No');
      }
    });
  })
</script>
@endpush