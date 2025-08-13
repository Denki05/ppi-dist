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

@if($is_see == true)
<div class="block">
    <div class="block-content">
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <main style="background:#fff">
                            <input style="display: none;" id="tab1" type="radio" name="tabs" checked>
                            <label style="padding: 15px 25px; cursor: pointer;" for="tab1" class="tab-label" data-tab="content1">Omset</label>

                            <input style="display: none;" id="tab2" type="radio" name="tabs">
                            <label style="padding: 15px 25px; cursor: pointer;" for="tab2" class="tab-label" data-tab="content2">Tabulasi</label>
                            
                            <input style="display: none;" id="tab3" type="radio" name="tabs">
                            <label style="padding: 15px 25px; cursor: pointer;" for="tab3" class="tab-label" data-tab="content3">Forecasting Principle</label>

                            <section id="content1" class="tab-content active">
                                <div class="mb-3 d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="btn-group" role="group" aria-label="Filter Omset">
                                            <button class="btn btn-primary btn-filter-type-omset active" data-type="all" style="margin-right: 6px;">ALL</button>
                                            <button class="btn btn-success btn-filter-type-omset" data-type="ppn" style="margin-right: 6px;">PPN</button>
                                            <button class="btn btn-warning btn-filter-type-omset" data-type="nonppn">NonPPN</button>
                                        </div>
                                    </div>
                                    {{-- Single Month and Year Picker for Omset forms --}}
                                    <div class="form-group mb-0 d-flex align-items-center">
                                        <input type="month" id="omset-month-year" class="form-control" style="width: 180px;" value="{{ $selectedMonthYear }}">
                                        <button class="btn btn-danger" id="reset-month-filter"><i class="fa fa-sync"></i></button>
                                    </div>
                                    {{-- End Single Month and Year Picker --}}
                                </div>

                                <table class="datatableOmset table table-striped" id="datatableOmset">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Brand</th>
                                            <th>Invoice</th>
                                            <th>Customer</th>
                                            <th>Cash</th>
                                            <th>Tempo</th>
                                            <th style="display:none;">TypeSO</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($progress as $row)
                                            @php
                                                $tipe = strtolower(trim($row->invoice_type ?? 'nonppn'));
                                                $tipe = str_replace(' ', '', $tipe);
                                            @endphp
                                            <tr data-type-so="{{ $tipe }}">
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $row->so_date }}</td>
                                                <td>{{ $row->invoice_brand }}</td>
                                                <td>{{ $row->invoice_code }}</td>
                                                <td>{{ $row->customer_name }} - {{ $row->customer_city }}</td>
                                                <td>{{ number_format($row->invoice_cash, 0, ',', '.') }}</td>
                                                <td>{{ number_format($row->invoice_tempo, 0, ',', '.') }}</td>
                                                <td style="display:none;">{{ $tipe }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5" style="text-align:right"></th>
                                            <th id="totalInvoiceCash" style="text-align: center;"></th>
                                            <th id="totalInvoiceTempo" style="text-align: center;"></th>
                                        </tr>
                                        <tr>
                                            <th colspan="6" style="text-align:right"></th>
                                            <th id="subTotal" style="text-align: center;"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </section>

                            <section id="content2" class="tab-content">
                                <form id="tabulasiForm" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6"> {{-- Bagian kiri untuk input bulan/tahun dan tipe laporan --}}
                                            <div class="mb-3 row align-items-center">
                                                <div class="col-4">
                                                    <input type="month" id="tabulasi-month-year" class="form-control" value="{{ $selectedMonthYear }}">
                                                </div>
                                                 <div class="col-4">
                                                    <select class="form-control js-select2" name="salesman_id_tabulasi" style="width: 100%;">
                                                        <option value="">Semua Salesman</option>
                                                        {{-- Contoh data salesman, ganti dengan loop data dari backend --}}
                                                        <option value="1">Salesman A</option>
                                                        <option value="2">Salesman B</option>
                                                        <option value="3">Salesman C</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mb-3 row align-items-center">
                                                <div class="col-4 col-form-label required"><h5>Tipe Laporan:</h5></div>
                                                <div class="col-8">
                                                    <label class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="report_type_tabulasi" id="type_customer_register" value="brand" checked>
                                                        <h6>R. by Brand</h6>
                                                    </label>
                                                    <label class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="report_type_tabulasi" id="zone_customer_register" value="zone">
                                                        <h6>R. by Zone</h6>
                                                    </label>
                                                </div>
                                            </div>
                                            
                                        </div>
                                        <div class="col-md-6 d-flex flex-column justify-content-start align-items-end"> {{-- Bagian kanan untuk tombol-tombol --}}
                                            <div class="btn-group-horizontal" role="group" aria-label="Tabulasi Actions">
                                                <button type="button" class="btn btn-primary mb-2" onclick="submitTabulasiForm('sync_register')">
                                                    <i class="fa fa-sync"></i> Sync
                                                </button>
                                                <button type="button" class="btn btn-danger mb-2" onclick="saveConfirmation('{{ route('superuser.report.customer_type_brand.removeDt') }}')">
                                                    <i class="fa fa-trash"></i> Remove
                                                </button>
                                                <button type="button" class="btn btn-success mb-2" onclick="submitTabulasiForm('export_register_pdf')">
                                                    <i class="fa fa-file-pdf-o" aria-hidden="true"></i> Export
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Hidden inputs for start_date and end_date for all tabulasi actions --}}
                                    <input type="hidden" name="start_date_tabulasi" id="period_from_tabulasi_hidden">
                                    <input type="hidden" name="end_date_tabulasi" id="period_to_tabulasi_hidden">
                                    <input type="hidden" name="action_type_tabulasi" id="action_type_tabulasi_hidden">
                                </form>
                            </section>
                            
                            <section id="content3" class="tab-content">
                                <h3>Konten untuk Forecasting Principle</h3>
                                <p>Ini adalah area untuk konten "Forecasting Principle".</p>
                                <p>Isi konten yang relevan untuk tab ini.</p>
                                <ul>
                                    <li>Prinsip 1: Data historis</li>
                                    <li>Prinsip 2: Analisis tren</li>
                                    <li>Prinsip 3: Faktor musiman</li>
                                </ul>
                            </section>
                        </main>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<style>
    /* Styling untuk label tab agar terlihat seperti tombol */
    .tab-label {
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

    .tab-label:hover {
        background-color: #e0e0e0;
    }

    /* Styling untuk label tab yang aktif */
    .tab-label.active-tab-label {
        background-color: #fff;
        border-color: #ccc;
        border-bottom: 1px solid #fff;
        color: #000;
    }

    /* Sembunyikan semua konten tab secara default */
    .tab-content {
        display: none;
        border: 1px solid #ccc;
        padding: 20px;
        border-radius: 0 5px 5px 5px;
        background-color: #fff;
    }

    /* Tampilkan konten tab yang aktif */
    .tab-content.active {
        display: block;
    }
</style>

<script>
// Fungsi untuk memperbarui hidden input tanggal pada form Tabulasi
function applyTabulasiMonthYearToForm() {
    var selectedMonthYear = $('#tabulasi-month-year').val(); // Format: YYYY-MM

    if (selectedMonthYear) {
        var year = parseInt(selectedMonthYear.substring(0, 4));
        var month = parseInt(selectedMonthYear.substring(5, 7)); // Bulan dari input (1-12)

        var startDate = selectedMonthYear + '-01';
        var lastDay = new Date(year, month, 0).getDate(); 
        var endDate = selectedMonthYear + '-' + (lastDay < 10 ? '0' : '') + lastDay; 

        $('#period_from_tabulasi_hidden').val(startDate);
        $('#period_to_tabulasi_hidden').val(endDate);
    }
}

// Fungsi untuk submit form Tabulasi dengan aksi yang berbeda
function submitTabulasiForm(actionType) {
    let form = $('#tabulasiForm'); 
    
    // Pastikan input tanggal tersembunyi di form sudah diperbarui
    applyTabulasiMonthYearToForm();

    // Set nilai hidden input action_type
    $('#action_type_tabulasi_hidden').val(actionType);

    // Atur action form berdasarkan actionType
    switch (actionType) {
        case 'sync_register':
            form.attr('action', "{{ route('superuser.report.customer_type_brand.postData') }}");
            form.removeAttr('target'); 
            break;
        case 'export_register_pdf':
            form.attr('action', "{{ route('superuser.report.customer_type_brand.print_report') }}");
            form.attr('target', '_blank'); 
            break;
        case 'upload_salesman':
            form.attr('action', "{{-- Ganti dengan route upload salesman Anda --}}"); 
            form.removeAttr('target');
            break;
        case 'export_salesman_excel':
            form.attr('action', "{{-- Ganti dengan route export excel salesman Anda --}}"); 
            form.removeAttr('target');
            break;
        default:
            // Default action jika tidak ada yang cocok
            form.removeAttr('action');
            form.removeAttr('target');
            console.warn('Unknown actionType:', actionType);
            return; // Hentikan proses jika actionType tidak dikenal
    }
    
    form.submit();
}

function saveConfirmation(url) {
    Swal.fire({
        title: 'Apakah Anda Yakin?',
        text: 'Tindakan ini tidak dapat dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}


$(document).ready(function () {
    // Inisialisasi Select2
    $('.js-select2').select2();

    $('#reset-month-filter').on('click', function() {
        // Redirect tanpa parameter bulan dan tahun, sehingga akan kembali ke default bulan berjalan
        window.location.href = "{{ route('superuser.index') }}";
    });

    // --- Inisialisasi DataTable untuk tab "Omset" ---
    var filterTypeOmset = 'all'; 
    var datatableOmset = $('.datatableOmset').DataTable({
        "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api();

            var totalCash = 0;
            var totalTempo = 0;

            api.rows({ filter: 'applied' }).every(function(){
                var node = this.node();
                var cashText = $(node).find('td:eq(5)').text().replace(/[.,]/g, '');
                var tempoText = $(node).find('td:eq(6)').text().replace(/[.,]/g, '');
                var cash = parseInt(cashText) || 0;
                var tempo = parseInt(tempoText) || 0;
                totalCash += cash;
                totalTempo += tempo;
            });

            var subTotal = totalCash + totalTempo;

            function formatRupiah(angka) {
                return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            $('#totalInvoiceCash').html('Cash: ' + formatRupiah(totalCash));
            $('#totalInvoiceTempo').html('Tempo: ' + formatRupiah(totalTempo));
            $('#subTotal').html('Subtotal: ' + formatRupiah(subTotal));
        }
    });

    // Custom filter berdasarkan type_so untuk DataTable Omset
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'datatableOmset') {
            return true; 
        }
        var rowType = $(datatableOmset.row(dataIndex).node()).data('type-so');
        if (filterTypeOmset === 'all') {
            return true;
        }
        return rowType === filterTypeOmset;
    });

    // Event tombol filter untuk Omset
    $('.btn-filter-type-omset').on('click', function() {
        $('.btn-filter-type-omset').removeClass('active');
        $(this).addClass('active');
        filterTypeOmset = $(this).data('type');
        datatableOmset.draw(); 
    });

    // --- Fungsionalitas Tab Toggle Umum (Omset, Tabulasi, Forecasting) ---

    // Sembunyikan semua konten tab kecuali yang pertama saat halaman dimuat
    $('.tab-content').not('.active').hide();
    // Berikan kelas aktif pada label tab pertama saat dimuat
    $('label[data-tab="content1"]').addClass('active-tab-label');

    // Tambahkan event listener untuk label tab utama
    $('.tab-label').on('click', function() {
        // Hapus kelas 'active' dari semua konten tab dan sembunyikan
        $('.tab-content').removeClass('active').hide();

        // Hapus kelas 'active-tab-label' dari semua label tab
        $('.tab-label').removeClass('active-tab-label');

        // Dapatkan ID konten yang akan ditampilkan dari atribut data-tab
        var targetTabId = $(this).data('tab');

        // Tambahkan kelas 'active' ke konten yang sesuai dan tampilkan
        $('#' + targetTabId).addClass('active').show();

        // Tambahkan kelas 'active-tab-label' ke label yang baru diklik
        $(this).addClass('active-tab-label');

        // Refresh DataTable jika tabnya diaktifkan
        if (targetTabId === 'content1') {
            setTimeout(function() {
                datatableOmset.columns.adjust().draw();
            }, 10);
        } else if (targetTabId === 'content2') {
            // Ketika tabulasi aktif, pastikan picker tanggalnya terisi dengan yang sedang aktif di URL
            // atau tanggal saat ini jika tidak ada di URL
            var urlParams = new URLSearchParams(window.location.search);
            var urlMonth = urlParams.get('month');
            var urlYear = urlParams.get('year');
            
            var currentMonthYear = '{{ $selectedMonthYear }}'; 
            if (urlMonth && urlYear) {
                currentMonthYear = urlYear + '-' + (urlMonth < 10 ? '0' : '') + urlMonth;
            }
            $('#tabulasi-month-year').val(currentMonthYear);
            applyTabulasiMonthYearToForm(); // Pastikan hidden inputs diperbarui
        }
    });

    // --- Handler untuk Omset Month/Year Picker (Otomatis) ---
    $('#omset-month-year').on('change', function() {
        var selectedMonthYear = $(this).val(); // Format YYYY-MM
        if (selectedMonthYear) {
            var year = selectedMonthYear.substring(0, 4);
            var month = selectedMonthYear.substring(5, 7);
            
            // Redirect ke URL dashboard dengan parameter bulan dan tahun baru
            window.location.href = "{{ route('superuser.index') }}" + "?month=" + month + "&year=" + year;
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Input Dibutuhkan',
                text: 'Harap pilih bulan dan tahun untuk menerapkan filter omset.',
                confirmButtonText: 'Oke'
            });
        }
    });

    // --- Handler untuk Tabulasi Month/Year Picker ---
    // Panggil ini saat halaman dimuat untuk memastikan nilai awal terisi
    applyTabulasiMonthYearToForm();

    $('#tabulasi-month-year').on('change', function() {
        applyTabulasiMonthYearToForm();
        Swal.fire({
            icon: 'success',
            title: 'Periode Diterapkan! ✅',
            text: 'Periode bulan dan tahun telah diperbarui untuk semua aksi di Tabulasi.',
            showConfirmButton: false,
            timer: 1500
        });
    });

}); // End of document ready
</script>
@endpush