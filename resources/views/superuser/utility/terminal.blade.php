@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item active">Terminal</span>
</nav>
<iframe src="{{ url('superuser/terminal/container') }}" frameborder="0" width="100%" height="600"></iframe>
@endsection