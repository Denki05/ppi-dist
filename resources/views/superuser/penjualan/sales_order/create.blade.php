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
            <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Team Leader<span class="text-danger">*</span></label>
            <div class="col-md-8">
              <select class="form-control js-select2" name="sales_senior_id">
                <option value="">Pilih Team Leader</option>
                @foreach($sales as $index => $row)
                  <option value="{{$row->id}}">{{$row->name}}</option>
                @endforeach
              </select>
            </div>
          </div>
          @endif
          @if($step == 1 || $step == 2 || $step == 9)
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Salesman<span class="text-danger">*</span></label>
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
        <div class="block-header block-header-default">
          <h3 class="block-title">Add Product</h3>
          <a href="#" class="row-add">
            <button type="button" class="btn bg-gd-sea border-0 text-white">
              <i class="fa fa-plus mr-10"></i> Row
            </button>
          </a>
        </div>
        <div class="block-content">
          <table id="datatable" class="table table-striped table-vcenter">
            <thead>
              <tr>
                <th class="text-center">Counter</th>
                <th class="text-center">Product</th>
                <th class="text-center">Brand</th>
                <th class="text-center">Category</th>
                <th class="text-center">Qty</th>
                <th class="text-center">Pack</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
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

    var table = $('#datatable').DataTable({
        paging: false,
        bInfo : false,
        searching: false,
        columns: [
          {name: 'counter', "visible": false},
          {name: 'sku', orderable: false, width: "25%"},
          {name: 'name', orderable: false, searcable: false},
          {name: 'quantity', orderable: false, searcable: false, width: "5%"},
          {name: 'price', orderable: false, searcable: false},
          {name: 'total', orderable: false, searcable: false},
          {name: 'action', orderable: false, searcable: false, width: "5%"}
        ],
        'order' : [[0,'desc']]
    })

    var counter = 1;

    $('a.row-add').on( 'click', function (e) {
      e.preventDefault();
      
      table.row.add([
                    counter,
                    '<select class="js-select2 form-control js-ajax" id="sku['+counter+']" name="sku[]" data-placeholder="Select SKU" style="width:100%" required></select>',
                    '<span class="name"></span>',
                    '<input type="number" class="form-control" name="quantity[]" readonly required>',
                    '<input type="number" class="form-control" name="price[]" readonly required>',
                    '<input type="number" class="form-control" name="total[]" readonly>',
                    '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
                  ]).draw( false );
                  // $('.js-select2').select2()
                  initailizeSelect2();
      counter++;
    });

    function initailizeSelect2(){
      $(".js-ajax").select2({
        ajax: {
          url: '{{ route('superuser.penjualan.sales_order.search_sku') }}',
          dataType: 'json',
          delay: 250,
          data: function (params) {
            return {
              q: params.term,
              _token: "{{csrf_token()}}"
            };
          },
          cache: true
        },
        minimumInputLength: 3,
      });

      $('.js-ajax').on('select2:select', function (e) {
        var name = e.params.data.name;
        $(this).parents('tr').find('.name').text(name);
        $(this).parents('tr').find('input[name="quantity[]"]').removeAttr('readonly');
        $(this).parents('tr').find('input[name="price[]"]').removeAttr('readonly');
      });

    };

    $('#datatable tbody').on( 'click', '.row-delete', function (e) {
      e.preventDefault();
      
      table.row( $(this).parents('tr') ).remove().draw();
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