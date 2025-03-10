@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Accounting</span>
  <span class="breadcrumb-item active">Product Tax</span>
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

@if(session()->has('collect_success') || session()->has('collect_error'))
<div class="container">
  <div class="row">
    <div class="col pl-0">
      <div class="alert alert-success alert-dismissable" role="alert" style="max-height: 300px; overflow-y: auto;">
        <h3 class="alert-heading font-size-h4 font-w400">Successful Import</h3>
        @foreach (session()->get('collect_success') as $msg)
        <p class="mb-0">{{ $msg }}</p>
        @endforeach
      </div>
    </div>
    <div class="col pr-0">
      <div class="alert alert-danger alert-dismissable" role="alert" style="max-height: 300px; overflow-y: auto;">
        <h3 class="alert-heading font-size-h4 font-w400">Failed Import</h3>
        @foreach (session()->get('collect_error') as $msg)
        <p class="mb-0">{{ $msg }}</p>
        @endforeach
      </div>
    </div>
  </div>
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

<div class="block">  
  <div class="block-content">
    <a href="{{ route('superuser.accounting.product_finance.create') }}">
      <button type="button" class="btn btn-outline-primary min-width-125">Create</button>
    </a>

    <button type="button" class="btn btn-outline-primary min-width-125" data-toggle="modal" data-target="#modal-import-export-ratio">
      Manage
    </button>
  </div>
  <div class="block-content block-content-full">
    <div class="form-group row">
      <label class="col-md-2 col-form-label text-left" for="period">Mitra :</label>
      <div class="col-md-4">
        <div class="input-group">
          <select id="mitra" name="mitra" class="form-control js-select2">
            <option value="">Pilih Mitra</option>
            @foreach ($mitra as $key)
              <option value="{{ $key->id }}">{{ $key->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </div>

    <hr class="my-20">

    <div class="row mb-30">
        <div class="col-12">
          <table class="table table-striped" id="productFinanceTable" style="width: 100%;">
            <thead>
              <tr>
                <th>#</th>
                <th>Kode</th>
                <th>Nama</th>
                <th>Brand</th>
                <th>Kemasan</th>
                <th>Mitra</th>
                <th>Harga Beli</th>
                <th>Harga Jual</th>
                <th>status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
    </div>
  </div>
</div>

<div id="modal-import-export-ratio" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import / Export Product Finance</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
              <!-- Import Form -->
              <form action="{{ route('superuser.accounting.product_finance.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                  <label>Import Product Ratio (Excel File)</label>
                  <input type="file" name="import_file" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Import</button>
              </form>

              <hr>

              <!-- Export Forms -->
              <div class="d-flex justify-content-between">
                <form action="{{ route('superuser.accounting.product_finance.import_template') }}" method="GET">
                  <button type="submit" class="btn btn-success">Export Template</button>
                </form>
                <form action="{{ route('superuser.accounting.product_finance.export') }}" method="GET">
                  <button type="submit" class="btn btn-info">Export</button>
                </form>
              </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Update Harga -->
<div class="modal fade" id="editPriceModal" tabindex="-1" aria-labelledby="editPriceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPriceModalLabel">Edit Harga Produk</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="updatePriceForm">
                <div class="modal-body">
                    <input type="hidden" id="product_id">
                    
                    <div class="form-group">
                        <label>Nama Produk</label>
                        <input type="text" class="form-control" id="product_name" readonly>
                    </div>

                    <div class="form-group">
                        <label>Harga Beli (USD)</label>
                        <input type="number" class="form-control" id="buying_price" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label>Harga Jual (USD)</label>
                        <input type="number" class="form-control" id="selling_price" step="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script type="text/javascript">
    $(document).ready(function () {
      $('.js-select2').select2();

      let datatableUrl = '{{ route('superuser.accounting.product_finance.json') }}';

      let productFinanceTable = $('#productFinanceTable').DataTable({
          processing: true,
          serverSide: false,
          ajax: {
              url: datatableUrl,
              dataType: "json",
              type: "GET",
              data: function (d) {
                  d.mitra = $('#mitra').val();
              },
          },
          columns: [
              { data: 'DT_RowIndex', name: 'id' },
              { data: 'kode' },
              { data: 'name' },
              { data: 'brand' },
              { data: 'packaging_name' },
              { data: 'mitra_name' },
              { data: 'uv_beli' },
              { data: 'uv_jual' },
              { data: 'status' },
              { data: 'action', orderable: false, searchable: false } // Tambahkan kolom aksi
          ],
          order: [[2, 'asc']],
          pageLength: 10,
          lengthMenu: [[20, 30, 50], [20, 30, 50]],
      });

      // Handle month dropdown change
      $('#mitra').change(function () {
        productFinanceTable.ajax.reload();
      });

      $(document).on('click', '.edit-price', function () {
        let id = $(this).data('id');
        let buy = $(this).data('buy');
        let sell = $(this).data('sell');
        let name = $(this).data('name');

        // Isi form modal dengan data produk
        $('#product_id').val(id);
        $('#product_name').val(name);
        $('#buying_price').val(buy);
        $('#selling_price').val(sell);

        // Tampilkan modal
        $('#editPriceModal').modal('show');
      });

      $('#updatePriceForm').submit(function (e) {
        e.preventDefault();
        let productId = $('#product_id').val();
        let buyingPrice = $('#buying_price').val();
        let sellingPrice = $('#selling_price').val();

        $.ajax({
            url: "{{ route('superuser.accounting.product_finance.updatePrice') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: productId,
                buying_price: buyingPrice,
                selling_price: sellingPrice
            },
            beforeSend: function () {
                $('.btn-primary').prop('disabled', true);
            },
            success: function (response) {
                if (response.status === 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Harga berhasil diperbarui!',
                        showConfirmButton: false,
                        timer: 2000
                    });

                    // Tutup modal dan refresh tabel
                    $('#editPriceModal').modal('hide');
                    productFinanceTable.ajax.reload();
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan, coba lagi!',
                });
            },
            complete: function () {
                $('.btn-primary').prop('disabled', false);
            }
        });
      });
    })
</script>
@endpush