@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Purchasing</span>
  <span class="breadcrumb-item">Purchase Order (PO)</span>
  <a class="breadcrumb-item" href="{{ route('superuser.gudang.purchase_order.step', $purchase_order->id) }}">{{ $purchase_order->code }}</a>
  <span class="breadcrumb-item active">Edit Product</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Add Product</h3>
  </div>
  <div class="block-content">
    <!-- <form class="ajax" data-action="{{ route('superuser.gudang.purchase_order.detail.store', $purchase_order->id) }}" data-type="POST" enctype="multipart/form-data"> -->
    <form id="frmCreate" action="#" data-type="POST" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="purchase_id" value="{{$purchase_order->id}}">
      <div class="form-group row">
        <div class="col-md-4">
        <label for="merek">Merek</label>
          <select class="form-control js-select2 select-brand" name="merek" id="merek" data-index="0">
              <option value="">Pilih Merek</option>
              @foreach($merek as $merek => $row)
              <option value="{{$row->id}}" {{ ($row->id == $purchase_order_detail->brand_lokal_id ) ? 'selected' : '' }}>{{$row->brand_name}}</option>
              @endforeach
            </select>
          <small id="merek" class="form-text text-muted">*Choose a brand first</small>
        </div>
      </div>
      <br>
      <div class="row">
        <div class="col-12 product-list">
          <div class="row">
            <div class="col-3">Product</div>
            <div class="col-1">Qty</div>
            <div class="col-2">Packaging</div>
            <div class="col-2">Notes</div>
            <div class="col-2">Cutomer</div>
            <div class="col">Action</div>
          </div>

          <div class="row mt-10 product-row">
            <div class="col-3">
              <select class="form-control js-select2 select-product" name="product_packaging_id[]" data-index="0">
                <option value="">Select product</option>
              </select>
            </div>
            <div class="col-1">
              <input type="number" name="qty[]" class="form-control input-qty" data-index="0" step="any">
            </div>
            <div class="col-2">
              <select name="packaging_id[]" class="form-control js-select2 select-packaging" data-index="0">
                <option value="">Select packaging</option>
              </select>
            </div>
            <div class="col-2">
              <input type="text" name="note_produksi[]" class="form-control note_produksi" data-index="0" step="any">
            </div>
            <div class="col-2">
              <input type="text" name="note_repack[]" class="form-control note_repack" data-index="0" step="any">
            </div>
            <div class="col"><button type="button" id="buttonAddProduct" class="btn btn-primary"><em class="fa fa-plus"></em></button></div>
            </div>
            <hr />

        </div>
      </div>
     
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.gudang.purchase_order.step', $purchase_order->id) }}">
            <button type="button" class="btn bg-gd-cherry border-0 text-white">
              <i class="fa fa-arrow-left mr-10"></i> Back
            </button>
          </a>
        </div>
        <div class="col-md-6 text-right">
          <button class="btn btn-primary btn-md btn-simpan" type="button"><i class="fa fa-save"></i> Submit</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script src="{{ asset('public/utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {

    $(".js-select2").select2({});

    $(document).on('click','.btn-simpan',function(){
      $('#frmCreate').submit();
    })

    $(document).on('submit','#frmCreate',function(e){
      e.preventDefault();
      if(confirm("Apakah anda yakin ingin menambakan Product ke PO ?")){
        let _form = $('#frmCreate');
        $.ajax({
          url : '{{route('superuser.gudang.purchase_order.detail.store', $purchase_order->id)}}',
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
                  document.location.href = '{{ route('superuser.gudang.purchase_order.step', $purchase_order->id) }}';
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

    $(document).on('click','#buttonAddProduct',function(){
      const productId = $('.select-product[data-index=0]').val();
      const productText = $('.select-product[data-index=0] option:selected').text();
      const qty = $('.input-qty[data-index=0]').val();
      const packagingId = $('.select-packaging[data-index=0]').val();
      const packagingText = $('.select-packaging[data-index=0] option:selected').text();
      const produksi = $('.note_produksi[data-index=0]').val();
      const repack = $('.note_repack[data-index=0]').val();
      const free = $('.input-free[data-index=0]').val();
     

      if (productId === null || productId === '' || qty === null || qty === '' || packagingId == null || packagingId === '' ) {
        Swal.fire(
          'Error!',
          'Please input all the data',
          'error'
        );
        return;
      }

      let html = "<div class='row mt-10 product-row product-" + productId + "'>";
      html += "  <div class='col-3'>";
      html += "    <input type='hidden' name='product_packaging_id[]' class='form-control' value='" + productId + "'>";
      html += productText;
      html += "  </div>";
      html += "  <div class='col-1 text-right'>";
      html += "    <input type='hidden' name='qty[]' class='form-control' value='" + qty + "'>";
      html += qty;
      html += "  </div>";
      html += "  <div class='col-2'>";
      html += "    <input type='hidden' name='packaging_id[]' class='form-control' value='" + packagingId + "'>";
      html += packagingText;
      html += "  </div>";
      html += "  <div class='col-2'>";
      html += "    <input type='hidden' name='note_produksi[]' class='form-control' value='" + produksi + "'>";
      html += produksi;
      html += "  </div>";
      html += "  <div class='col-2'>";
      html += "    <input type='hidden' name='note_repack[]' class='form-control' value='" + repack + "'>";
      html += repack;
      html += "  </div>";
      html += "  <div class='col'>";
      html += "    <button type='button' id='buttonDeleteProduct' class='btn btn-danger'><em class='fa fa-minus'></em></button>";
      html += "  </div>";
      html += "</div>";
      
      if ($('.product-row.product-' + productId).length > 0) {
        $('body').find('.product-row.product-' + productId + ':last').after(html);
      } else {
        $('body').find('.product-list').append(html);
      }

      $('.select-product[data-index=0]').val('').change();
      $('.input-qty[data-index=0]').val('');
      $('.select-packaging[data-index=0]').val('').change();
      $('.note_produksi[data-index=0]').val('').change();
      $('.note_repack[data-index=0]').val('').change();

      $('.select-product[data-index=0]').select2('focus');

      productCount++;
    });
    
    $(document).on('click','#buttonDeleteProduct',function(){
      $(this).parents(".product-row").remove();
    });

    // load Product
    var param = [];
    param["brand_name"] = "";

    loadProduct({});

    $(document).on('change','.select-brand',function(){
      if ($(this).val() === '') return;

      param["brand_name"] = $(this).val();
      loadProduct({
        brand_name:param["brand_name"],
        index: $(this).data("index")
      })
    })

    function loadProduct(param){
      $.ajax({
        url : '{{route('superuser.gudang.purchase_order.detail.get_product')}}',
        method : "GET",
        data : param,
        dataType : "JSON",
        success : function(resp){
          let option = "";
          option = '<option value="">Select Product</option>';
          $.each(resp.Data,function(i,e){
            option += '<option value="'+e.id+'">'+e.productCode+' - '+e.productName+'</option>';
          })
          $('.select-product[data-index=' + param.index + ']').html(option);
        },
        error : function(){
          alert("Cek Koneksi Internet");
        }
      })
    }
    
    // load packaging
    var param = [];
    param["product_id"] = "";

    loadPackaging({});

    $(document).on('change','.select-product',function(){
      if ($(this).val() === '') return;

      param["product_id"] = $(this).val();
      loadPackaging({
        product_id:param["product_id"],
        index: $(this).data("index")
      })
    })

    function loadPackaging(param){
      $.ajax({
        url : '{{route('superuser.penjualan.sales_order.get_packaging')}}',
        method : "GET",
        data : param,
        dataType : "JSON",
        success : function(resp){
          let option = "";
          option = '<option value="">Select Packaging</option>';
          $.each(resp.Data,function(i,e){
            option += '<option value="'+e.id+'">'+e.pack_name+'</option>';
          })
          $('.select-packaging[data-index=' + param.index + ']').html(option);
        },
        error : function(){
          alert("Cek Koneksi Internet");
        }
      })
    }
  })
</script>
@endpush
