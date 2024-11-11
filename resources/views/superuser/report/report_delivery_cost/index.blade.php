@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Report</span>
  <span class="breadcrumb-item active">Delivery Cost</span>
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

    <div class="form-group row">
      <div class="col-md-9">
        <div class="block">
          <div class="block-content">
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-left" for="period">Period From :</label>
              <div class="col-md-4">
                <div class="input-group">
                  <input type="date" class="form-control form-control" name="start_date" id="periode_from">
                </div>
              </div>
              <label class="col-md-2 col-form-label text-left" for="product">Period To :</label>
              <div class="col-md-4">
                <div class="input-group">
                  <input type="date" class="form-control form-control" name="end_date" id="periode_to">
                </div>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-left" for="customer_name">Customer :</label>
              <div class="col-md-4">
                <select class="js-select2 form-control" id="customer_name" name="customer_name" data-placeholder="Select Marketplace">
                  <option value="all">All</option>
                  @foreach ($customer as $key => $value)
                    <option value="{{ $value->id }}">{{ $value->name }} {{ $value->text_kota }}</option>
                  @endforeach
                </select>
              </div>
              <label class="col-md-2 col-form-label text-left" for="status">Code :</label>
              <div class="col-md-4">
                <select class="js-select2 form-control" id="do_code" name="do_code" data-placeholder="Search Code">
                    <option value="all">All</option>
                    @foreach ($do as $key => $value)
                    <option value="{{ $value->do_code }}">{{ $value->do_code }}</option>
                    @endforeach
                </select>
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
                <a href="#" id="btn-filter" class="btn bg-gd-corporate border-0 text-white pl-50 pr-50">
                  Filter <i class="fa fa-search ml-10"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="block">
        <div class="block-content block-content-full">
            <table id="datatables" class="table table-striped table-vcenter" style="width:100%">
                <thead>
                    <tr>
                        <th class="text-center">Date Sent</th>
                        <th class="text-center">Code</th>
                        <th class="text-center">Customer</th>
                        <th class="text-center">Free</th>
                        <th class="text-center">Ongkir</th>
                        <th class="text-center">Resi</th>
                        <th class="text-center">Ekspedisi</th>
                        <th class="text-center">Other Ekspedisi</th>
                        <th class="text-center">Image Resi 1</th>
                        <th class="text-center">Image Resi 2</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.daterangepicker')
@include('superuser.asset.plugin.magnific-popup')
@include('superuser.asset.plugin.datatables-button')

@push('scripts')
<script type="text/javascript">
    var start_date = $('#periode_from').val();
    var end_date = $('#periode_to').val();
    // var print_date = "SR-{{ \Carbon\Carbon::now()->format('dmy') }}-{{ \Carbon\Carbon::now()->format('dmy') }}";

    $(document).ready(function() {
        $('.js-select2').select2();

        // $('#datesearch').daterangepicker({
        //   autoUpdateInput: false
        // });
        // $('#datesearch').data('daterangepicker').setStartDate('{{ \Carbon\Carbon::now()->format('m/d/Y') }}');
        // $('#datesearch').data('daterangepicker').setEndDate('{{ \Carbon\Carbon::now()->format('m/d/Y') }}');
        // $('#datesearch').on('apply.daterangepicker', function(ev, picker) {
        //   $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
        //   start_date = picker.startDate.format('YYYY-MM-DD');
        //   end_date = picker.endDate.format('YYYY-MM-DD');
        //   print_date = "SR-"+picker.startDate.format('DDMMYY')+"-"+picker.endDate.format('DDMMYY');
        // });

        let datatableUrl = '{{ route('superuser.report.report_delivery_cost.json') }}';
        let firstDatatableUrl = datatableUrl + '?start_date=' + start_date + '&end_date=' + end_date +
        '&customer_name=all&code=all';

        var datatable = $('#datatables').DataTable({
            language: {
            processing: "<span class='fa-stack fa-lg'>\n\
                                    <i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\
                            </span>",
            },
            processing: true,
            serverSide: false,
            scrollX: true,
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
                    data: 'date_sent',
                    searchable: false
                },
                {
                    data: 'code',
                    searchable: false
                },
                {
                    data: 'customer'
                },
                {
                  data: 'free_ongkir',
                },
                {
                    data: 'ongkir_idr',
                    render: $.fn.dataTable.render.number('.', ',', 2, 'Rp. '),
                    searchable: false
                },
                {
                    data: 'resi_idr',
                    render: $.fn.dataTable.render.number('.', ',', 2, 'Rp. '),
                    searchable: false
                },     
                {
                    data: 'ekspedisi',
                    name: 'penjualan_do_details.delivery_cost_note'
                },
                {
                    data: 'other_ekspedisi',
                    searchable: false
                },
                { 
                  data: 'image_resi1', 
                  name: 'penjualan_do.image',
                  orderable: false, searchable: false
                },
                { 
                  data: 'image_resi2', 
                  name: 'penjualan_do.image2',
                  orderable: false, searchable: false
                },
            ],
            // paging: false,
            // info: false,
            // ordering: false,
            // searching: false,
            order: [
                [1, 'asc']
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
                  $.extend( true, {}, buttonCommon, {
                      extend: 'copyHtml5',

                  } ),
                  $.extend( true, {}, buttonCommon, {
                      extend: 'excelHtml5',

                  } ),
                  $.extend( true, {}, buttonCommon, {
                      extend: 'pdfHtml5',

                  } )
            ]
        });

        var buttonCommon = {
            extend : 'pdfHtml5',
            exportOptions: {
                      stripHtml: true,
                      // columns: 'th:not(:last-child)',

            exportOptions: {
                      stripHtml: true,
                      //columns: 'th:not(:nth-child(2))',
                format: {
                    body: function ( data, row, column, node ) {
                        // Strip $ from salary column to make it numeric
                        return column === 4 ?
                            data.replace( /[$,]/g, '' ) :
                            data;
                    }
                }
              }
            }
        };

        $('#btn-filter').on('click', function(e) {
          e.preventDefault();
          var customer = $('#customer_name').val();
          var code = $('#do_code').val();
          let periode_from = $("#periode_from").val();
          let periode_to = $("#periode_to").val();

          let newDatatableUrl = datatableUrl + '?start_date=' + periode_from + '&end_date=' + periode_to +
            '&customer_name=' + customer + '&do_code=' + code;
          datatable.ajax.url(newDatatableUrl).load();
        })

        // $('a.img-lightbox').magnificPopup({
        //   type: 'image',
        //   closeOnContentClick: true,
        // });
        $('.img-lightbox').magnificPopup({
          type: 'image',
          removalDelay: 300,
          mainClass: 'mfp-fade',
          gallery: {
            enabled: true
          },
          zoom: {
          enabled: true,
          duration: 300,
          easing: 'ease-in-out',
          opener: function (openerElement) {
              return openerElement.is('img') ? openerElement : openerElement.find('img');
            }
          }
        });
    });
</script>
@endpush