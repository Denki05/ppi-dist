@extends('superuser.app')

@section('content')
{{--<div class="row gutters-tiny">
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
</div>--}}

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
                      <option value="">==All Provinsi==</option>
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
          <div class="table-responsive">
                      <table class="table table-hover" id="datatables">
                        <thead>
                          <tr>
                            <th>Invoice Date</th>
                            <th>Invoice Number</th>
                            <th>Store | Member</th>
                            <th>Area</th>
                            <th>Revenue</th>
                            <th>Paid</th>
                            <th>Due Date</th>
                            <th>Is Due Date</th>
                          </tr>
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
                            <td>
                              @if($row->do->other_address == 0)
                                {{$row->do->customer->name ?? '-'}}
                              @elseif($row->do->other_address == 1)
                                {{$row->do->member->name ?? '-'}}
                              @endif
                            </td>
                            <td>
                              <b>
                              @if($row->do->other_address == 0)
                                {{$row->do->customer->text_provinsi ?? '-'}}
                              @elseif($row->do->other_address == 1)
                                {{$row->do->member->text_provinsi ?? '-'}}
                              @endif
                              </b>
                            </td>
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
                                <span class="badge badge-warning">H+30</span>
                              @elseif($due_date_60 <= date('Y-m-d') && $row->grand_total_idr > $row->payable_detail->sum('total'))
                                <span class="badge badge-danger">H+60</span>
                              @endif
                              @if($row->grand_total_idr <= $row->payable_detail->sum('total'))
                                <span class="badge badge-success">Paid Off</span>
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
        @endif
      </div>
      
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-6">
    <div class="block">
      <div class="block-header block-header-default">
        <h4 class="block-title">Statistik Sales Order</h4>
      </div>
      <div class="block-content block-content-full">
      <canvas id="mataChart" class="chartjs" width="undefined" height="undefined"></canvas>
      </div>
    </div>
  </div>
  {{--<div class="col-md-6">
    <div class="block">
      <div class="block-header block-header-default">
        <h4 class="block-title">Statistik Brand</h4>
      </div>
      <div class="block-content block-content-full">
      <canvas id="mataChart2" class="chartjs" width="undefined" height="undefined"></canvas>
      </div>
    </div>
  </div>--}}
</div>

@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.chart')

@push('scripts')

<script type="text/javascript">
  $(function(){
    $('#datatables').dataTable( {
    });

    $('.js-select2').select2();
  })

  var ctx = document.getElementById('mataChart').getContext('2d');
  var myChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($label); ?>,
        datasets: [{
            label: 'Statistik Sales Order',
            data: <?php echo json_encode($jumlah_so); ?>,
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)'
            ],
        }
      ]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

  var ctx = document.getElementById('mataChart2').getContext('2d');
  var myChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($label); ?>,
        datasets: [{
            label: 'Statistik Brand Order',
            data: <?php echo json_encode($jumlah_pay); ?>,
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)'
            ],
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>


@endpush