@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <span class="breadcrumb-item active">Delivery Order</span>
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
      <form method="get" action="{{ route('superuser.penjualan.delivery_order.index') }}">
        <div class="row">
          <div class="col-lg-3">
            <div class="form-group">
              <select class="form-control js-select2" name="field">
                <option value="">==Field==</option>
                <option value="do_code" {{isset($_GET['field']) && $_GET['field'] == 'do_code' ? 'selected="selected"' : ''}}>DO Code</option>
                <option value="referensiSO" {{isset($_GET['field']) && $_GET['field'] == 'referensiSO' ? 'selected="selected"' : ''}}>Referensi SO</option>
                <option value="customer" {{isset($_GET['field']) && $_GET['field'] == 'customer' ? 'selected="selected"' : ''}}>Customer</option>
              </select>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Keyword" name="search" value="{{isset($_GET['search']) && $_GET['search'] ? $_GET['search'] : ''}}">
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
                <th>DO Code</th>
                <th>Referensi SO</th>
                <th>Store / Member</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($table as $index => $row)
                <tr>
                  <td>{{$index+1}}</td>
                  <td>
                    {{$row->do_code}}
                  </td>
                  <td>{{$row->do_detail[0]->so_item->so->code ?? ''}}</td>
                  <td>
                    {{$row->customer->name ?? ''}} / {{$row->member->name ?? ''}}
                  </td>
                  <td>
                    <span
                        class="badge badge-{{ $row->do_status()->class }}">{{ $row->do_status()->msg }}</span>
                  </td>
                  <td>
                    <a href="{{route('superuser.penjualan.delivery_order.detail',$row->id)}}" class="btn btn-primary btn-sm btn-flat">
                      @if($row->status == 3)
                      <i class="fa fa-truck"></i> Kerjakan
                      @endif
                      @if($row->status == 4)
                      <i class="fa fa-truck"></i> Kerjakan
                      @endif
                      @if($row->status == 5)
                      <i class="fa fa-truck"></i> Selesaikan
                      @endif
                      @if($row->status == 6)
                      <i class="fa fa-eye"></i> View
                      @endif
                    </a>
                    @if($row->invoicing != null)
                    <a href="{{route('superuser.finance.invoicing.print_proforma',$row->invoicing->id)}}" class="btn btn-info btn-sm btn-flat mx-1" data-id="{{$row->invoicing->id}}" target="_blank"><i class="fa fa-print"></i> Print Proforma</a>
                    @endif
                    @if($row->status > 2)
                    <a href="{{route('superuser.penjualan.delivery_order.print',$row->id)}}" class="btn btn-info btn-sm btn-flat" data-id="{{$row->id}}" target="_blank"><i class="fa fa-print"></i> Print DO</a>
                    @endif
                    @if($row->status == 2 || $row->status == 1)
                    <a href="{{route('superuser.penjualan.delivery_order.print_manifest',$row->id)}}" class="btn btn-info btn-sm btn-flat" data-id="{{$row->id}}" target="_blank"><i class="fa fa-print"></i> Print Manifest</a>
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
<form method="post" action="{{route('superuser.penjualan.packing_order.destroy')}}" id="frmDestroyItem">
    @csrf
    <input type="hidden" name="id">
</form>
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

        $(document).on('click','.btn-delete',function(){
          if(confirm("Yakin ? ")){
            let id = $(this).data('id');
            $('#frmDestroyItem').find('input[name="id"]').val(id);
            $('#frmDestroyItem').submit();
          }
        })
      });
    })
  </script>
@endpush
