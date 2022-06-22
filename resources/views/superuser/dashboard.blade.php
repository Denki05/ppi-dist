@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item active">Dashboard</span>
</nav>

<div class="row gutters-tiny">
  <div class="col-4">
    <a class="block" href="javascript:void(0)">
      <div class="block-content block-content-full">
        <div class="row">
          <div class="col-6">
            <i class="fa fa-dollar fa-2x text-body-bg-dark"></i>
          </div>
          <div class="col-6 text-right">
            <span class="text-muted">{{ Swap::latest('USD/IDR')->getDate()->format('d M Y H:i:s') }}</span>
          </div>
        </div>
        <div class="row">
          <div class="col-6 text-right border-r">
            <div class="font-size-h3 font-w600">USD</div>
            <div class="font-size-h4 font-w600"><i class="fa fa-dollar"></i>1</div>
          </div>
          <div class="col-6">
            <div class="font-size-h3 font-w600">IDR</div>
            <div class="font-size-h4 font-w600">{{ rupiah(Swap::latest('USD/IDR', ['cache_ttl' => \Carbon\Carbon::now()->secondsUntilEndOfDay()])->getValue()) }}</div>
          </div>
        </div>
      </div>
    </a>
  </div>
</div>

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

<div class="row">
  <div class="col-12">
    <div class="block">
      <div class="block-content block-content-full">
        @if($is_see == true)
          <form>
            <div class="row">
              <div class="col-lg-2 pt-2">
                <h5>Transaction List</h5>
              </div>
              <div class="col-lg-3">
                <div class="form-group row">
                  <label class="col-md-3 col-form-label text-right">Customer</label>
                  <div class="col-md-9">
                    <select class="form-control js-select2" name="customer_id">
                      <option value="">==All Customer==</option>
                      @foreach($customer as $index => $row)
                      <option value="{{$row->id}}">{{$row->name}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>   
              </div>
              <div class="col-lg-3">
              <div class="form-group row">
                  <label class="col-md-3 col-form-label text-right">Area</label>
                  <div class="col-md-9">
                    <select class="form-control js-select2" name="province">
                      <option value="">==All Customer==</option>
                      @foreach($customer as $index => $row)
                      <option value="{{$row->id}}">{{$row->text_provinsi}}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-lg-4">
                <div class="form-group row">
                  <div class="col-md-3">
                    <label class="col-md-3 col-form-label text-right">Search</label>
                  </div>
                  <div class="col-md-9">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Keyword" name="search">
                        <div class="input-group-append">
                          <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                        </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>
          <div class="row">
            <div class="col-12">
              <div class="table-responsive">
                <table class="table table-striped">
                  <thead>
                    <th>Invoice Date</th>
                    <th>Invoice Number</th>
                    <th>Store / Member</th>
                    <th>Area</th>
                    <th>Revenue</th>
                    <th>Paid</th>
                    <th>Due Date</th>
                    <th>Is Due Date</th>
                  </thead>
                  <tbody>
                    <?php 
                      $total_invoice = 0;
                      $total_paid = 0;
                    ?>
                    @if(count($invoice) == 0)
                    <tr>
                      <td colspan="8">Data tidak ditemukan</td>
                    </tr>
                    @endif
                    @foreach($invoice as $index => $row)
                    <tr>
                      <td>
                        <?= date('d-m-Y',strtotime($row->created_at)); ?>
                      </td>
                      <td>{{$row->code}}</td>
                      <td>{{$row->do->customer->name ?? ''}} / {{$row->do->customer_other_address->name ?? ''}}</td>
                      <td>{{$row->do->customer->text_provinsi ?? ''}}</td>
                      <td>{{number_format($row->grand_total_idr,0,',','.')}}</td>
                      <td>{{number_format($row->payable_detail->sum('total'),0,',','.')}}</td>
                      <td>
                        <?php
                          $due_date = date('Y-m-d',strtotime($row->created_at."+ 30 days"));
                          $due_date_60 = date('Y-m-d',strtotime($row->created_at."+ 60 days"));
                        ?>
                        <?= date('d-m-Y',strtotime($due_date)); ?>
                      </td>
                      <td>
                        @if($due_date <= date('Y-m-d') && $row->grand_total_idr > $row->payable_detail->sum('total'))
                          <span class="badge badge-warning badge-xs">H+30</span>
                        @elseif($due_date_60 <= date('Y-m-d') && $row->grand_total_idr > $row->payable_detail->sum('total'))
                          <span class="badge badge-danger badge-xs">H+60</span>
                        @endif
                        @if($row->grand_total_idr <= $row->payable_detail->sum('total'))
                          <span class="badge badge-success badge-xs">Paid Off</span>
                        @endif
                      </td>
                    </tr>
                    
                    <?php
                      $total_invoice += $row->grand_total_idr;
                      $total_paid += $row->payable_detail->sum('total');
                    ?>
                    @endforeach
                  </tbody>
                  <tfoot class="text-center">
                    <tr>
                      <td colspan="4" class="text-right"><b>Total : </b></td>
                      <td>{{number_format($total_invoice,0,',','.')}}</td>
                      <td>{{number_format($total_paid,0,',','.')}}</td>
                      <td colspan="2"></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script type="text/javascript">
  $(function(){
    $('.js-select2').select2();
  })
</script>
@endpush