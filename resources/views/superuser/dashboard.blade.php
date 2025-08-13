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
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex flex-column gap-3"> {{-- Kontainer utama untuk menumpuk baris --}}

                                                        <div class="d-flex justify-content-start align-items-center gap-3"> {{-- Menggunakan justify-content-start untuk menjaga di kiri --}}
                                                            {{-- Tombol Aksi --}}
                                                            
                                                        </div>

                                                        {{-- Baris 1: Periode & Salesman --}}
                                                        <div class="d-flex justify-content-start align-items-center gap-3"> {{-- Menggunakan justify-content-start untuk menjaga di kiri --}}
                                                            {{-- Periode Laporan --}}
                                                            <div>
                                                                <label for="tabulasi-month-year" class="form-label visually-hidden">Periode:</label>
                                                                <input type="month" id="tabulasi-month-year" class="form-control form-control-sm" value="{{ $selectedMonthYear }}" style="width: 130px;">
                                                            </div>

                                                            <div>
                                                                <label for="report_type_tabulasi" class="form-label visually-hidden">Tipe Laporan:</label>
                                                                <select class="form-control form-control-sm js-select2" name="report_type_tabulasi" id="report_type_tabulasi" style="min-width: 180px;">
                                                                    <option value="brand">R. by Brand</option>
                                                                    <option value="zone">R. by Zone</option>
                                                                    <option value="salesman">R. by Salesman</option> {{-- Opsi baru: Salesman --}}
                                                                </select>
                                                            </div>

                                                            {{-- Salesman --}}
                                                            <div>
                                                                <label for="salesman_id_tabulasi" class="form-label visually-hidden">Salesman:</label>
                                                                {{-- Ubah name dari salesman_id_tabulasi menjadi salesman_officer[] agar sesuai dengan parameter controller ReportEmployeePerformanceController --}}
                                                                <select class="form-control form-control-sm js-select2" name="salesman_officer[]" id="salesman_id_tabulasi" style="min-width: 180px;" disabled> {{-- Awalnya disabled --}}
                                                                    <option value="">Semua Salesman</option>
                                                                    {{-- Tambahkan opsi salesman lain dari database jika ada --}}
                                                                    {{-- Contoh: @foreach($salesmen as $salesman) <option value="{{ $salesman->officer }}">{{ $salesman->officer }}</option> @endforeach --}}
                                                                    <option value="Erick">Erick</option>
                                                                    <option value="Lindy">Lindy</option>
                                                                    <option value="Kumala">Kumala</option>
                                                                </select>
                                                            </div>

                                                            <div class="btn-group" role="group" aria-label="Tabulasi Actions">
                                                                <button type="button" class="btn btn-primary btn-sm" onclick="submitTabulasiForm('sync_register')">
                                                                    <i class="fa fa-sync"></i> Sync
                                                                </button>
                                                                <button type="button" class="btn btn-danger btn-sm" onclick="saveConfirmation('{{ route('superuser.report.customer_type_brand.removeDt') }}')">
                                                                    <i class="fa fa-trash"></i> Hapus
                                                                </button>
                                                                <button type="button" class="btn btn-success btn-sm" onclick="submitTabulasiForm('export_register_pdf')">
                                                                    <i class="fa fa-file-pdf-o" aria-hidden="true"></i> Export
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            {{-- Hidden inputs untuk parameter yang dibutuhkan controller --}}
                                            {{-- Untuk ReportCustomerTypeBrandController --}}
                                            <input type="hidden" name="start" id="tabulasi_start_date">
                                            <input type="hidden" name="end" id="tabulasi_end_date">
                                            {{-- Untuk ReportEmployeePerformanceController --}}
                                            <input type="hidden" name="period_from" id="tabulasi_period_from">
                                            <input type="hidden" name="period_to" id="tabulasi_period_to">
                                            
                                            <input type="hidden" name="type" id="tabulasi_report_type_param">
                                            <input type="hidden" name="nominal" id="tabulasi_nominal_param">
                                            <input type="hidden" name="action" id="tabulasi_action_param"> {{-- Untuk ReportCustomerTypeBrandController@exportReport --}}
                                            <input type="hidden" name="action_type_tabulasi" id="action_type_tabulasi_hidden"> {{-- Pertahankan untuk sync_register --}}
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-4"> {{-- Tambahkan margin top agar tidak terlalu dekat --}}
                                        <div class="col-12">
                                            <iframe src="" type="application/pdf" id="iframePdf" style="width: 100%; height: 800px; border: 1px solid #ddd;">
                                                <p>Browser Anda tidak mendukung iframe atau tidak dapat menampilkan file PDF secara langsung. Silakan <a href="#" id="pdfDownloadLink">klik di sini untuk mengunduh PDF</a>.</p>
                                            </iframe>
                                        </div>
                                    </div>
                                </form>
                            </section>
                            
                            {{-- MODIFIKASI DIMULAI DI SINI --}}
                            <section id="content3" class="tab-content">
                                <form id="forecastingForm" method="POST" target="_blank"> {{-- Tambahkan form dan target="_blank" untuk PDF --}}
                                    @csrf
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex flex-column gap-3"> {{-- Kontainer utama untuk menumpuk baris --}}

                                                        {{-- Baris 1: Vendor & Periode --}}
                                                        <div class="d-flex justify-content-start align-items-center gap-3">
                                                            {{-- Select Vendor --}}
                                                            <div>
                                                                <label for="vendor_name_forecast" class="form-label visually-hidden">Vendor:</label>
                                                                <select class="form-control form-control-sm js-select2" name="vendor_name" id="vendor_name_forecast" style="min-width: 180px;">
                                                                    <option value="">Select Vendor</option>
                                                                    @foreach($vendor AS $row)
                                                                    <option value="{{$row->name}}">{{$row->name}}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            {{-- Period From (Month & Year) --}}
                                                            <div>
                                                                <label for="period_from_forecast" class="form-label visually-hidden">Dari Bulan & Tahun:</label>
                                                                <input type="month" name="period_from" id="period_from_forecast" class="form-control form-control-sm" style="width: 150px;">
                                                            </div>

                                                            {{-- Period To (Month & Year) --}}
                                                            <div>
                                                                <label for="period_to_forecast" class="form-label visually-hidden">Sampai Bulan & Tahun:</label>
                                                                <input type="month" name="period_to" id="period_to_forecast" class="form-control form-control-sm" style="width: 150px;">
                                                            </div>

                                                            {{-- Tombol Aksi --}}
                                                            <div class="btn-group" role="group" aria-label="Forecasting Actions">
                                                                
                                                                <button type="button" id="printReportForecast" class="btn btn-success btn-sm" onclick="submitForecastingForm('print_pdf')">
                                                                    <i class="fa fa-file-pdf-o" aria-hidden="true"></i> Export
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- Hidden inputs untuk parameter yang dibutuhkan controller --}}
                                            <input type="hidden" name="action_forecast" id="action_forecast_param">
                                        </div>
                                    </div>
                                </form>
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <iframe src="" type="application/pdf" id="iframeForecastPdf" style="width: 100%; height: 800px; border: 1px solid #ddd;">
                                            <p>Browser Anda tidak mendukung iframe atau tidak dapat menampilkan file PDF secara langsung. Silakan <a href="#" id="forecastPdfDownloadLink">klik di sini untuk mengunduh PDF</a>.</p>
                                        </iframe>
                                    </div>
                                </div>
                            </section>
                            {{-- MODIFIKASI BERAKHIR DI SINI --}}
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

    // Bersihkan semua hidden input tanggal terlebih dahulu
    $('#tabulasi_start_date').val('');
    $('#tabulasi_end_date').val('');
    $('#tabulasi_period_from').val('');
    $('#tabulasi_period_to').val('');

    if (selectedMonthYear) {
        var year = parseInt(selectedMonthYear.substring(0, 4));
        var month = parseInt(selectedMonthYear.substring(5, 7)); // Bulan dari input (1-12)
        
        var startDate = selectedMonthYear + '-01';
        // Mengambil hari terakhir bulan
        var lastDay = new Date(year, month, 0).getDate(); 
        var endDate = selectedMonthYear + '-' + (lastDay < 10 ? '0' : '') + lastDay; 

        // Isi hidden input untuk CustomerTypeBrandController
        $('#tabulasi_start_date').val(startDate);
        $('#tabulasi_end_date').val(endDate);

        // Isi hidden input untuk ReportEmployeePerformanceController
        $('#tabulasi_period_from').val(startDate);
        $('#tabulasi_period_to').val(endDate);
    }
}

// Fungsi untuk submit form Tabulasi dengan aksi yang berbeda
// Fungsi submitTabulasiForm diubah untuk menggunakan AJAX ketika mengekspor PDF
function submitTabulasiForm(actionType) {
    let form = $('#tabulasiForm');
    applyTabulasiMonthYearToForm(); // Pastikan input tanggal tersembunyi di form sudah diperbarui

    const selectedSalesman = $('#salesman_id_tabulasi').val();
    const selectedReportType = $('#report_type_tabulasi').val();
    const selectedMonthYear = $('#tabulasi-month-year').val();

    if (!selectedMonthYear) {
        Swal.fire({
            icon: 'error',
            title: 'Validasi Gagal',
            text: 'Periode Laporan harus diisi.',
            confirmButtonText: 'Oke'
        });
        return;
    }

    // Bersihkan nilai parameter spesifik laporan yang mungkin tidak relevan untuk aksi saat ini
    $('#tabulasi_report_type_param').val('');
    $('#tabulasi_nominal_param').val('');
    $('#tabulasi_action_param').val('');
    $('#action_type_tabulasi_hidden').val(actionType);

    let url = '';
    let method = 'POST';
    let formData = new FormData(form[0]); // Gunakan FormData untuk mengirim data form, termasuk file jika ada

    if (actionType === 'export_register_pdf') {
        $('#tabulasi_nominal_param').val(1); // Default: Dengan Nominal
        formData.append('nominal', 1); // Pastikan nominal terkirim

        if (selectedReportType === 'brand') {
            url = "{{ route('superuser.report.customer_type_brand.exportReport') }}";
            formData.append('type', 1); // Type 1 untuk Brand
            formData.append('action', 'print'); // Untuk exportReport agar menghasilkan PDF
        } else if (selectedReportType === 'zone') {
            if (selectedSalesman && selectedSalesman !== '') {
                Swal.fire({
                    icon: 'error',
                    title: 'Tidak Diizinkan',
                    text: 'Export "R. by Zone" tidak dapat dilakukan jika salesman dipilih.',
                    confirmButtonText: 'Oke'
                });
                return;
            }
            url = "{{ route('superuser.report.customer_type_brand.exportReport') }}";
            formData.append('type', 2); // Type 2 untuk Zone
            formData.append('action', 'print');
        } else if (selectedReportType === 'salesman') {
            url = "{{ route('superuser.report.employee_performance.print_report') }}";
            formData.append('type', 2); // Type 2 untuk Officer/Salesman
            // salesman_officer[] sudah otomatis terkirim dari form data
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Aksi Tidak Valid',
                text: 'Tipe laporan tidak dikenali untuk ekspor.',
                confirmButtonText: 'Oke'
            });
            return;
        }

        // Kirim permintaan AJAX untuk mendapatkan URL PDF
        $.ajax({
            url: url,
            type: method,
            data: formData,
            processData: false, // Penting: Jangan proses data (FormData akan mengurusnya)
            contentType: false, // Penting: Jangan set content type (FormData akan mengurusnya)
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Pastikan CSRF token terkirim
            },
            beforeSend: function() {
                Swal.fire({
                    title: 'Membuat Laporan...',
                    text: 'Mohon tunggu, laporan sedang dibuat.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response) {
                Swal.close();
                if (response.success && response.pdf_url) {
                    $('#iframePdf').attr('src', response.pdf_url);
                    $('#pdfDownloadLink').attr('href', response.pdf_url); // Update download link
                    Swal.fire({
                        icon: 'success',
                        title: 'Laporan Berhasil Dibuat! 🎉',
                        text: 'PDF telah dimuat di iframe.',
                        showConfirmButton: false,
                        timer: 2000
                    });
                } else if (response.error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Membuat Laporan',
                        text: response.error,
                        confirmButtonText: 'Oke'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan tidak dikenal.',
                        confirmButtonText: 'Oke'
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.close();
                let errorMessage = 'Terjadi kesalahan saat membuat laporan.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (error) {
                    errorMessage += ': ' + error;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage,
                    confirmButtonText: 'Oke'
                });
                console.error('AJAX Error:', status, error, xhr.responseText);
            }
        });

    } else if (actionType === 'sync_register') {
         // Dapatkan data dari form
            const periodFrom = form.find('input[name="period_from"]').val();
            const periodTo = form.find('input[name="period_to"]').val();
            // const vendorName = form.find('input[name="vendor_name"]').val(); <-- Dihapus sesuai permintaan.

            // Buat URL baru dengan parameter GET
            // Gunakan URL Helper dari Laravel untuk memastikan routing yang benar
            const baseUrl = "{{ route('superuser.report.customer_type_brand.postData') }}";

            // Buat objek URLSearchParams untuk menyusun parameter dengan aman
            const params = new URLSearchParams({
                period_from: periodFrom,
                period_to: periodTo
                // vendor_name: vendorName <-- Dihapus dari parameter
            });

            // Buat URL akhir
            const finalUrl = baseUrl + '?' + params.toString();

            // Arahkan browser ke URL baru
            window.location.href = finalUrl;
    } else {
        console.warn('Unknown actionType:', actionType);
        return;
    }
}

// === Fungsi untuk tab Forecasting Principle ===

// Fungsi untuk submit form Forecasting dengan aksi yang berbeda
function submitForecastingForm() {
        // Swal.fire for loading message
        Swal.fire({
            title: 'Membuat Laporan Forecasting...',
            html: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            onBeforeOpen: () => {
                Swal.showLoading();
            },
        });

        // Get values from the form
        let vendor_name = $('#vendor_name_forecast').val();
        let period_from = $('#period_from_forecast').val(); // YYYY-MM
        let period_to = $('#period_to_forecast').val();     // YYYY-MM

        // Construct full date strings for the controller
        // Period From: YYYY-MM-01
        let formatted_period_from = period_from ? period_from + '-01' : '';

        // Period To: YYYY-MM-LastDayOfMonth
        let formatted_period_to = '';
        if (period_to) {
            let parts = period_to.split('-');
            let year = parseInt(parts[0]);
            let month = parseInt(parts[1]);
            let lastDay = new Date(year, month, 0).getDate(); // Get last day of the month
            formatted_period_to = `${period_to}-${lastDay}`;
        }
        
        // Validation check
        if (!vendor_name || !formatted_period_from || !formatted_period_to) {
            Swal.fire({
                icon: 'error',
                title: 'Input Tidak Lengkap!',
                text: 'Vendor, Bulan Awal, dan Bulan Akhir tidak boleh kosong.',
            });
            return; // Stop the function if validation fails
        }

        $.ajax({
            url: "{{ route('superuser.report.forecast_supplier.printReport') }}",
            type: "POST",
            data: {
                vendor_name: vendor_name,
                period_from: formatted_period_from,
                period_to: formatted_period_to,
                _token: "{{ csrf_token() }}" // Laravel CSRF token
            },
            success: function(response) {
                Swal.close(); // Close loading message
                if (response.success) {
                    $('#iframeForecastPdf').attr('src', response.pdf_url);
                    $('#downloadForecastPdf').attr('href', response.pdf_url).show();
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Laporan berhasil dibuat.',
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: response.error || 'Terjadi kesalahan saat membuat laporan.',
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.close(); // Close loading message
                let errorMessage = 'Terjadi kesalahan tidak terduga.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                } else if (error) {
                    errorMessage = error;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMessage,
                });
            }
        });
    }

    // Bind the form submission to the new function
    $('#forecastingForm').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission
        submitForecastingForm();
    });

// Fungsi dummy untuk tombol sync di tab Forecasting Principle
function syncForecastingData() {
    Swal.fire({
        icon: 'info',
        title: 'Fungsi Sync',
        text: 'Fungsi sync untuk Forecasting Principle belum diimplementasikan.',
        confirmButtonText: 'Oke'
    });
}

// Fungsi dummy untuk tombol hapus di tab Forecasting Principle
function deleteForecastingData() {
    Swal.fire({
        icon: 'info',
        title: 'Fungsi Hapus',
        text: 'Fungsi hapus untuk Forecasting Principle belum diimplementasikan.',
        confirmButtonText: 'Oke'
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
    // Berikan kelas aktif pada label tab pertama
    $('label[for="tab1"]').addClass('active-tab-label');

    // Tangani perubahan tab
    $('input[name="tabs"]').on('change', function() {
        var tabId = $(this).attr('id');
        var contentId = $('label[for="' + tabId + '"]').data('tab');

        // Hapus kelas aktif dari semua label dan sembunyikan semua konten
        $('.tab-label').removeClass('active-tab-label');
        $('.tab-content').removeClass('active').hide();

        // Tambahkan kelas aktif ke label yang dipilih dan tampilkan konten yang sesuai
        $('label[for="' + tabId + '"]').addClass('active-tab-label');
        $('#' + contentId).addClass('active').show();
    });

    // Event listener untuk perubahan month-year picker di tab Omset
    $('#omset-month-year').on('change', function() {
        var selectedDate = $(this).val(); // Format: YYYY-MM
        if (selectedDate) {
            // Redirect ke halaman yang sama dengan parameter bulan dan tahun
            window.location.href = "{{ route('superuser.index') }}" + "?month_year=" + selectedDate;
        } else {
            // Jika dikosongkan, kembali ke default (bulan saat ini)
            window.location.href = "{{ route('superuser.index') }}";
        }
    });

    // Event listener untuk perubahan month-year picker di tab Tabulasi
    $('#tabulasi-month-year').on('change', function() {
        // Saat picker berubah, panggil fungsi untuk memperbarui hidden inputs
        applyTabulasiMonthYearToForm();
    });

    // Panggil fungsi ini saat halaman pertama kali dimuat untuk menginisialisasi nilai hidden inputs
    // Ini penting jika Anda ingin nilai default atau nilai dari $selectedMonthYear diterapkan saat tab Tabulasi pertama kali aktif
    applyTabulasiMonthYearToForm();

    // Logika untuk mengaktifkan/menonaktifkan dropdown Salesman berdasarkan pilihan Report Type di Tabulasi
    $('#report_type_tabulasi').on('change', function() {
        const selectedType = $(this).val();
        if (selectedType === 'salesman') {
            $('#salesman_id_tabulasi').prop('disabled', false);
        } else {
            $('#salesman_id_tabulasi').val('').trigger('change'); // Reset dan nonaktifkan
            $('#salesman_id_tabulasi').prop('disabled', true);
        }
    });

    // Trigger change event saat inisialisasi untuk memastikan state awal yang benar
    $('#report_type_tabulasi').trigger('change');
});
</script>
@endpush