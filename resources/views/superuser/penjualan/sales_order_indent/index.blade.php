@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sale</span>
  <span class="breadcrumb-item">Sales Order</span>
  <span class="breadcrumb-item active">Indent</span>
</nav>

@if($errors->any())
<div class="alert alert-danger alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Error</h3>
  @foreach ($errors->all() as $error)
  <p class="mb-0">{{ $error }}</p>
  @endforeach
</div>
@endif

<div id="alert-block"></div>

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

@if(session()->has('message'))
<div class="alert alert-success alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Success</h3>
  <p class="mb-0">{{ session()->get('message') }}</p>
</div>
@endif

@if(session('notification'))
    <div class="alert alert-{{ session('notification')['type'] }} alert-dismissible fade show" role="alert">
        <strong>{{ session('notification')['header'] ?? '' }}</strong> {!! session('notification')['content'] !!}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="block">
  <div class="block-content">
    @if(session('is_from_agenda', false))
      <button id="btn-back-agenda" class="btn btn-warning">
        ← Kembali ke Daftar SO
      </button>
    @endif

    <button type="button" class="btn btn-outline-info ml-10" data-toggle="modal" data-target="#modal-manage">Manage</button>
  </div>
  <div class="block-content block-content-full">
    <table id="datatable" class="table table-striped">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">Date</th>
          <th class="text-center">Code</th>
          <th class="text-center">Customer</th>
          <th class="text-center">Status</th>
          <th class="text-center">Created By</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($sales_order as $key)
            <tr>
                <td>{{$loop->iteration}}</td>
                <td>{{ $key->created_at }}</td>
                <td>{{ $key->so_code }}</td>
                <td>{{ $key->member->name }} {{ $key->member->text_kota }}</td>
                <td>{{ $key->so_indent_status() ?? '-' }}</td>
                <td>{{ $key->createdBySuperuser() ?? '-' }}</td>
                <td>
                    <button type="button" class="btn btn-info btn-sm btn-flat" data-toggle="modal" data-target="#myModal{{$key->id}}"><i class="fa fa-eye mr-10" aria-hidden="true"></i> View</button>
                    <a href="{{route('superuser.penjualan.sales_order_indent.print_out_indent',$key->id)}}" class="btn btn-primary btn-sm btn-flat" data-id="{{$key->id}}" target="_blank"><i class="fa fa-print"></i> Print</a>
                    <a class="btn btn-danger btn-sm btn-flat" href="javascript:deleteConfirmation('{{ route('superuser.penjualan.sales_order_indent.destroy', $key->id) }}')" role="button"><i class="fa fa-trash mr-10" aria-hidden="true"></i> Hapus</a>
                </td>
            </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<!-- modal show -->
@foreach($sales_order as $key)
<div class="modal fade bd-example-modal-lg" id="myModal{{$key->id}}" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">View #{{$key->so_code}}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="{{ route('superuser.penjualan.sales_order_indent.proses_ready') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="row">
            <div class="col">
              <div class="block">
                <div class="block-header block-header-default">
                  <h3 class="block-title">#Detail Nota Indent</h3>
                </div>
                <div class="block-content">
                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label for="invoice_date">Tanggal Indent</label>
                      <input type="text" name="invoice_date" class="form-control" value="{{ date('d-m-Y', strtotime($key->created_at)) }}" readonly>
                    </div>
                    <div class="form-group col-md-6">
                      <label for="invoice_code">Nomer Indent</label>
                      <input type="text" class="form-control" id="invoice_code" value="{{ $key->so_code }}" readonly>
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group col-md-12">
                      <label for="note">Catatan</label>
                      <input type="text" class="form-control" value="{{ $key->note ?? '-' }}" readonly>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="block">
                <div class="block-header block-header-default">
                  <h3 class="block-title">#Customer</h3>
                </div>
                <div class="block-content">
                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label for="type_transaction">Customer</label>
                      <input type="text" name="customer_name" class="form-control" value="{{ $key->member->name }} {{$key->member->text_kota}}" readonly>
                    </div>
                    <div class="form-group col-md-6">
                      <label for="note">Alamat Kirim</label>
                      <textarea class="form-control" rows="1" readonly>{{ $key->member->address }}</textarea>
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label for="customer_city">Kota</label>
                      <input type="text" name="customer_city" class="form-control" value="{{$key->member->text_kota}}" readonly>
                    </div>
                    <div class="form-group col-md-6">
                      <label for="customer_area">Provinsi</label>
                      <input type="text" name="customer_area" class="form-control" value="{{ $key->member->text_provinsi }}" readonly>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col">
              <table class="table">
                <thead>
                  <tr>
                    <th style="width: 5%;"></th>
                    <th style="width: 5%;">#</th>
                    <th style="width: 35%;">Product</th>
                    <th style="width: 15%;">Qty</th>
                    <th style="width: 20%;">Kemasan</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($key->so_detail as $index => $detail)
                    <tr>
                      <td>
                        <input 
                          class="form-check-input edit-checkbox" 
                          type="checkbox" 
                          value="{{ $detail->id }}" 
                          name="so_item_id[]" 
                          data-index="{{ $loop->iteration }}">
                      </td>
                      <td>{{ $loop->iteration }}</td>
                      <td>{{ $detail->product_pack->code }} - {{ $detail->product_pack->name }}</td>
                      
                      <!-- Qty field: Read-only by default, editable when checkbox is selected -->
                      <td>
                        <input 
                          type="number" 
                          name="qty[{{ $detail->id }}]" 
                          class="form-control qty-input" 
                          value="{{ $detail->qty }}" 
                          readonly
                        >
                      </td>

                      <td>{{ $detail->product_pack->kemasan()->pack_name }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-info mt-2">Edit Qty</button>
            <button type="button" class="btn btn-danger mt-2 delete-selected" data-id="{{ $key->id }}">Hapus Item Terpilih</button>
            <button type="button" class="btn btn-secondary mt-2" data-dismiss="modal">Close</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endforeach

@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@section('modal')

@include('superuser.component.modal-manage-indent', [
  'export_url' => route('superuser.penjualan.sales_order_indent.export')
])

@endsection

@push('scripts')
<script type="text/javascript">
  $(document).ready(function () {
    var table = $('#datatable').DataTable();

    // Event listener untuk checkbox
    $('.edit-checkbox').on('change', function () {
      var qtyInput = $(this).closest('tr').find('.qty-input');
      if (this.checked) {
        qtyInput.removeAttr('readonly');
      } else {
        qtyInput.attr('readonly', 'readonly');
      }
    });

    // Event listener untuk tombol hapus
    $('.delete-selected').on('click', function () {
      var selectedItems = [];
      var salesOrderId = $(this).data('id'); // Ambil ID sales order dari tombol
      var modalId = '#myModal' + salesOrderId;

      $(modalId).find('.edit-checkbox:checked').each(function () {
        selectedItems.push($(this).val());
      });

      if (selectedItems.length === 0) {
        alert('Silakan pilih item yang ingin dihapus.');
        return;
      }

      if (!confirm('Apakah Anda yakin ingin menghapus item yang dipilih?')) {
        return;
      }

      $.ajax({
        url: '{{ route("superuser.penjualan.sales_order_indent.deleteItems") }}',
        type: 'POST',
        data: {
          _token: '{{ csrf_token() }}',
          so_item_ids: selectedItems
        },
        success: function (response) {
          if (response.status === 'success') {
            alert(response.message);
            location.reload(); // Refresh halaman setelah sukses
          } else {
            alert('Terjadi kesalahan: ' + response.message);
          }
        },
        error: function (xhr) {
          alert('Terjadi kesalahan, silakan coba lagi.');
        }
      });
    });

    $('#btn-back-agenda').on('click', function() {
      window.location.href = "https://sys-af.lsfragrance.id/transaksi/sales_order/list";
    });

    @if(session('is_from_agenda', false))
        // Tambah state baru agar history browser diubah
        history.pushState(null, null, location.href);

        // Saat user klik back
        window.onpopstate = function(event) {
            // Redirect ke halaman yang kita tentukan (daftar SO)
            window.location.replace("https://sys-af.lsfragrance.id/transaksi/sales_order/list");
        };
    @endif
  });
</script>

@endpush