@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.canvasing.index') }}">Canvasing</a>
  <span class="breadcrumb-item active">Edit Item Canvasing</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-content">
    <form id="frmUpdate" action="#">
    @csrf
    <input type="hidden" name="id" value="{{$result->id}}">
    <div class="row">
      <div class="col-12">
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Item<span class="text-danger">*</span></label>
          <div class="col-md-8">
            <select class="form-control js-select2" name="product_id">
              <option value="">==Select item==</option>
              @foreach($product as $index => $row)
              <?php
                $selected = "";
                if($result->product_id == $row->id){
                  $selected = "selected";
                }
              ?>
              <option value="{{$row->id}}" <?= $selected; ?>>{{$row->code}} - {{$row->name}}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-12">
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Qty<span class="text-danger">*</span></label>
          <div class="col-md-8">
            <input type="number" name="qty" class="form-control" value="{{$result->qty}}">
          </div>
        </div>
      </div>
    </div>
    <div class="row mb-30">
      <div class="col-12">
        <a href="{{route('superuser.penjualan.canvasing.index')}}" class="btn btn-warning  btn-md text-white"><i class="fa fa-arrow-left"></i> Back</a>
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

    $(document).on('submit','#frmUpdate',function(e){
      e.preventDefault();
      if(confirm("Apakah anda yakin ingin mengubah item ini ?")){
        let _form = $('#frmUpdate');
        $.ajax({
          url : '{{route('superuser.penjualan.canvasing.update_item')}}',
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
                  let token = getToken('{{route('superuser.penjualan.canvasing.edit',$result->canvasing_id)}}');
                  document.location.href = '{{route('superuser.penjualan.canvasing.edit',$result->canvasing_id)}}' + '?token='+token;
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