@extends('superuser.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* ===== STYLE SAMPAI SEKARANG TIDAK DIUBAH ===== */
body{ background:#1f242a; font-family: "Segoe UI", Roboto, sans-serif; }
.crm-wrapper{ max-width:1000px; margin:0 auto; height:calc(100vh - 90px); }
.crm-card{ height:100%; border-radius:10px; border:none; box-shadow:0 4px 14px rgba(0,0,0,.08); }
.crm-card .card-body{ padding:10px 14px; overflow-y:auto; }
.form-label{ font-size:12px; font-weight:600; margin-bottom:3px; }
.form-control, .select2-container--default .select2-selection--single{ height:32px !important; font-size:13px; padding:4px 8px; }
.table-responsive{ padding:0 !important; margin:0 !important; max-height: calc(100vh - 260px); overflow-y: auto; overflow-x: hidden }
#datatable{ width:100% !important; table-layout:fixed !important; border-collapse:collapse !important; margin:0 !important; font-size:14px; }
#datatable thead th{ background:#f4f6f9; font-size:14px; font-weight:600; padding:4px 6px !important; border-bottom:1px solid #dfe3e8; text-transform:uppercase; letter-spacing:.3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; position: sticky; top: 0; z-index: 5; background: #f4f6f9; }
#datatable tbody td{ font-size:13.5px; padding:3px 6px !important; line-height:1.2 !important; border-bottom:1px solid #eef1f4; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; vertical-align:middle; }
#datatable tbody tr{ height:30px; }
#datatable tbody tr:hover{ background:#f8fafc; }
#datatable th, #datatable td{ border-right:1px solid #f1f3f5; }
.table > :not(caption) > * > *{ padding:0 !important; }
.table-striped tbody tr:nth-of-type(odd){ background:none !important; }
.dataTables_wrapper{ margin-top:4px; }
.dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter{ margin-bottom:6px; font-size:12px; }
.dataTables_wrapper .dataTables_info{ font-size:11px; padding-top:4px !important; }
.dataTables_wrapper .dataTables_paginate{ padding-top:2px !important; }
.dataTables_wrapper .dataTables_paginate .paginate_button{ padding:2px 8px !important; font-size:11px; }
.text-danger-strong{ color:#d92d20; font-weight:800; }
.text-warning-strong{ color:#f79009; font-weight:800; }
.text-success-strong{ color:#067647; font-weight:800; }
.dataTables_wrapper .dataTables_paginate{ display:flex !important; justify-content:center !important; margin-top:10px; }
.erp-pagination{ display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; }
.erp-pagination button{ background:#f4f6f9; border:1px solid #dfe3e8; padding:3px 8px; border-radius:4px; cursor:pointer; font-size:13px; }
.erp-pagination button:hover{ background:#e9eef5; }
.erp-pagination .page-info{ min-width:50px; text-align:center; }
#datatable tbody td.text-end{ font-size:13px; font-weight:700; letter-spacing:.3px; }
.text-danger-strong, .text-warning-strong, .text-success-strong{ font-size:13px; }
#datatable th:nth-child(5), #datatable td:nth-child(5), 
#datatable th:nth-child(6), #datatable td:nth-child(6){ width:65px !important; text-align:right; }

.ks-fixed-modal{
    height: 75vh;
    display: flex;
    flex-direction: column;
}

.ks-fixed-modal .modal-body{
    flex: 1;
    overflow: hidden;
}

.ks-modal-body {
    flex: 1;
    overflow: hidden;
}

#ksDetailModal .modal-content {
    height: 75vh;
    display: flex;
    flex-direction: column;
}

#ksDetailModal .modal-body {
    flex: 1;
    overflow: hidden;
}

/* ===== PERBAIKAN CENTER MODAL (TANPA MERUSAK STYLE LAIN) ===== */
#ksDetailModal .modal-dialog {
    max-width: 1200px;
}
</style>

@if($errors->any())
<div class="alert alert-danger alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
  <h3 class="alert-heading font-size-h4 font-w400">Error</h3>
  @foreach ($errors->all() as $error)
    <p class="mb-0">{{ $error }}</p>
  @endforeach
</div>
@endif

<div class="crm-wrapper">
    <div class="card crm-card">
        <div class="card-body">

            <div class="mb-3"><h5 class="fw-bold mb-0">Stock Monitoring</h5></div>

            <!-- FILTER -->
            <div class="row align-items-end mb-3">
                <div class="col-md-3">
                    <select class="js-select2 form-control" id="warehouse">
                        <option value="">Pilih Gudang</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="js-select2 form-control" id="brand" disabled>
                        <option value="">Pilih Brand</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->brand_name }}">{{ $brand->brand_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="js-select2 form-control" id="packaging" disabled>
                        <option value="">Pilih Packaging</option>
                        @foreach($packaging as $pack)
                            <option value="{{ $pack->id }}">{{ $pack->pack_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" id="product_name" placeholder="Nama Product" disabled>
                </div>
            </div>

            <!-- TABLE -->
            <div class="table-responsive">
                <table id="datatable" class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Product</th>
                            <th>Brand</th>
                            <th>Kemasan</th>
                            <th>Stock</th>
                            <th>KS</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- Modal Detail KS di luar tabel -->
<div class="modal fade" id="ksDetailModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
            Detail Stock 
            <span id="modalProductInfo" class="ms-2 text-muted fw-normal"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body ks-modal-body" id="ksDetailContent">
        <div class="text-center py-5">
          <div class="spinner-border text-primary"></div>
        </div>
      </div>
    </div>
  </div>
</div>


@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.datatables-button')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script>
$(document).ready(function(){
    $('.js-select2').select2();
    let datatableUrl = '{{ route("superuser.gudang.stock.json") }}';

    let table = $('#datatable').DataTable({
        processing:true,
        serverSide:false,
        ajax:{
            url: datatableUrl,
            type:'GET',
            data: function(d){
                let warehouseId = $('#warehouse').val();
                if(!warehouseId) return false;
                return {
                    warehouse_id: warehouseId,
                    brand: $('#brand').val(),
                    packaging: $('#packaging').val(),
                    product_name: $('#product_name').val()
                };
            },
            dataSrc:'data'
        },
        dom:'Brt',
        buttons:[
            {
                extend:'excelHtml5',
                text:'<i class="fa fa-file-excel"></i>',
                title:'Stock Monitoring',
                className:'btn btn-success btn-sm',
                exportOptions:{
                    columns: ':visible',
                    modifier:{
                        page:'all'
                    }
                }
            }
        ],
        columns: [
          { data: 'no', width: '40px' },
          { data: 'product_name' },
          { data: 'brand_name', width: '90px' },
          { data: 'pack_name', width: '100px' },
          { data: 'stock', className: 'text-end', width: '65px' },
          { 
              data: 'ks', 
              className:'text-end', 
              width:'65px',
              render: function(data, type, row){
                    return `
                        <a href="#" 
                           class="ks-detail-link"
                           data-encoded="${row.encoded_id}"
                           data-product="${row.product_name}"
                           data-pack="${row.pack_name}">
                           ${data}
                        </a>`;
                }
          },
        ],
        paging: false,
        // pageLength:12,
        lengthChange:false,
        autoWidth:false,
        scrollX:false,
        ordering:false,
        // pagingType:"simple",
        // language:{paginate:{previous:"<", next:">"}},
        // drawCallback: function(settings){
        //     let api = this.api();
        //     let pageInfo = api.page.info();
        //     $('.dataTables_info').hide();

        //     let current = pageInfo.page+1;
        //     let total = pageInfo.pages;

        //     if($('#customPagination').length===0){
        //         $('.dataTables_paginate').html(
        //             `<div id="customPagination" class="erp-pagination">
        //                 <button class="first"><<</button>
        //                 <button class="prev"><</button>
        //                 <span class="page-info">${current}/${total}</span>
        //                 <button class="next">></button>
        //                 <button class="last">>></button>
        //             </div>`
        //         );
        //     } else {
        //         $('.page-info').text(current+'/'+total);
        //     }

        //     $('.first').off().on('click', function(){ api.page('first').draw('page'); });
        //     $('.prev').off().on('click', function(){ api.page('previous').draw('page'); });
        //     $('.next').off().on('click', function(){ api.page('next').draw('page'); });
        //     $('.last').off().on('click', function(){ api.page('last').draw('page'); });
        // }
    });

    function reloadTable(){
        if($('#warehouse').val()){
            table.ajax.reload(null,false);
        }
    }

    $('#warehouse').on('select2:select', function(e){
        $('#brand,#packaging,#product_name').prop('disabled', false);
        reloadTable();
    });
    $('#brand,#packaging,#product_name').on('change', reloadTable);

    $('#datatable').on('click', '.ks-detail-link', function(e){
        e.preventDefault();
    
        let encoded      = $(this).data('encoded');
        let warehouseId  = $('#warehouse').val();
        let productName  = $(this).data('product');
        let packName     = $(this).data('pack');
    
        if(!warehouseId) return alert('Warehouse belum dipilih!');
    
        // Set header info
        $('#modalProductInfo').html(
            `: ${productName} / ${packName}`
        );
    
        let modal = new bootstrap.Modal(document.getElementById('ksDetailModal'));
    
        $('#ksDetailContent').html(
            '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>'
        );
    
        modal.show();
    
        let url = '{{ url("superuser/gudang/stock") }}' + warehouseId + '/detail/' + encoded;
    
        $.ajax({
            url: url,
            type: 'GET',
            success: function(html){
                $('#ksDetailContent').html(html);
            },
            error: function(){
                $('#ksDetailContent').html('<div class="text-danger text-center p-3">Gagal memuat data</div>');
            }
        });
    });
});
</script>
@endpush