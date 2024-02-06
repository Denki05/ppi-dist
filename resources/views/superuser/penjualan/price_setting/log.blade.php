@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <span class="breadcrumb-item">Price Setting</span>
  <span class="breadcrumb-item active">History</span>
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
    <div class="row mb-30">
      <div class="col-12">
        <div class="row">
          <div class="col-lg-2">
            <strong>Product</strong>
          </div>
          <div class="col-lg-10">
            : {{$result->code ?? ''}} - {{ $result->name ?? '' }}
          </div>
        </div>
        <div class="row">
          <div class="col-lg-2">
            <strong>Kemasan</strong>
          </div>
          <div class="col-lg-10">
            : {{$result->packaging->pack_name ?? ''}}
          </div>
        </div>
        <div class="row">
          <div class="col-lg-2">
            <strong>Warhouse</strong>
          </div>
          <div class="col-lg-10">
            : {{$result->warehouse->name}}
          </div>
        </div>
        <div class="row">
          <div class="col-lg-2">
            <strong>Selling Price Now</strong>
          </div>
          <div class="col-lg-10">
            : {{$result->price}}
          </div>
        </div>
      </div>
    </div>
    <div class="row mb-30">
      <div class="col-12">
        <div class="table-responsive">
          <table class="table table-striped table-bordered">
            <thead>
              <th>#</th>
              <th>Created at</th>
              <th>Selling Price</th>
            </thead>
            <tbody>
              @if(count($result->setting_price_log) > 0)
                @foreach($result->setting_price_log as $index => $row)
                  <tr>
                    <td>{{$index+1}}</td>
                    <td>{{$row->created_at}}</td>
                    <td>{{$row->price}}</td>
                  </tr>
                @endforeach
              @else
              <tr>
                <td colspan="3" align="center">Data tidak ditemukan</td>
              </tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="row mb-30">
      <div class="col-12">
        <a href="{{route('superuser.penjualan.setting_price.index')}}" class="btn btn-warning  btn-md text-white"><i class="fa fa-arrow-left"></i> Back</a>
      </div>
    </div>
  </div>
</div>
@endsection


