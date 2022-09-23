@extends('superuser.app')
@push('styles')
  <link rel="stylesheet" href="{{ asset('superuser_assets/css/page/packaging-order.css') }}">
  <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
@endpush

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.packing_order.index') }}">Packing Order</a>
  <span class="breadcrumb-item active">Create Packing Order</span>
</nav>
<div id="alert-block"></div>

<div class="block">
  <div class="block-content wizard">
    <form id="frmCreate" action="#">
    @csrf

      <div class="row steps mb-3">
        <div class="col-4 text-center step active" id="step1">Konfirmasi SO</div>
        <div class="col-4 text-center step" id="step2">Detail Kiriman</div>
        <div class="col-4 text-center step" id="step3">Finalisasi dan Cetak</div>
      </div>

      <div class="row step-container active" id="step1Container">
        <div class="col-12">

          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label">SO Referensi</label>
            <div class="col-6 col-md-9">
              Ini SO referensi
            </div>
          </div>

          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label">Packaging Order</label>
            <div class="col-6 col-md-9">
              Packaging Order
            </div>
          </div>

          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label">Customer</label>
            <div class="col-6 col-md-9">
              Customer
            </div>
          </div>

          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label">Kategori Barang</label>
            <div class="col-6 col-md-9">
              <strong>Kategori Barang</strong>
            </div>
          </div>

        </div>
      </div>

    </form>
  </div>
</div>


<div class="block">
  <div class="block-content">
    <form id="frmCreate" action="#">
    @csrf
    <div class="row">
      <div class="col-12">
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Warehouse<span class="text-danger">*</span></label>
          <div class="col-md-8">
            <select class="form-control js-select2" name="warehouse_id">
              <option value="">==Select warehouse==</option>
              @foreach($warehouse as $index => $row)
                <option value="{{$row->id}}">{{$row->name}}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Customer<span class="text-danger">*</span></label>
          <div class="col-md-8">
            <select class="form-control js-select2 select-customer" name="customer_id">
              <option value="">==Select customer==</option>
              @foreach($customer as $index => $row)
                <option value="{{$row->id}}">{{$row->name}}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Address</label>
          <div class="col-md-8">
            <textarea class="form-control" readonly name="address" rows="1"></textarea>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Delivery</label>
          <div class="col-md-8">
            <select class="form-control js-select2 select-other-address" name="customer_other_address_id">
              <option value="">==Select customer other address==</option>
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Delivery Address</label>
          <div class="col-md-8">
            <textarea class="form-control" readonly name="delivery_address" rows="1"></textarea>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">IDR Rate<span class="text-danger">*</span></label>
          <div class="col-md-8">
            <input type="text" name="idr_rate" class="form-control" step="any">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Transaction<span class="text-danger">*</span></label>
          <div class="col-md-8">
            <select class="form-control js-select2" name="type_transaction">
              <option value="">==Select type transaction==</option>
              <option value="1">Cash</option>
              <option value="2">Tempo</option>
              <option value="3">Marketplace</option>
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Ekspedisi</label>
          <div class="col-md-8">
            <select class="form-control js-select2" name="vendor_id">
              <option value="">==Select ekspedisi==</option>
              @foreach($vendor as $index => $row)
              <option value="{{$row->id}}">{{$row->name}}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Note</label>
          <div class="col-md-8">
            <textarea class="form-control summernote" name="note"></textarea>
          </div>
        </div>
      </div>
    </div>
    <div class="row mb-30">
      <div class="col-12">
        <a href="{{route('superuser.penjualan.packing_order.index')}}" class="btn btn-warning  btn-md text-white"><i class="fa fa-arrow-left"></i> Back</a>
        <button class="btn btn-primary btn-md" type="submit" disabled="disabled"><i class="fa fa-save"></i> Save</button>
      </div>
    </div>
    </form>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script>
  $(function(){
    $('button[type="submit"]').removeAttr('disabled');

    summernote = $('.summernote').length;
    if(summernote > 0){
      $('.summernote').summernote({
          toolbar: [
             ['style', ['style']],
               ['font', ['bold', 'italic', 'underline', 'clear']],
               ['fontname', ['fontname']],
               ['color', ['color']],
               ['para', ['ul', 'ol', 'paragraph']],
          ],
      });
    }

    $('.js-select2').select2();

    $(document).on('submit','#frmCreate',function(e){
      e.preventDefault();
      if(confirm("Apakah anda yakin ingin menambahkan packing order ini?")){
        let _form = $('#frmCreate');
        $.ajax({
          url : '{{route('superuser.penjualan.packing_order.store')}}',
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
                document.location.href = '{{route('superuser.penjualan.packing_order.index')}}';
              })
             
            }
          },
          error : function(){
            alert('Cek Koneksi Internet');
          },
          complete : function(){
            $('button[type="submit"]').html('<i class="fa fa-save"> Save</i>');
          }
        })
      }
    })

    $(document).on('change','.select-customer',function(){
      let val = $(this).val();
      if(val != ""){
        customer_address(val);
      }else{
        $('textarea[name="address"]').val("");
        $('.select-other-address').html('<option value="">==Select customer other address==</option>');
        $('textarea[name="delivery_address"]').val("");
      }
    })
    $(document).on('change','.select-other-address',function(){
      let val = $(this).val();
      if(val != ""){
        customer_other_detail(val);
      }else{
        $('textarea[name="delivery_address"]').val("");
      }
    })
    function customer_address(id){
      ajaxcsrfscript();
      $.ajax({
        url : '{{route('superuser.penjualan.packing_order.ajax_customer_detail')}}',
        method : "POST",
        data : {id:id},
        dataType : "JSON",
        success : function(resp){
          if(resp.IsError == true){
            showToast('danger',resp.Message);
          }
          else{
            $('textarea[name="address"]').val(resp.Data.address);
            customer_other_address(id);
          }
        },
        error : function(){
          alert('Cek Koneksi Internet');
        },
      })
    }
    function customer_other_address(customer_id){
      ajaxcsrfscript();
      $.ajax({
        url : '{{route('superuser.penjualan.packing_order.ajax_customer_other_address')}}',
        method : "POST",
        data : {customer_id:customer_id},
        dataType : "JSON",
        success : function(resp){
          if(resp.IsError == true){
            showToast('danger',resp.Message);
          }
          else{
            let option = '<option value="">==Select customer other address==</option>';
            $.each(resp.Data,function(i,e){
              option += '<option value="'+e.id+'">'+e.label+'</option>';
            })
            $('.select-other-address').html(option);
          }
        },
        error : function(){
          alert('Cek Koneksi Internet');
        },
      })
    }
    function customer_other_detail(id){
      ajaxcsrfscript();
      $.ajax({
        url : '{{route('superuser.penjualan.packing_order.ajax_customer_other_address_detail')}}',
        method : "POST",
        data : {id:id},
        dataType : "JSON",
        success : function(resp){
          if(resp.IsError == true){
            showToast('danger',resp.Message);
          }
          else{
            $('textarea[name="delivery_address"]').val(resp.Data.address);
          }
        },
        error : function(){
          alert('Cek Koneksi Internet');
        },
      })
    }

    function changeStep(stepNumber) {
      $(".wizard .step").removeClass('active');
      $(".wizard .step-container").removeClass('active');

      $("#step" + stepNumber).addClass('active');
      $("#step" + stepNumber + "Container").addClass('active');
    }
  })
</script>
@endpush