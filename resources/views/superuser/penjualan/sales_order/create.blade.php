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
      <div class="row">
        <div class="col-12">
          @if($step == 1 || $step == 2 || $step == 9)
            <input type="hidden" class="form-control" name="sales_senior_id" value="{{ $sales_senior_id }}">
          @endif
          @if($step == 1 || $step == 2 || $step == 9)
            <input type="hidden" class="form-control" name="sales_id" value="{{ $sales_id }}">
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
            <label class="col-md-2 col-form-label text-right">Transaksi<span class="text-danger">*</span></label>
            <div class="col-md-8">
              <select class="form-control js-select2 select-transaksi" name="type_transaction">
                <option value="">Pilih Jenis Transaksi</option>
                <option value="1">Cash</option>
                <option value="2">Tempo</option>
                <option value="3">Marketplace</option>
              </select>
            </div>
          </div>
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
              <textarea class="form-control" name="note" rows="1"></textarea>
            </div>
          </div>
          @endif
        </div>
      </div>
      

      <hr />

      <div class="row">
        <div class="col-12 product-list">
          <h5>Select Product</h5>

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

            <div class="col-1"><button type="button" id="buttonAddProduct" class="btn btn-primary"><i class="mdi mdi-plus"></i></button></div>
          </div>
          <hr />

        </div>
      </div>
      <hr />
      <div class="block-header block-header-default" id="frm-cash" style="display:none;">
        <div class="container">
          <div class="form-group row justify-content-end">
            <label class="col-md-3 col-form-label text-right" for="subtotal">IDR Sub Total</label>
            <div class="col-md-2">
              <input type="text" class="form-control" id="subtotal" name="subtotal" readonly>
            </div>
          </div>
          <div class="form-group row justify-content-end">
            <label class="col-md-3 col-form-label text-right" for="tax">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="tax_checked" name="tax_checked">
                <label class="form-check-label" for="tax_checked">
                  Tax
                </label>
              </div>
            </label>
            <div class="col-md-2">
              <input type="number" class="form-control" id="tax" name="tax" readonly>
            </div>
          </div>
          <div class="form-group row justify-content-end">
            <label class="col-md-3 col-form-label text-right" for="discount">IDR Discount</label>
            <div class="col-md-2">
              <input type="text" class="form-control" id="discount" name="discount">
            </div>
          </div>
          <div class="form-group row justify-content-end">
            <label class="col-md-3 col-form-label text-right" for="shipping_fee">Courier</label>
            <div class="col-md-2">
              <input type="text" class="form-control" id="shipping_fee" name="shipping_fee">
            </div>
          </div>
          <div class="form-group row justify-content-end">
            <label class="col-md-3 col-form-label text-right" for="grand_total">IDR Total</label>
            <div class="col-md-2">
              <input type="text" class="form-control" id="grand_total" name="grand_total" readonly>
            </div>
          </div>
        </div>
      </div>
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

  $('.select-transaksi').on('change', function() {
      if ( this.value == '1')
      {
        $("#frm-cash").show();
      }
      else
      {
        $("#frm-cash").hide();
      }
    });

  // $(function () {
  //     $('.select-customer').on('change', function(){
  //         let customer_id = $('.select-customer').val();

  //         $.ajax({
  //           type : 'POST',
  //           url : '{{route('superuser.penjualan.sales_order.getmember')}}',
  //           data : {customer_id:customer_id},
  //           cache : false,

  //           success: function(msg){
  //             $('.other_address').html(msg);
  //           },
  //           error : function(data){
  //             console.log('error:',data)
  //           },
  //         })
  //       })
  //   });
</script>
@endpush