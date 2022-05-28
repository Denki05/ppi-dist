@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Account</span>
  <a class="breadcrumb-item" href="{{ route('superuser.account.superuser.index') }}">Superuser</a>
  <a class="breadcrumb-item" href="{{ route('superuser.account.superuser.show', $account->id) }}">{{ $account->id }}</a>
  <span class="breadcrumb-item active">Edit</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Edit Superuser</h3>
  </div>
  <form class="ajax" data-action="{{ route('superuser.account.superuser.update', $account->id) }}" data-type="POST" enctype="multipart/form-data">
    <input type="hidden" name="_method" value="PUT">
    <div class="block-content block-content-full">
      <div class="form-group row">
        <label class="col-lg-3 col-form-label text-right">Username <span class="text-danger">*</span></label>
        <div class="col-lg-7">
          @role('SuperAdmin', 'superuser')
          <input type="text" class="form-control" name="username" value="{{ $account->username }}">
          @else
          <div class="form-control-plaintext">{{ $account->username }}</div>
          @endrole
        </div>
      </div>
      <div class="form-group row">
        <label class="col-lg-3 col-form-label text-right">Email <span class="text-danger">*</span></label>
        <div class="col-lg-7">
          @role('SuperAdmin', 'superuser')
          <input type="text" class="form-control" name="email" value="{{ $account->email }}">
          @else
          <div class="form-control-plaintext">{{ $account->email }}</div>
          @endrole
        </div>
      </div>
      @role('SuperAdmin', 'superuser')
      <div class="form-group row">
        <label class="col-lg-3 col-form-label text-right">Password <span class="text-danger">*</span></label>
        <div class="col-lg-7">
          <input type="password" class="form-control" name="password">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-lg-3 col-form-label text-right">Password Confirmation <span class="text-danger">*</span></label>
        <div class="col-lg-7">
          <input type="password" class="form-control" name="password_confirmation">
        </div>
      </div>
      @endrole
      <hr class="my-20">
      <div class="form-group row">
        <label class="col-lg-3 col-form-label text-right">Name</label>
        <div class="col-lg-7">
          <input type="text" class="form-control" name="name" value="{{ $account->name }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-lg-3 col-form-label text-right">Image</label>
        <div class="col-lg-7">
          <input type="file" id="image" name="image" data-max-file-size="2000" accept="image/png, image/jpeg" data-src="{{ $account->img }}">
        </div>
      </div>
    </div>
    <div class="block-content block-content-full block-content-sm bg-body-light font-size-sm text-right">
      <a href="{{ route('superuser.account.superuser.show', $account->id) }}">
        <button type="button" class="btn bg-gd-cherry border-0 text-white">
          <i class="fa fa-arrow-left mr-10"></i> Back
        </button>
      </a>
      <button type="submit" class="btn bg-gd-corporate border-0 text-white">
        Submit <i class="fa fa-arrow-right ml-10"></i>
      </button>
    </div>
  </form>
</div>

@if(Auth::guard('superuser')->user()->hasRole('SuperAdmin'))
<div class="pb-30">
  <h2 class="content-heading">Role</h2>
  <div class="row gutters-tiny text-center">
    @foreach($account->roles as $role)
    <div class="col-2">
      <a class="block block-link-shadow remove-role" href="javascript:void(0)" data-role-id="{{ $role->id }}" data-role-name="{{ $role->name }}">
        <div class="block-content block-content-full">
          <div class="font-size-sm font-w600 text-muted">{{ $role->name }}</div>
        </div>
      </a>
    </div>
    @endforeach
    @if(count($account->dontHaveRoles()) > 0)
    <div class="col-2">
      <a class="block block-link-shadow" href="javascript:void(0)" data-toggle="modal" data-target="#modal-assign-role">
        <div class="block-content block-content-full py-20">
          <div class="font-size-sm font-w600 text-muted"><i class="si si-plus fa-lg"></i></div>
        </div>
      </a>
    </div>
    @endif
  </div>
</div>
@endif

@if(Auth::guard('superuser')->user()->hasRole('SuperAdmin'))
<form class="ajax" data-action="{{ route('superuser.account.superuser.permission.sync', $account->id) }}" data-type="POST" enctype="multipart/form-data">
  <h2 class="content-heading">
    Permission
    <button type="submit" class="pull-right btn btn-warning btn-noborder btn-sm">
      Update
    </button>
  </h2>
  <div class="block">
    <div id="accordion" role="tablist" aria-multiselectable="true">
      @foreach(\App\Helper\PermissionHelper::MODULES as $name => $modules)
      @if (!$account->hasRole('Developer') && $name == 'DEVELOPER')
      @continue
      @endif
      <div class="block block-bordered mb-0">
        <div class="block-header" role="tab" id="accordion_h{{ $loop->iteration }}">
          <a class="font-w600" data-toggle="collapse" data-parent="#accordion" href="#accordion_q{{ $loop->iteration }}" aria-expanded="true" aria-controls="accordion_q{{ $loop->iteration }}">{{ $name }}</a>
        </div>
        <div id="accordion_q{{ $loop->iteration }}" class="show" role="tabpanel" aria-labelledby="accordion_h{{ $loop->iteration }}">
          <div class="block-content">
            <table class="table table-bordered table-vcenter table-hover">
              <thead>
                <tr>
                  <th width="5%">#</th>
                  <th width="30%">Name</th>
                  @foreach(\App\Helper\PermissionHelper::ACTIONS as $action)
                  <th>{{ $action }}</th>
                  @endforeach
                </tr>
              </thead>
              <tbody>
                @foreach($modules as $module)
                @php $mass_check = true; @endphp
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ ucwords($module) }}</td>
                  @foreach(\App\Helper\PermissionHelper::ACTIONS as $action)
                  <td>
                    @if(\App\Helper\PermissionHelper::isPermissionExists($module . '-' . $action, 'superuser'))
                    <label class="css-control css-control-success css-checkbox">
                      <input type="checkbox" class="css-control-input" name="permissions[]" value="{{ $module . '-' . $action }}" @if($account->can($module . '-' . $action)) checked @endif>
                      <span class="css-control-indicator"></span>
                    </label>
                    @else
                    <i class="si si-info text-warning"></i>
                    @php $mass_check = false; @endphp
                    @endif
                  </td>
                  @endforeach

                  @if ($mass_check)
                  <td>
                    <label class="css-control css-control-success css-checkbox">
                      <input type="checkbox" class="css-control-input mass-check">
                      <span class="css-control-indicator"></span>
                    </label>
                  </td>
                  @endif
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</form>
@endif
@endsection

@section('modal')
@if(count($account->dontHaveRoles()) > 0)
<div class="modal fade" id="modal-assign-role" tabindex="-1" role="dialog" aria-labelledby="modal-fadein" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form class="ajax" data-action="{{ route('superuser.account.superuser.role.assign', $account->id) }}" data-type="POST" enctype="multipart/form-data">
        <div class="block block-themed block-transparent mb-0">
          <div class="block-header bg-primary-dark">
            <h3 class="block-title">Assign Role</h3>
            <div class="block-options">
              <button type="button" class="btn-block-option" data-dismiss="modal" aria-label="Close">
                <i class="si si-close"></i>
              </button>
            </div>
          </div>
          <div id="alert-block"></div>
          <div class="block-content">
            <div class="form-group">
              <select class="form-control" name="role">
                @foreach($account->dontHaveRoles() as $role)
                <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-alt-success">
            <i class="fa fa-check"></i> Save
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

<div class="modal fade" id="modal-remove-role" tabindex="-1" role="dialog" aria-labelledby="modal-fadein" aria-hidden="true">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="block block-themed block-transparent mb-0">
        <div class="block-header bg-primary-dark">
          <h3 class="block-title">Remove Role</h3>
          <div class="block-options">
            <button type="button" class="btn-block-option" data-dismiss="modal" aria-label="Close">
              <i class="si si-close"></i>
            </button>
          </div>
        </div>
        <div class="block-content block-content-full text-center">
          <span id="role-name"></span>
        </div>
      </div>
      <div class="modal-footer justify-content-center">
        <form class="ajax" data-action="{{ route('superuser.account.superuser.role.remove', $account->id) }}" data-type="POST" enctype="multipart/form-data">
          <button type="submit" class="btn btn-alt-danger">
            <i class="fa fa-trash"></i> Remove
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.fileinput')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
$(document).ready(function () {
  $('#image').fileinput({
    theme: 'explorer-fa',
    browseOnZoneClick: true,
    showCancel: false,
    showClose: false,
    showUpload: false,
    browseLabel: '',
    removeLabel: '',
    initialPreview: $('#image').data('src'),
    initialPreviewAsData: true,
    fileActionSettings: {
      showDrag: false,
      showRemove: false
    },
    initialPreviewConfig: [
      {
        caption: '{{ $account->image }}'
      }
    ]
  });

  $('.remove-role').on('click', function (e) {
    e.preventDefault()

    $('#modal-remove-role').modal('show');

    let role_id = $(this).data('role-id')
    let role_name = $(this).data('role-name')

    $('#modal-remove-role #role-name').text(role_name)
    $('#modal-remove-role form').append('<input type="hidden" name="role" value="'+ role_id +'">')
  })

  $('#modal-remove-role').on('hidden.bs.modal', function (e) {
    $('#modal-remove-role #role-name').text('')
    $('#modal-remove-role form').children('input[name=role]').remove()
  })

  $('input.mass-check').on('change', function () {
    $(this).parents('td').siblings().children('label').children('input').attr('checked', this.checked)
  })
})
</script>
@endpush