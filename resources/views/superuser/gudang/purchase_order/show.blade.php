@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Gudang</span>
  <a class="breadcrumb-item" href="{{ route('superuser.gudang.purchase_order.index') }}">Purchase Order (PO)</a>
  <span class="breadcrumb-item active">Show</span>
</nav>

@endsection