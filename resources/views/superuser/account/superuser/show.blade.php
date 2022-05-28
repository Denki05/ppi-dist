@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Account</span>
  <a class="breadcrumb-item" href="{{ route('superuser.account.superuser.index') }}">Superuser</a>
  <span class="breadcrumb-item active">{{ $account->id }}</span>
</nav>
<h2 class="content-heading">Superuser</h2>
<div class="block">
  <div class="block-content">
    <div class="row">
      <label class="col-lg-3 col-form-label text-right">Username</label>
      <div class="col-lg-7">
        <div class="form-control-plaintext">{{ $account->username }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-lg-3 col-form-label text-right">Email</label>
      <div class="col-lg-7">
        <div class="form-control-plaintext">{{ $account->email }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-lg-3 col-form-label text-right">Name</label>
      <div class="col-lg-7">
        <div class="form-control-plaintext">{{ $account->name }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-lg-3 col-form-label text-right">Image</label>
      <div class="col-lg-7">
        <a class="img-link img-link-zoom-in img-lightbox" href="{{ $account->img }}">
          <img class="img-fluid img-show-small" src="{{ $account->img }}">
        </a>
      </div>
    </div>
    <div class="row">
      <label class="col-lg-3 col-form-label text-right">Status</label>
      <div class="col-lg-7">
        <div class="form-control-plaintext">
          @if($account->is_active)
          <i class="fa fa-lg fa-check text-success"></i>
          @else
          <i class="fa fa-lg fa-close text-danger"></i>
          @endif
        </div>
      </div>
    </div>
    {{-- <div class="row">
      <label class="col-lg-3 col-form-label text-right">Created By</label>
      <div class="col-lg-7">
        <div class="form-control-plaintext">{{ $account->createdBySuperuser() }}</div>
      </div>
    </div> --}}
    <div class="row pt-30 mb-15">
      <div class="col-md-6">
        <a href="{{ route('superuser.account.superuser.index') }}">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
      </div>
      @if($account->is_active)
      <div class="col-md-6 text-right">
        <a href="javascript:deleteConfirmation('{{ route('superuser.account.superuser.destroy', $account->id) }}', true)">
          <button type="button" class="btn bg-gd-pulse border-0 text-white">
            Delete <i class="fa fa-trash ml-10"></i>
          </button>
        </a>
        <a href="{{ route('superuser.account.superuser.edit', $account->id) }}">
          <button type="button" class="btn bg-gd-leaf border-0 text-white">
            Edit <i class="fa fa-pencil ml-10"></i>
          </button>
        </a>
      </div>
      @endif
    </div>
  </div>
</div>

<div class="pb-30">
  <h2 class="content-heading">Role</h2>
  <div class="row gutters-tiny text-center">
    @if($account->roles->isNotEmpty())
    @foreach($account->roles as $role)
    <div class="col-2">
      <a class="block block-link-shadow" href="javascript:void(0)">
        <div class="block-content block-content-full">
          <div class="font-size-sm font-w600 text-muted">{{ $role->name }}</div>
        </div>
      </a>
    </div>
    @endforeach
    @else
    <div class="col-2">
      <a class="block block-link-shadow bg-warning text-white" href="javascript:void(0)">
        <div class="block-content block-content-full">
          <div class="font-size-sm font-w600"><i class="fa fa-exclamation-triangle"></i> No Role <i class="fa fa-exclamation-triangle"></i></div>
        </div>
      </a>
    </div>
    @endif
  </div>
</div>

<h2 class="content-heading">Permission</h2>
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
      <div id="accordion_q{{ $loop->iteration }}" class="collapse" role="tabpanel" aria-labelledby="accordion_h{{ $loop->iteration }}" data-parent="#accordion">
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
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ ucwords($module) }}</td>
                @foreach(\App\Helper\PermissionHelper::ACTIONS as $action)
                <td>
                  @if($account->can($module . '-' . $action))
                  <i class="si si-check text-success"></i>
                  @else
                  <i class="si si-close text-danger"></i>
                  @endif
                </td>
                @endforeach
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

@include('superuser.asset.plugin.magnific-popup')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script>
$(document).ready(function () {
  $('a.img-lightbox').magnificPopup({
    type: 'image',
    closeOnContentClick: true,
  });
})
</script>
@endpush