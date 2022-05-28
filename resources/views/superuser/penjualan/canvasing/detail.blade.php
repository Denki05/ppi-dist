@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.canvasing.index') }}">Canvasing</a>
  <span class="breadcrumb-item active">Detail Canvasing</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-content">
    <div class="row">
      <div class="col-12">
        <div class="row mb-30">
          <div class="col-lg-6">
            <span class="badge badge-{{ $result->canvasing_status()->class }}">{{ $result->canvasing_status()->msg }}</span>
          </div>
          <div class="col-lg-6 text-right">
            {{date('d F Y',strtotime($result->created_at))}}
          </div>
        </div>
        <div class="row mb-30">
          <div class="col-12">
            <div class="row">
              <div class="col-lg-2">
                <strong>Code</strong>
              </div>
              <div class="col-lg-10">
                : {{$result->code ?? ''}}
              </div>
            </div>
            <div class="row">
              <div class="col-lg-2">
                <strong>Warehouse</strong>
              </div>
              <div class="col-lg-10">
                : {{$result->warehouse->name ?? ''}}
              </div>
            </div>
            <div class="row">
              <div class="col-lg-2">
                <strong>Sales</strong>
              </div>
              <div class="col-lg-10">
                : {{$result->sales->name ?? ''}}
              </div>
            </div>
            <div class="row">
              <div class="col-lg-2">
                <strong>Address</strong>
              </div>
              <div class="col-lg-10">
                : {{$result->address ?? ''}}
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
                  <th>Code</th>
                  <th>Produt</th>
                  <th>Qty</th>
                </thead>
                <tbody>
                  @if(count($result->canvasing_item) == 0)
                    <tr>
                      <td colspan="4">Data tidak ditemukan</td>
                    </tr>
                  @endif
                  @foreach($result->canvasing_item as $index => $row)
                    <tr>
                      <td>{{$index+1}}</td>
                      <td>{{$row->product->code ?? ''}}</td>
                      <td>{{$row->product->name ?? ''}}</td>
                      <td>{{$row->qty ?? ''}}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="row mb-3">
          <div class="col-12">
            <a href="{{route('superuser.penjualan.canvasing.index')}}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Back</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script>
</script>
@endpush