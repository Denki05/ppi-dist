@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Finance</span>
  <span class="breadcrumb-item active">Invoicing</span>
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
          <a href="#" class="btn btn-primary btn-add"><i class="fa fa-plus"></i> Add Invoicing</a>
        </div>
      </div>
      <form method="get" action="{{ route('superuser.finance.invoicing.index') }}">
        <div class="row">
          <div class="col-lg-3">
            <div class="form-group">
              <select class="form-control js-select2" name="do_id">
                <option value="">==All DO Code==</option>
                @foreach($order as $index => $row)
                <option value="{{$row->id}}">{{$row->do_code}} - {{$row->customer->name ?? ''}}</option>
                @endforeach
              </select>
            </div>          
          </div>
          <div class="col-lg-3">
            <div class="form-group">
              <select class="form-control js-select2" name="customer_id">
                <option value="">==All Customer==</option>
                @foreach($customer as $index => $row)
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
          <table class="table table-striped table-vcenter table-responsive" id="datatables">
            <thead>
              <tr>
                <th>#</th>
                <th>INV Code</th>
                <th>DO Code</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Created At</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($table as $index => $row)
                <tr>
                  <td>{{$table->firstItem() + $index}}</td>
                  <td>
                    <a href="{{route('superuser.finance.invoicing.history_payable',$row->id)}}">{{$row->code}}</a>
                  </td>
                  <td>{{$row->do->do_code ?? ''}}</td>
                  <td>{{$row->do->customer->name ?? ''}}</td>
                  <td>{{number_format($row->grand_total_idr,0,',','.')}}</td>
                  <td>
                    <?= date('d-m-Y h:i:s',strtotime($row->created_at)); ?>
                  </td>
                  <td>
                    <a href="{{route('superuser.finance.invoicing.detail',$row->do->id ?? 0)}}" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-eye"></i> Detail</a>
                    <a href="{{route('superuser.finance.invoicing.print',$row->id)}}" class="btn btn-info btn-sm btn-flat" data-id="{{$row->id}}" target="_blank"><i class="fa fa-print"></i> Print Invoice</a>
                    <a href="{{route('superuser.finance.invoicing.print_proforma',$row->id)}}" class="btn btn-info btn-sm btn-flat" data-id="{{$row->id}}" target="_blank"><i class="fa fa-print"></i> Print Proforma</a>
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

@include('superuser.finance.invoicing.modal')
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

        $(document).on('click','.btn-add',function(){
          $('#modalSelectDO').modal('show');
        })

      });
    })
  </script>
@endpush
