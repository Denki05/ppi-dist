@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item active">Backup</span>
</nav>
@if($errors->any())
<div class="alert alert-danger alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Error</h3>
  @foreach ($errors->all() as $error)
  <p class="mb-0">{{ $error }}</p>
  @endforeach
</div>
@endif
<div class="block">
  <div class="block-content">
    <a href="{{ route('superuser.backup.createBackup') }}">
      <button type="button" class="btn btn-outline-primary min-width-125">
        <i class="fa fa-download" aria-hidden="true"></i>
      </button>
    </a>
  </div>
  <hr class="my-20">
  
</div>
@endsection
