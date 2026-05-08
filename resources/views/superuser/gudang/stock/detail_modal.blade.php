{{-- DataTables Bootstrap 4 --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.bootstrap4.min.css">

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>

<div id="modal-content-wrapper">

<div class="row align-items-center mb-3">

<div class="col-md-6 mb-2 mb-md-0 d-flex align-items-center">
<label class="form-label mb-0 font-weight-bold text-secondary mr-3">
Periode:
</label>

<div class="input-group input-group-sm" style="max-width:200px;">
<div class="input-group-prepend">
<span class="input-group-text bg-white">
<i class="fas fa-calendar-alt"></i>
</span>
</div>

<input type="month"
class="form-control"
id="month_filter"
value="{{ $month }}"
{{ $lock ? 'disabled' : '' }}>
</div>
</div>

<div class="col-md-6 text-md-right">

<a href="{{ route('superuser.gudang.stock.print', [$warehouse->id, base64_encode($product->id)]) }}"
target="_blank"
id="btnPrintPdf"
class="btn btn-primary btn-sm">

<i class="fas fa-print"></i> PDF
</a>

</div>
</div>

<div class="card border-0 shadow-sm h-100 d-flex flex-column">

<div class="table-responsive ks-scroll-wrapper flex-grow-1">

<table class="table table-hover align-middle mb-0" id="ksDetailTable">

<thead>
<tr>
<th class="text-center" style="width:150px;">Tanggal</th>
<th class="text-center" style="width:100px;">Transaksi</th>
<th class="text-right" style="width:80px;">Masuk</th>
<th class="text-right" style="width:80px;">Keluar</th>
<th class="text-right" style="width:80px;">Saldo</th>
<th class="text-center" style="width:220px;">Keterangan</th>
</tr>
</thead>

<tbody>

@foreach($collects as $c)

<tr>

<td class="text-muted text-center">
{{ $c['created_at'] }}
</td>

<td class="text-center transaksi-cell
{{ $c['in'] && $c['in'] !== '-' ? 'transaksi-masuk' : ($c['out'] && $c['out'] !== '-' ? 'transaksi-keluar' : 'transaksi-netral') }}">
{{ $c['transaction'] }}
</td>

<td class="text-right text-success font-weight-bold">
{{ $c['in'] !== '-' ? $c['in'] : '-' }}
</td>

<td class="text-right text-danger font-weight-bold">
{{ $c['out'] !== '-' ? $c['out'] : '-' }}
</td>

<td class="text-right font-weight-bold">
{{ $c['balance'] }}
</td>

<td class="small text-secondary text-center">
{{ $c['description'] ?: '-' }}
</td>

</tr>

@endforeach

</tbody>
</table>

</div>
</div>

</div>

<script>

$(document).ready(function(){

function loadKsDetail(month){

let warehouseId = '{{ $warehouse->id }}';
let productEncoded = encodeURIComponent('{{ base64_encode($product->id) }}');

$('#ksDetailTable tbody').html(
'<tr><td colspan="6" class="text-center py-4">' +
'<i class="fas fa-spinner fa-spin"></i> Memproses...' +
'</td></tr>'
);

$.ajax({

url: '/superuser/gudang/stock/' + warehouseId + '/detail/' + productEncoded,
type: 'GET',
data: { month: month },
dataType: 'html',

success: function(response){

let wrapped = $('<div>').append(response);
let newContent = wrapped.find('#modal-content-wrapper').html();

$('#modal-content-wrapper').empty().html(newContent);

attachChangeEvent();
initDataTable();
},

error: function(){

$('#ksDetailTable tbody').html(
'<tr><td colspan="6" class="text-center text-danger py-4 small">' +
'Terjadi kesalahan sistem' +
'</td></tr>'
);

}

});

}

function attachChangeEvent(){

function updatePrintLink(month){

let baseUrl = '{{ route("superuser.gudang.stock.print", [$warehouse->id, base64_encode($product->id)]) }}';

$('#btnPrintPdf').attr('href', baseUrl + '?month=' + month);

}

let initialMonth = $('#month_filter').val();

updatePrintLink(initialMonth);

$('#month_filter').off('change').on('change', function(){

let selectedMonth = $(this).val();

updatePrintLink(selectedMonth);

loadKsDetail(selectedMonth);

});

}

function initDataTable(){

if ($.fn.DataTable.isDataTable('#ksDetailTable')) {

$('#ksDetailTable').DataTable().destroy();

}

$('#ksDetailTable').DataTable({

paging:false,
searching:false,
ordering:false,
info:false,
lengthChange:false,

language:{
emptyTable:"Tidak ada data transaksi pada periode ini"
},

columnDefs:[
{ targets:[2,3,4], className:'text-right' }
]

});

}

attachChangeEvent();
initDataTable();

});

</script>

<style>
#ksDetailTable thead th{
    position: sticky;
    top: 0;
    z-index: 10;
    /* background: #007bff; */
    /* color: #fff; */
    font-weight: 600;
    font-size: 0.95rem;
    text-transform: uppercase;
}

#ksDetailTable tbody td{
    font-size: 0.9rem;
    padding-top: 6px !important;
    padding-bottom: 6px !important;
    vertical-align: middle;
}

.transaksi-cell{
    font-size: 1rem;
    font-weight: 600;
}

.transaksi-masuk{ color:#28a745; }
.transaksi-keluar{ color:#dc3545; }
.transaksi-netral{ color:#495057; }

.ks-scroll-wrapper{
    max-height: calc(75vh - 160px);
    overflow-y: auto;
}

.table-responsive::-webkit-scrollbar{
    width:6px;
}

.table-responsive::-webkit-scrollbar-thumb{
    background:#c1c1c1;
    border-radius:10px;
}
</style>