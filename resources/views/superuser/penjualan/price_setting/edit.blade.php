@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.product_category.index') }}">Setting Price</a>
  <span class="breadcrumb-item active">Edit Setting Price</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-content">
    <div class="row mb-30">
      <div class="col-12">
        <div class="row">
          <div class="col-lg-1">
            <strong>Type</strong>
          </div>
          <div class="col-lg-11">
            : {{$result->type->name ?? ''}}
          </div>
        </div>
        <div class="row">
          <div class="col-lg-1">
            <strong>Category</strong>
          </div>
          <div class="col-lg-11">
            : {{$result->category->name ?? ''}}
          </div>
        </div>
        <div class="row">
          <div class="col-lg-1">
            <strong>Product</strong>
          </div>
          <div class="col-lg-11">
            : {{$result->name}}
          </div>
        </div>
      </div>
    </div>
    <form id="frmEdit" action="#">
    @csrf
    <input type="hidden" name="id" value="{{$result->id}}">
    <div class="row">
      <div class="col-12">
        <div class="table-responsive">
          <table class="table table-striped table-bordered" >
            <thead>
              <th>Old Price</th>
              <th>New Price</th>
            </thead>
            <tbody>
              <tr>
                <td>Buying Price : <strong>{{$result->buying_price}}</strong></td>
                <td>
                  <input type="text" name="buying_price" class="form-control" placeholder="Buying Price">
                </td>
              </tr>
              <tr>
                <td>Selling Price : <strong>{{$result->selling_price}}</strong></td>
                <td>
                  <input type="text" name="selling_price" class="form-control" placeholder="Selling Price">
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="row mb-30">
      <div class="col-12">
        <a href="{{route('superuser.penjualan.setting_price.index')}}" class="btn btn-warning  btn-md text-white"><i class="fa fa-arrow-left"></i> Back</a>
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
  $(document).ready(function () {
    $('button[type="submit"]').removeAttr('disabled');

    $(document).on('submit','#frmEdit',function(e){
      e.preventDefault();
      if(confirm("Yakin ?")){
        let _form = $('#frmEdit');
        $.ajax({
          url : '{{route('superuser.penjualan.setting_price.update')}}',
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
                  document.location.href = '{{route('superuser.penjualan.setting_price.index')}}'; 
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
</script>
@endpush