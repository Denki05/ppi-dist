@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Purchasing</span>
  <span class="breadcrumb-item">Purchase Order (PPB)</span>
  <a class="breadcrumb-item" href="{{ route('superuser.gudang.purchase_order.step', $purchase_order->id) }}">{{ $purchase_order->code }}</a>
  <span class="breadcrumb-item active">Add Product</span>
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

      <div class="card shadow-sm border mb-4" style="max-width: 500px;">
        <div class="card-body p-3">
          <h5 class="mb-3 font-weight-bold" style="font-size: 1rem;">Pilih Merek</h5>
          <div class="form-group mb-2">
            <label for="merek" style="font-size: 0.95rem;">Merek</label>
            <select class="form-control js-select2 select-brand" name="merek" data-index="0" style="font-size: 0.95rem;">
              <option value="">Pilih Merek</option>
              @foreach($merek as $merek => $row)
              <option value="{{$row->brand_name}}">{{$row->brand_name}}</option>
              @endforeach
            </select>
            <strong class="form-text text-danger" style="font-size: 0.85rem;">*Pilih merek terlebih dahulu sebelum memilih produk</strong>
          </div>
        </div>
      </div>

      <div class="card shadow-sm border mb-4">
        <div class="card-body">
          <h5 class="mb-3 font-weight-bold">Tambah Produk</h5>
          <div class="form-row align-items-end">
            <div class="form-group col-md-3">
              <label>Produk</label>
              <select class="form-control js-select2 select-product" name="product_packaging_id[]" data-index="0">
                <option value="">Pilih Produk</option>
              </select>
            </div>
            <div class="form-group col-md-1">
              <label>Qty</label>
              <input type="number" name="qty[]" class="form-control input-qty" data-index="0" placeholder="0" step="any">
            </div>
            <div class="form-group col-md-2">
              <label>Kemasan</label>
              <select name="packaging_id[]" class="form-control js-select2 select-packaging" data-index="0">
                <option value="">Pilih Kemasan</option>
              </select>
            </div>
            <div class="form-group col-md-2">
              <label>Catatan Produksi</label>
              <input type="text" name="note_produksi[]" class="form-control note_produksi" data-index="0" placeholder="Catatan...">
            </div>
            <div class="form-group col-md-2">
              <label>Catatan Repack</label>
              <input type="text" name="note_repack[]" class="form-control note_repack" data-index="0" placeholder="Catatan...">
            </div>
            <div class="form-group col-md-2 text-right">
              <button type="button" id="buttonAddProduct" class="btn btn-success">
                <i class="fa fa-plus"></i> Tambah
              </button>
            </div>
          </div>

          <hr class="mt-4" />

          <div class="product-list mt-3"></div>
        </div>
      </div>

      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.gudang.purchase_order.step', $purchase_order->id) }}">
            <button type="button" class="btn btn-danger">
              <i class="fa fa-arrow-left mr-2"></i> Kembali
            </button>
          </a>
        </div>
        <div class="col-md-6 text-right">
          <button class="btn btn-primary btn-md btn-simpan" type="button">
            <i class="fa fa-save mr-1"></i> Simpan
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
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
     
      let newProductID = 0;
      if (productId.indexOf('/') > 5) {
        newProductID = productId.replace('/', '\\/');
      }

      if (newProductID === null || newProductID === '' || qty === null || qty === '' || packagingId == null || packagingId === '' ) {
        Swal.fire(
          'Error!',
          'Please input all the data',
          'error'
        );
        return;
      }

      let html = "<div class='row mt-10 product-row product-" + newProductID + "'>";
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
      
      if ($('.product-row.product-' + newProductID).length > 0) {
        $('body').find('.product-row.product-' + newProductID + ':last').after(html);
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
            option += '<option value="'+e.id+'">'+e.productCode+' - '+e.productName+' - '+e.warehouseName+'</option>';
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
            option += '<option value="'+e.id+'">'+e.pack_name+ ' - ' + e.type +  '</option>';
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