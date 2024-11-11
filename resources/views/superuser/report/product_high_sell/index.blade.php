@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Report</span>
  <span class="breadcrumb-item">Operasional</span>
  <span class="breadcrumb-item active">Product High Sell</span>
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

<form id="form" target="_blank" action="#"
    enctype="multipart/form-data" method="POST">
    @csrf
    <input type="hidden" name="download_type" id="download_type" value="">
    <div class="form-group row">
      <div class="col-md-9">
        <div class="block">
          <div class="block-content">
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-left" for="period">Period From :</label>
              <div class="col-md-4">
                <div class="input-group">
                  <input type="date" class="form-control form-control" name="periode_from" id="periode_from">
                </div>
              </div>
              <label class="col-md-2 col-form-label text-left" for="product">Period To :</label>
              <div class="col-md-4">
                <div class="input-group">
                  <input type="date" class="form-control form-control" name="periode_to" id="periode_to">
                </div>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-left" for="brand_name">Brand / Merek :</label>
              <div class="col-md-4">
                <select class="js-select2 form-control" id="brand_name" name="brand_name[]" data-placeholder="Select Brand/Merek" multiple>
                  <option value="all">All</option>
                  @foreach ($brand as $value)
                    <option value="{{ $value->brand_name }}">{{ $value->brand_name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="form-group row">
                <div class="mb-3 row">
                <div class="col-3 col-form-label required"><h5>Type Report :</h5></div>
                <div class="col">
                    <label class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="radios-inline" id="type_customer" value="1">
                    <h6>Semester</h6>
                    </label>
                    <label class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="radios-inline" id="zone_customer" value="2">
                    <!-- <span class="form-check-label">Customer by Zone (transaksi)</span> -->
                    <h6>Zone</h6>
                    </label>
                </div>
            </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="block">
          <div class="block-content">
            <div class="form-group row">
              <div class="col-md-12 text-center">
                <a href="#" id="btn-print" class="btn bg-gd-corporate border-0 text-white pl-50 pr-50">
                  Print <i class="fa fa-print ml-10"></i>
                </a>
              </div>
            </div>

            <div class="form-group row">
              <div class="col-md-12 text-center">
                <a href="#" id="btn-filter" class="btn bg-gd-corporate border-0 text-white pl-50 pr-50">
                  Filter <i class="fa fa-search ml-10"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="form-group row">
      <div class="block">
        <div class="block-content">
          <table id="datatables" class="table table-striped table-vcenter" style="width:100%">
            <thead>
              <tr>
                <th class="text-center">Merek</th>
                <th class="text-center">Variant</th>
                <th class="text-center">Qty</th>
              </tr>
            </thead>
              <tbody>
              </tbody>
          </table>
        </div>
      </div>
    </div>
  </form>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.datatables-button')

@push('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        var start_date = $('#periode_from').val();
        var end_date = $('#periode_to').val();

        $('.js-select2').select2();

        $('#btn-print').on('click', function(e) {
            e.preventDefault();
            var brand = $('#brand_name').val();
            var start_date = $('#periode_from').val();
            var end_date = $('#periode_to').val();
            let typeReport = $("input:radio[name=radios-inline]:checked").val();

            $.ajax({
            type:'POST',
            url:"{{ route('superuser.report.product_high_sell.print_report') }}",
            data:{"_token": "{{ csrf_token() }}", "start":start_date, "end":end_date, "brand":brand, "type":typeReport},
            success:function(response){
                // console.log(response);
            }
            });
        });

        let datatableUrl = '{{ route('superuser.report.product_high_sell.json') }}';
        let firstDatatableUrl = datatableUrl + '?start_date=' + start_date + '&end_date=' + end_date +
        '&brand=all';

        var datatable = $('#datatables').DataTable({
          language: {
            processing: "<span class='fa-stack fa-lg'>\n\
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
            {
              data: 'brand',
              name: 'master_products.brand_name'
            },
            {
              data: 'variant',
            },
            {
              data: 'total_qty',
            },
          ],
          order: [
            [2, 'desc']
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
              extend: 'excelHtml5',
              text: '<i class="fa fa-file-excel-o"></i>',
              titleAttr: 'Excel',
              title: 'Product-High Sell',
            },
            {
              extend: 'pdfHtml5',
              orientation: 'portrait',
              pageSize: 'A5',
              text: '<i class="fa fa-file-pdf-o"></i>',
              titleAttr: 'PDF',
              title: 'Product-High Sell',
            }
          ],
        })

        $('#btn-filter').on('click', function(e) {
          e.preventDefault();
          var brand = $('#brand_name').val();
          let periode_from = $("#periode_from").val();
          let periode_to = $("#periode_to").val();

          let newDatatableUrl = datatableUrl + '?start_date=' + periode_from + '&end_date=' + periode_to +
            '&brand=' + brand;
          datatable.ajax.url(newDatatableUrl).load();
        })
    })
</script>
@endpush