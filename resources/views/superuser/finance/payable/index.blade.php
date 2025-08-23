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
                                        <input type="hidden" id="saldo_sisa" class="form-control" value="0" readonly>
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
                                            <textarea name="note" id="note" class="form-control" rows="1"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-primary w-25" id="processPayment">Proses</button>
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
    // Inisialisasi saldo transfer
    let saldoTransfer = 0;
    
    // Ketika input saldo_transfer diubah, update saldo_sisa
    $('#saldo_transfer').on('input', function() {
        saldoTransfer = parseFloat($(this).val()) || 0;
        // Tampilkan saldo sisa di input tersembunyi
        $('#saldo_sisa').val(saldoTransfer);
    });

    // Pencarian customer (tidak ada perubahan di sini)
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

    // Pilih customer -> load invoice (tidak ada perubahan di sini)
    $(document).on('click','.customer-item',function(e){
        e.preventDefault();
        let id = $(this).data('id');
        $('#customer_id').val(id);
        $('#searchCustomer').val($(this).text());
        $('#customerList').hide();

        // Kosongkan form detail invoice saat customer baru dipilih
        $('#detail_invoice_id').val('');
        $('#detail_invoice_total').val('');
        $('#payment_amount').val('');
        
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

    // Reset customer (tidak ada perubahan di sini)
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
        
        // Reset saldo
        saldoTransfer = 0;
        $('#saldo_transfer').val('');
        $('#saldo_sisa').val(0);
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

    // Hapus duplikasi kode ini, karena ini mirip dengan .invoice-row
    // $(document).on('click','.btn-select-invoice',function(){
    //     $('#detail_invoice_id').val($(this).data('id'));
    //     $('#detail_invoice_total').val($(this).data('total'));
    // });

    // Pilihan invoice dan pengisian otomatis (perbaikan)
    $(document).on('click', '.invoice-row', function(){
        // Ambil data dari baris yang diklik
        let invoiceId = $(this).data('id');
        let invoiceCode = $(this).data('code');
        let totalTagihan = $(this).data('total');

        // Isi form detail pembayaran
        $('#detail_invoice_id').val(invoiceId);
        // $('#detail_invoice_code').val(invoiceCode); // Tidak ada input dengan id ini di HTML
        $('#detail_invoice_total').val(totalTagihan);
        
        // Cek apakah saldo transfer mencukupi untuk pembayaran penuh
        let saldoSisa = parseFloat($('#saldo_sisa').val()) || 0;
        
        if (totalTagihan <= saldoSisa) {
            // Jika saldo cukup, isi otomatis payment_amount
            $('#payment_amount').val(totalTagihan);
        } else {
            // Jika tidak cukup, biarkan user input manual
            $('#payment_amount').val(saldoSisa);
        }

        // Highlight baris aktif
        $('#invoice_table tbody tr').removeClass('selected-row');
        $(this).addClass('selected-row');
    });

    // Proses pembayaran (perbaikan)
    $('#processPayment').on('click', function(e) {
        e.preventDefault();

        let customerId = $('#customer_id').val();
        let payDate = $('#pay_date').val();
        let invoiceId = $('#detail_invoice_id').val();
        let paymentAmount = parseFloat($('#payment_amount').val()) || 0;
        let note = $('#note').val();
        
        // Validasi sederhana di frontend
        if (!customerId || !payDate || !invoiceId || paymentAmount <= 0) {
            alert('Mohon lengkapi data pembayaran dengan benar!');
            return;
        }

        // Ambil saldo sisa dari input tersembunyi
        let saldoSisa = parseFloat($('#saldo_sisa').val()) || 0;
        
        if(paymentAmount > saldoSisa){
            alert('Saldo transfer tidak mencukupi untuk pembayaran ini');
            return;
        }

        // Siapkan data untuk dikirim ke server
        let data = {
            _token: '{{ csrf_token() }}',
            customer_id: customerId,
            pay_date: payDate,
            note: note,
            repeater: [{
                invoice_id: invoiceId,
                payable: paymentAmount
            }]
        };
        
        // Menggunakan AJAX untuk mengirim data
        $.ajax({
            url: "{{ route('superuser.finance.payable.store') }}",
            method: "POST",
            data: data,
            success: function(res) {
                if (res.success) {
                    alert('Pembayaran berhasil diproses!');
                    
                    // Kurangi saldo transfer di sisi frontend
                    saldoSisa -= paymentAmount;
                    $('#saldo_sisa').val(saldoSisa);
                    
                    // Update tampilan saldo di input Transfer
                    $('#saldo_transfer').val(saldoSisa);

                    // Hapus baris invoice yang sudah dibayar dari tabel
                    $(`tr[data-id="${invoiceId}"]`).remove();
                    
                    // Reset form detail
                    $('#detail_invoice_id').val('');
                    $('#detail_invoice_total').val('');
                    $('#payment_amount').val('');
                    $('#note').val('');
                    
                    // Hapus highlight
                    $('#invoice_table tbody tr').removeClass('selected-row');

                } else {
                    alert('Terjadi kesalahan: ' + res.message);
                }
            },
            error: function(xhr) {
                let error = JSON.parse(xhr.responseText);
                if (error.errors) {
                    let errorMessage = '';
                    for (let key in error.errors) {
                        errorMessage += error.errors[key][0] + '\n';
                    }
                    alert('Gagal memproses pembayaran:\n' + errorMessage);
                } else {
                    alert('Gagal terhubung ke server. Mohon coba lagi.');
                }
            }
        });
    });
});
</script>
@endpush