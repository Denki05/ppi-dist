@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <span class="breadcrumb-item active">Packing Order</span>
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
<!--
<div class="block">
  <div class="block-content block-content-full">
      <div class="row">
        <div class="col-12">
          <a href="{{route('superuser.penjualan.packing_order.create')}}" class="btn btn-primary"><i class="fa fa-plus"></i> Add Packing Order(PO)</a>
        </div>
      </div>
  </div>
</div>
-->
<div class="block">
  <hr class="my-20">
  <div class="block-content block-content-full">
      <form method="get" action="{{ route('superuser.penjualan.packing_order.index') }}">
        <div class="row">
          <div class="col-lg-3">
            <div class="form-group">
              <select class="form-control js-select2" name="field">
                <option value="">==Field==</option>
                <option value="code" {{isset($_GET['field']) && $_GET['field'] == 'code' ? 'selected="selected"' : ''}}>Code</option>
                <option value="customer" {{isset($_GET['field']) && $_GET['field'] == 'customer' ? 'selected="selected"' : ''}}>Customer</option>
                <option value="referensiSO" {{isset($_GET['field']) && $_GET['field'] == 'referensiSO' ? 'selected="selected"' : ''}}>Referensi SO</option>
                <option value="transaksi" {{isset($_GET['field']) && $_GET['field'] == 'transaksi' ? 'selected="selected"' : ''}}>Transaksi</option>
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
          <table class="table table-striped table-vcenter table-responsive" id="datatables">
            <thead>
              <tr>
                <th>#</th>
                <th>Code</th>
                <th>Customer</th>
                <th>Kategori / Tanggal</th>
                <th>Referensi SO</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($table as $index => $row)
                <tr>
                  <td>{{$index+1}}</td>
                  <td>
                    @if($row->status != 1)
                      <a href="{{route('superuser.penjualan.packing_order.edit',$row->id)}}">{{$row->code}}</a>
                    @else
                      {{$row->code}}
                    @endif
                  </td>
                  <td>
                    {{$row->customer->name ?? ''}}
                  </td>
                  <td>
                    {{$row->do_detail[0]->so_item->product->category->name ?? ''}} / <?= date('d-m-Y h:i:s',strtotime($row->created_at)); ?>
                  </td>
                  <td>{{$row->do_detail[0]->so_item->so->code ?? ''}}</td>
                  <td>
                    @if($row->status <= 3)
                    <span
                        class="badge badge-{{ $row->do_status()->class }}">{{ $row->do_status()->msg }}</span>
                    @else
                    <span
                        class="badge badge-info">Ready</span>
                    @endif
                  </td>
                  <td>
                    @if($row->status == 1)
                    <div class="d-flex mb-2">
                      <a href="{{route('superuser.penjualan.packing_order.edit',$row->id)}}" class="btn btn-primary btn-sm btn-flat mx-1"><i class="fa fa-edit"></i> Kerjakan</a>
                      <a href="#" class="btn btn-danger btn-sm btn-flat btn-delete" data-id="{{$row->id}}"><i class="fa fa-trash"></i> Delete</a>
                    </div>
                    @elseif($row->status == 2)
                    <div class="d-flex mb-2">
                      <a href="#" class="btn btn-success btn-sm btn-flat btn-ready" data-id="{{$row->id}}"><i class="fa fa-send"></i> Naik Ke DO</a>
                      @if($row->invoicing != null)
                      <a href="{{route('superuser.finance.invoicing.print_proforma',$row->invoicing->id)}}" class="btn btn-info btn-sm btn-flat mx-1" data-id="{{$row->invoicing->id}}" target="_blank"><i class="fa fa-print"></i> Print Proforma</a>
                      <a href="{{route('superuser.finance.invoicing.print',$row->invoicing->id)}}" class="btn btn-primary btn-sm btn-flat mx-1" data-id="{{$row->invoicing->id}}" target="_blank"><i class="fa fa-print"></i> Print Invoice</a>
                      @endif
                    </div>
                    @elseif($row->status == 3)
                    <div class="d-flex mb-2">
                      @if($row->invoicing != null)
                      <a href="{{route('superuser.finance.invoicing.print_proforma',$row->invoicing->id)}}" class="btn btn-info btn-sm btn-flat mx-1" data-id="{{$row->invoicing->id}}" target="_blank"><i class="fa fa-print"></i> Print Proforma</a>
                      <a href="{{route('superuser.finance.invoicing.print',$row->invoicing->id)}}" class="btn btn-primary btn-sm btn-flat mx-1" data-id="{{$row->invoicing->id}}" target="_blank"><i class="fa fa-print"></i> Print Invoice</a>
                      @endif
                    </div>
                    @else
                    <div class="d-flex mb-2">
                      @if($row->invoicing != null)
                      <a href="{{route('superuser.finance.invoicing.print_proforma',$row->invoicing->id)}}" class="btn btn-info btn-sm btn-flat mx-1" data-id="{{$row->invoicing->id}}" target="_blank"><i class="fa fa-print"></i> Print Proforma</a>
                      <a href="{{route('superuser.finance.invoicing.print',$row->invoicing->id)}}" class="btn btn-primary btn-sm btn-flat mx-1" data-id="{{$row->invoicing->id}}" target="_blank"><i class="fa fa-print"></i> Print Invoice</a>
                      @endif
                    </div>
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
<form method="post" action="{{route('superuser.penjualan.packing_order.prepare')}}" id="frmPrepare">
    @csrf
    <input type="hidden" name="id">
</form>
<form method="post" action="{{route('superuser.penjualan.packing_order.order')}}" id="frmOrder">
    @csrf
    <input type="hidden" name="id">
</form>
<form method="post" action="{{route('superuser.penjualan.packing_order.ready')}}" id="frmReady">
    @csrf
    <input type="hidden" name="id">
</form>
<form method="post" action="{{route('superuser.penjualan.packing_order.packed')}}" id="frmPacked">
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

        $(document).on('click','.btn-delete',function(){
          if(confirm("Apakah anda yakin ingin menghapus packing order ini? ")){
            let id = $(this).data('id');
            $('#frmDestroyItem').find('input[name="id"]').val(id);
            $('#frmDestroyItem').submit();
          }
        })

        $(document).on('click','.btn-order',function(){
          if(confirm("Apakah anda yakin ingin mengubah status packing order ke ordered?")){
            let id = $(this).data('id');
            $('#frmOrder').find('input[name="id"]').val(id);
            $('#frmOrder').submit();
          }
        })

        $(document).on('click','.btn-ready',function(){
          if(confirm("Apakah anda yakin ingin mengubah status packing order ke Ready?")){
            let id = $(this).data('id');
            $('#frmReady').find('input[name="id"]').val(id);
            $('#frmReady').submit();
          }
        })

        $(document).on('click','.btn-packed',function(){
          if(confirm("Apakah anda yakin ingin mengubah status packing order ke Packed?")){
            let id = $(this).data('id');
            $('#frmPacked').find('input[name="id"]').val(id);
            $('#frmPacked').submit();
          }
        })
      });
    })
  </script>
@endpush
