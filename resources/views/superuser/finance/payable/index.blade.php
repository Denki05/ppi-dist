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
                <div class="col-6">
                    <div class="card">
                        <div class="card-body">
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

                <div class="col-6">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="saldo_transfer">Transfer</label>
                                        <input type="text" name="saldo_transfer" id="saldo_transfer" class="form-control">
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

                    <div class="card">
                        <div class="card-body">
                            <div id="invoice_detail">
                                <input type="hidden" name="invoice_id" id="detail_invoice_id">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="detail_invoice_total">Total Tagihan</label>
                                            <input type="text" id="detail_invoice_total" class="form-control text-center" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="payment_amount">Jumlah Bayar</label>
                                            <div class="input-group">
                                                <input type="text" name="payment_amount" id="payment_amount" class="form-control text-center" required>
                                                <div class="input-group-text">
                                                    <input type="checkbox" id="balance_checkbox" disabled>
                                                    <label for="balance_checkbox" class="mb-0 ms-2">Balance</label>
                                                </div>
                                            </div>
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

    <!-- TAB DONE -->
    <div class="tab-content" id="content2">
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered table-striped" id="paid_invoices_table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 15%;">Payment Code</th>
                            <th style="width: 15%;">Payment Date</th>
                            <th style="width: 15%;">Invoice</th>
                            <th style="width: 20%;">Store</th>
                            <th style="width: 10%;">Total</th>
                            <th style="width: 10%;">Status</th>
                            <th style="width: 10%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payable as $p)
                        @php
                            $code = DB::table('finance_invoicing')
                                ->leftJoin('finance_payable_detail', 'finance_invoicing.id', '=', 'finance_payable_detail.invoice_id')
                                ->leftJoin('finance_payable', 'finance_payable_detail.payable_id', '=', 'finance_payable.id')
                                ->where('finance_payable.id', $p->id)
                                ->select('finance_invoicing.code')
                                ->first();
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $p->code }}</td>
                            <td>{{ $p->pay_date }}</td>
                            <td>
                                {{ $code->code ?? '-' }}
                            </td>
                            <td>{{ $p->customer->name }} {{ $p->customer->text_kota }}</td>
                            <td>{{ number_format($p->total, 0, ',', '.') }}</td>
                            <td>{{ $p->status() }}</td>
                            <td>
                                @if($p->status() == "ACC")
                                    <a href="{{ route('superuser.finance.payable.detail', $p->id) }}">
                                        <button type="button" class="btn btn-sm btn-circle btn-alt-secondary" title="View">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </a>

                                    <form action="{{ route('superuser.finance.payable.destroy', $p->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini? Aksi ini tidak bisa dibatalkan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-circle btn-alt-secondary" title="Destroy">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
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
    // Helper function untuk format angka ke Rupiah
    function formatRupiah(angka, prefix) {
        let number_string = angka.toString().replace(/[^,\d]/g, '').toString(),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
    }

    // Helper function untuk membersihkan format Rupiah
    function cleanRupiah(rupiah) {
        let cleaned = rupiah.toString().replace(/[^0-9,]/g, '').replace(',', '.');
        return parseFloat(cleaned) || 0;
    }

    let saldoTransfer = 0;
    
    $('#saldo_transfer').on('input', function() {
        let cleanValue = cleanRupiah($(this).val());
        saldoTransfer = cleanValue;
        $('#saldo_sisa').val(saldoTransfer);
        $(this).val(formatRupiah(cleanValue, 'Rp. '));
    });

    // Handle input payment amount
    $('#payment_amount').on('input', function() {
        let cleanValue = cleanRupiah($(this).val());
        $(this).val(formatRupiah(cleanValue, 'Rp. '));
    });

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

    $(document).on('click','.customer-item',function(e){
        e.preventDefault();
        let id = $(this).data('id');
        $('#customer_id').val(id);
        $('#searchCustomer').val($(this).text());
        $('#customerList').hide();

        $('#detail_invoice_id').val('');
        $('#detail_invoice_total').val('');
        $('#payment_amount').val('');
        $('#balance_checkbox').prop('disabled', true).prop('checked', false);
        
        $.get("{{ route('superuser.finance.payable.unpaidInvoices') }}",{customer_id:id},function(res){
            let rows = '';
            if(res.length > 0){
                res.forEach(item=>{
                    rows += `
                        <tr class="invoice-row" 
                            data-id="${item.id}" 
                            data-code="${item.code}" 
                            data-total-raw="${item.sisa_tagihan.replace(/\./g, '').replace(/,/g, '.')}"
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
        
        saldoTransfer = 0;
        $('#saldo_transfer').val('');
        $('#saldo_sisa').val(0);
        $('#balance_checkbox').prop('disabled', true).prop('checked', false);
    });

    var table = $('#invoice_table').DataTable({
      scrollY: "300px",
      scrollX: true, // <-- Tambahkan ini
      scrollCollapse: false,
      paging: false,
      searching: false,
      info: false,
      ordering: false,
      autoWidth: true // <-- Ubah ini
    });

    $(document).on('click', '.invoice-row', function(){
        let invoiceId = $(this).data('id');
        let totalTagihan = cleanRupiah($(this).data('total-raw').toString());
        
        $('#detail_invoice_id').val(invoiceId);
        $('#detail_invoice_total').val(formatRupiah(totalTagihan, 'Rp. '));
        $('#payment_amount').val('');
        
        // Perbaikan: Aktifkan checkbox "Balance" setelah invoice dipilih
        $('#balance_checkbox').prop('disabled', false).prop('checked', false);

        $('#invoice_table tbody tr').removeClass('selected-row');
        $(this).addClass('selected-row');
    });

    // Proses pembayaran
    $('#processPayment').on('click', function(e) {
        e.preventDefault();

        if (!confirm('Apakah Anda yakin ingin memproses pembayaran ini?')) {
            return;
        }

        let customerId = $('#customer_id').val();
        let payDate = $('#pay_date').val();
        let invoiceId = $('#detail_invoice_id').val();
        let note = $('#note').val();
        let paymentAmount = cleanRupiah($('#payment_amount').val());
        let isBalanced = $('#balance_checkbox').is(':checked');
        let totalTagihan = cleanRupiah($('#detail_invoice_total').val());

        if (!customerId || !payDate || !invoiceId || (paymentAmount <= 0 && !isBalanced)) {
            alert('Mohon lengkapi data pembayaran dengan benar!');
            return;
        }

        let saldoSisa = parseFloat($('#saldo_sisa').val()) || 0;
        
        let amountToSend;
        if (isBalanced) {
            amountToSend = totalTagihan;
        } else {
            amountToSend = paymentAmount;
            if (amountToSend > saldoSisa) {
                alert('Saldo transfer tidak mencukupi untuk pembayaran ini');
                return;
            }
        }

        let data = {
            _token: '{{ csrf_token() }}',
            customer_id: customerId,
            pay_date: payDate,
            note: note,
            repeater: [{
                invoice_id: invoiceId,
                is_balanced: isBalanced ? 1 : 0, 
                payable: amountToSend
            }]
        };
        
        $.ajax({
            url: "{{ route('superuser.finance.payable.store') }}",
            method: "POST",
            data: data,
            success: function(res) {
                if (res.success) {
                    alert('Pembayaran berhasil diproses!');
                    
                    // Perbarui saldo sisa berdasarkan amount yang dikirim
                    saldoSisa -= amountToSend; 
                    $('#saldo_sisa').val(saldoSisa);
                    $('#saldo_transfer').val(formatRupiah(saldoSisa, 'Rp. '));

                    $(`tr[data-id="${invoiceId}"]`).remove();
                    
                    // Reset form detail
                    $('#detail_invoice_id').val('');
                    $('#detail_invoice_total').val('');
                    $('#payment_amount').val('');
                    $('#note').val('');
                    
                    // Hapus highlight
                    $('#invoice_table tbody tr').removeClass('selected-row');
                    
                    // Reset tanggal
                    $('#pay_date').val('');
                    $('#balance_checkbox').prop('checked', false).prop('disabled', true);

                } else {
                    // Perbaikan: Tampilkan pesan error dari server
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
                    alert('Gagal terhubung ke server. Mohon coba lagi. ' + (error.message || ''));
                }
            }
        });
    });

    $('input[name="tabs"]').on('change', function() {
        let tabId = $(this).attr('id');
        $('.tab-content').removeClass('active');
        $(`#content${tabId.replace('tab', '')}`).addClass('active');
    });

    var paidInvoicesTable = $('#paid_invoices_table').DataTable({
        scrollCollapse: true,
        paging: true,
        searching: true,
        info: true,
        ordering: true,
        autoWidth: false,
        columnDefs: [
            { width: "5%", targets: 0 },
            { width: "15%", targets: 1 },
            { width: "15%", targets: 2 },
            { width: "15%", targets: 3 },
            { width: "20%", targets: 4 },
            { width: "10%", targets: 5 },
            { width: "10%", targets: 6 },
            { width: "10%", targets: 7, orderable: false, searchable: false }
        ],
        order: [
            [2, 'desc']
        ],
        dom: "<'row'<'col-sm-2'l><'col-sm-7 text-left'B><'col-sm-3'f>>" +
          "<'row'<'col-sm-12'tr>>" +
          "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    });
});
</script>
@endpush