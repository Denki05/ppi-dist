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
        <div class="col-4">
          <div class="card">
            <div class="card-body">
                <div class="col-10">
                  @if($step == 1 || $step == 2 || $step == 9)
                    <div class="form-group row">
                    <label class="col-md-4 col-form-label text-right">Sales Senior<span class="text-danger">*</span></label>
                    <div class="col-md-8">
                      <select class="form-control js-select2" name="sales_senior_id">
                        <option value="">Pilih Sales Senior</option>
                        @foreach(\App\Entities\Penjualan\SalesOrder::SALES_SENIOR as $sales_senior => $sales_value)
                          <option value="{{ $sales_value }}" {{ ($result->sales_senior_id == $sales_value) ? 'selected' : '' }}>{{ $sales_senior }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  @endif
                </div>
                <div class="col-10">
                  @if($step == 1 || $step == 2 || $step == 9)
                    <div class="form-group row">
                    <label class="col-md-4 col-form-label text-right">Sales<span class="text-danger">*</span></label>
                    <div class="col-md-8">
                      <select class="form-control js-select2" name="sales_id">
                        <option value="">Pilih Sales</option>
                        @foreach(\App\Entities\Penjualan\SalesOrder::SALES as $sales => $sales_value)
                          <option value="{{ $sales_value }}" {{ ($result->sales_id == $sales_value) ? 'selected' : '' }}>{{ $sales }}</option>
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
                      <label class="col-md-4 col-form-label text-right">Brand</label>
                      <div class="col-md-6">
                        <select class="form-control js-select2 select-brand" data-index="0">
                          <option value="">Pilih Merek</option>
                          @foreach($brand as $index => $row)
                          <option value="{{$row->brand_name}}">{{$row->brand_name}}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                    @endif
                </div>
                {{--<div class="col-md-6">
                  @if($step == 1)
                    <div class="form-group row">
                      <label class="col-md-4 col-form-label text-right">Transaksi<span class="text-danger">*</span></label>
                      <div class="col-md-6">
                        @if ($result->customer->has_tempo == 0)
                          <input type="text" class="form-control input-type-transaction" name="input-type-transaction" placeholder="CASH" readonly>
                          <input type="hidden" class="form-control type_transaction" name="type_transaction" id="type_transaction" value="CASH">
                        @elseif($result->customer->has_tempo == 1)
                          <input type="text" class="form-control input-type-transaction" name="input-type-transaction" placeholder="TEMPO" readonly>
                          <input type="hidden" class="form-control type_transaction" name="type_transaction" id="type_transaction" value="TEMPO">
                        @endif
                      </div>
                    </div>
                    @endif
                </div>--}}
                <div class="col-md-6">
                  @if($step == 1)
                    <div class="form-group row">
                      <label class="col-md-4 col-form-label text-right">Note</label>
                      <div class="col-8">
                        <textarea class="form-control" name="note" rows="1">{{ $result->note }}</textarea>
                      </div>
                    </div>
                    @endif
                </div>
                <div class="col-md-6">
                  @if($step == 1)
                    <div class="form-group row">
                      <label class="col-md-4 col-form-label text-right">Kurs</label>
                      <div class="col-md-6">
                        <input type="text" name="idr_rate" class="form-control" value="{{ $result->idr_rate }}">
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

      <div class="row">
        <div class="col-12 product-list">
          <h5>Select Product</h5>

          <div class="row">
            <div class="col-3">Product</div>
            <div class="col-3">Packaging</div>
            <div class="col-1">Qty</div>
            <div class="col-1">Disc Usd</div>
            <div class="col-1">Free</div>
            <div class="col">Action</div>
          </div>

          @if($step == 1 || $step == 9)
          <div class="row mt-10 product-row">
            <div class="col-3">
              <select class="form-control js-select2 select-product" name="product_id[]" data-index="0">
                <option value="">==Select product==</option>
              </select>
            </div>
            <div class="col-3">
              <select name="packaging_id[]" class="form-control js-select2 select-packaging" data-index="0">
                <option value="">Select packaging</option>
              </select>
            </div>
            <div class="col-1">
              <input type="number" name="qty[]" class="form-control input-qty" data-index="0" step="any">
            </div>
            <div class="col-1">
              <input type="number" name="usd[]" class="form-control input-usd" data-index="0" step="any">
            </div>
            <div class="col-1">
              <input type="checkbox" class="form-check-input input-gift" id="gift" name="gift">
              <input class="form-control input-free" type="hidden" id="free_product" name="free_product[]" data-index="0" step="any">
            </div>
            <div class="col-1"><button type="button" id="buttonAddProduct" class="btn btn-primary"><em class="fa fa-plus"></em></button></div>
          </div>
          @endif
          
          @if(count($result->so_detail) > 0)
            @foreach($result->so_detail as $index => $row)
              <div class='row mt-10 product-row'>
                <div class="col-3">
                  <input type='hidden' name='product_id[]' class='form-control' value='{{ $row->product_pack->id }}'>
                  {{ $row->product_pack->code }} - {{ $row->product_pack->name }}
                </div>
                <div class="col-3">
                  <input type='hidden' name='packaging_id[]' class='form-control' value='{{ $row->packaging_id }}'>
                  {{ $row->packaging->pack_name }}
                </div>
                <div class="col-1 text-right">
                  <input type='hidden' name='qty[]' class='form-control' value='{{ $row->qty }}'>
                  {{ $row->qty }}
                </div>
                <div class="col-1 text-right">
                  <input type='hidden' name='usd[]' class='form-control' value='{{ $row->disc_usd }}'>
                  {{ $row->disc_usd }}
                </div>
                <div class="col-1">
                  <input type='hidden' name='free_product[]' class='form-control' value='{{ $row->free_product }}'>
                  @if($row->free_product == 0)
                    <span>NO</span>
                  @elseif($row->free_product == 1)
                    <span>YES</span>
                  @endif
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
      </div>

      <br>

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

    $(document).on('click','#buttonAddProduct',function(){
      const productId = $('.select-product[data-index=0]').val();
      const productText = $('.select-product[data-index=0] option:selected').text();
      const qty = $('.input-qty[data-index=0]').val();
      const usd = $('.input-usd[data-index=0]').val();
      const packagingId = $('.select-packaging[data-index=0]').val();
      const packagingText = $('.select-packaging[data-index=0] option:selected').text();
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
      html += "    <input type='hidden' name='product_id[]' class='form-control' value='" + productId + "'>";
      html += productText;
      html += "  </div>";
      html += "  <div class='col-3'>";
      html += "    <input type='hidden' name='packaging_id[]' class='form-control' value='" + packagingId + "'>";
      html += packagingText;
      html += "  </div>";
      html += "  <div class='col-1 text-right'>";
      html += "    <input type='hidden' name='qty[]' class='form-control' value='" + qty + "'>";
      html += qty;
      html += "  </div>";
      html += "  <div class='col-1 text-right'>";
      html += "    <input type='hidden' name='usd[]' class='form-control' value='" + usd + "'>";
      html += '$'+usd;
      html += "  </div>";
      html += "  <div class='col-1'>";
      html += "    <input type='hidden' name='free_product[]' class='form-control free' value='" + free + "'>";
      html += free;
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
      $('.input-usd[data-index=0]').val('');
      $('.select-packaging[data-index=0]').val('').change();
      $('.input-free[data-index=0]').val();

      $('.select-product[data-index=0]').select2('focus');

      productCount++;
    });

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

    // load product
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
        url : '{{route('superuser.penjualan.sales_order.get_product')}}',
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

    $('.input-gift').click(function(){
      if($(this).is(':checked')){
          $('.input-free[data-index=0]').val(1);
      } else {
          $('.input-free[data-index=0]').val(0);
      }
    });
  })
</script>
@endpush