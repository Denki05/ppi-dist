@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Finance</span>
  <span class="breadcrumb-item active">Invoicing Cash</span>
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
    <div class="card">
        <!-- <div class="card-header">
            Quote
        </div> -->
        <div class="card-body">
            <table class="table table-striped" id="datatables">
                <thead>
                <tr>
                    <th>#</th>
                    <th>PRF Code</th>
                    <th>SO Code</th>
                    <th>Transaction</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($proforma as $index => $row)
                    <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        {{$row->code}}
                    </td>
                    <td>{{$row->so->code ?? ''}}</td>
                    <td>{{$row->so_type_transaction()->scalar ?? ''}}</td>
                    <td>{{$row->so->member->name ?? ''}}</td>
                    <td>{{number_format($row->grand_total_idr,0,',','.')}}</td>
                    <td>{{ $row->status }}</td>
                    <td>
                        <?= date('d-m-Y h:i:s',strtotime($row->created_at)); ?>
                    </td>
                    <td>
                        <a class="btn btn-primary" href="#" role="button"><i class="fa fa-money" aria-hidden="true"></i></a>
                        <a class="btn btn-danger btn-cancel" data-id="{{$row->id}}" href="#" role="button"><i class="fa fa-ban" aria-hidden="true"></i></a>
                    </td>
                    </tr>
                @endforeach
                </tbody>
          </table>
        </div>
    </div>
</div>

<form method="post" action="{{route('superuser.finance.proforma.cancel')}}" id="frmCancel">
  @csrf
  <input type="hidden" name="id">
</form>
@endsection

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

        $(document).on('click','.btn-cancel',function(){
          if(confirm("Apakah anda yakin ingin 'Cancel/Revisi' Proforma ini!")){
            let id = $(this).data('id');
            $('#frmCancel').find('input[name="id"]').val(id);
            $('#frmCancel').submit();
          }
        })

      });
    })
  </script>
@endpush