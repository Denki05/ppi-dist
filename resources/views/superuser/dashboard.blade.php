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
                                <div class="mb-3 d-flex justify-content-between align-items-center">
                                    {{-- Single Month and Year Picker for Omset forms --}}
                                    <div class="form-group mb-0 d-flex align-items-center">
                                        <input type="month" id="omset-month-year" class="form-control" style="width: 150px;" value="{{ $selectedMonthYear }}">
                                        <button class="btn btn-danger" id="reset-month-filter"><i class="fa fa-sync"></i></button>
                                    </div>
                                    {{-- End Single Month and Year Picker --}}
                                    <div>
                                        <div class="btn-group" role="group" aria-label="Filter Omset">
                                            <button class="btn btn-primary btn-outline-primary btn-filter-type-omset active" data-type="all" style="margin-right: 6px;">ALL</button>
                                            <button class="btn btn-success btn-filter-type-omset" data-type="ppn" style="margin-right: 6px;">PPN</button>
                                            <button class="btn btn-warning btn-filter-type-omset" data-type="nonppn">NonPPN</button>
                                        </div>
                                    </div>
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
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex flex-column">
                                                <div class="d-flex justify-content-between align-items-center mb-4"> {{-- mb-4 untuk jarak dari iframe --}}
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
                                                                    <select class="form-control form-control-sm js-select2" id="tabulasi-year-select" style="width: 100px;">
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
                                                                {{-- Tambahkan opsi salesman jika relevan --}}
                                                                <option value="salesman">R. by Salesman</option>
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
                                                {{-- iframe akan mengikuti lebar container induknya (card-body) --}}
                                                <iframe src="" type="application/pdf" id="iframePdf" style="width: 100%; height: 800px; border: 1px solid #ddd;">
                                                    <p>Browser Anda tidak mendukung iframe atau tidak dapat menampilkan file PDF secara langsung. Silakan <a href="#" id="pdfDownloadLink">klik di sini untuk mengunduh PDF</a>.</p>
                                                </iframe>

                                            </div> {{-- Akhir dari d-flex flex-column --}}
                                        </div>
                                    </div>

                                    {{-- Hidden inputs (tetap di luar flex container utama) --}}
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
                            
                            {{-- MODIFIKASI DIMULAI DI SINI --}}
                            <section id="content3" class="tab-content">
                                <form id="forecastingForm" method="POST" target="_blank"> {{-- Tambahkan form dan target="_blank" untuk PDF --}}
                                    @csrf
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-body">
                                                    {{-- Baris utama untuk inputan dan tombol, menggunakan d-flex untuk mengatur horizontal --}}
                                                    <div class="d-flex justify-content-start align-items-center mb-4"> {{-- mb-4 untuk jarak dari iframe --}}
                                                        {{-- Group input Vendor, Period From, Period To --}}
                                                        <div class="d-flex align-items-center gap-3"> {{-- Menggunakan gap-3 untuk jarak antar input --}}
                                                            {{-- Select Vendor --}}
                                                            <div>
                                                                <label for="vendor_name_forecast" class="form-label visually-hidden">Vendor:</label>
                                                                <select class="form-control form-control-sm js-select2" name="vendor_name" id="vendor_name_forecast" style="min-width: 280px;">
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
                                                        </div>

                                                        {{-- Tombol Aksi - Pindahkan ke kanan jika diinginkan, atau biarkan mengikuti input --}}
                                                        {{-- Jika ingin tombol di kanan terpisah, gunakan justify-content-between di div utama --}}
                                                        <div class="ms-auto"> {{-- ms-auto untuk mendorong tombol ke kanan --}}
                                                            <div class="btn-group" role="group" aria-label="Forecasting Actions">
                                                                <button type="button" id="printReportForecast" class="btn btn-success btn-sm" onclick="submitForecastingForm('print_pdf')">
                                                                    <i class="fa fa-file-pdf-o" aria-hidden="true"></i> Export
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    {{-- Akhir dari d-flex untuk inputan dan tombol --}}

                                                </div> {{-- Akhir card-body --}}
                                            </div> {{-- Akhir card --}}
                                        </div>
                                    </div>
                                    {{-- Hidden inputs untuk parameter yang dibutuhkan controller --}}
                                    <input type="hidden" name="action_forecast" id="action_forecast_param">
                                </form>

                                {{-- Iframe berada di row terpisah dengan margin atas --}}
                                <div class="row mt-4"> {{-- mt-4 untuk jarak vertikal dari card di atas --}}
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
    function applyTabulasiMonthYearToForm() {
        let startDate = '';
        let endDate = '';

        const filterType = $('input[name="period_filter_type"]:checked').val();
        const selectedMonth = $('#tabulasi-month-select').val(); // Format: MM
        const selectedYear = $('#tabulasi-year-select').val();   // Format: YYYY

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

        $('#tabulasi_start_date').val('');
        $('#tabulasi_end_date').val('');
        $('#tabulasi_period_from').val('');
        $('#tabulasi_period_to').val('');

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
            const periodFrom = form.find('input[name="tabulasi_period_from"]').val();
            const periodTo = form.find('input[name="tabulasi_period_to"]').val();

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
        let period_from = $('#period_from_forecast').val(); 
        let period_to = $('#period_to_forecast').val();     

        let formatted_period_from = period_from ? period_from + '-01' : '';

        let formatted_period_to = '';
        if (period_to) {
            let parts = period_to.split('-');
            let year = parseInt(parts[0]);
            let month = parseInt(parts[1]);
            let lastDay = new Date(year, month, 0).getDate(); 
            formatted_period_to = `${period_to}-${lastDay}`;
        }
        
        if (!vendor_name || !formatted_period_from || !formatted_period_to) {
            Swal.fire({
                icon: 'error',
                title: 'Input Tidak Lengkap!',
                text: 'Vendor, Bulan Awal, dan Bulan Akhir tidak boleh kosong.',
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
                _token: "{{ csrf_token() }}" 
            },
            success: function(response) {
                Swal.close(); 
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

        // --- Inisialisasi DataTable untuk tab "Omset" ---
        var filterTypeOmset = 'all'; 
        var datatableOmset = $('.datatableOmset').DataTable({
            "info": false,
            "dom": '<"top"f><"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-6 d-flex align-items-center"il><"col-sm-12 col-md-6"p>>',
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

        // Event listener untuk perubahan dropdown bulan atau tahun, atau radio button
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

        // **LOGIKA BARU: Mengaktifkan/menonaktifkan dropdown berdasarkan radio button**
        $('input[name="period_filter_type"]').on('change', function() {
            const filterType = $(this).val();
            if (filterType === 'month') {
                $('#tabulasi-month-select').prop('disabled', false).trigger('change'); // Aktifkan bulan
                $('#tabulasi-year-select').prop('disabled', true);   // Nonaktifkan tahun
            } else { // filterType === 'year'
                $('#tabulasi-month-select').prop('disabled', true);   // Nonaktifkan bulan
                $('#tabulasi-year-select').prop('disabled', false).trigger('change');  // Aktifkan tahun
            }
            applyTabulasiMonthYearToForm(); // Panggil ulang untuk update tanggal setelah perubahan mode
        }).trigger('change'); // Panggil trigger change saat load untuk set initial state
    });
</script>
@endpush