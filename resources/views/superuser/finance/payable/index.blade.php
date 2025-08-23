@extends('superuser.app')

@section('content')
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

@if(session()->has('success'))
<div class="alert alert-success alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Success</h3>
  <p class="mb-0">{{ session()->get('success') }}</p>
</div>
@endif

<div class="block finance-tabs">
  <div class="block-content block-content-full">
    <main class="tab-header">
        <input type="radio" id="tab1" name="tabs" checked hidden>
        <label for="tab1" class="tab-label tab-payment" data-tab="content1">Payment</label>

        <input type="radio" id="tab2" name="tabs" hidden>
        <label for="tab2" class="tab-label tab-list" data-tab="content2">Done</label>
    </main>

    <div class="tab-content active" id="content1">
        <form id="payableForm" method="POST" action="{{ route('superuser.finance.payable.store') }}">
        @csrf
            <div class="row align-items-end mb-3">
                <div class="col-md-4">
                    <!-- <label for="searchCustomer" class="form-label">Cari Customer</label> -->
                    <div class="input-group">
                        <input type="text" id="searchCustomer" class="form-control" placeholder="Ketik nama customer...">
                        <input type="hidden" name="customer_id" id="customer_id">
                        <button class="btn btn-danger" type="button" id="resetCustomer">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                    <div id="customerList" class="list-group" style="position:absolute; z-index:1000; width:100%; display:none;">
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- GRID KIRI: daftar invoice --}}
                <div class="col-6">
                    <div class="card">
                        <div class="card-body">
                            <!-- <h5 class="card-title">List Tagihan</h5> -->
                            <div id="invoice_section">
                                <table class="table table-bordered" id="invoice_table">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Nota</th>
                                            <th>Tagihan</th>
                                            <th>Sisa</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- GRID KANAN --}}
                <div class="col-6">
                    
                    {{-- CARD BARU: Saldo Transfer Customer --}}
                    <div class="card mb-3">
                        <div class="card-body">
                            <!-- <h5 class="card-title">Saldo Transfer Customer</h5> -->
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="saldo_transfer">Transfer</label>
                                        <input type="number" step="0.01" name="saldo_transfer" id="saldo_transfer" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="pay_date">Tanggal Bayar</label>
                                            <input type="date" name="pay_date" id="pay_date" class="form-control" required>
                                        </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- CARD EXISTING: Invoice Detail --}}
                    <div class="card">
                        <div class="card-body">
                            <!-- <h5 class="card-title">Detail Pembayaran</h5> -->
                            <div id="invoice_detail">
                                <input type="hidden" name="invoice_id" id="detail_invoice_id">

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="detail_invoice_total">Total Tagihan</label>
                                            <input type="text" id="detail_invoice_total" class="form-control text-center" readonly>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="payment_amount">Jumlah Bayar</label>
                                            <input type="number" step="0.01" name="payment_amount" id="payment_amount" class="form-control text-center" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="note">Catatan</label>
                                            <!-- <input type="text" name="note" id="note" class="form-control"> -->
                                            <textarea name="note" id="note" class="form-control" rows="1"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-25">Proses</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>

<style>
/* CSS Anda sebelumnya */
.finance-tabs .tab-label::before {
    font-family: "Font Awesome 6 Free","Font Awesome 5 Free";
    font-weight: 900;
    margin-right: 6px;
}
.finance-tabs .tab-payment::before { content: "\f571"; }
.finance-tabs .tab-list::before { content: "\f03a"; }
.finance-tabs .tab-label {
    background-color: #f0f0f0;
    border: 1px solid #ddd;
    border-bottom: none;
    padding: 10px 20px;
    cursor: pointer;
    display: inline-block;
    border-radius: 5px 5px 0 0;
    margin-right: 5px;
    transition: background-color 0.3s ease;
    font-weight: bold;
    color: #555;
}
.finance-tabs .tab-label:hover { background-color: #e0e0e0; }
.finance-tabs .tab-label.active-tab-label {
    background-color: #fff;
    border-color: #ccc;
    border-bottom: 1px solid #fff;
    color: #000;
}
.finance-tabs .tab-content {
    display: none;
    border: 0px solid #ccc;
    padding: 20px;
    border-radius: 0 5px 5px 5px;
    background-color: #fff;
}
.finance-tabs .tab-content.active { display: block; }

#invoice_table {
    table-layout: fixed;
    width: 100% !important;
}
#invoice_table th, 
#invoice_table td {
    text-align: center;
    vertical-align: middle;
    white-space: nowrap;
}

#invoice_table tbody tr {
    cursor: pointer;
}
#invoice_table tbody tr.selected-row {
    background-color: #d1ecf1; /* biru muda */
}

</style>

<script>
$(document).ready(function () {
    // pencarian customer
    $('#searchCustomer').on('keyup', function(){
        let query = $(this).val().trim();

        if(query.length < 2){
            $('#customerList').hide();
            return;
        }

        $.get("{{ route('superuser.finance.payable.customerSearch') }}",{query}, function(res){
            let list = $('#customerList').empty();
            if(res.length > 0){
                res.forEach(c=>{
                    list.append(`<a href="#" class="list-group-item list-group-item-action customer-item" data-id="${c.id}">${c.display_name}</a>`);
                });
            }else{
                list.append('<div class="list-group-item">Tidak ditemukan</div>');
            }
            list.show();
        });
    });

    // pilih customer -> load invoice
    $(document).on('click','.customer-item',function(e){
        e.preventDefault();
        let id = $(this).data('id');
        $('#customer_id').val(id);
        $('#searchCustomer').val($(this).text());
        $('#customerList').hide();

        $.get("{{ route('superuser.finance.payable.unpaidInvoices') }}",{customer_id:id},function(res){
            let rows = '';
            if(res.length > 0){
                res.forEach(item=>{
                    rows += `
                        <tr class="invoice-row" 
                            data-id="${item.id}" 
                            data-code="${item.code}" 
                            data-total="${item.sisa_tagihan}">
                            <td>${item.date}</td>
                            <td>${item.code}</td>
                            <td>${item.tagihan}</td>
                            <td>${item.sisa_tagihan}</td>
                        </tr>`;
                });
                $('#invoice_table tbody').html(rows);
            }else{
                $('#invoice_table tbody').html('<tr><td colspan="4" class="text-center">Tidak ada invoice yang belum dibayar.</td></tr>');
            }
        });

    });

    // klik pilih invoice -> isi detail
    $(document).on('click','.btn-select-invoice',function(){
        $('#detail_invoice_id').val($(this).data('id'));
        $('#detail_invoice_total').val($(this).data('total'));
    });

    // reset
    $('#resetCustomer').on('click', function(){
        $('#searchCustomer').val('');
        $('#customer_id').val('');
        $('#customerList').hide();
        $('#invoice_table tbody').html('<tr><td colspan="4" class="text-center">Silahkan cari customer terlebih dahulu.</td></tr>');
        $('#detail_invoice_id').val('');
        $('#detail_invoice_total').val('');
        $('#payment_amount').val('');
        $('#pay_date').val('');
        $('#note').val('');
    });

    var table = $('#invoice_table').DataTable({
        scrollY: "300px",        // tinggi scroll
        scrollCollapse: false,    // collapse jika data sedikit
        paging: false,           // matikan pagination
        searching: false,        // matikan search box
        info: false,             // matikan info
        ordering: false,         // matikan sorting
        autoWidth: false,        // jangan pakai width otomatis
        columnDefs: [
            { width: "10%", targets: 0 }, // tanggal
            { width: "10%", targets: 1 }, // nota
            { width: "10%", targets: 2 }, // tagihan
            { width: "10%", targets: 3 }  // action
        ]
    });

    // pastikan kolom sinkron setelah render
    table.columns.adjust().draw();

    $(document).on('click', '.invoice-row', function(){
        $('#detail_invoice_id').val($(this).data('id'));
        $('#detail_invoice_code').val($(this).data('code'));
        $('#detail_invoice_total').val($(this).data('total'));

        // highlight baris aktif
        $('#invoice_table tbody tr').removeClass('selected-row');
        $(this).addClass('selected-row');
    });
});
</script>
@endpush