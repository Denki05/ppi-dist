@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.delivery_order_mutation.index') }}">Delivery Order Mutation</a>
  <span class="breadcrumb-item active">Create Delivery Order Mutation</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-content">
    <form id="frmCreate" action="#">
    @csrf
    <div class="row">
      <div class="col-12">
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right" for="name">Origin Warehouse<span class="text-danger">*</span></label>
          <div class="col-md-8">
            <select class="form-control js-select2" name="origin_warehouse_id">
              <option value="">==Select origin warehouse==</option>
              @foreach($warehouse as $index => $row)
                <option value="{{$row->id}}">{{$row->name}}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right" for="name">Destination Warehouse <span class="text-danger">*</span></label>
          <div class="col-md-8">
            <select class="form-control js-select2" name="destination_warehouse_id">
              <option value="">==Select destination warehouse==</option>
              @foreach($warehouse as $index => $row)
                <option value="{{$row->id}}">{{$row->name}}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Address<span class="text-danger">*</span></label>
          <div class="col-md-8">
            <input type="text" name="address" class="form-control">
          </div>
        </div>
      </div>
    </div>
    <div class="row mb-30">
      <div class="col-12">
        <a href="{{route('superuser.penjualan.delivery_order_mutation.index')}}" class="btn btn-warning  btn-md text-white"><i class="fa fa-arrow-left"></i> Back</a>
        <button class="btn btn-primary btn-md" type="submit" disabled="disabled"><i class="fa fa-save"></i> Save</button>
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
    $('button[type="submit"]').removeAttr('disabled');

    $('.js-select2').select2();

    $(document).on('submit','#frmCreate',function(e){
      e.preventDefault();
      if(confirm("Yakin ?")){
        let _form = $('#frmCreate');
        $.ajax({
          url : '{{route('superuser.penjualan.delivery_order_mutation.store')}}',
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
                  document.location.href = '{{route('superuser.penjualan.delivery_order_mutation.index')}}';
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
</script>
@endpush