@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Laporan</span>
  <span class="breadcrumb-item ">Operasional</span>
  <span class="breadcrumb-item ">Produk</span>
  <span class="breadcrumb-item active">Produk - Material</span>
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
    <div class="form-group row">
      <div class="col-md-8">
        <div class="block">
          <div class="block-content">
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-left" for="vendor">Vendor :</label>
              <div class="col-md-4">
                <select class="js-select2 form-control" id="vendor" name="vendor" data-placeholder="Select Vendor" multiple="multiple">
                    <option value="all">All</option>
                    @foreach($vendor AS $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="block">
          <div class="block-content">
            <div class="form-group row">
              <div class="col-md-6 text-center">
                <a href="#" id="btn-filter" class="btn bg-gd-sea border-0 text-white pl-50 pr-50">
                 <i class="fa fa-search ml-10"></i>
                </a>
              </div>

              <div class="col-md-6 text-center">
                <a href="{{ route('superuser.master.product.print_product_material') }}" class="btn bg-gd-sea border-0 text-white pl-50 pr-50">
                 <i class="fa fa-print ml-10"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="block">
        <div class="block-content block-content-full">
            <div class="row mb-30">
              <div class="col-12">
                <table class="table table-striped" id="datatables">
                  <thead>
                    <tr>
                      <th>Vendor</th>
                      <th>Material</th>
                      <th>Brand</th>
                      <th>Produk</th>
                      <th>Harga</th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
              </div>
            </div>
        </div>
      </div>
    </div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.daterangepicker')
@include('superuser.asset.plugin.datatables-button')

@push('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        $('.js-select2').select2();

        let datatableUrl = '{{ route('superuser.master.product.json2') }}';
        let firstDatatableUrl = datatableUrl + '?vendor=all';

        var datatable = $('#datatables').DataTable({
          "language": {
              "processing": "<span class='fa-stack fa-lg'>\n\
                                <i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\
                            </span>",
          },
          processing: true,
          serverSide: false,
          ajax: {
              "url": firstDatatableUrl,
              "dataType": "json",
              "type": "GET",
              "data": {
                  _token: "{{ csrf_token() }}"
              }
          },
          columns: [
            { data: 'vendor_name' },
            { data: 'material' },
            { data: 'brand' },
            { data: 'produk' },
            { data: 'harga' },
          ],
          order: [
            [0, 'asc']
          ],
          pageLength: 10,
          lengthMenu: [
            [10, 25, 50, 100],
            [10, 25, 50, 100]
          ],
          dom: "<'row'<'col-sm-2'l><'col-sm-7 text-left'B><'col-sm-3'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
          buttons: [
            {
              extend: 'excel',
              text: '<i class="fa fa-file-excel-o"></i>',
              title: 'Report Produk - Material',
              exportOptions: {
                modifier: {
                  page: 'all'
                }
              }
            },
            {
                  extend: 'pdf',
                  text: '<i class="fa fa-file-pdf-o"></i>',
                  title: 'Report Produk - Material',
                  orientation: 'landscape',
                  pageSize: 'A4',
                  exportOptions: {
                      columns: ':visible'
                  },
                  customize: function(doc) {
                      doc.content[1].table.widths = ['30%', '20%', '15%', '20%', '15%']; // Lebar kolom
                      doc.styles.tableHeader.alignment = 'center'; // Header kolom rata tengah
                      doc.styles.tableHeader.fontSize = 10;

                      // Rata tengah setiap kolom pada PDF
                      doc.content[1].table.body.forEach(function(row, index) {
                          if (index > 0) {
                              row.forEach(function(cell) {
                                  cell.alignment = 'center';
                              });
                          }
                      });
                  }
              }
          ]
        })

        $('#btn-filter').on('click', function(e) {
          e.preventDefault();
          var vendor = $('#vendor').val();  // Get selected vendor(s)

          // Check if vendor is 'all' or an array of vendors
          if (vendor && vendor != 'all') {
            // For multiple vendor selections, join IDs by commas
            vendor = vendor.join(',');
          }

          // Update the DataTable URL with new filter values
          let newDatatableUrl = datatableUrl + '?vendor=' + vendor;

          // Reload the DataTable with the new URL
          datatable.ajax.url(newDatatableUrl).load();
        });
    })
</script>
@endpush