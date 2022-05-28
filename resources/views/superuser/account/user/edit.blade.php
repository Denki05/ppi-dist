@extends('superuser.app')
@push('styles')
<style type="text/css">
  input[type="checkbox"]{
    text-align: center; /* center checkbox horizontally */
    vertical-align: middle; /* center checkbox vertically */
    width: 20px;
    height: 20px;
  }
</style>
@endpush

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Account</span>
  <a class="breadcrumb-item" href="{{ route('superuser.account.user.index') }}">User</a>
  <span class="breadcrumb-item active">Edit User</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-content">
    <form id="frmEdit" action="#">
    @csrf
    <input type="hidden" name="user_id" value="{{$result->id}}">
    <div class="row">
      <div class="col-12">
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Name<span class="text-danger">*</span></label>
          <div class="col-md-8">
            <input type="text" name="name" class="form-control" value="{{$result->name}}">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Division<span class="text-danger">*</span></label>
          <div class="col-md-8">
            <input type="text" name="division" class="form-control" value="{{$result->division}}">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Email<span class="text-danger">*</span></label>
          <div class="col-md-8">
            <input type="text" name="email" class="form-control" value="{{$result->email}}">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Username<span class="text-danger">*</span></label>
          <div class="col-md-8">
            <input type="text" name="username" class="form-control" value="{{$result->username}}">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Password<span class="text-danger">*</span></label>
          <div class="col-md-8">
            <input type="password" name="password" class="form-control">
            <p style="margin:0"><i>Kosongkan jika tidak diubah</i></p>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-md-2 col-form-label text-right">Password Confirm<span class="text-danger">*</span></label>
          <div class="col-md-8">
            <input type="password" name="password_confirm" class="form-control">
            <p style="margin:0"><i>Kosongkan jika tidak diubah</i></p>
          </div>
        </div>
      </div>
    </div>
    <div class="row mb-30">
      <div class="col-12">
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <th>No</th>
              <th>Menu</th>
              <th>View</th>
              <th>Create</th>
              <th>Edit</th>
              <th>Approve</th>
              <th>Print</th>
              <th>Delete</th>
            </thead>
            <tbody>
              @foreach($menu as $index => $row)
              <?php 
                $user_menu = \DB::table('setting_user_menu')->where('menu_id',$row->id)
                                                            ->where('user_id',$result->id)
                                                            ->first();
                $can_read = "";
                $can_create = "";
                $can_update = "";
                $can_delete = "";
                $can_approve = "";
                $can_print = "";

                if(!empty($user_menu)){
                  if($user_menu->can_read == 1){
                    $can_read = "checked";
                  }
                  if($user_menu->can_create == 1){
                    $can_create = "checked";
                  }
                  if($user_menu->can_update == 1){
                    $can_update = "checked";
                  }
                  if($user_menu->can_delete == 1){
                    $can_delete = "checked";
                  }
                  if($user_menu->can_approve == 1){
                    $can_approve = "checked";
                  }
                  if($user_menu->can_print == 1){
                    $can_print = "checked";
                  }
                }
              ?>
              <input type="hidden" name="repeater[{{$index}}][menu_id]" value="{{$row->id}}">
              <tr class="text-center">
                <td>{{$index+1}}</td>
                <td>{{$row->name}}</td>
                <td>
                  <input type="checkbox" name="repeater[{{$index}}][can_read]" value="1" <?= $can_read; ?>>
                </td>
                <td>
                  <input type="checkbox" name="repeater[{{$index}}][can_create]" value="1" <?= $can_create; ?>>
                </td>
                <td>
                  <input type="checkbox" name="repeater[{{$index}}][can_update]" value="1" <?= $can_update; ?>>
                </td>
                <td>
                  <input type="checkbox" name="repeater[{{$index}}][can_approve]" value="1" <?= $can_approve; ?>>
                </td>
                <td>
                  <input type="checkbox" name="repeater[{{$index}}][can_print]" value="1" <?= $can_print; ?>>
                </td>
                <td>
                  <input type="checkbox" name="repeater[{{$index}}][can_delete]" value="1" <?= $can_delete; ?>>
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
        <a href="{{route('superuser.account.user.index')}}" class="btn btn-warning  btn-md text-white"><i class="fa fa-arrow-left"></i> Back</a>
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

    $(document).on('submit','#frmEdit',function(e){
      e.preventDefault();
      if(confirm("Apakah anda yakin ingin mengubah user ini ?")){
        let _form = $('#frmEdit');
        $.ajax({
          url : '{{route('superuser.account.user.update')}}',
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
                  document.location.href = '{{route('superuser.account.user.index')}}';
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