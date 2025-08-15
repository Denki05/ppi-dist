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
                            <label style="padding: 5px 10px; cursor: pointer;" for="tab1" class="tab-label" data-tab="content1">Omset</label>

                            <input style="display: none;" id="tab2" type="radio" name="tabs">
                            <label style="padding: 5px 10px; cursor: pointer;" for="tab2" class="tab-label" data-tab="content2">Tabulasi</label>
                            
                            <input style="display: none;" id="tab3" type="radio" name="tabs">
                            <label style="padding: 5px 10px; cursor: pointer;" for="tab3" class="tab-label" data-tab="content3">Forecasting Principle</label>

                            <section id="content1" class="tab-content active">
                                <input type="hidden" class="form-control" id="default_month" name="default_month" value="{{ $selectedMonthYear }}">
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
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex flex-column">
                                                <div class="d-flex justify-content-between align-items-center mb-4">
                                                    {{-- Group input --}}
                                                    <div class="d-flex align-items-center me-3">
                                                        <div>
                                                            <label class="form-label visually-hidden">Periode:</label>
                                                            <div class="d-flex align-items-center">
                                                                {{-- Radio button dan Dropdown Bulan --}}
                                                                <div class="form-check me-2">
                                                                    <input class="form-check-input" type="radio" name="period_filter_type" id="filterByMonth" value="month" checked>
                                                                        <select class="form-control form-control-sm js-select2 me-3" id="tabulasi-month-select" style="width: 130px;">
                                                                        @php
                                                                            $months = [
                                                                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                                                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                                                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                                                            ];
                                                                            $currentMonth = date('n'); // Bulan saat ini (1-12)
                                                                        @endphp
                                                                        @foreach($months as $num => $name)
                                                                            <option value="{{ sprintf('%02d', $num) }}" {{ $num == $currentMonth ? 'selected' : '' }}>{{ $name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                

                                                                {{-- Radio button dan Dropdown Tahun --}}
                                                                <div class="form-check me-2">
                                                                    <input class="form-check-input" type="radio" name="period_filter_type" id="filterByYear" value="year">
                                                                    <select class="form-control form-control-sm js-select2" id="tabulasi-year-select" style="width: 130px;">
                                                                        @php
                                                                            $currentYear = date('Y');
                                                                            // Misalnya, tampilkan tahun 5 tahun ke belakang dan 5 tahun ke depan
                                                                            for ($year = $currentYear - 5; $year <= $currentYear + 5; $year++) {
                                                                                echo "<option value='{$year}'" . ($year == $currentYear ? ' selected' : '') . ">{$year}</option>";
                                                                            }
                                                                        @endphp
                                                                    </select>
                                                                </div>
                                                                
                                                            </div>
                                                        </div>
                                                        <div class="ms-3">
                                                            <label for="report_type_tabulasi" class="form-label visually-hidden">Tipe Laporan:</label>
                                                            <select class="form-control form-control-sm js-select2" name="report_type_tabulasi" id="report_type_tabulasi" style="min-width: 180px;">
                                                                <option value="brand">R. by Brand</option>
                                                                <option value="zone">R. by Zone</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    {{-- Group tombol --}}
                                                    <div class="btn-group" role="group" aria-label="Filter Omset">
                                                        <button type="button" class="btn btn-primary btn-sm me-2" onclick="submitTabulasiForm('sync_register')">
                                                            <i class="fa fa-sync"></i> Sync
                                                        </button>
                                                        <button type="button" class="btn btn-danger btn-sm me-2" onclick="saveConfirmation('{{ route('superuser.report.customer_type_brand.removeDt') }}')">
                                                            <i class="fa fa-trash"></i> Hapus
                                                        </button>
                                                        <button type="button" class="btn btn-success btn-sm" onclick="submitTabulasiForm('export_register_pdf')">
                                                            <i class="fa fa-file-pdf-o" aria-hidden="true"></i> Export
                                                        </button>
                                                    </div>
                                                </div>

                                                {{-- Bagian Iframe --}}
                                                <iframe src="" type="application/pdf" id="iframePdf" style="width: 100%; height: 800px; border: 1px solid #ddd;">
                                                    <p>Browser Anda tidak mendukung iframe atau tidak dapat menampilkan file PDF secara langsung. Silakan <a href="#" id="pdfDownloadLink">klik di sini untuk mengunduh PDF</a>.</p>
                                                </iframe>

                                            </div>
                                        </div>
                                    </div>

                                    {{-- Hidden inputs --}}
                                    <input type="hidden" name="start" id="tabulasi_start_date">
                                    <input type="hidden" name="end" id="tabulasi_end_date">
                                    <input type="hidden" name="period_from" id="tabulasi_period_from">
                                    <input type="hidden" name="period_to" id="tabulasi_period_to">
                                    <input type="hidden" name="type" id="tabulasi_report_type_param">
                                    <input type="hidden" name="nominal" id="tabulasi_nominal_param">
                                    <input type="hidden" name="action" id="tabulasi_action_param">
                                    <input type="hidden" name="action_type_tabulasi" id="action_type_tabulasi_hidden">
                                </form>
                            </section>
                            
                            <section id="content3" class="tab-content">
                                <form id="forecastingForm" method="POST" target="_blank">
                                    @csrf
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-start align-items-center mb-4">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div>
                                                                <label for="vendor_name_forecast" class="form-label visually-hidden">Vendor:</label>
                                                                <select class="form-control form-control-sm js-select2" name="vendor_name" id="vendor_name_forecast" style="min-width: 280px;">
                                                                    <option value="">Select Vendor</option>
                                                                    @foreach($vendor AS $row)
                                                                    <option value="{{$row->name}}">{{$row->name}}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <div>
                                                                <label for="period_from_forecast" class="form-label visually-hidden">Dari Bulan & Tahun:</label>
                                                                 <input type="month" name="period_from" id="period_from_forecast" class="form-control form-control-sm" style="width: 150px;" value="{{ $selectedMonthYearFirst }}">
                                                            </div>

                                                            <div>
                                                                <label for="period_to_forecast" class="form-label visually-hidden">Sampai Bulan & Tahun:</label>
                                                                <input type="month" name="period_to" id="period_to_forecast" class="form-control form-control-sm" style="width: 150px;" value="{{ $selectedMonthYear }}">
                                                            </div>

                                                            <div>
                                                                <label for="semester_count_forecast" class="form-label visually-hidden">Select Semester:</label>
                                                                <select class="form-control form-control-sm js-select2" name="semester_count" id="semester_count_forecast" style="min-width: 280px;">
                                                                    <option value="">Pilih Semester</option>
                                                                    <option value="1">1 Semester</option>
                                                                    <option value="2">2 Semester</option>
                                                                    <option value="3">3 Semester</option>
                                                                    <option value="4">4 Semester</option>
                                                                    <option value="6">6 Semester</option>
                                                                </select>
                                                            </div>

                                                            <div>
                                                                <button type="button" id="printReportForecast" class="btn btn-success btn-sm" onclick="submitForecastingForm()">
                                                                    <i class="fa fa-file-pdf-o" aria-hidden="true"></i> Export
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <!-- <div class="ms-auto">
                                                            <div class="btn-group" role="group" aria-label="Forecasting Actions">
                                                                
                                                            </div>
                                                        </div> -->
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="action_forecast" id="action_forecast_param">
                                </form>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <iframe src="" type="application/pdf" id="iframeForecastPdf" style="width: 100%; height: 800px; border: 1px solid #ddd;">
                                            <p>Browser Anda tidak mendukung iframe atau tidak dapat menampilkan file PDF secara langsung. Silakan <a href="#" id="forecastPdfDownloadLink">klik di sini untuk mengunduh PDF</a>.</p>
                                        </iframe>
                                    </div>
                                </div>
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

    .tab-label.active-tab-label {
        background-color: #fff;
        border-color: #ccc;
        border-bottom: 1px solid #fff;
        color: #000;
    }

    .tab-content {
        display: none;
        border: 1px solid #ccc;
        padding: 20px;
        border-radius: 0 5px 5px 5px;
        background-color: #fff;
    }

    .tab-content.active {
        display: block;
    }
</style>

<script>
    function applyTabulasiMonthYearToForm() {
        let startDate = '';
        let endDate = '';

        const filterType = $('input[name="period_filter_type"]:checked').val();
        // Pastikan ada nilai default jika dropdown belum terpilih atau tidak ada value
        const selectedMonth = $('#tabulasi-month-select').val() || '{{ sprintf('%02d', date('n')) }}'; // Default ke bulan saat ini
        const selectedYear = $('#tabulasi-year-select').val() || '{{ date('Y') }}';   // Default ke tahun saat ini

        if (filterType === 'month') {
            const year = parseInt(selectedYear);
            const month = parseInt(selectedMonth);

            startDate = `${year}-${selectedMonth}-01`;
            const lastDay = new Date(year, month, 0).getDate();
            endDate = `${year}-${selectedMonth}-${lastDay}`;
        } else if (filterType === 'year') {
            const year = parseInt(selectedYear);
            startDate = `${year}-01-01`;
            endDate = `${year}-12-31`;
        }

        // Hapus baris-baris ini, karena hanya akan menyebabkan input kosong sesaat.
        // $('#tabulasi_start_date').val('');
        // $('#tabulasi_end_date').val('');
        // $('#tabulasi_period_from').val('');
        // $('#tabulasi_period_to').val('');

        $('#tabulasi_start_date').val(startDate);
        $('#tabulasi_end_date').val(endDate);
        $('#tabulasi_period_from').val(startDate);
        $('#tabulasi_period_to').val(endDate);
    }

    function submitTabulasiForm(actionType) {
        let form = $('#tabulasiForm');
        applyTabulasiMonthYearToForm(); 

        const selectedSalesman = $('#salesman_id_tabulasi').val();
        const selectedReportType = $('#report_type_tabulasi').val();
        const startDate = $('#tabulasi_start_date').val();
        const endDate = $('#tabulasi_end_date').val();

        if (!startDate || !endDate) {
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                text: 'Periode Laporan harus diisi. Pilih Bulan atau Tahun.',
                confirmButtonText: 'Oke'
            });
            return;
        }

        $('#tabulasi_report_type_param').val('');
        $('#tabulasi_nominal_param').val('');
        $('#tabulasi_action_param').val('');
        $('#action_type_tabulasi_hidden').val(actionType);

        let url = '';
        let method = 'POST';
        let formData = new FormData(form[0]); 

        if (actionType === 'export_register_pdf') {
            $('#tabulasi_nominal_param').val(1); 
            formData.append('nominal', 1); 

            if (selectedReportType === 'brand') {
                url = "{{ route('superuser.report.customer_type_brand.exportReport') }}";
                formData.append('type', 1); 
                formData.append('action', 'print'); 
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
                formData.append('type', 2); 
                formData.append('action', 'print');
            } else if (selectedReportType === 'salesman') {
                url = "";
                formData.append('type', 2); 
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Aksi Tidak Valid',
                    text: 'Tipe laporan tidak dikenali untuk ekspor.',
                    confirmButtonText: 'Oke'
                });
                return;
            }

            $.ajax({
                url: url,
                type: method,
                data: formData,
                processData: false, 
                contentType: false, 
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') 
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
                        $('#pdfDownloadLink').attr('href', response.pdf_url); 
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
            let startDate = '';
            let endDate = '';

            const selectedMonth = $('#tabulasi-month-select').val() || '{{ sprintf('%02d', date('n')) }}'; // Default ke bulan saat ini
            const selectedYear = $('#tabulasi-year-select').val() || '{{ date('Y') }}';   // Default ke tahun saat ini

            const year = parseInt(selectedYear);
            const month = parseInt(selectedMonth);

            startDate = `${year}-${selectedMonth}-01`;
            const lastDay = new Date(year, month, 0).getDate();
            endDate = `${year}-${selectedMonth}-${lastDay}`;

            const periodFrom = startDate
            const periodTo = endDate

            // Tambahkan validasi di sini sebelum redirect
            if (!periodFrom || !periodTo) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: 'Periode Sync (Dari Bulan & Sampai Bulan) tidak boleh kosong.',
                    confirmButtonText: 'Oke'
                });
                return; // Hentikan proses jika periode kosong
            }

            const baseUrl = "{{ route('superuser.report.customer_type_brand.postData') }}";

            const params = new URLSearchParams({
                period_from: periodFrom,
                period_to: periodTo
            });

            const finalUrl = baseUrl + '?' + params.toString();

            window.location.href = finalUrl;
        } else {
            console.warn('Unknown actionType:', actionType);
            return;
        }
    }

    // UPDATED FUNCTION: submitForecastingForm
    function submitForecastingForm() {
        Swal.fire({
            title: 'Membuat Laporan Forecasting...',
            html: 'Mohon tunggu sebentar',
            allowOutsideClick: false,
            onBeforeOpen: () => {
                Swal.showLoading();
            },
        });

        let vendor_name = $('#vendor_name_forecast').val();
        // semester_count tetap diambil, namun tidak digunakan untuk kalkulasi tanggal di frontend
        let semester_count = $('#semester_count_forecast').val(); 
        let period_from_input = $('#period_from_forecast').val();
        let period_to_input = $('#period_to_forecast').val();

        let formatted_period_from = '';
        let formatted_period_to = '';

        // Prioritaskan input manual untuk periode
        if (!period_from_input || !period_to_input) {
            Swal.fire({
                icon: 'error',
                title: 'Input Tidak Lengkap!',
                text: 'Periode (Dari Bulan & Sampai Bulan) tidak boleh kosong.',
            });
            return;
        }

        // Jika input manual ada, gunakan itu
        formatted_period_from = period_from_input + '-01'; // Tambahkan -01 untuk tanggal awal bulan
        
        // Hitung hari terakhir dari bulan untuk period_to_input
        let parts = period_to_input.split('-');
        let year = parseInt(parts[0]);
        let month = parseInt(parts[1]);
        let lastDay = new Date(year, month, 0).getDate();
        formatted_period_to = `${period_to_input}-${lastDay}`;
        
        // Validasi Vendor
        if (!vendor_name) {
            Swal.fire({
                icon: 'error',
                title: 'Input Tidak Lengkap!',
                text: 'Vendor tidak boleh kosong.',
            });
            return;
        }

        $.ajax({
            url: "{{ route('superuser.report.forecast_supplier.printReport') }}",
            type: "POST",
            data: {
                vendor_name: vendor_name,
                period_from: formatted_period_from,
                period_to: formatted_period_to,
                semester_count: semester_count, // Tetap kirim semester_count (opsional di backend)
                _token: "{{ csrf_token() }}" 
            },
            success: function(response) {
                Swal.close(); 
                if (response.success) {
                    $('#iframeForecastPdf').attr('src', response.pdf_url);
                    $('#forecastPdfDownloadLink').attr('href', response.pdf_url).show();
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
                Swal.close(); 
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

    $('#forecastingForm').on('submit', function(e) {
        e.preventDefault(); 
        submitForecastingForm();
    });

    function syncForecastingData() {
        Swal.fire({
            icon: 'info',
            title: 'Fungsi Sync',
            text: 'Fungsi sync untuk Forecasting Principle belum diimplementasikan.',
            confirmButtonText: 'Oke'
        });
    }

    function deleteForecastingData() {
        Swal.fire({
            icon: 'info',
            title: 'Fungsi Hapus',
            text: 'Fungsi hapus untuk Forecasting Principle belum diimplementasikan.',
            confirmButtonText: 'Oke'
        });
    }

    $(document).ready(function () {
        $('.js-select2').select2();

        $('#reset-month-filter').on('click', function() {
            window.location.href = "{{ route('superuser.index') }}";
        });

        var filterTypeOmset = 'all'; 
        var datatableOmset = $('.datatableOmset').DataTable({
            info: false,
            dom: '<"row mb-2"<"col-sm-12 col-md-6 custom-filter-placeholder"><"col-sm-12 col-md-6"f>>' + // baris atas
                '<"row"<"col-sm-12"tr>>' + 
                '<"row"<"col-sm-12 col-md-6 d-flex align-items-center"l><"col-sm-12 col-md-6"p>>',  // baris bawah
            footerCallback: function (row, data, start, end, display) {
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
            },
            initComplete: function () {
                var selectedMonthYear = $('#default_month').val() || '';
                $('.custom-filter-placeholder').html(`
                    <div id="custom-filter-group" class="d-flex align-items-center flex-wrap">
                        <input type="month" id="omset-month-year" 
                            class="form-control form-control-sm me-2 mb-2" 
                            style="width: 150px;" 
                            value="${selectedMonthYear}">
                        <button class="btn btn-danger btn-sm me-2 mb-2" id="reset-month-filter">
                            <i class="fa fa-sync"></i>
                        </button>
                        <button class="btn btn-primary btn-sm btn-outline-primary btn-filter-type-omset active me-2 mb-2" data-type="all">ALL</button>
                        <button class="btn btn-success btn-sm btn-filter-type-omset me-2 mb-2" data-type="ppn">PPN</button>
                        <button class="btn btn-warning btn-sm btn-filter-type-omset mb-2" data-type="nonppn">NonPPN</button>
                    </div>
                `);

                $('#reset-month-filter').on('click', function() {
                    window.location.href = "{{ route('superuser.index') }}";
                });
            }
        });


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

        $('.btn-filter-type-omset').on('click', function() {
            $('.btn-filter-type-omset').removeClass('active');
            $(this).addClass('active');
            filterTypeOmset = $(this).data('type');
            datatableOmset.draw(); 
        });

        $('.tab-content').not('.active').hide();
        $('label[for="tab1"]').addClass('active-tab-label');

        $('input[name="tabs"]').on('change', function() {
            var tabId = $(this).attr('id');
            var contentId = $('label[for="' + tabId + '"]').data('tab');

            $('.tab-label').removeClass('active-tab-label');
            $('.tab-content').removeClass('active').hide();

            $('label[for="' + tabId + '"]').addClass('active-tab-label');
            $('#' + contentId).addClass('active').show();
        });

        $('#omset-month-year').on('change', function() {
            var selectedDate = $(this).val(); 
            if (selectedDate) {
                window.location.href = "{{ route('superuser.index') }}" + "?month_year=" + selectedDate;
            } else {
                window.location.href = "{{ route('superuser.index') }}";
            }
        });

        // Event listener untuk perubahan dropdown bulan atau tahun, atau radio button (Tabulasi)
        $('#tabulasi-month-select, #tabulasi-year-select, input[name="period_filter_type"]').on('change', function() {
            applyTabulasiMonthYearToForm();
        });

        // Logika untuk mengaktifkan/menonaktifkan dropdown Salesman berdasarkan pilihan Report Type di Tabulasi
        $('#report_type_tabulasi').on('change', function() {
            const selectedType = $(this).val();
            if (selectedType === 'salesman') {
                $('#salesman_id_tabulasi').prop('disabled', false);
            } else {
                $('#salesman_id_tabulasi').val('').trigger('change'); 
                $('#salesman_id_tabulasi').prop('disabled', true);
            }
        });

        $('#report_type_tabulasi').trigger('change');
        applyTabulasiMonthYearToForm();

        // LOGIKA BARU: Mengaktifkan/menonaktifkan dropdown berdasarkan radio button (Tabulasi)
        $('input[name="period_filter_type"]').on('change', function() {
            const filterType = $(this).val();
            if (filterType === 'month') {
                $('#tabulasi-month-select').prop('disabled', false).trigger('change');
                $('#tabulasi-year-select').prop('disabled', true);
            } else { // filterType === 'year'
                $('#tabulasi-month-select').prop('disabled', true);
                $('#tabulasi-year-select').prop('disabled', false).trigger('change');
            }
            applyTabulasiMonthYearToForm();
        }).trigger('change');
    });
</script>
@endpush