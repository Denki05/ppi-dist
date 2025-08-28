@extends('superuser.app')

@section('content')
<!-- Notifikasi -->
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

            {{-- Tab Header --}}
            <main class="tab-header">
                <input type="radio" id="tab1" name="tabs" checked hidden>
                <label for="tab1" class="tab-label tab-list" data-tab="content1">Kasir</label>

                <input type="radio" id="tab2" name="tabs" hidden>
                <label for="tab2" class="tab-label tab-list" data-tab="content2">SPV</label>

                <input type="radio" id="tab3" name="tabs" hidden>
                <label for="tab3" class="tab-label tab-list" data-tab="content3">Done</label>
            </main>

            {{-- Tab Content: Kasir --}}
            <div class="tab-content active" id="content1">
                <form id="payableForm" method="POST" action="{{ route('superuser.finance.nota_kredit_finance.store') }}">
                    @csrf
                    <input type="hidden" name="action_type" id="action_type">
                    <!-- <h5>Verifikasi Nota Kredit (Kasir)</h5> -->
                    <div class="row">
                        {{-- List Retur --}}
                        <div class="col-md-6">
                            <table class="table table-striped" id="table_retur">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Code</th>
                                        <th>Customer</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($retur as $p)
                                    @if($p->fat_status == 0)
                                        <tr class="retur-row"
                                            style="cursor:pointer"
                                            data-retur_id="{{ $p->id }}"
                                            data-do_id="{{ $p->invoice->id ?? '-' }}"
                                            data-nota_awal="{{ $p->invoice->do_code ?? '-' }}"
                                            data-jumlah_nota_awal="{{ $p->invoice->do_detail_cost->purchase_total_idr ?? 0 }}"
                                            data-nota_kredit="{{ $p->code }}"
                                            data-jumlah_nota_kredit="{{ $p->cost->purchase_total_idr ?? 0 }}"
                                            data-payment_status="{!! $p->payment_status() !!}"
                                        >
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $p->code }}</td>
                                            <td>{{ $p->customer->name }} {{ $p->customer->text_kota }}</td>
                                            <td>{{ number_format($p->cost->purchase_total_idr,0,',','.') }}</td>
                                            <td>{!! $p->payment_status() !!}</td>
                                        </tr>
                                    @endif
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Detail Piutang --}}
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="table_piutang">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 40%;">Keterangan</th>
                                                    <th style="width: 20%;">Code</th>
                                                    <th style="width: 20%;">Jumlah</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Nota Awal</td>
                                                    <td>
                                                        <input type="text" id="nota_awal_cell" style="font-size: 1rem; font-weight: bold;" class="form-control form-control-sm text-center" readonly>
                                                        <input type="hidden" id="do_id" name="do_id">
                                                    </td>
                                                    <td>
                                                        <input type="text" id="jumlah_nota_awal_cell" name="jumlah_nota_awal_cell" style="font-size: 1rem;" class="form-control form-control-sm text-end" readonly>
                                                    </td>
                                                    
                                                </tr>
                                                <tr>
                                                    <td>Nota Kredit</td>
                                                    <td>
                                                        <input type="text" id="nota_kredit_cell" style="font-size: 1rem; font-weight: bold;" class="form-control form-control-sm text-center" readonly>
                                                        <input type="hidden" id="retur_id" name="retur_id">
                                                    </td>
                                                    <td>
                                                        <input type="text" id="jumlah_nota_kredit_cell" name="jumlah_nota_kredit_cell" style="font-size: 1rem;" class="form-control form-control-sm text-end" readonly>
                                                    </td>
                                                </tr>
                                                <tr class="table-secondary fw-bold">
                                                    <td>Total Piutang Akhir</td>
                                                    <td colspan="2"><input type="text" style="font-size: 1rem;" id="total_piutang_cell" name="total_piutang_cell" class="form-control form-control-sm text-end" readonly></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <button type="submit" id="btnNext" class="btn btn-info" disabled>LANJUT</button>
                                        <button type="submit" id="btnVerify" class="btn btn-primary" disabled>VERIFIKASI</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Tab Content: SPV --}}
            <div class="tab-content" id="content2">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Code</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($retur as $p)
                            @if($p->fat_status() == 'KASIR')
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $p->code }}</td>
                                    <td>{{ $p->customer_name }}</td>
                                    <td>{{ number_format($p->total, 0, ',', '.') }}</td>
                                    <td>{{ $p->fat_status() }}</td>
                                    <td>
                                        <button class="btn btn-primary btn-sm">Verifikasi</button>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Tab Content: Done --}}
            <div class="tab-content" id="content3">
                <!-- <h5>Transaksi Retur Selesai (Done)</h5> -->
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Code</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Jenis</th>
                            <th>Dokumen</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- contoh data --}}
                        <tr>
                            <td>1</td>
                            <td>R25H001</td>
                            <td>Customer X</td>
                            <td>2,000,000</td>
                            <td>Retur Tanpa Ganti Barang</td>
                            <td><a href="#" class="btn btn-sm btn-secondary">Print Refund</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<style>
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

.retur-row { cursor: pointer; }
.active-row { background-color: #fff3cd; }
</style>

<script type="text/javascript">
$(document).ready(function(){
    $('input[name="tabs"]').on('change', function() {
        let tabId = $(this).attr('id');
        $('.tab-content').removeClass('active');
        $('#content' + tabId.replace('tab', '')).addClass('active');
    });

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function formatIDR(n){
        return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(n || 0);
    }
    
    function cleanNumericString(str) {
        if (!str) return 0;
        str = str.replace(/[.]/g, ''); 
        return parseFloat(str);
    }

    $('#table_retur').on('click', '.retur-row', function(){
        let nota_awal = $(this).data('nota_awal') || '-';
        let jumlah_nota_awal = parseFloat($(this).data('jumlah_nota_awal')) || 0;
        let nota_kredit = $(this).data('nota_kredit') || '-';
        let jumlah_nota_kredit = parseFloat($(this).data('jumlah_nota_kredit')) || 0;
        let do_id = $(this).data('do_id') || '';
        let retur_id = $(this).data('retur_id') || '';
        let payment_status = $(this).data('payment_status').trim();
        let total_piutang = jumlah_nota_awal - jumlah_nota_kredit;

        $('#nota_awal_cell').val(nota_awal);
        $('#jumlah_nota_awal_cell').val(formatIDR(jumlah_nota_awal));
        $('#nota_kredit_cell').val(nota_kredit);
        $('#jumlah_nota_kredit_cell').val(formatIDR(jumlah_nota_kredit));
        $('#total_piutang_cell').val(formatIDR(total_piutang));

        $('#do_id').val(do_id);
        $('#retur_id').val(retur_id);

        $('.retur-row').removeClass('active-row');
        $(this).addClass('active-row');

        if (do_id && retur_id) {
            if (payment_status === 'LUNAS') {
                $('#btnNext').prop('disabled', false);
                $('#btnVerify').prop('disabled', true);
            } else if (payment_status === 'BELUM LUNAS') {
                $('#btnNext').prop('disabled', true);
                $('#btnVerify').prop('disabled', false);
            } else {
                $('#btnNext').prop('disabled', true);
                $('#btnVerify').prop('disabled', true);
            }
        } else {
            $('#btnNext').prop('disabled', true);
            $('#btnVerify').prop('disabled', true);
        }
    });

    // Menangani klik tombol untuk mengisi hidden input
    $('#btnNext').on('click', function() {
        $('#action_type').val('next');
    });

    $('#btnVerify').on('click', function() {
        $('#action_type').val('verify');
    });

    // Handler form submission menggunakan AJAX
    $('#payableForm').on('submit', function(e) {
        e.preventDefault(); // Mencegah form dikirim secara normal

        // Mengambil data form
        var formData = $(this).serialize();

        // Mengirim data menggunakan AJAX
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            beforeSend: function() {
                // Opsional: Tampilkan loading
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sukses!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        window.location.reload();
                    });
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON.errors;
                var message = xhr.responseJSON.message || 'Terjadi kesalahan. Silakan coba lagi.';
                
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    html: message + '<br>' + (errors ? Object.values(errors).join('<br>') : ''),
                    showConfirmButton: true
                });
            }
        });
    });
});
</script>
@endpush