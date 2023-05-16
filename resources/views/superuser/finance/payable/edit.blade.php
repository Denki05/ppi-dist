@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Finance</span>
  <span class="breadcrumb-item">Payable</span>
  <span class="breadcrumb-item ">Detail</span>
  <span class="breadcrumb-item active">Edit {{$result->code}}</span>
</nav>

@endsection