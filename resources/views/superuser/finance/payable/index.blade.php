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
                <div class="row">
                    <div class="col-6">
                        <div class="card">
                            <div class="card-body">
                                <div id="invoice_section">
                                    <table class="table table-bordered" id="invoice_table">
                                        <thead>
                                            <tr>
                                                <th style="width: 5%;">#</th>
                                                <th style="width: 10%;">Tanggal</th>
                                                <th style="width: 10%;">Nota</th>
                                                <th style="width: 15%;">Brand</th>
                                                <th style="width: 15%;">Tagihan</th>
                                                <th style="width: 15%;">Sisa</th>
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
                        {{-- CARD BARU UNTUK INPUT --}}
                        <div class="card mb-3">
                            <div class="card-body">
                                {{-- PENCARIAN CUSTOMER DIPINDAHKAN KESINI --}}
                                <div class="row align-items-end mb-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <div class="input-group d-flex align-items-center">
                                                <select name="customer_id" id="selectCustomer" class="form-control" style="width: 85%;"></select>
                                                <button class="btn btn-danger" type="button" id="resetCustomer">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="saldo_transfer">Tanggal Bayar</label>
                                            <input type="date" name="pay_date" id="pay_date" class="form-control" required value="{{ old('pay_date', date('Y-m-d')) }}">
                                        </div>
                                    </div>
                                </div>

                                {{-- END PENCARIAN CUSTOMER --}}

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="pay_date">Transfer</label>
                                            <input type="text" name="saldo_transfer text-center" id="saldo_transfer" class="form-control" autocomplete="off">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="pay_date">Sisa Transfer</label>
                                            <div class="input-group">
                                                <input type="text" name="saldo_sisa" id="saldo_sisa" class="form-control" autocomplete="off" readonly>
                                                <button type="button" id="lockSaldoBtn" class="btn btn-outline-secondary">
                                                    <i class="fa fa-lock"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="invoice_detail">
                                    <input type="hidden" name="invoice_id" id="detail_invoice_id">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="info_tagihan">Kode - Tipe</label>
                                                <input type="text" id="info_tagihan" name="info_tagihan" class="form-control text-center border-0" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="detail_invoice_total">Total Tagihan</label>
                                                <input type="text" id="detail_invoice_total" class="form-control text-center border-0" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label for="payment_amount">Jumlah Bayar</label>
                                                <div class="input-group">
                                                    <input type="text" name="payment_amount" id="payment_amount" class="form-control text-center" autocomplete="off" required>
                                                    <div class="input-group-text">
                                                        <input type="checkbox" id="balance_checkbox" disabled>
                                                        <label for="balance_checkbox" class="mb-0 ms-2">Adjusment</label>
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
                                    <div class="mt-2">
                                        <button type="button" id="prosesBtn" class="btn btn-warning">
                                            <i class="fa fa-cogs"></i> Proses
                                        </button>
                                        <button type="button" id="settelBtn" class="btn btn-success" disabled>
                                            <i class="fa fa-check"></i> Settel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

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

.invoice-unpaid {
    background-color: #fff3cd; /* Warna kuning muda */
}

.invoice-paid {
    background-color: #d4edda; /* Warna hijau muda */
}

/* pastikan select2 dan span rata tengah */
.input-group {
    display: flex;
    align-items: center; /* bikin semua anak sejajar vertikal */
}

/* style span */
.status-text {
    font-size: 1rem;
    font-weight: bold;
    white-space: nowrap;
}

/* warna tulisan (tanpa background block penuh) */
.status-success {
    color: #28a745; /* hijau */
}

.status-danger {
    color: #dc3545; /* merah */
}


</style>

<script>
    $(document).ready(function () {
        $('#lockSaldoBtn').removeClass("btn-success").addClass("btn-danger");
        $('#lockSaldoBtn').find('i').removeClass("fa-lock").addClass("fa-lock-open");

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

        $('#saldo_transfer').on('blur', function() {
            let cleanValue = cleanRupiah($(this).val());
            $(this).val(formatRupiah(cleanValue, 'Rp. '));
            $('#saldo_sisa').val(formatRupiah(cleanValue, 'Rp. '));

            if (cleanValue > 0) {
                $(this).prop("readonly", true);
                $('#lockSaldoBtn').removeClass("btn-danger").addClass("btn-success");
                $('#lockSaldoBtn').find('i').removeClass("fa-lock-open").addClass("fa-lock");
            }
        });

        $("#lockSaldoBtn").on("click", function () {
            const saldoInput   = $("#saldo_transfer");
            const saldoSisa    = cleanRupiah($("#saldo_sisa").val());
            const icon         = $(this).find("i");
            const isReadonly   = saldoInput.prop("readonly");

            // Jika sedang terkunci → coba buka
            if (isReadonly) {
                if (saldoSisa === 0) {
                    saldoInput.prop("readonly", false);
                    $(this).toggleClass("btn-success btn-danger");
                    icon.toggleClass("fa-lock fa-lock-open");
                } else {
                    alert("Saldo belum 0, tidak bisa release!");
                }
                return;
            }

            // Jika sedang terbuka → coba kunci
            const saldoSekarang = cleanRupiah(saldoInput.val());
            if (saldoSekarang > 0) {
                saldoInput.prop("readonly", true);
                $(this).toggleClass("btn-danger btn-success");
                icon.toggleClass("fa-lock-open fa-lock");
            } else {
                alert("Masukkan nominal transfer terlebih dahulu!");
            }
        });


        $('#payment_amount').on('input', function() {
            let cleanValue = cleanRupiah($(this).val());
            $(this).val(formatRupiah(cleanValue, 'Rp. '));
        });

        $('#selectCustomer').select2({
            placeholder: 'Ketik nama customer...',
            allowClear: true,
            ajax: {
                url: "{{ route('superuser.finance.payable.customerSearch') }}",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        query: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.map(function(customer) {
                            return {
                                id: customer.id,
                                text: `${customer.name} ${customer.text_kota}`
                            };
                        })
                    };
                },
                cache: true
            }
        });

        $('#selectCustomer').on('change', function(){
            let customerId = $(this).val();

            $('#detail_invoice_id').val('');
            $('#info_tagihan').val('');
            $('#detail_invoice_total').val('');
            $('#payment_amount').val('');
            $('#balance_checkbox').prop('disabled', true).prop('checked', false);
            
            if (customerId) {
                $.get("{{ route('superuser.finance.payable.unpaidInvoices') }}",{customer_id: customerId},function(res){
                    let rows = '';
                    if(res.length > 0){
                        res.forEach(item=>{
                            // Tambahkan class .invoice-unpaid pada setiap baris
                            rows += `
                                <tr class="invoice-row invoice-unpaid" 
                                    data-id="${item.id}" 
                                    data-code="${item.code}" 
                                    data-total-raw="${item.sisa_tagihan.replace(/\./g, '').replace(/,/g, '.')}"
                                    data-total="${item.sisa_tagihan}"
                                    data-type-nota="${item.type_name}">
                                    <td>${item.type_name}</td>
                                    <td>${item.date}</td>
                                    <td>${item.code}</td>
                                    <td>${item.brand}</td>
                                    <td>${item.tagihan}</td>
                                    <td>${item.sisa_tagihan}</td>
                                </tr>`;
                        });
                        $('#invoice_table tbody').html(rows);
                    }else{
                        $('#invoice_table tbody').html('<tr><td colspan="5" class="text-center">Tidak ada invoice yang belum dibayar.</td></tr>');
                    }
                });
            } else {
                $('#invoice_table tbody').html('<tr><td colspan="5" class="text-center">Silahkan cari customer terlebih dahulu.</td></tr>');
            }
        });

        $('#resetCustomer').on('click', function(){
            $('#selectCustomer').val(null).trigger('change');
            
            $('#detail_invoice_id').val('');
            $('#detail_invoice_total').val('');
            $('#payment_amount').val('');
            $('#pay_date').val('');
            $('#note').val('');
            
            saldoTransfer = 0;
            $('#saldo_transfer').val('');
            $('#saldo_transfer').prop("readonly", false); // Add this line
            $('#saldo_sisa').val(0);

            // Reset the button's state to unlocked
           
            
            $('#balance_checkbox').prop('checked', false).prop('disabled', true);

            $('#status_span').addClass('d-none');
        });

        var table = $('#invoice_table').DataTable({
            scrollY: "320px",
            scrollX: false,
            scrollCollapse: false,
            paging: false,
            searching: false,
            info: false,
            ordering: false,
            autoWidth: false,
            columnDefs: [
                    // Lebar kolom yang dioptimalkan
                    { "width": "5%", "targets": 0 },
                    { "width": "10%", "targets": 1 },
                    { "width": "10%", "targets": 2 },
                    { "width": "15%", "targets": 3 },
                    { "width": "15%", "targets": 4 },
                    { "width": "15%", "targets": 5 }
            ],
        });

        $(document).on('click', '.invoice-row', function(){
            let invoiceId = $(this).data('id');
            let totalTagihan = cleanRupiah($(this).data('total-raw').toString());
            let invoiceCode = $(this).data('code');
            let typeNota = $(this).data('type-nota');
            
            $('#detail_invoice_id').val(invoiceId);
            $('#detail_invoice_total').val(formatRupiah(totalTagihan, 'Rp. '));
            $('#payment_amount').val(formatRupiah(totalTagihan, 'Rp. '));
            $('#info_tagihan').val(`${invoiceCode} - ${typeNota}`);

            $('#balance_checkbox').prop('disabled', false).prop('checked', false);

            $('#invoice_table tbody tr').removeClass('selected-row');
            $(this).addClass('selected-row');
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

        // penampung data sementara
        let draftData = {
            _token: $('meta[name="csrf-token"]').attr("content"),
            customer_id: null,
            pay_date: null,
            note: null,
            repeater: [] // menampung banyak invoice
        };

        $("#prosesBtn").on("click", function () {
            let customerId = $('#selectCustomer').val();
            let payDate = $('#pay_date').val();
            let invoiceId = $('#detail_invoice_id').val();
            let note = $('#note').val();
            let paymentAmount = cleanRupiah($('#payment_amount').val());
            let isBalanced = $('#balance_checkbox').is(':checked');
            let totalTagihan = cleanRupiah($('#detail_invoice_total').val());
            let saldoSisa = cleanRupiah($('#saldo_sisa').val());

            if (!customerId || !payDate || !invoiceId || (paymentAmount <= 0 && !isBalanced)) {
                alert('Mohon lengkapi data pembayaran dengan benar!');
                return;
            }

            let amountToPay = isBalanced ? totalTagihan : paymentAmount;

            if (amountToPay > saldoSisa && !isBalanced) {
                alert('Saldo transfer tidak mencukupi untuk pembayaran ini');
                return;
            }

            // kalau data baru, set header
            if (!draftData.customer_id) {
                draftData.customer_id = customerId;
                draftData.pay_date = payDate;
                draftData.note = note;
            }

            // cek apakah invoice sudah pernah diproses
            let existing = draftData.repeater.find(item => item.invoice_id === invoiceId);
            if (existing) {
                alert("Invoice ini sudah diproses!");
                return;
            }

            // simpan invoice ke draft array
            draftData.repeater.push({
                invoice_id: invoiceId,
                is_balanced: isBalanced ? 1 : 0,
                payable: amountToPay
            });

            // update saldo_sisa di frontend
            let newSaldoSisa = saldoSisa - amountToPay;
            $('#saldo_sisa').val(formatRupiah(newSaldoSisa, 'Rp. '));

            // update tabel invoice
            let row = $(`tr[data-id="${invoiceId}"]`);
            let sisaTagihan = totalTagihan - amountToPay;
            row.find('td:last').text(formatRupiah(sisaTagihan, 'Rp. '));
            if (sisaTagihan <= 0) {
                row.removeClass('invoice-unpaid').addClass('invoice-paid');
                row.off('click').addClass('disabled-row');
            }

            // aktifkan tombol settel
            $("#settelBtn").prop("disabled", false);

            alert("Invoice berhasil diproses sementara. Klik Settel untuk simpan ke database.");
        });

        $("#settelBtn").on("click", function () {
            if (!draftData || draftData.repeater.length === 0) {
                alert("Belum ada data invoice yang diproses!");
                return;
            }

            $.ajax({
                url: "{{ route('superuser.finance.payable.store') }}",
                method: "POST",
                data: draftData,
                success: function (res) {
                    if (res.success) {
                        alert("Data berhasil disimpan!");
                        // reset draft
                        draftData = {
                            _token: $('meta[name="csrf-token"]').attr("content"),
                            customer_id: null,
                            pay_date: null,
                            note: null,
                            repeater: []
                        };
                        $("#settelBtn").prop("disabled", true);

                        // reset form detail
                        $('#detail_invoice_id').val('');
                        $('#info_tagihan').val('');
                        $('#detail_invoice_total').val('');
                        $('#payment_amount').val('');
                        $('#note').val('');
                        $('#invoice_table tbody tr').removeClass('selected-row');
                        $('#balance_checkbox').prop('checked', false).prop('disabled', true);
                    } else {
                        alert('Terjadi kesalahan: ' + res.message);
                    }
                },
                error: function (xhr) {
                    alert("Terjadi kesalahan saat menyimpan!");
                    console.error(xhr.responseText);
                }
            });
        });
    });
</script>
@endpush