@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Finance</span>
  <span class="breadcrumb-item active">Payable</span>
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
          <a href="#" class="btn btn-primary btn-add"><i class="fa fa-plus"></i> Add Payable</a>
        </div>
      </div>
      <form method="get" action="{{ route('superuser.finance.payable.index') }}">
        <div class="row">
          <div class="col-lg-3">
            <div class="form-group">
              <select class="form-control js-select2" name="customer_id">
                <option value="">==All Customer==</option>
                @foreach($customer as $index => $row)
                  <option value="{{$row->id}}">{{$row->name}} {{$row->text_kota}}</option>
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
          <table class="table table-striped" id="datatables">
            <thead>
              <tr>
                <th>#</th>
                <th>Payable Code</th>
                <th>Store</th>
                <th>Total</th>
                <th>Created At</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($table as $index => $row)
                <tr>
                  <td>{{$table->firstItem() + $index}}</td>
                  <td>{{$row->code}}</td>
                  <td>{{$row->customer->name}} {{ $row->customer->text_kota }}</td>
                  <td>{{number_format($row->total,0,',','.')}}</td>
                  <td>
                    <?= date('d-m-Y h:i:s',strtotime($row->created_at)); ?>
                  </td>
                  <td>
                    {{ $row->status() }}
                  </td>
                  <td>
                    @if($row->status == 1)
                      <a class="btn btn-warning" href="#" role="button" title="edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                      <a class="btn btn-success" href="javascript:saveConfirmation('{{ route('superuser.finance.payable.approve', $row->id) }}')" role="button" title="acc"><i class="fa fa-check" aria-hidden="true"></i></a>
                      <a class="btn btn-danger" href="javascript:deleteConfirmation('{{ route('superuser.finance.payable.destroy', $row->id) }}')" role="button" title="delete"><i class="fa fa-trash" aria-hidden="true"></i></a>
                    @endif
                    @if($row->status == 2)
                      <a class="btn btn-info" href="{{ route('superuser.finance.payable.detail', $row->id) }}" role="button" title="view"><i class="fa fa-eye" aria-hidden="true"></i></a>
                      <a class="btn btn-danger" href="javascript:saveConfirmation('{{ route('superuser.finance.payable.cancel_approve', $row->id) }}')" role="button" title="Cancel Acc"><i class="fa fa-times" aria-hidden="true"></i></a>
                    @endif
                    @if($row->status == 3)
                      <a class="btn btn-warning" href="{{ route('superuser.finance.payable.cancel_edit', $row->id) }}" role="button" title="edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                    @endif
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

@include('superuser.finance.payable.modal')


@endsection

<!-- Modal -->


@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')

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
          $('#modalSelectCustomer').modal('show');
        })

        $("#select_customer").select2({
            dropdownParent: $('#modalSelectCustomer .modal-content')
        });

      });
    })
  </script>
@endpush