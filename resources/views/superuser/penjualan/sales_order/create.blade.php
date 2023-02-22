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
        <div class="col-4">
          <div class="card">
            <div class="card-body">
                <div class="col-10">
                  @if($step == 1 || $step == 2 || $step == 9)
                    <div class="form-group row">
                    <label class="col-md-4 col-form-label text-right">Team Leader<span class="text-danger">*</span></label>
                    <div class="col-md-8">
                      <select class="form-control js-select2" name="sales_senior_id">
                        <option value="">Pilih TL</option>
                        @foreach($sales as $index => $row)
                          <option value="{{$row->id}}">{{$row->name}}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  @endif
                </div>
                <div class="col-10">
                  @if($step == 1 || $step == 2 || $step == 9)
                    <div class="form-group row">
                    <label class="col-md-4 col-form-label text-right">Salesman<span class="text-danger">*</span></label>
                    <div class="col-md-8">
                      <select class="form-control js-select2" name="sales_id">
                        <option value="">Pilih Salesman</option>
                        @foreach($sales as $index => $row)
                          <option value="{{$row->id}}">{{$row->name}}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  @endif
                </div>
            </div>
          </div>
        </div>

        <div class="col-8">
          <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  @if($step == 1)
                    <div class="form-group row">
                      <label class="col-md-4 col-form-label text-right">Transaksi<span class="text-danger">*</span></label>
                      <div class="col-md-6">
                        <select class="form-control js-select2 select-transaksi" name="type_transaction">
                          <option value="">Type Transaksi</option>
                          <option value="1">Cash</option>
                          <option value="2">Tempo</option>
                          <option value="3">Marketplace</option>
                        </select>
                      </div>
                    </div>
                    @endif
                </div>
                <div class="col-md-6">
                  @if($step == 1)
                    <div class="form-group row">
                      <label class="col-md-4 col-form-label text-right">Kurs</label>
                      <div class="col-md-6">
                        <input type="number" name="idr_rate" class="form-control" value="0">
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
              </div>
            </div>
          </div>
        </div>
      </div>

      <hr />
        <div class="card">
          <div class="card-header">
              <h5>#Product List</h5>
          </div>

          <div class="card-body">
              <table class="table" id="products_table">
                  <thead>
                      <tr>
                          <th width="25%">Product</th>
                          <th width="10%">Brand</th>
                          <th width="10%">Category</th>
                          <th width="5%">Quantity</th>
                          <th width="10%">Packaging</th>
                      </tr>
                  </thead>
                  <tbody>
                      <tr id="product0">
                          <td>
                              <select name="products[]" class="form-control js-select2">
                                  <option value="">Pilih Product</option>
                                  @foreach ($products as $product)
                                      <option value="{{ $product->id }}">
                                          {{$product->code}}-{{$product->name}}
                                      </option>
                                  @endforeach
                              </select>
                          </td>
                          <td>
                            <input class="form-control brand_name" name="brand_name" readonly>
                          </td>
                          <td>
                            <input class="form-control category_name" name="category_name" readonly>
                          </td>
                          <td>
                              <input type="number" name="qty[]" class="form-control" value="1" />
                          </td>
                          <td>
                            <span><span>
                          </td>
                      </tr>
                      <tr id="product1"></tr>
                  </tbody>
              </table>

              <div class="row">
                  <div class="col-md-12">
                      <button id="add_row" class="btn btn-primary pull-left">+ Add Row</button>
                      <button id='delete_row' class="pull-right btn btn-danger">- Delete Row</button>
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
  $(function(){
    $('button[type="submit"]').removeAttr('disabled');

    $('.js-select2').select2();

    $(document).ready(function(){
      let row_number = 1;
      $("#add_row").click(function(e){
        e.preventDefault();
        let new_row_number = row_number - 1;
        $('#product' + row_number).html($('#product' + new_row_number).html()).find('td:first-child');
        $('#products_table').append('<tr id="product' + (row_number + 1) + '"></tr>');
        row_number++;
      });

      $("#delete_row").click(function(e){
        e.preventDefault();
        if(row_number > 1){
          $("#product" + (row_number - 1)).html('');
          row_number--;
        }
      });
    });

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

  function delay(fn, ms) {
      let timer = 0
      return function(...args) {
        clearTimeout(timer)
        timer = setTimeout(fn.bind(this, ...args), ms || 0)
      }
    }

  // $('.select-transaksi').on('change', function() {
  //     if ( this.value == '1')
  //     {
  //       $("#frm-cash").show();
  //     }
  //     else
  //     {
  //       $("#frm-cash").hide();
  //     }
  //   });

</script>
@endpush