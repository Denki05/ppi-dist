@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Report</span>
  <span class="breadcrumb-item active">Revenue</span>
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
      <div class="row mb-30">
        <div class="col-12">
          <a href="#" class="btn btn-success btn-print" ><i class="fa fa-print"></i> Print</a>
        </div>
      </div>
      <form>
        <div class="row">
          <div class="col-lg-3">
            <div class="form-group">
              <label>Set Period From</label>
              <input type="date" name="period_from" class="form-control">
            </div>   
          </div>
          <div class="col-lg-3">
            <div class="form-group">
              <label>Set Period To</label>
              <input type="date" name="period_to" class="form-control">
            </div>   
          </div>
        </div>
        <div class="row">
          <div class="col-lg-3">
            <div class="form-group">
              <label>Customer</label>
              <select class="form-control js-select2" name="customer_id">
                <option value="">==All Customer==</option>
                @foreach($customer as $index => $row)
                <option value="{{$row->id}}">{{$row->name}}</option>
                @endforeach
              </select>
            </div>   
          </div>
          <div class="col-lg-3">
            <div class="form-group">
              <label>Sales Senior Name</label>
              <select class="form-control js-select2" name="sales_senior_id">
                <option value="">==All Sales Senior==</option>
                @foreach($sales as $index => $row)
                <option value="{{$row->id}}">{{$row->name}}</option>
                @endforeach
              </select>
            </div>   
          </div>
          <div class="col-lg-3">
            <div class="form-group">
              <label>Sales</label>
              <select class="form-control js-select2" name="sales_id">
                <option value="">==All Sales==</option>
                @foreach($sales as $index => $row)
                <option value="{{$row->id}}">{{$row->name}}</option>
                @endforeach
              </select>
            </div>   
          </div>
          <div class="col-lg-3">
            <div class="form-group">
              <button class="btn btn-primary " type="submit" style="margin-top: 25px;"><i class="fa fa-search"></i> Filter</button>
            </div>   
          </div>
        </div>
      </form>

      <div class="row mt-10">
        <div class="col-12">
          <div class="row">
            <div class="col-lg-6 text-left pt-20">
              @if(!empty(request()->get('customer_id')))
              {{$customer_filter->phone}} - {{$customer_filter->name}}<br>
              @endif
              @if(!empty(request()->get('sales_senior_id')))
              {{$sales_senior_filter->name}}
              @endif
              @if(!empty(request()->get('sales_senior_id')) && !empty(request()->get('sales_id')))
              -
              @endif
              @if(!empty(request()->get('sales_id')))
              {{$sales_filter->name}}
              @endif
            </div>
            <div class="col-lg-6 text-right">
              <h3><b>Revenue Report</b></h3>
              @if(!empty(request()->get('period_from')))
              Period From {{request()->get('period_from')}}
              @endif
              @if(!empty(request()->get('period_from')) && !empty(request()->get('period_to')))
              -
              @endif
              @if(!empty(request()->get('period_to')))
              {{request()->get('period_to')}}
              @endif
            </div>
          </div>
        </div>
      </div>
      <div class="row mt-20">
        <div class="col-12">
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <th>Date</th>
                <th>DO Number</th>
                <th>Invoice Number</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Due Date</th>
                <th>Is Due Date</th>
              </thead>
              <tbody>
                <?php 
                  $total_invoice = 0;
                  $total_paid = 0;

                  $due_total_invoice = 0;
                  $due_total_paid = 0;
                ?>
                @if(count($invoice) == 0)
                <tr>
                  <td colspan="8">Data tidak ditemukan</td>
                </tr>
                @endif
                @foreach($invoice as $index => $row)
                <tr>
                  <td>
                    <?= date('d-m-Y',strtotime($row['invoice']['created_at'])); ?>
                  </td>
                  <td>{{$row['do']['do_code']}}</td>
                  <td>{{$row['invoice']['code']}}</td>
                  <td>{{number_format($row['invoice']['grand_total_idr'],0,',','.')}}</td>
                  <td>
                    <?php
                      $payable = 0;
                    ?>
                    @foreach($row['payable'] as $payment)
                      <?php
                        $payable += $payment["total"];
                      ?>
                    @endforeach
                    {{number_format($payable,0,',','.')}}
                  </td>
                  <td>
                    <?php
                      $due_date = date('Y-m-d',strtotime($row['invoice']['created_at']."+ 30 days"));
                      $due_date_60 = date('Y-m-d',strtotime($row['invoice']['created_at']."+ 60 days"));

                      if($due_date <= date('Y-m-d') && $row['invoice']['grand_total_idr'] > $payable){
                        $due_payment = $row["invoice"]["grand_total_idr"] - $payable;
                        if($due_payment < 0){
                          $due_payment = 0;
                        }
                        $due_total_invoice += $due_payment;
                        foreach ($row["payable"] as $key => $value) {
                          $due_total_paid += $value["total"];
                        }
                      }
                    ?>
                    {{$due_date}}
                  </td>
                  <td>
                    @if($due_date <= date('Y-m-d') && $row['invoice']['grand_total_idr'] > $payable)
                      <span class="badge badge-warning badge-xs">H+30</span>
                    @elseif($due_date_60 <= date('Y-m-d') && $row['invoice']['grand_total_idr'] > $payable)
                      <span class="badge badge-danger badge-xs">H+60</span>
                    @endif
                    @if($row['invoice']['grand_total_idr'] <= $payable)
                      <span class="badge badge-success badge-xs">Paid Off</span>
                    @endif
                  </td>
                  <?php
                    $total_invoice += $row['invoice']['grand_total_idr'];
                    foreach ($row["payable"] as $key => $value) {
                      $total_paid += $value["total"];
                    }
                    
                  ?>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="row mt-20">
        <div class="col-12">
          <div class="row">
            <div class="col-12">
              <div class="row">
                <div class="col-lg-3">
                  Total Due Date Revenue
                </div>
                <div class="col-lg-9">
                  : {{number_format($due_total_invoice,0,',','.')}}
                </div>
              </div>
              <div class="row">
                <div class="col-lg-3">
                  Total Invoice
                </div>
                <div class="col-lg-9">
                  : {{number_format($total_invoice,0,',','.')}}
                </div>
              </div>
              <div class="row">
                <div class="col-lg-3">
                  Total Payment
                </div>
                <div class="col-lg-9">
                  : {{number_format($total_paid,0,',','.')}}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')

@push('scripts')

  <script type="text/javascript">
    
    $(function(){
      

      $('.js-select2').select2();

      $(document).on('click','.btn-print',function(){
          let period_to = '<?= $_GET["period_to"] ?? null ?>';
          let period_from = '<?= $_GET["period_from"] ?? null ?>';
          let customer_id = '<?= $_GET["customer_id"] ?? null ?>';
          let sales_senior_id = '<?= $_GET["sales_senior_id"] ?? null ?>';
          let sales_id = '<?= $_GET["sales_id"] ?? null ?>';

          window.open('{{route('superuser.report.revenue.print')}}'+'?period_from='+period_from+'&period_to='+period_to+'&customer_id='+customer_id+'&sales_senior_id='+sales_senior_id+'&sales_id='+sales_id,'_blank');
      })

    
    });
  </script>
@endpush
