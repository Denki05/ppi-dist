@extends('superuser.app')

@section('content')

@if ( $purchase_order->status() == 'DRAFT' )
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Gudang</span>
    <span class="breadcrumb-item">Purchase Order (PO)</span>
    <span class="breadcrumb-item">New</span>
    <span class="breadcrumb-item active">Add Product</span>
  </nav>
@else
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Gudang</span>
    <span class="breadcrumb-item">Purchase Order (PO)</span>
    <span class="breadcrumb-item">{{ $purchase_order->code }}</span>
    <span class="breadcrumb-item active">Edit Product</span>
  </nav>
@endif

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
<div id="alert-block"></div>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">New Purchase Order (PO)</h3>
  </div>
  <div class="block-content">
    <div class="row">
      <label class="col-md-3 col-form-label text-right">PO Code</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->code }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Warehouse</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->warehouse->name }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">ETD</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ \Carbon\Carbon::parse($purchase_order->etd)->format('d-m-Y')}}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Status</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->status() }}</div>
      </div>
    </div>

    <div class="row pt-30 mb-15">
      <div class="col-md-6">
        @if ($purchase_order->status != $purchase_order::STATUS['DRAFT'])
        <a href="{{ route('superuser.gudang.purchase_order.index') }}">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
        @endif
      </div>
      @if ($purchase_order->status == $purchase_order::STATUS['DRAFT'])
      <div class="col-md-6 text-right">
      <a href="{{ route('superuser.gudang.purchase_order.edit', $purchase_order->id) }}">
          <button type="button" class="btn bg-gd-sea border-0 text-white">
            Edit <i class="fa fa-pencil ml-10"></i>
          </button>
        </a>
        <a href="{{ route('superuser.gudang.purchase_order.publish', $purchase_order->id) }}" class="btn bg-gd-leaf border-0 text-white" title="Publish">
          Publish <i class="fa fa-check ml-10"></i>
        </a>
      </div>
      @else
      <div class="col-md-6 text-right">
        @if($purchase_order->edit_marker == 0)
        <a href="{{ route('superuser.gudang.purchase_order.edit', $purchase_order->id) }}">
          <button type="button" class="btn bg-gd-sea border-0 text-white">
            Edit <i class="fa fa-pencil ml-10"></i>
          </button>
        </a>
        @endif
        @if($purchase_order->edit_marker == 1)
        <a href="{{ route('superuser.gudang.purchase_order.save_modify', [$purchase_order->id, 'save']) }}" class="btn bg-gd-corporate border-0 text-white" title="Save">
          Save <i class="fa fa-check ml-10"></i>
        </a>
        @endif
        <a href="{{ route('superuser.gudang.purchase_order.save_modify', [$purchase_order->id, 'save-acc']) }}" class="btn bg-gd-leaf border-0 text-white" title="Acc">
          ACC <i class="fa fa-check ml-10"></i>
        </a>
      </div>
      @endif
    </div>
  </div>
</div>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">{{ ( $purchase_order->status() == 'DRAFT' ? 'Add' : 'Edit' ) }} Product </h3>
    
    <!-- <button type="button" class="btn btn-outline-info mr-10 min-width-125 pull-right" data-toggle="modal" data-target="#modal-manage">Import</button> -->
    
    <!-- <a href="#">
      <button type="button" class="btn btn-outline-primary min-width-125 pull-right">Create</button>
    </a> -->
    <button type="button" class="btn btn-outline-primary min-width-125 pull-right" data-toggle="modal" data-target=".bd-example-modal-lg">Add</button>
  </div>
  <div class="block-content">
    <table id="datatable" class="table table-striped">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">Nama Varian</th>
          <th class="text-center">Kode</th>
          <th class="text-center">Qty (KG)</th>
          <th class="text-center">Packaging</th>
          <th class="text-center">Produksi</th>
          <th class="text-center">Repack</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        
      </tbody>
      
    </table>
  </div>
</div>

<!-- Modal input Product -->
<div class="modal fade bd-example-modal-lg" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" style="max-width: 80%;">
    <div class="modal-content">
      <form class="ajax" data-action="{{ route('superuser.gudang.purchase_order.store_item', $purchase_order->id) }}" data-type="POST" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title">Input Product PO - #{{$purchase_order->code}}</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" style="max-height: 800px">
          <div class="form-group row">
            <div class="col-md-7">
              <select class="form-control js-select2 select-brand" data-index="0">
                <option value="">Pilih Merek</option>
                @foreach($merek as $merek => $row)
                <option value="{{$row->id}}">{{$row->brand_name}}</option>
                @endforeach
              </select>
            </div>
          </div>
          <hr>
            <div class="row">
              <div class="col-12 product-list">
                <div class="row">
                  <div class="col-3">Product</div>
                  <div class="col-1">Qty (KG)</div>
                  <div class="col-3">Packaging</div>
                  <div class="col-1">Action</div>
                </div>

                <div class="row mt-10 product-row">
                  <div class="col-3">
                    <select class="form-control js-select2 select-product" name="product_id[]" data-index="0">
                      <option value="">Select product</option>
                    </select>
                  </div>
                  <div class="col-1">
                    <input type="number" name="qty[]" class="form-control input-qty" data-index="0" step="any">
                  </div>
                  <div class="col-3">
                    <select name="packaging_id[]" class="form-control js-select2 select-packaging" data-index="0">
                      <option value="">Select packaging</option>
                    </select>
                  </div>
                  <div class="col-1"><button type="button" id="buttonAddProduct" class="btn btn-primary"><em class="fa fa-plus"></em></button></div>
                </div>
                <hr />

              </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $('.js-select2').select2()

    $('#datatable').DataTable();

    $(document).on('click','#buttonAddProduct',function(){
      const productId = $('.select-product[data-index=0]').val();
      const productText = $('.select-product[data-index=0] option:selected').text();
      const qty = $('.input-qty[data-index=0]').val();
      const packagingId = $('.select-packaging[data-index=0]').val();
      const packagingText = $('.select-packaging[data-index=0] option:selected').text();
     

      if (productId === null || productId === '' || qty === null || qty === '' || packagingId == null || packagingId === '' ) {
        Swal.fire(
          'Error!',
          'Please input all the data',
          'error'
        );
        return;
      }

      let html = "<div class='row mt-10 product-row product_id-" + productId + "'>";
      html += "  <div class='col-2'>";
      html += "    <input type='hidden' class='form-control' value='" + productId + "'>";
      html += productText;
      html += "  <div class='col-1'>";
      html += "    <input type='hidden' name='qty[]' class='form-control' value='" + qty + "'>";
      html += qty;
      html += "  </div>";
      html += "  <div class='col-3'>";
      html += "    <input type='hidden' name='packaging_id[]' class='form-control' value='" + packagingId + "'>";
      html += packagingText;
      html += "  </div>";
      html += "  <div class='col-1'>";
      html += "    <button type='button' id='buttonDeleteProduct' class='btn btn-danger'><em class='fa fa-minus'></em></button>";
      html += "  </div>";
      html += "</div>";
      
      if ($('.product-row.product_id-' + productId).length > 0) {
        $('body').find('.product-row.product_id-' + productId + ':last').after(html);
      } else {
        $('body').find('.product-list').append(html);
      }

      $('.select-product[data-index=0]').val('').change();
      $('.input-qty[data-index=0]').val('');
      $('.select-packaging[data-index=0]').val('').change();

      $('.select-product_id[data-index=0]').select2('focus');

      productCount++;
    });

    $(document).on('click','#buttonDeleteProduct',function(){
      $(this).parents(".product-row").remove();
    });

    var param = [];
    param["brand_lokal_id"] = "";

    loadProduct({});

    $(document).on('change','.select-brand',function(){
      if ($(this).val() === '') return;

      param["brand_lokal_id"] = $(this).val();
      loadProduct({
        brand_lokal_id:param["brand_lokal_id"],
        index: $(this).data("index")
      })
    })

    function loadProduct(param){
      $.ajax({
        url : '{{route('superuser.gudang.purchase_order.get_product')}}',
        method : "GET",
        data : param,
        dataType : "JSON",
        success : function(resp){
          let option = "";
          let option2 = "";
          option = '<option value="">Select Product</option>';
          $.each(resp.Data,function(i,e){
            option += '<option value="'+e.id+'">'+e.productCode+' - '+e.productName+' ('+e.packName+')</option>';
          })
          $('.select-product[data-index=' + param.index + ']').html(option);
        },
        error : function(){
          alert("Cek Koneksi Internet");
        }
      })
    }
  });
</script>
@endpush
