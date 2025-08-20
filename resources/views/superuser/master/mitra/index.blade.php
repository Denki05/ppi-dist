@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <span class="breadcrumb-item active">Mitra</span>
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
    <a href="{{ route('superuser.master.mitra.create') }}">
      <button type="button" class="btn btn-outline-primary min-width-125">Create</button>
    </a>
  </div>
  <div class="block-content block-content-full">
    <table id="mitra" class="table table-striped table-hover">
      <thead>
        <tr>
          <th></th> <!-- Kolom untuk tombol expand/collapse -->
          <th>#</th>
          <th>Created at</th>
          <th>Code</th>
          <th>Name</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($mitra as $row)
        @if($row->status == 1)
          <tr data-id="{{ $row->id }}">
            <td class="details-control">
              <i class="fa fa-plus-circle"></i>
            </td>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $row->created_at }}</td>
            <td>{{ $row->code }}</td>
            <td>{{ $row->name }}</td>
            <td>{{ $row->status() }}</td>
            <td>
              <a href="{{ route('superuser.master.mitra.show', $row->id) }}">
                <button type="button" class="btn btn-sm btn-circle btn-alt-secondary" title="Show Mitra">
                  <i class="fa fa-eye"></i>
                </button>
              </a>
              <a href="{{ route('superuser.master.mitra.add_customer', $row->id) }}">
                <button type="button" class="btn btn-sm btn-circle btn-alt-secondary" title="Add Customer">
                  <i class="fa fa-user-plus"></i>
                </button>
              </a>
              <a href="{{ route('superuser.master.mitra.edit', $row->id) }}">
                <button type="button" class="btn btn-sm btn-circle btn-alt-secondary" title="View">
                  <i class="fa fa-pencil"></i>
                </button>
              </a>
              <a href="javascript:deleteConfirmation('{{ route('superuser.master.mitra.destroy', $row->id) }}')">
                <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete">
                  <i class="fa fa-trash"></i>
                </button>
              </a>

              <!-- Check setting mitra -->
              <button type="button" class="btn btn-sm btn-circle btn-alt-info" data-toggle="modal" data-target="#modal-import-export-ratio">
                <i class="fa fa-cog" aria-hidden="true"></i>
              </button>
            </td>
          </tr>
        @endif
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<div id="modal-import-export-ratio" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import / Export Mitra Setting</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Import Form -->
                <form action="{{ route('superuser.master.mitra.setting_saldo_import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label>Import Product Ratio (Excel File)</label>
                        <input type="file" name="import_file" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Import</button>
                </form>

                <hr>

                <!-- Export Form -->
                <form action="{{ route('superuser.master.mitra.template_setting_mitra') }}" method="GET">
                    <button type="submit" class="btn btn-success">Export Template</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
  function showSettingWarning() {
      Swal.fire({
          icon: 'warning',
          title: 'Perhatian!',
          text: 'Setting saldo sudah disimpan, Tidak bisa melakukannya kembali!',
          confirmButtonColor: '#d33',
          confirmButtonText: 'OK'
      });
  }

  $(document).ready(function () {
      var table = $('#mitra').DataTable();

      function format(rowData) {
        var id = $(rowData).data("id");
        var url = "{{ route('superuser.master.mitra.getCustomers', ':id') }}".replace(':id', id);

        console.log("Fetching data from URL:", url); // Debugging URL

        var html = `<table class="table table-sm">
                    <thead>
                      <tr>
                        <th><strong>ID</strong></th>
                        <th><strong>Customer</strong></th>
                        <th><strong>Kota</strong></th>
                      </tr>
                    </thead>
                  <tbody>`;

        var tr = $(rowData);
        var row = table.row(tr);
        
        // Tambahkan indikator loading
        row.child(`<div class="text-center p-2">Loading data...</div>`).show();
        tr.find("i").removeClass("fa-plus-circle").addClass("fa-spinner fa-spin");

        $.ajax({
            url: url,
            method: "GET",
            success: function (data) {
                console.log("Response Data:", data); // Debugging response

                if (data.customers.length > 0) {
                    data.customers.forEach((customer) => {
                        html += `<tr>
                                    <td>${customer.id}</td>
                                    <td>${customer.name}</td>
                                    <td>${customer.text_kota}</td>
                                </tr>`;
                    });
                } else {
                    html += `<tr><td colspan="2" class="text-center">No Customers Found</td></tr>`;
                }
            },
            error: function (xhr, status, error) {
                console.log("AJAX Error:", error); // Debugging error
                html += `<tr><td colspan="2" class="text-center text-danger">Failed to load data</td></tr>`;
            },
            complete: function () {
                row.child(html + `</tbody></table>`).show();
                tr.find("i").removeClass("fa-spinner fa-spin").addClass("fa-minus-circle");
            }
        });

        return ''; // Hindari return yang salah saat AJAX masih berjalan
      }

      $('#mitra tbody').on('click', 'td.details-control', function () {
          var tr = $(this).closest('tr');
          var row = table.row(tr);
          var icon = $(this).find("i"); // Hanya ikon dalam tombol expand

          if (row.child.isShown()) {
              row.child.hide();
              icon.removeClass("fa-minus-circle").addClass("fa-plus-circle");
          } else {
              format(tr, icon); // Kirim ikon ke function format
          }
      });

      function format(rowData, icon) {
          var id = $(rowData).data("id");
          var url = "{{ route('superuser.master.mitra.getCustomers', ':id') }}".replace(':id', id);

          console.log("Fetching data from URL:", url); // Debugging URL

            var html = `<table class="table table-sm">
                    <thead>
                      <tr>
                        <th><strong>ID</strong></th>
                        <th><strong>Customer</strong></th>
                        <th><strong>City</strong></th>
                      </tr>
                    </thead>
                  <tbody>`;

          var tr = $(rowData);
          var row = table.row(tr);

          // Tambahkan indikator loading hanya di dalam baris child
          row.child(`<div class="text-center p-2">Loading data...</div>`).show();
          icon.removeClass("fa-plus-circle").addClass("fa-spinner fa-spin"); // Hanya ikon expand yang berubah

          $.ajax({
              url: url,
              method: "GET",
              success: function (data) {
                  console.log("Response Data:", data); // Debugging response

                  if (data.customers.length > 0) {
                      data.customers.forEach((customer) => {
                          html += `<tr>
                                      <td>${customer.id}</td>
                                      <td>${customer.name}</td>
                                      <td>${customer.text_kota}</td>
                                  </tr>`;
                      });
                  } else {
                      html += `<tr><td colspan="2" class="text-center">No Customers Found</td></tr>`;
                  }
              },
              error: function (xhr, status, error) {
                  console.log("AJAX Error:", error); // Debugging error
                  html += `<tr><td colspan="2" class="text-center text-danger">Failed to load data</td></tr>`;
              },
              complete: function () {
                  row.child(html + `</tbody></table>`).show();
                  icon.removeClass("fa-spinner fa-spin").addClass("fa-minus-circle"); // Hanya ikon expand yang berubah
              }
          });

          return ''; // Hindari return yang salah saat AJAX masih berjalan
      }

  });
</script>
@endpush