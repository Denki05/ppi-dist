@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <span class="breadcrumb-item">Sales Order</span>
  <span class="breadcrumb-item active">Edit Item SO</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-content">
    <form id="frmEdit">
      @csrf
      <input type="hidden" name="id" value="{{$result->id}}">
      <div class="row">
        <div class="col-12">
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Product Category</label>
            <div class="col-md-8">
              <select class="form-control js-select2 select-category">
                <option value="">==Select product category==</option>
                @foreach($product_category as $index => $row)
                  <option value="{{$row->id}}">{{$row->name}}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Product</label>
            <div class="col-md-8">
              <select class="form-control js-select2 select-product" name="product_id">
                <option value="">==Select product</option>
              </select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Qty</label>
            <div class="col-md-8">
              <input type="number" name="qty" class="form-control" value="{{$result->qty}}" step="any">
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Packaging</label>
            <div class="col-md-8">
              <select name="packaging" class="form-control js-select2">
                <option value="">==Select packaging==</option>
                <option value="1" @if($result->packaging == 1) selected @endif>100gr (0.1)</option>
                <option value="2" @if($result->packaging == 2) selected @endif>500gr (0.5)</option>
                <option value="3" @if($result->packaging == 3) selected @endif>Jerigen 5kg (5)</option>
                <option value="4" @if($result->packaging == 4) selected @endif>Alumunium 5kg (5)</option>
                <option value="5" @if($result->packaging == 5) selected @endif>Jerigen 25kg (25)</option>
                <option value="6" @if($result->packaging == 6) selected @endif>Drum 25kg (25)</option>
                <option value="7" @if($result->packaging == 7) selected @endif>Free</option>
              </select>
            </div>
          </div>
        </div>
      </div>
      <div class="row mb-30">
        <div class="col-12">
          <a href="{{route('superuser.penjualan.sales_order.edit',$result->so_id)}}" class="btn btn-warning  btn-md text-white"><i class="fa fa-arrow-left"></i> Back</a>
          <button class="btn btn-primary btn-md" type="submit" disabled="disabled"><i class="fa fa-save"></i> Save Item</button>
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
  $(function(){
    let param = [];
    param["category_id"] = "";
    param["type_id"] = "";

    $('button[type="submit"]').removeAttr('disabled');
    $('.js-select2').select2();

    @if(!empty($result->product_id))
      loadProduct({},"{{$result->product_id}}");
    @else
        loadProduct({},0);
    @endif
    

    $(document).on('change','.select-category',function(){
      param["category_id"] = $(this).val();
      loadProduct({
        category_id:param["category_id"],
        type_id : param["type_id"]
      },0)
    })

    $(document).on('submit','#frmEdit',function(e){
      e.preventDefault();
      if(confirm("Apakah anda yakin ingin mengubah item SO ini ?")){
        let _form = $('#frmEdit');
        $.ajax({
          url : '{{route('superuser.penjualan.sales_order.update_item')}}',
          method : "POST",
          data : getFormData(_form),
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
                  let token = getToken('{{route('superuser.penjualan.sales_order.edit',$result->so_id)}}');
                  location.href = '{{route('superuser.penjualan.sales_order.edit',$result->so_id)}}' + '?token=' + token;
              })
            }
          },
          error : function(){
            alert("Cek Koneksi Internet");
          },
          complete : function(){
            $('button[type="submit"]').html('<i class="fa fa-save"> Save</i>');
          }
        })
      }
    })

  })
  function loadProduct(param,$selected = 0){
    $.ajax({
      url : '{{route('superuser.penjualan.sales_order.get_product')}}',
      method : "GET",
      data : param,
      dataType : "JSON",
      success : function(resp){
        let option = "";
        option = '<option value="">==Select product==</option>';
        $.each(resp.Data,function(i,e){
          if($selected != 0){
              if($selected == e.id){
                option += '<option value="'+e.id+'" selected>'+e.name+'</option>';
              }
              else{
                option += '<option value="'+e.id+'">'+e.name+'</option>';
              }
          }
          else{
            option += '<option value="'+e.id+'">'+e.name+'</option>';
          }
          
        })
        $('.select-product').html(option);
      },
      error : function(){
        alert("Cek Koneksi Internet");
      }
    })
  }
</script>
@endpush