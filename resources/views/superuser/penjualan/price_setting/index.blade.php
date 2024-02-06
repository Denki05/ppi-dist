@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <span class="breadcrumb-item active">Price Setting</span>
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
  <hr class="my-20">
  <div class="block-content block-content-full">
    <form method="get" action="{{ route('superuser.penjualan.setting_price.index') }}">
      <div class="row">
        <div class="col-lg-3">
          <div class="form-group">
            <select class="form-control js-select2" name="id_product">
              <option value="">==All Products==</option>
              @foreach($product as $index => $row)
                <option value="{{$row->id}}">{{$row->name}}</option>
              @endforeach
            </select>
          </div>          
        </div>
        <div class="col-lg-3">
          <div class="form-group">
            <select class="form-control js-select2" name="id_packaging">
              <option value="">==All Category==</option>
              @foreach($packaging as $index => $row)
                <option value="{{$row->id}}">{{$row->pack_name}}</option>
              @endforeach
            </select>
          </div>          
        </div>
        <div class="col-lg-3">
          <button class="btn btn-primary"><i class="fa fa-search"></i></button>
        </div>
      </div>
      <div class="row mb-30">
        <div class="col-12">
          <table class="table table-striped" id="datatables">
            <thead>
              <tr>
                <th>
                  #
                </th>
                <th>Product</th>
                <th>Packaging</th>
                <th>Warehouse</th>
                <th>Selling Price</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($result as $row)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $row->code }} - {{ $row->name }}</td>
                  <td>{{ $row->packaging->pack_name }}</td>
                  <td>{{ $row->warehouse->name ?? '' }}</td>
                  <td>{{ $row->price }}</td>
                  <td>
                    <a href="{{route('superuser.penjualan.setting_price.edit', base64_encode($row->id))}}" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-edit"></i> Edit</a>
                    <a href="{{route('superuser.penjualan.setting_price.history', base64_encode($row->id))}}" class="btn btn-info btn-sm btn-flat"><i class="fa fa-history"></i> History</a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
  <script type="text/javascript">
    $(document).ready(function() {
      $('#datatables').DataTable({
          paging   :  true,
          info     :  false,
          searching : false,
          order: [
            [1, 'asc']
          ],
          pageLength: 10,
          lengthMenu: [
            [10, 30, 100, -1],
            [10, 30, 100, 'All']
          ],
      })

    $('.js-select2').select2({})
    });
  </script>
@endpush
