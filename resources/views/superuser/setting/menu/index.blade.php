@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Setting</span>
  <span class="breadcrumb-item active">Menu</span>
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
  <div class="block-content block-content-full">
      <div class="row">
        <div class="col-12">
          <a href="#" class="btn btn-primary btn-add"><i class="fa fa-plus"></i> Add Menu</a>
        </div>
      </div>
  </div>
</div>
<div class="block">
  <hr class="my-20">
  <div class="block-content block-content-full">
      <form method="get" action="{{ route('superuser.setting.menu.index') }}">
        <div class="row">
          <div class="col-lg-6"></div>
          <div class="col-lg-6">
            <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Keyword" name="search">
                <div class="input-group-append">
                  <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                </div>
              </div>
          </div>
        </div>
      </form>
      <div class="row mt-20">
        <div class="col-12">
          <div class="table-responsive">
            <table class="table table-striped" id="datatables">
              <thead>
                <th>#</th>
                <th>Name</th>
                <th>Route Name</th>
                <th>Action</th>
              </thead>
              <tbody>
                @foreach($table as $index => $row)
                <tr>
                  <td>{{$table->firstItem() + $index}}</td>
                  <td>{{$row->name}}</td>
                  <td>{{$row->route_name}}</td>
                  <td>
                    <a href="#" class="btn btn-primary btn-sm btn-flat btn-edit" data-id="{{$row->id}}" data-name="{{$row->name}}" data-route="{{$row->route_name}}"><i class="fa fa-edit"></i> Edit</a>
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
          {{$table->links()}}
        </div>
      </div>
  </div>

<form id="frmDestroy" method="post" action="{{route('superuser.setting.menu.destroy')}}">
  @csrf
  <input type="hidden" name="id">
</form>
</div>

@include('superuser.setting.menu.modal')
@endsection


@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')

@push('scripts')

  <script type="text/javascript">
    
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

      $(document).on('click','.btn-add',function(){
        $('#modalCreate').modal('show');
      })

      $(document).on('click','.btn-edit',function(){
        let id = $(this).data('id');
        let name = $(this).data('name');
        let route_name = $(this).data('route');

        $('#frmEdit').find('input[name="id"]').val(id);
        $('#frmEdit').find('input[name="name"]').val(name);
        $('#frmEdit').find('input[name="route_name"]').val(route_name);
        $('#modalEdit').modal('show');
      })

      $(document).on('click','.btn-delete',function(){
        if(confirm("Apakah anda yakin ingin menghapus menu ini ? ")){
          let id = $(this).data('id');
          $('#frmDestroy').find('input[name="id"]').val(id);
          $('#frmDestroy').submit();
        }
      })

      $(document).on('submit','#frmCreate',function(e){
        e.preventDefault();
        if(confirm("Apakah anda yakin ingin menambakan menu ini ?")){
          let _form = $('#frmCreate');
          $.ajax({
            url : '{{route('superuser.setting.menu.store')}}',
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
                    document.location.href = '{{route('superuser.setting.menu.index')}}';
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

      $(document).on('submit','#frmEdit',function(e){
        e.preventDefault();
        if(confirm("Apakah anda yakin ingin mengubah menu ini ?")){
          let _form = $('#frmEdit');
          $.ajax({
            url : '{{route('superuser.setting.menu.update')}}',
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
                    document.location.href = '{{route('superuser.setting.menu.index')}}';
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
    
    });
  </script>
@endpush
