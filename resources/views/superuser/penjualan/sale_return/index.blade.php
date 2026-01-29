@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <span class="breadcrumb-item active">Nota Kredit</span>
</nav>

@if (session('success'))
    <div id="alert-message" class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div id="alert-message" class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="block">
  <div class="block-content">
    <a href="{{ route('superuser.penjualan.sale_return.create') }}">
      <button type="button" class="btn btn-outline-primary min-width-125">Create</button>
    </a>
  </div>
  <hr class="my-20">
  <div class="block-content block-content-full">
    <table id="datatables" class="table table-striped">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">Created at</th>
          <th class="text-center">Type</th>
          <th class="text-center">Code</th>
          <th class="text-center">Customer</th>
          <th class="text-center">Reff Invoice</th>
          <th class="text-center">Status</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($sales_return as $index => $row)
          <tr>
            <td>{{$index+1}}</td>
            <td>{{$row->created_at}}</td>
            <td>{{$row->type()}}</td>
            <td>{{$row->code}}</td>
            <td>{{$row->customer->name}} {{ $row->customer->text_kota }}</td>
            <td>{{$row->invoice->do_code ?? '-'}}</td>
            <td>{{$row->status()}}</td>
            <td>
            @if($superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Developer" OR $superuser->division == "Finance")
              <a href="{{ route('superuser.penjualan.sale_return.show', $row->id) }}" class="btn btn-sm btn-primary" title="Show"><i class="fa fa-eye"></i></a>
              @if($row->status() == 'ACTIVE')
                <form action="{{ route('superuser.penjualan.sale_return.acc', $row->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to confirm this retur?');">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-success">
                      <i class="fa fa-check"></i>
                  </button>
                </form>
                <form action="{{ route('superuser.penjualan.sale_return.destroy', $row->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this sale return?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this sale return?');"><i class="fa fa-trash"></i></button>
                </form>
              @endif
              @if($row->status() == 'ACC')
                <a href="{{ route('superuser.penjualan.sale_return.pdf', $row->id) }}" class="btn btn-sm btn-success" target="_blank" title="Nota Kredit"><i class="fa fa-file-invoice"></i></a>

                @if($row->type == 2)
                <form action="{{ route('superuser.penjualan.sale_return.proses', $row->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah sudah membuat Sales Order baru?');">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-info">
                      <i class="fa fa-arrow-right" aria-hidden="true"></i>
                  </button>
                </form>
                @endif
              @endif

              @if($row->status() == 'PROSES')
                <a href="{{ route('superuser.penjualan.sale_return.pdf', $row->id) }}" class="btn btn-sm btn-success" target="_blank" title="Nota Kredit"><i class="fa fa-file-invoice"></i></a>
              @endif
            @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(function(){
    $('#datatables').DataTable( {
        "paging":   true,
        "ordering": true,
        "info":     false,
        "searching" : true,
        "columnDefs": [{
          "targets": 0,
          "orderable": false
        }]
    });

      setTimeout(function() {
        const alert = document.getElementById('alert-message');
        if (alert) {
            // Menggunakan Bootstrap 5 API untuk dismiss alert
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }
    }, 4000);
  });
</script>
@endpush