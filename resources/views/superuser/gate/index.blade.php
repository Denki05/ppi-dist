@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item active">Gate</span>
</nav>

<div class="pb-30">
  <h2 class="content-heading">
    Role
    <a href="javascript:void(0)" data-toggle="modal" data-target="#modal-guard" class="pull-right font-size-sm">
      Guard
    </a>
  </h2>
  <div class="row gutters-tiny text-center">
    @foreach($roles as $role)
    <div class="col-2">
      <a class="block block-link-shadow show-role" href="javascript:void(0)" data-role-id="{{ $role->id }}" data-role-name="{{ $role->name }}">
        <div class="block-content block-content-full">
          <div class="font-size-h2 font-w700">{{ $role->user()->count() }}</div>
          <div class="font-size-sm font-w600 text-muted">{{ $role->name }}</div>
        </div>
      </a>
    </div>
    @endforeach
    @if(\App\Helper\PermissionHelper::countSuperuserWithoutRole() > 0)
    <div class="col-2">
      <a class="block block-link-shadow bg-warning text-white show-role" href="javascript:void(0)" data-role-id="-1" data-role-name="Superuser Without Any Role">
        <div class="block-content block-content-full">
          <div class="font-size-h2 font-w700">{{ \App\Helper\PermissionHelper::countSuperuserWithoutRole() }}</div>
          <div class="font-size-sm font-w600">Superuser Without Role</div>
        </div>
      </a>
    </div>
    @endif
    <div class="col-2">
      <a class="block block-link-shadow" href="javascript:void(0)" data-toggle="modal" data-target="#modal-create-role">
        <div class="block-content block-content-full py-29">
          <div class="font-size-h1 font-w700"><i class="si si-plus fa-lg"></i></div>
        </div>
      </a>
    </div>
  </div>
</div>

<h2 class="content-heading">Permission</h2>
<div class="block">
  <div id="accordion" role="tablist" aria-multiselectable="true">
    @foreach($permission_modules as $name => $modules)
    <div class="block block-bordered mb-0">
      <div class="block-header" role="tab" id="accordion_h{{ $loop->iteration }}">
        <a class="font-w600" data-toggle="collapse" data-parent="#accordion" href="#accordion_q{{ $loop->iteration }}" aria-expanded="true" aria-controls="accordion_q{{ $loop->iteration }}">{{ $name }}</a>
      </div>
      <div id="accordion_q{{ $loop->iteration }}" class="collapse" role="tabpanel" aria-labelledby="accordion_h{{ $loop->iteration }}" data-parent="#accordion">
        <div class="block-content">
          <table class="table table-bordered table-vcenter table-hover">
            <thead>
              <tr>
                <th>#</th>
                <th>Name</th>
                @foreach($permission_actions as $action)
                <th>{{ $action }}</th>
                @endforeach
              </tr>
            </thead>
            <tbody>
              @foreach($modules as $module)
              @php $reload = false; @endphp
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ ucwords($module) }}</td>
                @foreach($permission_actions as $action)
                <td>
                  {{-- <i class="si si-check text-success"></i> --}}
                  {{-- <i class="si si-close text-danger"></i> --}}
                  @if(\App\Helper\PermissionHelper::isPermissionExists($module . '-' . $action, 'superuser'))
                  <i class="si si-check text-success"></i>
                  @else
                  <i class="si si-info text-warning"></i>
                  @php $reload = true; @endphp
                  @endif
                </td>
                @endforeach

                @if($reload)
                <td>
                  <form class="ajax" data-action="{{ route('superuser.gate.save.permission') }}" data-type="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" value="reload">
                    <input type="hidden" name="guard" value="superuser">
                    <input type="hidden" name="permission" value="{{ $module }}">
                    <button class="btn" type="submit">
                      <i class="si si-reload text-warning"></i>
                    </button>
                  </form>
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
@endsection

@section('modal')
<div class="modal fade" id="modal-guard" tabindex="-1" role="dialog" aria-labelledby="modal-fadein" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form class="ajax" data-action="{{ route('superuser.gate.save.guard') }}" data-type="POST" enctype="multipart/form-data">
        <div class="block block-themed block-transparent mb-0">
          <div class="block-header bg-primary-dark">
            <h3 class="block-title">Guard</h3>
            <div class="block-options">
              <button type="button" class="btn-block-option" data-dismiss="modal" aria-label="Close">
                <i class="si si-close"></i>
              </button>
            </div>
          </div>
          <div id="alert-block"></div>
          <div class="block-content">
            <div class="form-group">
              <textarea class="form-control" name="name" rows="5">{{ setting('system.guard') }}</textarea>
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

<div class="modal fade" id="modal-create-role" tabindex="-1" role="dialog" aria-labelledby="modal-fadein" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form class="ajax" data-action="{{ route('superuser.gate.save.role') }}" data-type="POST" enctype="multipart/form-data">
        <div class="block block-themed block-transparent mb-0">
          <div class="block-header bg-primary-dark">
            <h3 class="block-title">Role - Create</h3>
            <div class="block-options">
              <button type="button" class="btn-block-option" data-dismiss="modal" aria-label="Close">
                <i class="si si-close"></i>
              </button>
            </div>
          </div>
          <div id="alert-block"></div>
          <div class="block-content">
            <div class="form-group">
              <label>Name</label>
              <input type="text" class="form-control" name="name">
            </div>
            <div class="form-group">
              <label>Guard</label>
              <select class="form-control" name="guard">
                @foreach(textarea_to_array(setting('system.guard')) as $guard)
                <option value="{{ $guard }}">{{ $guard }}</option>
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

<div class="modal fade" id="modal-show-role" tabindex="-1" role="dialog" aria-labelledby="modal-fadein" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="block block-themed block-transparent mb-0">
        <div class="block-header bg-primary-dark">
          <h3 class="block-title">Role - Show - <span id="role-name"></span></h3>
          <div class="block-options">
            <button type="button" class="btn-block-option" data-dismiss="modal" aria-label="Close">
              <i class="si si-close"></i>
            </button>
          </div>
        </div>
        <div class="block-content block-content-full">
          <p class="text-center" id="show-loader">
            <i class="fa fa-4x fa-cog fa-spin"></i>
          </p>
          <table id="datatable" class="table table-striped table-vcenter table-responsive" style="display: none">
            <thead>
              <tr>
                <th>#</th>
                <th>Username</th>
                <th>Email</th>
                <th>Profile</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
      <div class="modal-footer" style="display: none">
        <a href="" class="btn btn-alt-danger" id="btn-delete-role">
          <i class="fa fa-trash"></i> Delete
        </a>
      </div>
    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.datatables')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
$(document).ready(function () {
  var datatable = $('#datatable').DataTable({
    autoWidth: !1,
    searching: !1,
    oLanguage: {
        sLengthMenu: ""
    },
    dom: "<'row'<'col-sm-12'tr>><'row'<'col-sm-6'i><'col-sm-6'p>>"
  });

  $('.show-role').on('click', function (e) {
    e.preventDefault()

    $('#modal-show-role').modal('show');

    let role_id = $(this).data('role-id')
    let role_name = $(this).data('role-name')

    $('#modal-show-role #role-name').text(role_name)

    $.ajax({
      url: "{{ route('superuser.gate.show.role') }}" + '/' + role_id,
      contentType: false,
      cache: false,
      processData: false,
      type: 'GET',
    }).done(function (response) {
      $.each(response, function (key, val) {
        let button
        
        if (val.type == 'superuser') {
          button = '<a href="{{ route("superuser.account.superuser.show", "replace_id") }}" target="_blank" class="btn btn-sm btn-secondary"><i class="fa fa-user"></i></button>'.replace('replace_id', val.id)
        } else {
          button = ''
        }

        datatable.row.add([
          key+1,
          val.username,
          val.email,
          button
        ]);
      })

      datatable.draw()
      $("#show-loader").hide()
      $("#datatable").show()

      if (!datatable.data().any()) {
        $('#modal-show-role .modal-footer').show()
        $('#modal-show-role .modal-footer > #btn-delete-role').attr('href', "{{ route('superuser.gate.delete.role') }}" + '/' + role_id)
      }

    }).fail(function (request, status, error) {
      log(request)
    });
  })

  // $('#modal-show-role').on('shown.bs.modal', function (e) {
  //   console.log(1)
  // })

  $('#modal-show-role').on('hidden.bs.modal', function (e) {
    datatable.clear().draw()
    $("#show-loader").show()
    $("#datatable").hide()
  })
})
</script>
@endpush