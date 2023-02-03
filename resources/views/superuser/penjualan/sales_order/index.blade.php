@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <span class="breadcrumb-item active">Sales Order</span>
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

<!-- button add -->
{{--@if($step == 1 || $step == 9)
<div class="block">
  <div class="block-content block-content-full">
      <div class="row">
        <div class="col-lg-3">
          <a href="{{route('superuser.penjualan.sales_order.create', $step)}}" class="btn btn-primary"><i class="fa fa-plus"></i> Add Sales Order {{ $step_txt }} (SO)</a>
        </div>
        <div class="col-lg-3">
          
        </div>
      </div>
  </div>
</div>
@endif--}}

<!-- add SO via search -->
<div class="block">
  <div class="block-content block-content-full">
    <table id="customer-table" class="table ">
      <thead class="thead-dark">
        <tr>
          <th></th>
          <th>Store</th>
          <th>Address</th>
          <th>Category</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($customers as $row)
          <tr class="clickable js-tabularinfo-toggle" data-toggle="collapse" id="row2" data-target=".a{{ $row->id }}">
              <td>
                <div class="col-sm-6">
                  <div class="row mb-2">
                    <a href="#" class="link">
                      <button type="button" name='edit' id='{{ $row->id }}'>#</button>
                    </a>
                  </div>
                </div>
              </td>
              <td>{{ $row->name }}</td>
              <td>{{ $row->address }}</td>
              <td>{{ $row->category->name ?? '-' }}</td>
              <td>
                @if($row->status == $row::STATUS['ACTIVE'])
                  <span class="badge badge-success">ACTIVE</span>
                @elseif($row->status == $row::STATUS['DELETED'])
                  <span class="badge badge-danger">IN ACTIVE</span>
                @endif
              </td>
          </tr>

          <tr class="tabularinfo__subblock collapse a{{ $row->id }}">
                  <td colspan="8">
                    <table class="table-active table table-bordered">
                            <tr>
                                <th>Member</th>
                                <th>Invoice Brand</th>
                                <th width="10%"></th>
                            </tr>

                            <tbody>
                                @foreach ($other_address as $index)
                                    @if ($row->id == $index->customer_id)
                                        <tr>
                                            <td>{{ $index->name }}</td>
                                            <td>
                                              <select class="form-control js-select2 select-brand">
                                                <option value="">Pilih Brand Invoice</option>
                                                @foreach ($brand as $key => $i)
                                                  <option value="{{ $i->id }}">{{ $i->brand_name }}</option>
                                                @endforeach
                                              </select>
                                            </td>
                                            <td>
                                              @if($step == 1 || $step == 9)
                                                <a id="add-so" href="{{route('superuser.penjualan.sales_order.create', ['id' => $row->id, 'step' => $step, 'member' => $index->id])}}" class="btn btn-primary"><i class="fa fa-plus"></i> Add Sales Order {{ $step_txt }} (SO)</a>
                                              @endif
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                    </table>
                </td>
            </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<form method="post" action="{{route('superuser.penjualan.sales_order.destroy')}}" id="frmDestroyItem">
    @csrf
    <input type="hidden" name="id">
</form>
<form method="post" action="{{route('superuser.penjualan.sales_order.lanjutkan')}}" id="frmLanjutkan">
    @csrf
    <input type="hidden" name="id">
</form>
<form method="post" action="{{route('superuser.penjualan.sales_order.kembali')}}" id="frmKembali">
    @csrf
    <input type="hidden" name="id">
</form>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')


@push('scripts')

  <script type="text/javascript">
    
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

      $('#table-Customer').DataTable( {
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
        if(confirm("Apakah anda yakin ingin menghapus SO ini ? ")){
          let id = $(this).data('id');
          $('#frmDestroyItem').find('input[name="id"]').val(id);
          $('#frmDestroyItem').submit();
        }
      })

      $(document).on('click','.btn-lanjutan',function(){
        if(confirm("Apakah anda yakin ingin mengajukan sales order ke Lanjutan?")){
          let id = $(this).data('id');
          $('#frmLanjutkan').find('input[name="id"]').val(id);
          $('#frmLanjutkan').submit();
        }
      })

      $(document).on('click','.btn-kembali-ke-awal',function(){
        if(confirm("Apakah anda yakin ingin mengembalikan sales order ini?")){
          let id = $(this).data('id');
          $('#frmKembali').find('input[name="id"]').val(id);
          $('#frmKembali').submit();
        }
      })
    });
  </script>
@endpush
