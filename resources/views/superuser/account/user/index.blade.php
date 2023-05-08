@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Account</span>
  <span class="breadcrumb-item active">User</span>
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
          <a href="{{route('superuser.account.user.create')}}" class="btn btn-primary"><i class="fa fa-plus"></i> Add User</a>
        </div>
      </div>
  </div>
</div>
<div class="block">
  <hr class="my-20">
  <div class="block-content block-content-full">
      <form>
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
      <div class="row mb-30">
        <div class="col-12">
          <table class="table table-striped" id="datatables">
            <thead>
              <tr>
                <th>#</th>
                <th>Email</th>
                <th>Username</th>
                <th>Division</th>
                <th>Is Active</th>
                <th>Created At</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($table as $index => $row)
                <tr>
                  <td>{{$table->firstItem() + $index}}</td>
                  <td>{{$row->email}}</td>
                  <td>{{$row->username}}</td>
                  <td>{{$row->division}}</td>
                  <td>{{$row->is_active()->scalar ?? ''}}</td>
                  <td>{{$row->created_at}}</td>
                  <td>
                    <a href="{{route('superuser.account.user.edit',$row->id)}}" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-edit"></i> Edit</a>
                    @if($row->is_active == 1)
                    <a href="#" class="btn btn-danger btn-sm btn-flat btn-delete" data-id="{{$row->id}}"><i class="fa fa-trash"></i> Delete</a>
                   	@else
                   	<a href="#" class="btn btn-warning btn-sm btn-flat btn-restore" data-id="{{$row->id}}"><i class="fa fa-reload"></i> Restore</a>
                   	@endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      
      <div class="row mb-30">
        <div class="col-12">
          {{$table->links()}}
        </div>
      </div>
  </div>
</div>
<form method="post" action="{{route('superuser.account.user.destroy')}}" id="frmDestroy">
    @csrf
    <input type="hidden" name="id">
</form>
<form method="post" action="{{route('superuser.account.user.restore')}}" id="frmRestore">
    @csrf
    <input type="hidden" name="id">
</form>
@endsection

<!-- Modal -->


@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')

@push('scripts')

  <script type="text/javascript">
    $(function(){
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

        $(document).on('click','.btn-delete',function(){
          if(confirm("Apakah anda yakin ingin menghapus user ini ? ")){
            let id = $(this).data('id');
            $('#frmDestroy').find('input[name="id"]').val(id);
            $('#frmDestroy').submit();
          }
        })

        $(document).on('click','.btn-restore',function(){
          if(confirm("Apakah anda yakin ingin mengaktifkan ini ? ")){
            let id = $(this).data('id');
            $('#frmRestore').find('input[name="id"]').val(id);
            $('#frmRestore').submit();
          }
        })
      });
    })
  </script>
@endpush
