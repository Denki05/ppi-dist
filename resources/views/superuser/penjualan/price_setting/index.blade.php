@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <span class="breadcrumb-item active">Price Setting</span>
</nav>
@if($errors->any())
<div class="alert alert-danger alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Error</h3>
  @foreach ($errors->all() as $error)
  <p class="mb-0">{{ $error }}</p>
  @endforeach
</div>
@endif
<div class="block">
  <hr class="my-20">
  <div class="block-content block-content-full">
    <form method="get" action="{{ route('superuser.penjualan.setting_price.index') }}">
      <div class="row">
        <div class="col-lg-3">
          <div class="form-group">
            <select class="form-control js-select2" name="id_product">
              <option value="">==All Products==</option>
              @foreach($product as $index => $row)
                <option value="{{$row->id}}">{{$row->name}}</option>
              @endforeach
            </select>
          </div>          
        </div>
        <div class="col-lg-3">
          <div class="form-group">
            <select class="form-control js-select2" name="id_category">
              <option value="">==All Category==</option>
              @foreach($product_category as $index => $row)
                <option value="{{$row->id}}">{{$row->name}}</option>
              @endforeach
            </select>
          </div>          
        </div>
        <div class="col-lg-3">
          <div class="form-group">
            <select class="form-control js-select2" name="id_type">
              <option value="">==All Type==</option>
              @foreach($product_type as $index => $row)
                <option value="{{$row->id}}">{{$row->name}}</option>
              @endforeach
            </select>
          </div>          
        </div>
        <div class="col-lg-3">
          <button class="btn btn-primary"><i class="fa fa-search"></i></button>
        </div>
      </div>
      <div class="row mb-30">
        <div class="col-12">
          <table class="table table-striped" id="datatables">
            <thead>
              <tr>
                <th>
                  <input type="checkbox" name="" class="select-all" style="width: 20px;height: 20px;">
                </th>
                <th>Code</th>
                <th>Product</th>
                <th>Buying Price</th>
                <th>Selling Price</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($table as $index => $row)
                <tr>
                  <td>
                    <input type="checkbox" value="{{$row->id}}" style="width: 20px;height: 20px;">
                  </td>
                  <td>{{$row->code}}</td>
                  <td>{{$row->name}}</td>
                  <td>{{$row->buying_price}}</td>
                  <td>{{$row->selling_price}}</td>
                  <td>
                    <a href="{{route('superuser.penjualan.setting_price.edit',$row->id)}}" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-edit"></i> Edit</a>
                    <a href="{{route('superuser.penjualan.setting_price.history',$row->id)}}" class="btn btn-info btn-sm btn-flat"><i class="fa fa-history"></i> History</a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      
      <div class="row mb-30">
        <div class="col-lg-2">
          <select class="form-control" name="limit">
            <option value="10">10</option>
            <option value="30">30</option>
            <option value="50">50</option>
            <option value="100">100</option>
            <option value="1000">1000</option>
          </select>
        </div>
        <div class="col-lg-10">
          {{$table->links()}}
        </div>
      </div>
      <div class="row mb-30">
        <div class="col-12">
          <button type="button" class="btn btn-warning btn-md btn-print-product"><i class="fa fa-print"></i> Print Product</button>
          <button type="button" class="btn btn-warning btn-md btn-print-product-price"><i class="fa fa-print"></i> Print Product Price</button>
        </div>
      </div>
    </form>
  </div>
</div>

<form class="d-none" id="frmPrintProduct" method="post" action="{{route('superuser.penjualan.setting_price.print_product')}}" target="_blank">
  @csrf
  <input type="hidden" name="id_product">
  <input type="hidden" name="id_category">
  <input type="hidden" name="id_type">
  <div class="area-checkbox">
    
  </div>
</form>

<form class="d-none" id="frmPrintProductPrice" method="post" action="{{route('superuser.penjualan.setting_price.print_product_price')}}" target="_blank">
  @csrf
  <input type="hidden" name="id_product">
  <input type="hidden" name="id_category">
  <input type="hidden" name="id_type">
  <div class="area-checkbox">
    
  </div>
</form>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')

@push('scripts')

  <script type="text/javascript">
    
    $(function(){
      let param = [];
      param["checkbox"] = [];
      param["id_product"] = '<?= $_GET["id_product"] ?? null ?>';
      param["id_category"] = '<?= $_GET["id_category"] ?? null ?>';
      param["id_type"] = '<?= $_GET["id_type"] ?? null ?>';

      $('#datatables').DataTable( {
        "paging":   false,
        "ordering": true,
        "info":     false,
        "searching" : false,
        "columnDefs": [{
          "targets": 0,
          "orderable": false
        }]
      });

      $('.js-select2').select2();

      $(document).on('click','.select-all',function(){
        if($(this).is(':checked')){
          isSelected();
        }
        else{
          removeSelected();
        }
      })


      $(document).on('click','.btn-print-product',function(){
        
        param["checkbox"] = [];
        $('tbody').find('input[type="checkbox"]').each(function(i,e){
          if(this.checked){
            param["checkbox"].push(this.value);
          }
        })

        let push_checkbox = "";
        $('#frmPrintProduct').find('input[name="id_category"]').val(param["id_category"]);
        $('#frmPrintProduct').find('input[name="id_product"]').val(param["id_product"]);
        $('#frmPrintProduct').find('input[name="id_type"]').val(param["id_type"]);


        $.each(param["checkbox"],function(i,e){
            push_checkbox += '<input name="checkbox[]" value="'+param['checkbox'][i]+'">';
        })

        if(param["checkbox"].length > 0){
          $('#frmPrintProduct').find('.area-checkbox').html(push_checkbox);
        }

        $('#frmPrintProduct').submit();

      })

      $(document).on('click','.btn-print-product-price',function(){

        param["checkbox"] = [];
        $('tbody').find('input[type="checkbox"]').each(function(i,e){
          if(this.checked){
            param["checkbox"].push(this.value);
          }
        })

        let push_checkbox = "";
        $('#frmPrintProductPrice').find('input[name="id_category"]').val(param["id_category"]);
        $('#frmPrintProductPrice').find('input[name="id_product"]').val(param["id_product"]);
        $('#frmPrintProductPrice').find('input[name="id_type"]').val(param["id_type"]);


        $.each(param["checkbox"],function(i,e){
            push_checkbox += '<input name="checkbox[]" value="'+param['checkbox'][i]+'">';
        })

        if(param["checkbox"].length > 0){
          $('#frmPrintProductPrice').find('.area-checkbox').html(push_checkbox);
        }

        $('#frmPrintProductPrice').submit();

      })
    })
    function isSelected(){
      $('tbody').find('input[type="checkbox"]').attr('checked','checked');
      $('tbody').find('input[type="checkbox"]').prop('checked', true);
    }
    function removeSelected(){
      $('tbody').find('input[type="checkbox"]').removeAttr('checked');
     $('tbody').find('input[type="checkbox"]').prop('checked', false);
    }
  </script>
@endpush
