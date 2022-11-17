@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Gudang</span>
  <span class="breadcrumb-item active">Stock</span>
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
  <hr class="my-20">
  <div class="block-content block-content-full">
      <form method="get" action="{{ route('superuser.gudang.stock.index') }}">
        <div class="row">
          <div class="col-lg-3">
            <div class="form-group">
              <select class="form-control js-select2" name="warehouse_id">
                <option value="">==All Warehouse==</option>
                @foreach($warehouse as $index => $row)
                <option value="{{$row->id}}">{{$row->name}}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Keyword" name="search">
                <div class="input-group-append">
                  <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                </div>
              </div>
          </div>
        </div>
      </form>
      <div class="row mb-30">
        <div class="col-12">
          <table class="table table-hover" id="datatables">
            <thead>
              <tr>
                <th>#</th>
                <th>Code</th>
                <th>Warehouse</th>
                <th>Product</th>
                <th>In</th>
                <th>Out</th>
                <th>Stock</th>
                <th>Forecast</th>
                <th>Effective</th>
                <th>Detail</th>
              </tr>
            </thead>
            <tbody>
              @foreach($table as $index => $row)
                <tr>
                  <td>{{$table->firstItem() + $index}}</td>
                  <td>{{$row->product->code ?? ''}}</td>
                  <td>{{$row->warehouse->name ?? ''}}</td>
                  <td>{{$row->product->name ?? ''}}</td>
                  <td>{{$row->stock_in ?? ''}}</td>
                  <td>{{$row->stock_out ?? ''}}</td>
                  <td>{{$row->stock ?? ''}}</td>
                  <td>{{$row->so ?? ''}}</td>
                  <td>{{$row->effective ?? ''}}</td>
                  <td>
                    <a href="{{ route('superuser.gudang.stock.detail') }}?product_id={{$row->product_id}}&warehouse_id={{$row->warehouse_id}}" class="btn btn-primary btn-sm btn-flate"><i class="fa fa-eye"></i> Detail</a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      
      <div class="row mb-30">
        <div class="col-12">
          {{$table->links()}}
        </div>
      </div>
  </div>
</div>
@endsection

<!-- Modal -->


@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')

@push('scripts')

  <script type="text/javascript">
    $(function(){
      $(function(){
        $('#datatables').DataTable( {
          "paging":   false,
          "ordering": true,
          "info":     false,
          "searching" : false,
          "columnDefs": [{
            "targets": 0,
            "orderable": false
          }]
        });


        $('.js-select2').select2();

      });
    })
  </script>
@endpush
