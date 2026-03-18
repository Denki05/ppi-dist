@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sales Order</span>
  <span class="breadcrumb-item active">Proforma</span>
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

<div id="alert-block"></div>

<div class="block">
  <div class="block-content block-content-full">
    <table id="datatable" class="table table-bordred table-striped" style="width:100%">
      <thead>
        <tr>
          <th>#</th>
          <th>Created at</th>
          <th>Code</th>
          <th>Brand</th>
          <th>Customer</th>
          <th>Created By</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($results AS $row)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $row->created_at }}</td>
          <td>{{ $row->code }}</td>
          <td>{{ $row->so_brand_name }}</td>
          <td>
            @if($row->exsisting_customer == 0)
              {{$row->customer_name}}
            @elseif($row->exsisting_customer == 1)
            {{$row->member->name}} {{ $row->member->text_kota }}
            @endif
          </td>
          <td>{{ $row->createdBySuperuser() }}</td>
          <td>{{ $row->status }}</td>
          <td>
            @if($row->status == "DELETED")
              <a href="{{ route('superuser.penjualan.so_proforma.show', $row->id) }}">
                <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Show">
                  <i class="fa fa-eye"></i>
                </button>
              </a>
            @endif
            @if($row->status == "ACTIVE")
              @if(!empty($row->details_cost->grand_total_idr))
             <button type="button"
                class="btn btn-sm btn-circle btn-alt-danger btn-approval"
                data-url="{{ route('superuser.penjualan.so_proforma.acc', $row->id) }}"
                title="Acc">
                <i class="fa fa-check"></i>
            </button>
              @endif
              <a href="{{ route('superuser.penjualan.so_proforma.edit', $row->id) }}">
                <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Edit">
                  <i class="fa fa-pencil"></i>
                </button>
              </a>
              @if(!empty($row->details_cost->grand_total_idr))
              <a href="{{ route('superuser.penjualan.so_proforma.print_so_proforma', $row->id) }}">
                <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Print">
                  <i class="fa fa-print" aria-hidden="true"></i>
                </button>
              </a>
              @endif
              @if($row->so_id)
                <button type="button"
                    class="btn btn-sm btn-circle btn-alt-warning btn-rollback"
                    data-url="{{ route('superuser.penjualan.so_proforma.rollbackProforma', $row->so_id) }}"
                    title="Kembalikan ke SO Awal">
                    <i class="fa fa-undo"></i>
                </button>
              @endif
              <a href="javascript:deleteConfirmation('{{ route('superuser.penjualan.so_proforma.destroy', $row->id) }}')">
                <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete">
                  <i class="fa fa-trash"></i>
                </button>
              </a>
            @endif
            @if($row->status == "ACC")
              <a href="#">
                <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Show">
                  <i class="fa fa-eye"></i>
                </button>
              </a>
            @endif
            @if($row->status == "LANJUTAN")
              <a href="{{ route('superuser.penjualan.so_proforma.show', $row->id) }}">
                <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Show">
                  <i class="fa fa-eye"></i>
                </button>
              </a>
            @endif
            
            @if($row->status == "REVISI")
              @if(!empty($row->details_cost->grand_total_idr))
                 <button type="button"
                    class="btn btn-sm btn-circle btn-alt-danger btn-approval"
                    data-url="{{ route('superuser.penjualan.so_proforma.acc', $row->id) }}"
                    title="Acc">
                    <i class="fa fa-check"></i>
                </button>
              @endif
              <a href="{{ route('superuser.penjualan.so_proforma.edit', $row->id) }}">
                <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Edit">
                  <i class="fa fa-pencil"></i>
                </button>
              </a>
              @if(!empty($row->details_cost->grand_total_idr))
              <a href="{{ route('superuser.penjualan.so_proforma.print_so_proforma', $row->id) }}">
                <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Print">
                  <i class="fa fa-print" aria-hidden="true"></i>
                </button>
              </a>
              @endif
              @if($row->so_id)
                <button type="button"
                    class="btn btn-sm btn-circle btn-alt-warning btn-rollback"
                    data-url="{{ route('superuser.penjualan.so_proforma.rollbackProforma', $row->so_id) }}"
                    title="Kembalikan ke SO Awal">
                    <i class="fa fa-undo"></i>
                </button>
              @endif
              <a href="javascript:deleteConfirmation('{{ route('superuser.penjualan.so_proforma.destroy', $row->id) }}')">
                <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete">
                  <i class="fa fa-trash"></i>
                </button>
              </a>
            @endif
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript">
    var table = $('#datatable').DataTable({});
    
    $(document).on('click', '.btn-approval', function () {

        let button = $(this);
        let url = button.data('url');
    
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "SO akan diteruskan dan DO dibuat.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, lanjutkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
    
            if (result.isConfirmed) {
    
                button.prop('disabled', true);
    
                $.ajax({
                    url: url,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    beforeSend: function () {
                        Swal.fire({
                            title: 'Processing...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function (response) {
    
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.notification.content,
                            timer: 2000,
                            showConfirmButton: false
                        });
    
                        setTimeout(function () {
                            location.reload();
                        }, 2000);
                    },
                    error: function (xhr) {

                        button.prop('disabled', false);

                        let message = 'Terjadi kesalahan sistem';

                        if (xhr.responseJSON) {

                            if (xhr.responseJSON.notification) {
                                message = xhr.responseJSON.notification.content;

                                if (xhr.responseJSON.notification.file) {
                                    message += "\n\nFile : " + xhr.responseJSON.notification.file;
                                    message += "\nLine : " + xhr.responseJSON.notification.line;
                                }
                            }

                            if (xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message
                        });
                        }
                });
            }
        });
    });

    $(document).on('click', '.btn-rollback', function () {

let button = $(this);
let url = button.data('url');

Swal.fire({
    title: 'Kembalikan ke SO Awal?',
    text: "Proforma akan dihapus dan SO kembali ke tahap awal.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, kembalikan',
    cancelButtonText: 'Batal'
}).then((result) => {

    if (result.isConfirmed) {

        button.prop('disabled', true);

        $.ajax({
            url: url,
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}"
            },
            beforeSend: function () {
                Swal.fire({
                    title: 'Processing...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function (response) {

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: response.notification.content,
                    timer: 2000,
                    showConfirmButton: false
                });

                setTimeout(function () {
                    location.reload();
                }, 2000);
            },
            error: function () {

                button.prop('disabled', false);

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan sistem'
                });
            }
        });
    }
});
});
</script>
@endpush