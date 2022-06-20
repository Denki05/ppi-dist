@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Gudang</span>
  <span class="breadcrumb-item">Stock Adjustment</span>
  <span class="breadcrumb-item active">Create</span>
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
  <hr class="my-20">
  <div class="block-content block-content-full">
    <div class="row">
      <div class="col-lg-2">
        Warehouse Code
      </div>
      <div class="col-lg-10">
        : {{$warehouse->code}}
      </div>
    </div>
    <div class="row">
      <div class="col-lg-2">
        Warehouse Name
      </div>
      <div class="col-lg-10">
        : {{$warehouse->name}}
      </div>
    </div>
  </div>
</div>
<div class="block">
  <div class="block-content block-content-full">
    <form id="frmUpdateStock" method="post">
      @csrf
      <input type="hidden" name="warehouse_id" value="{{$warehouse->id}}">
      <div class="row">
        <div class="col-12">
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right"> Select Product<span class="text-danger">*</span></label>
            <div class="col-md-8">
              <select class="form-control js-select2 select-product" name="product_id">
                <option value="">==Select Product==</option>
                @foreach($product as $index => $row)
                  <option value="{{$row->product->id}}">{{$row->product->code ?? ''}} - {{$row->product->name ?? ''}}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Prev Stock<span class="text-danger">*</span></label>
            <div class="col-md-8">
              <input type="number" name="prev" class="form-control" readonly>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Plus Stock</label>
            <div class="col-md-8">
              <input type="number" name="plus" class="form-control count">
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Min Stock</label>
            <div class="col-md-8">
              <input type="number" name="min" class="form-control count">
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Update Stock<span class="text-danger">*</span></label>
            <div class="col-md-8">
              <input type="number" name="update" class="form-control" readonly>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Note</label>
            <div class="col-md-8">
              <input type="text" name="note" class="form-control" >
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-12">
          <a href="{{route('superuser.gudang.stock_adjustment.index')}}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Back</a>
          <button type="submit" class="btn btn-primary"><i class="fa fa-save"> Save </i></button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

<!-- Modal -->


@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')

@push('scripts')

  <script type="text/javascript">
    let warehouse_id = "{{$warehouse->id}}";

    $(function(){
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

    

      $(document).on('change','.select-product',function(){
        let val = $(this).val();
        if(val !== ""){
          check_product_warehouse(val);
        }
        else{
          $('input[name="prev"]').val(0);
          $('input[name="update"]').val(0);
        }
      })

      $(document).on('keyup','.count',function(){
        total();
      })

      $(document).on('submit','#frmUpdateStock',function(e){
        e.preventDefault();
        if(confirm("Yakin ?")){
          let _form = $('#frmUpdateStock');
          $.ajax({
            url : '{{route('superuser.gudang.stock_adjustment.store')}}',
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
                    window.location.href = '{{route('superuser.gudang.stock_adjustment.index')}}';
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



    });

    function check_product_warehouse(product_id){
      ajaxcsrfscript();
      $.ajax({
        url : '{{route('superuser.gudang.stock_adjustment.check_product_warehouse')}}',
        method : "POST",
        data : {warehouse_id : warehouse_id , product_id : product_id},
        dataType : "JSON",
        success : function(resp){
          if(resp.IsError == true){
            showToast('danger',resp.Message);
          }
          else{
            $('input[name="prev"]').val(resp.Data.quantity);
            $('input[name="update"]').val(resp.Data.quantity);
          }
        },
        error : function(){
          alert('Cek Koneksi Internet');
        },
      })
    }
    function total(){
      let prev = parseInt($('input[name="prev"]').val());
      let plus = parseInt($('input[name="plus"]').val());
      let min = parseInt($('input[name="min"]').val());

      if(isNaN(prev)){
        prev = 0;
      }
      if(isNaN(plus)){
        plus = 0;
      }
      if(isNaN(min)){
        min = 0;
      }

      update = ( prev + plus ) - min;

      $('input[name="update"]').val(update);
    }
  </script>
@endpush
