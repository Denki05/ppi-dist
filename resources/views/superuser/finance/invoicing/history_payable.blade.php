@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Finance</span>
  <span class="breadcrumb-item active">History Payable Invoice</span>
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
    <div class="row">
      <div class="col-lg-2">
        <strong>Invoice Code</strong>
      </div>
      <div class="col-lg-10">
        {{$result->code}}
      </div>
    </div>
    <div class="row">
      <div class="col-lg-2">
        <strong>Customer</strong>
      </div>
      <div class="col-lg-10">
        {{$result->do->customer->name ?? ''}}
      </div>
    </div>
    <div class="row">
      <div class="col-lg-2">
        <strong>Total</strong>
      </div>
      <div class="col-lg-10">
        {{number_format($result->grand_total_idr,0,',','.')}}
      </div>
    </div>
    <div class="row">
      <div class="col-lg-2">
        <strong>Created at</strong>
      </div>
      <div class="col-lg-10">
        {{$result->created_at}}
      </div>
    </div>
  </div>
</div>
<div class="block">
  <hr class="my-20">
  <div class="block-content block-content-full">
    <div class="row">
      <div class="col-12">
        <div class="table-responsive">
          <table class="table table-bordered table-striped text-center">
            <thead>
              <th>No</th>
              <th>Payable Code</th>
              <th>Total</th>
              <th>Created At</th>
            </thead>
            <tbody>
              @if(count($result->payable_detail) <= 0)
                <tr>
                  <td colspan="4" class="text-center">Data tidak ditemukan</td>
                </tr>
              @endif
              @foreach($result->payable_detail as $index => $row)
              <tr>
                <td>{{$index+1}}</td>
                <td>
                  {{$row->payable->code ?? ''}}
                </td>
                <td>{{number_format($row->total,0,',','.')}}</td>
                <td>{{$row->created_at}}</td>
              </tr>
              @endforeach
              @if(count($result->payable_detail) > 0)
                <tr>
                  <td colspan="2" class="text-center">Total</td>
                  <td>{{number_format($result->payable_detail->sum('total') ?? 0,0,',','.')}}</td>
                  <td></td>
                </tr>
              @endif
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="row mb-10">
      <div class="col-12">
        <a href="{{route('superuser.finance.invoicing.index')}}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Back</a>
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
        $('.js-select2').select2();
    })
  </script>
@endpush
