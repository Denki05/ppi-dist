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

        {{-- Tab Header --}}
        <main class="tab-header">
            <input type="radio" id="tab1" name="tabs" checked hidden>
            <label for="tab1" class="tab-label tab-list" data-tab="content1">Nota Kredit</label>
        </main>

        {{-- Tab Content: Kasir --}}
        <div class="tab-content active" id="content1">
            <form id="payableForm" method="POST" action="{{ route('superuser.finance.nota_kredit_finance.store') }}">
                @csrf
                <input type="hidden" name="action_type" id="action_type">

                {{-- Baris pertama: Tabel Retur dan Tabel Piutang --}}
                <div class="row">
                    {{-- Kolom Kiri: List Retur --}}
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="table-responsive table-fixed-height">
                                    <table class="table table-striped" id="table_retur">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Code</th>
                                                <th>Customer</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($retur as $p)
                                            @if($p->fat_status == 0)
                                            <tr class="retur-row"
                                                style="cursor:pointer"
                                                data-retur_id="{{ $p->id }}"
                                                data-invoice_id="{{ $p->invoice->invoicing->id ?? '-' }}"
                                                data-do_id="{{ $p->invoice->id ?? '-' }}"
                                                data-nota_awal="{{ $p->invoice->do_code ?? '-' }}"
                                                data-jumlah_nota_awal="{{ $p->invoice->do_detail_cost->purchase_total_idr ?? 0 }}"
                                                data-nota_kredit="{{ $p->code }}"
                                                data-jumlah_nota_kredit="{{ $p->cost->purchase_total_idr ?? 0 }}"
                                                data-payment_status="{!! $p->payment_status() !!}"
                                                data-retur_type="{{ $p->type() }}"

                                                data-nota_baru_id="{{ $p->invoiceNew->id ?? '-' }}"
                                                data-nota_baru="{{ $p->invoiceNew->do_code ?? '-' }}"
                                                data-jumlah_nota_baru="{{$p->invoiceNew->do_detail_cost->purchase_total_idr ?? 0}}"
                                            >
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $p->code }}</td>
                                                <td>{{ $p->customer->name }} {{ $p->customer->text_kota }}</td>
                                                <td>{!! $p->payment_status() !!}</td>
                                            </tr>
                                            @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Kolom Kanan: Detail Piutang --}}
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="table-responsive table-fixed-height">
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
                                                    <input type="hidden" id="invoice_id" name="invoice_id">
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
                                    <div class="d-flex justify-content-end mt-3">
                                        <button type="button" class="btn btn-info me-2" data-toggle="modal" data-target="#printModal" id="btnGenerate">CETAK</button>
                                        <button type="submit" id="btnVerify" class="btn btn-primary" disabled>VERIFIKASI</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="printModal" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl custom-modal">
    <div class="modal-content h-100">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-0">
        <div class="row no-gutters h-100">
          <div class="col-12 h-100">
            <iframe id="modalIframe" src="" class="iframe-full"></iframe>
          </div>
          <!-- <div class="col-6 h-100">
            <iframe id="modalIframe2" src="" class="iframe-full"></iframe>
          </div> -->
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.select2')

@push('scripts')
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

.table-fixed-height {
    height: 350px;
    overflow-y: auto;
}
.custom-modal {
    max-width: 95% !important;   /* hampir full lebar */
    width: 95% !important;
    height: 95vh;               /* tinggi hampir penuh viewport */
}

.custom-modal .modal-content {
    height: 95vh;               /* isi modal penuh */
}

.modal-body {
    height: calc(100% - 100px); /* sisakan ruang untuk header+footer */
    overflow: hidden;           /* buang scrollbar */
}

.iframe-full {
    width: 100%;
    height: 100%;
    border: none;
}

.table-fixed-height {
    max-height: 400px; /* atur sesuai kebutuhan */
    overflow-y: auto;
}
</style>

<script type="text/javascript">
$(document).ready(function(){
    $('.js-select2').select2();

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

    // Tombol CETAK tidak lagi dinonaktifkan secara default
    $('#btnVerify').prop('disabled', true);

    $('#table_retur').on('click', '.retur-row', function(){
        let nota_awal = $(this).data('nota_awal') || '-';
        let jumlah_nota_awal = parseFloat($(this).data('jumlah_nota_awal')) || 0;
        let nota_kredit = $(this).data('nota_kredit') || '-';
        let jumlah_nota_kredit = parseFloat($(this).data('jumlah_nota_kredit')) || 0;

        let do_id = $(this).data('do_id') || '';
        let invoice_id = $(this).data('invoice_id') || '';
        let retur_id = $(this).data('retur_id') || '';


        let total_piutang = jumlah_nota_awal - jumlah_nota_kredit;

        $('#nota_awal_cell').val(nota_awal);
        $('#jumlah_nota_awal_cell').val(formatIDR(jumlah_nota_awal));
        $('#nota_kredit_cell').val(nota_kredit);
        $('#jumlah_nota_kredit_cell').val(formatIDR(jumlah_nota_kredit));
        $('#total_piutang_cell').val(formatIDR(total_piutang));

        $('#do_id').val(do_id);
        $('#invoice_id').val(invoice_id);
        $('#retur_id').val(retur_id);

        $('.retur-row').removeClass('active-row');
        $(this).addClass('active-row');
        
        // Tombol VERIFIKASI tetap dinonaktifkan
        $('#btnVerify').prop('disabled', true);
    });

    // Handler untuk tombol CETAK
    $('#btnGenerate').on('click', function() {
        let returId = $('#retur_id').val();
        let invoicingId = $('#invoice_id').val();

        // Cek apakah returId sudah ada (berarti baris sudah dipilih)
        if (!returId) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: 'Harap pilih salah satu Nota Kredit dari daftar di samping terlebih dahulu.',
            });
            return; // Hentikan eksekusi jika tidak ada baris yang dipilih
        }

        // Ambil data dari row aktif
        let returType = $('.retur-row.active-row').data('retur_type');
        let paymentStatus = $('.retur-row.active-row').data('payment_status');

        // Tentukan URL PDF pertama
        let pdfUrl1 = '';
        if (returType === 'RETUR' && paymentStatus.trim() === 'LUNAS') {
            pdfUrl1 = "{{ route('superuser.penjualan.sale_return.pdf_refund', ['data' => '']) }}" + returId;    
        } else if (returType === 'TUKAR BARANG' && paymentStatus.trim() === 'LUNAS') {
            pdfUrl1 = "{{ route('superuser.penjualan.sale_return.pdf_refund', ['data' => '']) }}" + returId;
        } else {
            pdfUrl1 = "{{ route('superuser.penjualan.sale_return.mergePdf', ['invoice' => 0, 'retur' => 0]) }}";
            pdfUrl1 = pdfUrl1.replace('/0/0', '/' + invoicingId + '/' + returId);

        }

        // Load URL ke iframe
        $('#modalIframe').attr('src', pdfUrl1); 

        // Tampilkan modal (Bootstrap 4)
        $('#printModal').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#closeModalButton').hide();

        // Aktifkan tombol verifikasi
        $('#btnVerify').prop('disabled', false);
    });

    // Event saat modal ditutup
    $('#printModal').on('hidden.bs.modal', function () {
        // Reset iframe
        $('#modalIframe').attr('src', '');
        $('#modalIframe2').attr('src', '');
    });


    // Handler untuk tombol Download
    $('#downloadButton').on('click', function() {
        let returId = $('#retur_id').val();
        let downloadUrl = "{{ route('superuser.penjualan.sale_return.pdf_download', ['id' => 'placeholder']) }}".replace('placeholder', returId);
        window.open(downloadUrl, '_blank');
        
        // Setelah tombol download diklik, aktifkan kembali tombol tutup
        $('#closeModalButton').show();

        // Aktifkan tombol verifikasi
        $('#btnVerify').prop('disabled', false);
    });

    // Handler untuk tombol Print
    $('#printButton').on('click', function() {
        let iframe = document.getElementById('modalIframe');
        iframe.contentWindow.print();
        
        // Setelah tombol print diklik, aktifkan kembali tombol tutup
        $('#closeModalButton').show();

        // Aktifkan tombol verifikasi
        $('#btnVerify').prop('disabled', false);
    });

    // Menangani klik tombol untuk mengisi hidden input
    $('#btnVerify').on('click', function() {
        $('#action_type').val('verify');
    });
    
    // Handler form submission menggunakan AJAX
    $('#payableForm').on('submit', function(e) {
        e.preventDefault(); // Mencegah form dikirim secara normal

        var formData = $(this).serialize();

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

    // TAB SPV
    var tableSpv = $('#table_spv').DataTable({});

    $('#selectCode').on('change', function() {
        let type = $(this).find(':selected').data('type');
        let status = $(this).find(':selected').data('status');
        let returId = $(this).val();

        $('#inputType').val(type ?? '');
        $('#inputStatus').val(status ?? '');

        if (returId) {
            // set src untuk iframe Nota Kredit FAT
            $('#iframeNotaKreditFat').attr(
                'src',
                "{{ route('superuser.penjualan.sale_return.pdf_tt_fat', ['data' => '']) }}" + returId
            );

            
        } else {
            $('#iframeNotaKreditFat').attr('src', '');
        }
    });
});
</script>
@endpush