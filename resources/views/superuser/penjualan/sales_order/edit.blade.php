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
<div id="alert-block"></div>
<div class="block">
  <div class="block-content">
    <form id="frmEditSOMaster" method="post" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="id" value="{{$result->id}}">
      <input type="hidden" name="step" value="{{$step}}">
      <input type="hidden" name="customer_id" id="customer_id" value="{{ $result->customer->id }}">
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
                          <option value="{{$row->id}}" @if($result->sales_senior_id == $row->id && $result->so_for == 1) selected @endif>{{$row->name}}</option>
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
                          <option value="{{$row->id}}" @if ($result->sales_id == $row->id && $result->so_for == 1) selected  @endif>{{$row->name}}</option>
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
                        <option value="">Transaksi</option>
                        <option value="1" <?= $selected1 ?>>Cash</option>
                        <option value="2" <?= $selected2 ?>>Tempo</option>
                        <option value="3" <?= $selected3 ?>>Marketplace</option>
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
                        <input type="number" name="idr_rate" class="form-control" value="{{$result->idr_rate}}">
                      </div>
                    </div>
                    @endif
                </div>
                <div class="col-md-6">
                    @if($step == 1)
                      <div class="form-group row">
                        <label class="col-md-4 col-form-label text-right">Invoice Brand<span class="text-danger">*</span></label>
                        <div class="col-md-6">
                          <select class="form-control js-select2 brand_type" name="brand_type">
                            <option value="">Pilih Brand</option>
                            @foreach ($brand as $index)
                            <option value="{{ $index->id }}" @if($result->brand_type == $index->id &&$result->so_for == 1) selected @endif>{{ $index->brand_name }}</option>
                            <option value="{{$row->id}}" @if($result->sales_senior_id == $row->id && $result->so_for == 1) selected @endif>{{$row->name}}</option>
                            @endforeach
                          </select>
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
        <div class="block">
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
                  <th class="text-center">Cari Produk</th>
                  <th class="text-center">Produk</th>
                  <th class="text-center">Acuan(USD)</th>
                  <th class="text-center">Quantity</th>
                  <th class="text-center">Pack NO</th>
                  <th class="text-center">Kemasan</th>
                  <th class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
              @foreach ($result->so_detail as $detail)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td><select class="js-select2 form-control js-ajax" id="product_id[{{ $loop->iteration }}]" name="product_id[]" data-placeholder="Select SKU" style="width:100%" required><option value="{{ $detail->product_id }}">{{ $detail->product_pack->code }}</option></select></td>
                  <td><span class="name">{{ $detail->product_pack->name }}</span><input type="hidden" class="form-control" name="edit[]" value="{{ $detail->id }}"></td>
                  <td><span class="name">{{ $detail->product_pack->selling_price }}</span><input type="hidden" class="form-control" name="edit[]" value="{{ $detail->id }}"></td>
                  <td><input type="text" class="form-control" name="qty[]" value="{{ $detail->qty }}"></td>
                  <td><input type="text" class="form-control" name="packaging[]" value="{{ $detail->product_pack->kemasan()->id }}" readonly></td>
                  <td><span class="name">{{ $detail->product_pack->kemasan()->pack_name }}</span></td>
                  <td><a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a></td>
                </tr>
              @endforeach
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
          <button class="btn btn-primary btn-md" type="submit" disabled="disabled"><i class="fa fa-save"></i> Save</button>
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
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $('button[type="submit"]').removeAttr('disabled');

    $('.js-select2').select2();

    var table = $('#datatable').DataTable({
        paging: false,
        bInfo : false,
        searching: false,
        columns: [
          {name: 'counter', "visible": false},
          {name: 'product', orderable: false, width: "25%"},
          {name: 'name', orderable: false, searcable: false, width: "20%"},
          {name: 'price', orderable: false, searcable: false, width: "10%"},
          {name: 'qty', orderable: false, searcable: false, width: "5%"},
          {name: 'packaging', orderable: false, searcable: false, width: "5%"},
          {name: 'kemasan', orderable: false, searcable: false, width: "20%"},
          {name: 'action', orderable: false, searcable: false, width: "5%"}
        ],
        'order' : [[0,'desc']]
    })

    var counter = 1;
    
    $('a.row-add').on( 'click', function (e) {
      e.preventDefault();
      
      table.row.add([
                    counter,
                    '<select class="js-select2 form-control js-ajax" id="product['+counter+']" name="product_id[]" data-placeholder="Select Product" style="width:100%" required></select>',
                    '<span class="name"></span>',
                    '<span class="price"></span>',
                    '<input type="number" class="form-control" name="qty[]" value="1" readonly required>',
                    '<input type="text" class="form-control pack" name="packaging[]" readonly required>',
                    '<span class="packname"></span>',
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
          results: function (data, params) {
            return {
              results: data
            };
          },
          cache: true
        },
        minimumInputLength: 3,
        escapeMarkup: function (markup) { return markup; },
        templateResult: formatData,
        placeholder: "Select Product",
      });

      function formatData (data) {
        if (data.loading) return data.productName;

        markup = data.text + '&nbsp - &nbsp' + data.productName + '&nbsp - &nbsp' + data.packagingName;

        return markup;
      };

      function formatDataSelection (data) {
        return data.productName;
      };

      $('.js-ajax').on('select2:select', function (e) {
        var name = e.params.data.productName;
        var price = e.params.data.productPrice;
        var no = e.params.data.packNo;
        var pack = e.params.data.packagingName;
        $(this).parents('tr').find('.name').text(name);
        $(this).parents('tr').find('.packname').text(pack);
        $(this).parents('tr').find('.pack').val(no);
        $(this).parents('tr').find('.price').text('$' + price);
        $(this).parents('tr').find('input[name="qty[]"]').removeAttr('readonly');
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

    // $(document).on('click','.btn-simpan-dan-ajukan-ke-lanjutan',function(){
    //   $('#frmCreate').find('input[name="ajukankelanjutan"]').val(1);
    //   $('#frmCreate').submit();
    // })

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

  function formatRupiah(angka, prefix){
    angka = angka.toString();
    var number_string = angka.replace(/[^,\d]/g, '').toString(),
    split       = number_string.split(','),
    sisa        = split[0].length % 3,
    rupiah        = split[0].substr(0, sisa),
    ribuan        = split[0].substr(sisa).match(/\d{3}/gi);
   
    // tambahkan titik jika yang di input sudah menjadi angka ribuan
    if(ribuan){
      separator = sisa ? '.' : '';
      rupiah += separator + ribuan.join('.');
    }
   
    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
  };

  $(document).on('keyup','.formatRupiah',function(){
      let val = $(this).val();
      $(this).val(formatRupiah(val));
  })
</script>
@endpush