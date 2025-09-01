@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <span class="breadcrumb-item active">Retur</span>
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
            <td>{{$row->code}}</td>
            <td>{{$row->customer->name}} {{ $row->customer->text_kota }}</td>
            <td>{{$row->invoice->do_code}}</td>
            <td>{{$row->status()}}</td>
            <td>
            @if($superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Developer")
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
                @if($row->do_new_id == null)
                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#notaModal{{ $row->id }}" title="Upload Nota Pengganti">
                  <i class="fa fa-upload"></i>
                </button>
                @endif

                <!-- Modal for Nota Pengganti -->
                <div class="modal fade" id="notaModal{{ $row->id }}" tabindex="-1" aria-labelledby="notaModalLabel{{ $row->id }}" aria-hidden="true">
                  <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="notaModalLabel{{ $row->id }}">
                          
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">

                        <table class="table table-bordered table-sm">
                          <thead>
                            <tr>
                              <th>#</th>
                              <th>Kode SO</th>
                              <th>Tanggal</th>
                              <th>Aksi</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach($salesOrder->where('customer_other_address_id', $row->customer_other_address_id) as $so)
                              <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $so->code }}</td>
                                <td>{{ \Carbon\Carbon::parse($so->created_at)->format('d-m-Y') }}</td>
                                <td>
                                  <button class="btn btn-sm btn-primary pilih-nota"
                                          data-so="{{ $so->do[0]->id }}"
                                          data-retur="{{ $row->id }}">
                                    Pilih
                                  </button>
                                </td>
                              </tr>
                            @endforeach
                          </tbody>
                        </table>

                      </div>
                    </div>
                  </div>
                </div>
              @endif
              @if($row->status() == 'ACC')
                <a href="{{ route('superuser.penjualan.sale_return.pdf', $row->id) }}" class="btn btn-sm btn-success" target="_blank" title="Nota Kredit"><i class="fa fa-file-invoice"></i></a>
                {{--<a href="{{ route('superuser.penjualan.sale_return.pdf_sj', $row->id) }}" class="btn btn-sm btn-info" target="_blank" title="Surat Jalan"><i class="fa fa-file-pdf-o" aria-hidden="true"></i></a>--}}
                {{--@if($row->payment_status() == 'BELUM')
                  <a href="{{ route('superuser.penjualan.sale_return.pdf_tt', $row->id) }}" class="btn btn-sm btn-info" target="_blank" title="TT"><i class="fa fa-files-o" aria-hidden="true"></i></a>
                @endif--}}
              @endif
            @endif
            @if($superuser->division == "Finance")
                @if($row->status() == 'ACC')
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
  $(document).on('click', '.pilih-nota', function() {
    let soId = $(this).data('so');
    let returId = $(this).data('retur');

    let url = "{{ route('superuser.penjualan.sale_return.updateDoNewId', ':id') }}";
    url = url.replace(':id', returId);

    $.ajax({
        url: url,
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            do_new_id: soId
        },
        success: function(res) {
            if (res.success) {
                alert(res.message);
                location.reload();
            } else {
                alert("⚠️ Gagal: " + res.message);
            }
        },
        error: function(xhr) {
            console.error(xhr.responseText);
            alert("❌ Gagal update DO baru! Cek console log.");
        }
    });
});

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