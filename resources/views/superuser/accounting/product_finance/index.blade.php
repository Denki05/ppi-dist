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
  <div class="block-header block-header-default">
  </div>
  <div class="block-content">
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

<!-- Modal Update Cost -->
<div class="modal fade" id="updateCost" tabindex="-1" role="dialog" aria-labelledby="updateCostLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateCostLabel">Update Harga</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="updateCostForm" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="buying_price_usd_drum">Harga Beli Drum (USD)</label>
                        <input type="text" class="form-control" id="buying_price_usd_drum" name="buying_price_usd_drum" required>
                    </div>
                    <div class="form-group">
                        <label for="selling_price_usd_drum">Harga Jual Drum (USD)</label>
                        <input type="text" class="form-control" id="selling_price_usd_drum" name="selling_price_usd_drum" required>
                    </div>
                    <div class="form-group">
                        <label for="buying_price_usd_unit">Harga Beli Unit (USD)</label>
                        <input type="text" class="form-control" id="buying_price_usd_unit" name="buying_price_usd_unit" required>
                    </div>
                    <div class="form-group">
                        <label for="selling_price_usd_unit">Harga Jual Unit (USD)</label>
                        <input type="text" class="form-control" id="selling_price_usd_unit" name="selling_price_usd_unit" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Harga</button>
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
                  d.mitra = $('#mitra').val(); // Send selected month
              },
          },
          columns: [
              {data: 'DT_RowIndex', name: 'id'},
              { data: 'kode' },
              { data: 'name' },
              { data: 'brand' },
              { data: 'packaging_name' },
              { data: 'mitra_name' },
              { data: 'uv_beli' },
              { data: 'uv_jual' },
              { data: 'status' },
              { data: 'action', orderable: false, searchable: false },
          ],
          order: [[2, 'asc']],
          pageLength: 10,
          lengthMenu: [[20, 30, 50], [20, 30, 50]],
      });

      // Handle month dropdown change
      $('#mitra').change(function () {
        productFinanceTable.ajax.reload();
      });

      $('body').on('click', '.openModalUpdateCost', function () {
          var id = $(this).data('id'); // Ambil ID produk yang dikodekan
          var url = '{{ route('superuser.accounting.product_finance.update_cost', '') }}/' + id; // URL ke controller update_cost

          // Kirim request untuk mendapatkan data harga produk
          $.get(url, function (data) {
              if (data.product) {
                  $('#buying_price_usd_drum').val(data.product.buying_price_usd_drum);
                  $('#selling_price_usd_drum').val(data.product.selling_price_usd_drum);
                  $('#buying_price_usd_unit').val(data.product.buying_price_usd_unit);
                  $('#selling_price_usd_unit').val(data.product.selling_price_usd_unit);
                  $('#updateCostForm').attr('action', '{{ route('superuser.accounting.product_finance.update_cost', '') }}/' + id);
              }
          });
      });

      // Handle form submit
      $('#updateCostForm').submit(function (e) {
          e.preventDefault();

          var formData = $(this).serialize(); // Ambil data form
          var actionUrl = $(this).attr('action');

          // Kirim data update ke controller menggunakan AJAX
          $.ajax({
              url: actionUrl,
              method: 'POST',
              data: formData,
              success: function (response) {
                  alert(response.notification.content); // Menampilkan notifikasi
                  $('#updateCost').modal('hide'); // Tutup modal
                  location.reload(); // Reload halaman
              },
              error: function (xhr, status, error) {
                  var errorMsg = xhr.responseJSON.errors ? xhr.responseJSON.errors.join(", ") : 'Terjadi kesalahan';
                  alert(errorMsg);
              }
          });
      });
    })
</script>
@endpush
