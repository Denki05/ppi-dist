@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sales</span>
  <span class="breadcrumb-item active">SO Khusus</span>
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
  <div class="block-content">
    <a href="{{ route('superuser.penjualan.sales_order_ppn.create') }}">
      <button type="button" class="btn btn-outline-primary min-width-125">Create</button>
    </a>
    <hr>
  </div>
  <div class="block-content block-content-full">
    <input style="display: none;" id="tab1" type="radio" name="tabs" checked>
    <label style="padding: 15px 25px;" for="tab1">SO Khusus</label>
      
    <input style="display: none;" id="tab2" type="radio" name="tabs">
    <label style="padding: 15px 25px;" for="tab2">SO PPN</label>

    <section id="content1">
      <div class="row mb-30">
        <div class="col-12">
          <table class="table table-striped" id="khusus">
            <thead>
              <tr>
                <th>#</th>
                <th>Code</th>
                <th>Tanggal</th>
                <th>Customer</th>
                <th>Sales</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($so_khusus as $row => $key)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $key->code }}</td>
                  <td>{{ $key->so_date }}</td>
                  <td>{{ $key->member->name }}</td>
                  <td>{{ $key->so_sales() }}</td>
                  <td></td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <section id="content2">
      <div class="row mb-30">
        <div class="col-12">
          <table class="table table-striped" id="ppn">
            <thead>
              <tr>
                <th>#</th>
                <th>Code</th>
                <th>Tanggal</th>
                <th>Customer</th>
                <th>Sales</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($so_ppn as $row => $key)
              
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>{{ $key->code }}</td>
                  <td>{{ $key->so_date }}</td>
                  <td>{{ $key->member->name }}</td>
                  <td>{{ $key->so_sales() }}</td>
                  <td>
                    <a class="btn btn-primary" href="{{ route('superuser.penjualan.sales_order_ppn.show', $key->id) }}" role="button">Show</a>
                  </td>
                </tr>
                
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</div>


<!-- <form method="post" action="{{route('superuser.penjualan.sales_order_ppn.delete')}}" id="frmDestroyItem">
    @csrf
    <input type="hidden" name="id">
</form> -->
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  // $(function(){
  //   $('#datatables').DataTable( {
  //       "paging":   false,
  //       "ordering": true,
  //       "info":     false,
  //       "searching" : false,
  //       "columnDefs": [{
  //         "targets": 0,
  //         "orderable": false
  //       }]
  //     });

  //     $('.js-select2').select2();
  // });
  $(document).ready(function() {
    $('#khusus').DataTable({
      "ordering": true,
      "info":     false,
    })

    $('#ppn').DataTable({
      "ordering": true,
      "info":     false,
    })

    $('.js-select2').select2();
  });
</script>
@endpush
