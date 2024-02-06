@extends('superuser.app')

@section('content')

<div class="row">
  <div class="col-12">
    <div class="block">
      <div class="block-content block-content-full">
        @role('Developer', 'superuser')
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
                      @foreach($tabelProvinsi as $index => $row)
                      <option value="{{$row->prov_id}}">{{$row->prov_name}}</option>
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
                            <th>Invoice Code</th>
                            <th>Customer</th>
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
                            <td>{{ $row->do->customer->name ?? '' }}</td>
                            <td>
                              <b>
                              {{$row->do->customer->text_provinsi ?? ''}}
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
        @endrole
      </div>
      
    </div>
  </div>
</div>

@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
  $( document ).ready(function() {
    $('#datatables').dataTable( {
      paging   :  true,
      info     :  false,
      searching : true,
      order: [
        [2, 'desc']
      ],
      pageLength: 10,
      lengthMenu: [
        [10, 30, 100, -1],
        [10, 30, 100, 'All']
      ],
    });

    $('.js-select2').select2();
  });
</script>
@endpush