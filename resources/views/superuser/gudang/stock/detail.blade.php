@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Gudang</span>
  <span class="breadcrumb-item">Stock</span>
  <span class="breadcrumb-item active">Detail Stock</span>
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
    <div class="row">
      <div class="col-lg-3">
        Product
      </div>
      <div class="col-lg-9">
        : {{$result->product->name ?? ''}}
      </div>
    </div>
    <div class="row">
      <div class="col-lg-3">
        Type
      </div>
      <div class="col-lg-9">
        : {{$result->product->type->name ?? ''}}
      </div>
    </div>
    <div class="row">
      <div class="col-lg-3">
        Category
      </div>
      <div class="col-lg-9">
        : {{$result->product->category->name ?? ''}}
      </div>
    </div>
    <div class="row">
      <div class="col-lg-3">
        Warehouse
      </div>
      <div class="col-lg-9">
        : {{$result->warehouse->name ?? ''}}
      </div>
    </div>
  </div>
</div>
<div class="block">
  <hr class="my-20">
  <div class="block-content block-content-full">
    <div class="row">
      <div class="col-12">
        <table class="table table-hover" >
          <thead>
            <tr>
              <th>Code</th>
              <th>Warehouse</th>
              <th>Product</th>
              <th>In</th>
              <th>Out</th>
              <th>Stock</th>
              <th>Forecast</th>
              <th>Effective</th>
            </tr>
          </thead>
          <tbody>
              <tr>
                <td>{{$result->product->code ?? ''}}</td>
                <td>{{$result->warehouse->name ?? ''}}</td>
                <td>{{$result->product->name ?? ''}}</td>
                <td>{{$result->stock_in ?? ''}}</td>
                <td>{{$result->stock_out ?? ''}}</td>
                <td>{{$result->stock ?? ''}}</td>
                <td>{{$result->so ?? ''}}</td>
                <td>{{$result->effective ?? ''}}</td>
              </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<div class="block">
  <hr class="my-20">
  <div class="block-content block-content-full">
    <div class="row">
      <div class="col-12">
        <h5>#Moving Stock</h5>
        <table class="table table-hover" id="datatables">
          <thead>
            <tr>
              <th>Date</th>
              <th>Transaction</th>
              <th>In</th>
              <th>Out</th>
              <th>Balance</th>
            </tr>
          </thead>
          <tbody>
                @if(count($stock_move) <= 0)
                  <tr>
                    <td colspan="5" align="center">Data tidak ditemukan</td>
                  </tr>
                @endif
                @foreach($stock_move as $index => $row)
                  <tr>
                    <td>{{$row->created_at}}</td>
                    <td>{{$row->code_transaction}}</td>
                    <td>{{$row->stock_in}}</td>
                    <td>{{$row->stock_out}}</td>
                    <td>{{$row->stock_balance}}</td>
                  </tr>
                @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<!-- <div class="block">
  <hr class="my-20">
  <div class="block-content block-content-full">
    <div class="row">
      <div class="col-12">
        <h5>#Detail DO</h5>
        <table class="table table-striped table-vcenter table-responsive" id="datatables">
          <thead>
            <tr>
              <th>Code</th>
              <th>Product</th>
              <th>Out</th>
              <th>Created At</th>
            </tr>
          </thead>
          <tbody>
              <?php
                $do_total_out = 0;
              ?>
              @if(count($result_do) <= 0)
                <tr>
                  <td colspan="4" align="center">Data tidak ditemukan</td>
                </tr>
              @endif
              @foreach($result_do as $index => $row)
              <?php
                $do_total_out += $row->qty;
              ?>
              <tr>
                <td>{{$row->product->code ?? ''}}</td>
                <td>{{$row->product->name ?? ''}}</td>
                <td>{{$row->qty ?? ''}}</td>
                <td>{{$row->created_at}}</td>
              </tr>
              @endforeach
              <tr>
                <td colspan="2" align="center"><strong>Total DO</strong></td>
                <td><span class="text-primary"><strong>{{$do_total_out}}</strong></span></td>
                <td></td>
              </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="block">
  <hr class="my-20">
  <div class="block-content block-content-full">
    <div class="row">
      <div class="col-12">
        <h5>#Detail DO Mutation</h5>
        <table class="table table-striped table-vcenter table-responsive" id="datatables">
          <thead>
            <tr>
              <th>Code</th>
              <th>Product</th>
              <th>Out</th>
              <th>Created At</th>
            </tr>
          </thead>
          <tbody>
              <?php
                $do_total_out = 0;
              ?>
              @if(count($result_do_mutation) <= 0)
                <tr>
                  <td colspan="4" align="center">Data tidak ditemukan</td>
                </tr>
              @endif
              @foreach($result_do_mutation as $index => $row)
              <?php
                $do_total_out += $row->qty;
              ?>
              <tr>
                <td>{{$row->product->code ?? ''}}</td>
                <td>{{$row->product->name ?? ''}}</td>
                <td>{{$row->qty ?? ''}}</td>
                <td>{{$row->created_at}}</td>
              </tr>
              @endforeach
              <tr>
                <td colspan="2" align="center"><strong>Total DO Mutation</strong></td>
                <td><span class="text-primary"><strong>{{$do_total_out}}</strong></span></td>
                <td></td>
              </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="block">
  <hr class="my-20">
  <div class="block-content block-content-full">
    <div class="row">
      <div class="col-12">
        <h5>#Detail Sales Mutation</h5>
        <table class="table table-striped table-vcenter table-responsive" id="datatables">
          <thead>
            <tr>
              <th>Code</th>
              <th>Product</th>
              <th>Out</th>
              <th>Created At</th>
            </tr>
          </thead>
          <tbody>
              <?php
                $do_total_out = 0;
              ?>
              @if(count($result_canvasing) <= 0)
                <tr>
                  <td colspan="4" align="center">Data tidak ditemukan</td>
                </tr>
              @endif
              @foreach($result_canvasing as $index => $row)
              <?php
                $do_total_out += $row->qty;
              ?>
              <tr>
                <td>{{$row->product->code ?? ''}}</td>
                <td>{{$row->product->name ?? ''}}</td>
                <td>{{$row->qty ?? ''}}</td>
                <td>{{$row->created_at}}</td>
              </tr>
              @endforeach
              <tr>
                <td colspan="2" align="center"><strong>Total Sales Canvasing</strong></td>
                <td><span class="text-primary"><strong>{{$do_total_out}}</strong></span></td>
                <td></td>
              </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div class="row mb-20">
      <div class="col-12">
        <a href="{{route('superuser.gudang.stock.index')}}" class="btn btn-warning" ><i class="fa fa-arrow-left"></i> Back</a>
      </div>
    </div>
  </div>
</div> -->
@endsection

<!-- Modal -->


@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')

@push('scripts')

  <script type="text/javascript">
    $(function(){
      $('#datatables').DataTable();
    })
  </script>
@endpush
