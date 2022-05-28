@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <span class="breadcrumb-item active">Detail Delivery Order Mutation</span>
</nav>
@if(session('error') || session('success'))
<div class="alert alert-{{ session('error') ? 'danger' : 'success' }} alert-dismissible fade show" role="alert">
    @if (session('error'))
    <strong>Error!</strong> {!! session('error') !!}
    @elseif (session('success'))
    <strong>Berhasil!</strong> {!! session('success') !!}
    @endif
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif
<div class="block">
  <div class="block-content block-content-full">
    <div class="row mb-30">
      <div class="col-12">
        <div class="row mb-30">
          <div class="col-lg-6">
            {{$result->code}} <span class="badge badge-{{ $result->do_mutation_status()->class }}">{{ $result->do_mutation_status()->msg }}</span>
          </div>
          <div class="col-lg-6 text-right">
            {{date('d F Y',strtotime($result->created_at))}}
          </div>
        </div>
        <div class="row mb-30">
          <div class="col-12">
            <div class="row">
              <div class="col-lg-2">
                <strong>Origin Warehouse</strong>
              </div>
              <div class="col-lg-10">
                : {{$result->origin_warehouse->name ?? ''}}
              </div>
            </div>
            <div class="row">
              <div class="col-lg-2">
                <strong>Destination Warehouse</strong>
              </div>
              <div class="col-lg-10">
                : {{$result->destination_warehouse->name ?? ''}}
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
                  <th>Packaging</th>
                  <th>Qty</th>
                  <th>Note</th>
                </thead>
                <tbody>
                  @if(count($result->do_mutation_item) == 0)
                    <tr>
                      <td colspan="6">Data tidak ditemukan</td>
                    </tr>
                  @endif
                  @foreach($result->do_mutation_item as $index => $row)
                    <tr>
                      <td>{{$index+1}}</td>
                      <td>{{$row->product->code ?? ''}}</td>
                      <td>{{$row->product->name ?? ''}}</td>
                      <td>{{$row->packaging_txt()->scalar ?? ''}}</td>
                      <td>{{$row->qty ?? ''}}</td>
                      <td>{{$row->note ?? ''}}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <a href="{{route('superuser.penjualan.delivery_order_mutation.index')}}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Back</a>
      </div>
    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script type="text/javascript">
</script>
@endpush
