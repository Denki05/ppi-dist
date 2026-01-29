@extends('superuser.app')

@section('content')
  <!-- <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item"></span>
    <a class="breadcrumb-item" href="">Mutation From Display</a>
    <span class="breadcrumb-item active">Show</span>
  </nav> -->
  <div id="alert-block"></div>

  <div class="block">
    <div class="block-header block-header-default">
      <h3 class="block-title">Show Mutation Out</h3>
    </div>
    <div class="block-content">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">Code</label>
        <div class="col-md-7">
          <div class="form-control-plaintext">{{ $mutasi_out->code }}</div>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="warehouse_from">Warehouse From</label>
        <div class="col-md-7">
          <div class="form-control-plaintext">{{ $mutasi_out->warehouse_from_attribute->name }}</div>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="warehouse_to">Warehouse To</label>
        <div class="col-md-7">
          <div class="form-control-plaintext">{{ $mutasi_out->warehouse_to_attribute->name }}</div>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="warehouse_to">Ref SPK</label>
        <div class="col-md-7">
          <div class="form-control-plaintext">{{ $mutasi_out->spk->code }}</div>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="status">Status</label>
        <div class="col-md-7">
          <div class="form-control-plaintext">{{ $mutasi_out->status() }}</div>
        </div>
      </div>
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.gudang.mutasi_out.index') }}">
            <button type="button" class="btn bg-gd-cherry border-0 text-white">
              <i class="fa fa-arrow-left mr-10"></i> Back
            </button>
          </a>
        </div>
      </div>

    </div>
  </div>

  <div class="block">
    <div class="block-header block-header-default">
      <h3 class="block-title">Product</h3>
    </div>
    <div class="block-content">
      <table id="datatable" class="table table-striped">
        <thead>
          <tr>
            <th class="text-center">#</th>
            <th class="text-center">Product</th>
            <th class="text-center">Kemasan</th>
            <th class="text-center">Quantity</th>
            <th class="text-center">Keterangan</th>
          </tr>
        </thead>
        <tbody>
          @foreach($mutasi_out->mutasiOutDetails as $row)
            <td class="text-center">{{ $loop->iteration }}</td>
            <td class="text-center">{{ $row->productPackaging->code }} - {{ $row->productPackaging->name }}</td>
            <td class="text-center">{{ $row->productPackaging->packaging->pack_name }}</td>
            <td class="text-center">{{ $row->quantity }}</td>
            <td class="text-center">{{ $row->note }}</td>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

@endsection
@include('superuser.asset.plugin.datatables')

@push('scripts')
  <script type="text/javascript">
    $(document).ready(function() {
      $('#datatable').DataTable({})

    });

  </script>
@endpush
