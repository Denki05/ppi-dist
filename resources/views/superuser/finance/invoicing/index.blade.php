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
  <div class="block-content block-content-full">
      {{--<div class="row mb-30">
        <div class="col-12">
          <a href="#" class="btn btn-primary btn-add"><i class="fa fa-plus"></i> Add Invoicing</a>
        </div>
      </div>--}}
      <div class="row mb-30">
        <div class="col-12">
          <table class="table table-striped" id="datatables">
            <thead>
              <tr>
                <th>#</th>
                <th>INV Code</th>
                <th>DO Code</th>
                <th>Acccount</th>
                <th>Member</th>
                <th>Total</th>
                <th>Created At</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($table as $index => $row)
                @if($row->status != 3)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>
                    <a href="{{route('superuser.finance.invoicing.history_payable',$row->id)}}">{{$row->code}}</a>
                  </td>
                  <td>{{$row->do->do_code ?? ''}}</td>
                  <td>{{$row->do->customer->name}} {{ $row->do->customer->text_kota }}</td>
                  <td>{{$row->do->member->name ?? ''}} {{ $row->do->customer->text_kota }}</td>
                  <td>{{number_format($row->grand_total_idr,0,',','.')}}</td>
                  <td>
                    <?= date('d-m-Y h:i:s',strtotime($row->created_at)); ?>
                  </td>
                  <td>
                    <a href="{{route('superuser.finance.invoicing.detail',$row->do->id ?? 0)}}" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-eye"></i> Detail</a>
                    <a href="{{route('superuser.finance.invoicing.print',$row->id)}}" class="btn btn-info btn-sm btn-flat" data-id="{{$row->id}}" target="_blank"><i class="fa fa-print"></i> Print</a>
                    @if($row->do->status <= 6 OR $row->do->type_transaction == "CASH" AND $row->do->so->shipping_cost_buyer == 1)
                      <a href="{{route('superuser.finance.invoicing.print2',$row->id)}}" class="btn btn-info btn-sm btn-flat" data-id="{{$row->id}}" target="_blank"><i class="fa fa-print"></i> Print FULL</a>
                    @endif
                  </td>
                </tr>
                @endif
              @endforeach
            </tbody>
          </table>
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

        $(document).on('click','.btn-add',function(){
          $('#modalSelectDO').modal('show');
        })

      });
    })
  </script>
@endpush
