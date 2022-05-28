@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.canvasing.index') }}">Canvasing</a>
  <span class="breadcrumb-item active">Edit Canvasing</span>
</nav>
<div id="alert-block"></div>
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
    <div class="row">
      <div class="col-12">
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Warehouse</label>
          <div class="col-md-8">
            <input type="text" name="warehouse" value="{{$result->warehouse->name ?? ''}}" class="form-control" readonly>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-12">
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Sales</label>
          <div class="col-md-8">
            <input type="text" name="sales" value="{{$result->sales->name ?? ''}}" class="form-control" readonly>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-12">
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Address</label>
          <div class="col-md-8">
            <input type="text" name="address" class="form-control"  value="{{$result->address}}" readonly>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="block">
  <div class="block-content">
    <form id="frmSaveItem">
      @csrf
      <input type="hidden" name="canvasing_id" value="{{$result->id}}">
      <div class="row">
        <div class="col-12">
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Item<span class="text-danger">*</span></label>
            <div class="col-md-8">
              <select class="form-control js-select2" name="product_id">
                <option value="">==Select item==</option>
                @foreach($product as $index => $row)
                <option value="{{$row->id}}">{{$row->code}} - {{$row->name}}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Qty<span class="text-danger">*</span></label>
            <div class="col-md-8">
              <input type="number" name="qty" class="form-control">
            </div>
          </div>
        </div>
      </div>
      <div class="row mb-30">
        <div class="col-12">
          <button class="btn btn-primary btn-md" type="submit" disabled="disabled"><i class="fa fa-save"></i> Save Item</button>
        </div>
      </div>
    </form>
  </div>
</div>
<div class="block">
  <div class="block-content">
    <div class="row">
      <div class="col-12">
        <div class="table table-responsive">
          <table class="table table-striped table-bordered">
            <thead>
              <th>Code</th>
              <th>Item</th>
              <th>Qty</th>
              <th>Aksi</th>
            </thead>
            <tbody>
              @if(count($result->canvasing_item) <= 0)
                <tr>
                  <td colspan="4" align="center">Tidak ada data ditemukan</td>
                </tr>
              @endif
              @foreach($result->canvasing_item as $index => $row)
                <tr>
                  <td>{{$row->product->code ?? ''}}</td>
                  <td>{{$row->product->name ?? ''}}</td>
                  <td>{{$row->qty}}</td>
                  <td>
                    <a href="{{route('superuser.penjualan.canvasing.edit_item',$row->id)}}" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-edit"></i> Edit</a>
                    <a href="#" class="btn btn-danger btn-sm btn-flat btn-delete" data-id="{{$row->id}}"><i class="fa fa-trash"></i> Delete</a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="row mb-30">
      <div class="col-12">
        <a href="{{route('superuser.penjualan.canvasing.index')}}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Back</a>
        <a href="#" class="btn btn-success btn-sent" data-id="{{$result->id}}"><i class="fa fa-send"></i> Sent</a>
      </div>
    </div>
  </div>
</div>
<form method="post" action="{{route('superuser.penjualan.canvasing.destroy_item')}}" id="frmDestroyItem">
    @csrf
    <input type="hidden" name="id">
</form>
<form method="post" action="{{route('superuser.penjualan.canvasing.sent')}}" id="frmSent">
    @csrf
    <input type="hidden" name="id">
</form>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script>
  $(function(){
    $('button[type="submit"]').removeAttr('disabled');

    $('.js-select2').select2();

    $(document).on('click','.btn-sent',function(){
      if(confirm("Apakah anda yakin ingin mengubah status sales mutation ini ? ")){
        let id = $(this).data('id');
        $('#frmSent').find('input[name="id"]').val(id);
        $('#frmSent').submit();
      }
    })

    $(document).on('click','.btn-delete',function(){
      if(confirm("Apakah anda yakin ingin menghapus item ini ? ")){
        let id = $(this).data('id');
        $('#frmDestroyItem').find('input[name="id"]').val(id);
        $('#frmDestroyItem').submit();
      }
    })

    $(document).on('submit','#frmSaveItem',function(e){
      e.preventDefault();
      if(confirm("Apakah anda yakin ingin menyimpan item ini ?")){
        let _form = $('#frmSaveItem');
        $.ajax({
          url : '{{route('superuser.penjualan.canvasing.store_item')}}',
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
                  location.reload();
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