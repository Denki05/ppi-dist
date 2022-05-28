@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Finance</span>
  <span class="breadcrumb-item">Payable</span>
  <span class="breadcrumb-item active">Detail</span>
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
        <strong>Payable Code</strong>
      </div>
      <div class="col-lg-10">
        : {{$result->code}}
      </div>
    </div>
    <div class="row">
      <div class="col-lg-2">
        <strong>Customer</strong>
      </div>
      <div class="col-lg-10">
        : {{$result->customer->name ?? ''}}
      </div>
    </div>
    <div class="row">
      <div class="col-lg-2">
        <strong>Total</strong>
      </div>
      <div class="col-lg-10">
        : {{number_format($result->total,0,',','.')}}
      </div>
    </div>
    <div class="row">
      <div class="col-lg-2">
        <strong>Created at</strong>
      </div>
      <div class="col-lg-10">
        : {{$result->created_at}}
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
          <table class="table table-striped table->bordered">
            <thead>
              <th>Invoice</th>
              <th>Prev Account Receivable</th>
              <th>Total</th>
            </thead>
            <tbody>
              @foreach($result->payable_detail as $index => $row)
                <tr>
                  <td>
                    <a href="{{route('superuser.finance.invoicing.history_payable',$row->invoice->id ?? '')}}">{{$row->invoice->code ?? ''}}</a>
                  </td>
                  <td>{{number_format($row->prev_account_receivable,0,',','.')}}</td>
                  <td>{{number_format($row->total,0,',','.')}}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="row mb-30">
      <div class="col-12">
        <a href="{{route('superuser.finance.payable.index')}}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Back</a>
      </div>
    </div>
  </div>
</div>
@endsection

<!-- Modal -->


@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')

@push('scripts')

  <script type="text/javascript">
  </script>
@endpush
