@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.sales_order.index_' . strtolower($step_txt)) }}">Sales Order {{ $step_txt }}</a>
  <span class="breadcrumb-item active">Edit Sales Order</span>
</nav>
@if(session('error') || session('success'))
<div class="alert alert-{{ session('error') ? 'danger' : 'success' }} alert-dismissible fade show" role="alert">
    @if (session('error'))
    <strong>Error!</strong> {!! session('error') !!}
    @elseif (session('success'))
    <strong>Berhasil!</strong> {!! session('success') !!}
    @endif
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif
<div class="block">
  <div class="block-content">
    <form id="frmEditSOMaster" method="post" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="id" value="{{$result->id}}">
      <input type="hidden" name="step" value="{{$step}}">
      <div class="row">
        <div class="col-12">
          <h5>#Data Pesanan</h5>
          @if($step == 1 || $step == 2 || $step == 9)
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right" for="name">Sales Senior<span class="text-danger">*</span></label>
            <div class="col-md-8">
              <select class="form-control js-select2" name="sales_senior_id" @if($step == 2) disabled @endif>
                <option value="">==Select sales senior==</option>
                @foreach($sales as $index => $row)
                  <option value="{{$row->id}}" @if($result->sales_senior_id == $row->id) selected @endif>{{$row->name}}</option>
                @endforeach
              </select>
            </div>
          </div>
          @endif
          @if($step == 1 || $step == 2 || $step == 9)
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right" for="name">Sales <span class="text-danger">*</span></label>
            <div class="col-md-8">
              <select class="form-control js-select2" name="sales_id" @if($step == 2) disabled @endif>
                <option value="">==Select sales==</option>
                @foreach($sales as $index => $row)
                  <option value="{{$row->id}}" @if($result->sales_id == $row->id) selected @endif>{{$row->name}}</option>
                @endforeach
              </select>
            </div>
          </div>
          @endif
          @if($step == 2 || $step == 9)
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Origin warehouse<span class="text-danger">*</span></label>
            <div class="col-md-8">
              <select class="form-control js-select2" name="origin_warehouse_id">
                <option value="">==Select origin warehouse==</option>
                @foreach($warehouse as $index => $row)
                  <option value="{{$row->id}}" @if($result->origin_warehouse_id == $row->id) selected @endif>{{$row->name}}</option>
                @endforeach
              </select>
            </div>
          </div>
          @endif
          @if($step == 9)
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Destination warehouse</label>
            <div class="col-md-1 d-none">
              <input type="checkbox" name="checkbox_destination_warehouse" value="1" class="form-control select-checkbox mx-auto" style="width: 20px;" @if($result->so_for == 2) checked="true" @endif>
            </div>
            <div class="col-md-8">
              <select class="form-control js-select2 select-warehouse" name="destination_warehouse_id" @if($result->so_for == 1) disabled="true" @endif>
                <option value="">==Select destination warehouse==</option>
                @foreach($warehouse as $index => $row)
                  <option value="{{$row->id}}" @if($result->destination_warehouse_id == $row->id && $result->so_for == 2) selected @endif>{{$row->name}}</option>
                @endforeach
              </select>
            </div>
          </div>
          @endif
          @if($step == 1 || $step == 2)
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Member</label>
            <div class="col-md-8">
              <select class="form-control js-select2 select-customer" name="customer_other_address_id" @if($step == 2) disabled @endif>
                <option value="">==Select customer==</option>
                @foreach($customer as $index => $row)
                  <option value="{{$row->id}}" @if($result->customer_other_address_id == $row->id && $result->so_for == 1) selected @endif>{{$row->name}}</option>
                @endforeach
              </select>
            </div>
            @if($step == 2)
            <div class="col-md-2">
              <button type="button" class="btn-cek-customer btn btn-danger btn-md">Cek Invoice</button>
            </div>
            @endif
          </div>
          @endif
          @if($step == 1 || $step == 2)
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Address</label>
            <div class="col-md-8">
              @if($result->so_for == 1)
              <textarea type="text" name="address" class="form-control" readonly>{{$result->customer->address ?? ''}}</textarea>
              @else
              <textarea type="text" name="address" class="form-control" readonly>{{$result->warehouse->address ?? ''}}</textarea>
              @endif
            </div>
          </div>
          @endif
          @if($step == 1 && $result->status == 3)
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Keterangan Tidak Lanjut</label>
            <div class="col-md-8">
              <textarea type="text" name="address" class="form-control" readonly>{{$result->keterangan_tidak_lanjut ?? ''}}</textarea>
            </div>
          </div>
          @endif
          @if($step == 2)
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Transaction<span class="text-danger">*</span></label>
            <div class="col-md-8">
              <select class="form-control js-select2" name="type_transaction" >
                <?php
                  $selected1 = "";
                  $selected2 = "";
                  $selected3 = "";

                  if($result->type_transaction == 1){
                    $selected1 = "selected";
                  }
                  else if($result->type_transaction == 2){
                    $selected2 = "selected";
                  }
                  else{
                    $selected3 = "selected";
                  }
                ?>
                <option value="">==Select type transaction==</option>
                <option value="1" <?= $selected1 ?>>Cash</option>
                <option value="2" <?= $selected2 ?>>Tempo</option>
                <option value="3" <?= $selected3 ?>>Marketplace</option>
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
                <option value="{{$row->id}}" @if($result->ekspedisi_id == $row->id) selected @endif>{{$row->name}}</option>
                @endforeach
              </select>
            </div>
          </div>
          @endif
          @if($step == 1 || $step == 2)
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Note</label>
            <div class="col-md-8">
              <textarea class="form-control" name="note" rows="3">{{$result->note}}</textarea>
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
            <div class="col-3">Product Category</div>
            <div class="col-3">Product</div>
            <div class="col-1">Qty</div>
            <div class="col-3">Packaging</div>
          </div>

          @if($step == 1 || $step == 9)
          <div class="row mt-10 product-row">
            <div class="col-3">
              <select class="form-control js-select2 select-category" data-index="0">
                <option value="">==Select product category==</option>
                @foreach($product_category as $index => $row)
                  <option value="{{$row->id}}">{{$row->name}}</option>
                @endforeach
              </select>
            </div>
            <div class="col-3">
              <select class="form-control js-select2 select-product" name="product_id[]" data-index="0">
                <option value="">==Select product==</option>
              </select>
            </div>
            <div class="col-1">
              <input type="number" name="qty[]" class="form-control input-qty" data-index="0" step="any">
            </div>
            <div class="col-3">
              <select name="packaging[]" class="form-control js-select2 select-packaging" data-index="0">
                <option value="">==Select packaging==</option>
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
          @endif
          
          @if(count($result->so_detail) > 0)
            @foreach($result->so_detail as $index => $row)
              <div class='row mt-10 product-row'>
                <div class="col-3">
                  <input type='hidden' class='form-control' value='{{ $row->product->category->id }}'>
                  {{ $row->product->category->name }}
                </div>
                <div class="col-3">
                  <input type='hidden' name='product_id[]' class='form-control' value='{{ $row->product->id }}'>
                  {{ $row->product->code }} - {{ $row->product->name }}
                </div>
                <div class="col-1 text-right">
                  <input type='hidden' name='qty[]' class='form-control' value='{{ $row->qty }}'>
                  {{ $row->qty }}
                </div>
                <div class="col-3">
                  <input type='hidden' name='packaging[]' class='form-control' value='{{ $row->packaging }}'>
                  {{ $packaging_dictionary[$row->packaging] }}
                </div>
                @if($step == 1 || $step == 9)
                <div class="col-1">
                  <button type='button' id='buttonDeleteProduct' class='btn btn-danger'><em class='fa fa-minus'></em></button>
                </div>
                @endif
              </div>
            @endforeach
          @endif
        </div>
        <hr />
      </div>

      <hr />

      <div class="row mb-30">
        <div class="col-12">
          <a href="{{route('superuser.penjualan.sales_order.index_' . strtolower($step_txt))}}" class="btn btn-warning  btn-md text-white"><i class="fa fa-arrow-left"></i> Back</a>
          <button class="btn btn-primary btn-md" type="submit" disabled="disabled"><i class="fa fa-save"></i> Save</button>
        </div>
      </div>
    </form>
    
  </div>
</div>

<!-- Modal -->
<!-- End Modal -->
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script>
  $(function(){
    $('button[type="submit"]').removeAttr('disabled');
    $('.js-select2').select2();

    let param = [];
    param["category_id"] = "";
    param["type_id"] = "";

    loadProduct({});

    $(document).on('change','.select-category',function(){
      param["category_id"] = $(this).val();
      loadProduct({
        category_id:param["category_id"],
        type_id : param["type_id"]
      })
    })

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
      const categoryId = $('.select-category[data-index=0]').val();
      const categoryText = $('.select-category[data-index=0] option:selected').text();
      const productId = $('.select-product[data-index=0]').val();
      const productText = $('.select-product[data-index=0] option:selected').text();
      const qty = $('.input-qty[data-index=0]').val();
      const packagingId = $('.select-packaging[data-index=0]').val();
      const packagingText = $('.select-packaging[data-index=0] option:selected').text();
      if (categoryId === null || categoryId === '' || productId === null || productId === '' || qty === null || qty === '' || packagingId == null || packagingId === '') {
        Swal.fire(
          'Error!',
          'Please input all the data',
          'error'
        );
        return;
      }

      let html = "<div class='row mt-10 product-row category-" + categoryId + "'>";
      html += "  <div class='col-3'>";
      html += "    <input type='hidden' class='form-control' value='" + categoryId + "'>";
      html += categoryText;
      html += "  </div>";
      html += "  <div class='col-3'>";
      html += "    <input type='hidden' name='product_id[]' class='form-control' value='" + productId + "'>";
      html += productText;
      html += "  </div>";
      html += "  <div class='col-1 text-right'>";
      html += "    <input type='hidden' name='qty[]' class='form-control' value='" + qty + "'>";
      html += qty;
      html += "  </div>";
      html += "  <div class='col-3'>";
      html += "    <input type='hidden' name='packaging[]' class='form-control' value='" + packagingId + "'>";
      html += packagingText;
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

      //let option = '<option value="">==Select product==</option>';
      //$('.select-product[data-index=0]').html(option);

      //$('.select-category[data-index=0]').val('').change();
      $('.select-product[data-index=0]').val('').change();
      $('.input-qty[data-index=0]').val('');
      $('.select-packaging[data-index=0]').val('').change();

      $('.select-category[data-index=0]').select2('focus');

      productCount++;
    })

    $(document).on('click','#buttonDeleteProduct',function(){
      $(this).parents(".product-row").remove();
    })

    $(document).on('click','.btn-cek-customer',function(){
      $('#modalCustomerInvoice').modal('show');
    })

    $(document).on('submit','#frmEditSOMaster',function(e){
      e.preventDefault();
      if(confirm("Apakah anda yakin ingin mengubah sales order ini ? ?")){
        let _form = $('#frmEditSOMaster');
        $.ajax({
          url : '{{route('superuser.penjualan.sales_order.update')}}',
          method : "POST",
          data : $('#frmEditSOMaster').serializeArray(),
          dataType : "JSON",
          beforeSend : function(){
            $('#frmEditSOMaster').find('button[type="submit"]').html('Loading...');
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
                  location.reload();
              })
              
            }
          },
          complete : function(){
            $('#frmEditSOMaster').find('button[type="submit"]').html('<i class="fa fa-save"> Save</i>');
          }
        })
      }
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
        option = '<option value="">==Select product==</option>';
        $.each(resp.Data,function(i,e){
          option += '<option value="'+e.id+'">'+e.code+' - '+e.name+'</option>';
        })
        $('.select-product').html(option);
      },
      error : function(){
        alert("Cek Koneksi Internet");
      }
    })
  }

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
</script>
@endpush