@extends('superuser.app')

@section('content')
<style>
    body { background-color: #1f242a; }

    .crm-wrapper {
        max-width: 1050px; /* DITAMBAH */
        margin: 0 auto;
        height: calc(100vh - 90px);
    }

    .crm-row {
        display: flex;
        gap: 10px;
        height: 100%;
    }

    .card {
        border-radius: 14px;
        border: none;
        height: 100%;
        background: #fff;
    }

    /* FRAME A */
    .frame-a {
        flex: 0 0 470px; /* DITAMBAH */
        display: flex;
        flex-direction: column;
    }

    /* FRAME B */
    .frame-b {
        flex: 1;
        min-width: 0;
    }

    .frame-b .card-body {
        overflow-y: auto;
        padding-bottom: 80px;
    }

    .frame-title {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    /* TABS */
    .mutasi-tabs {
        display: flex;
        gap: 6px;
        margin-bottom: 8px;
    }

    .mutasi-tabs .tab-btn {
        padding: 5px 12px;
        font-size: 12px;
        border-radius: 20px;
        border: 1px solid #ddd;
        cursor: pointer;
        background: #f8f9fa;
        color: #555;
    }

    .mutasi-tabs .tab-btn.active {
        background: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
    }

    /* TABLE */
    .mutasi-table {
        font-size: 12px;
        margin-bottom: 0;
    }

    .mutasi-table thead th {
        font-weight: 600;
        color: #666;
        background: #f8f9fa;
        border-bottom: 1px solid #ddd;
        position: sticky;
        top: 0;
        z-index: 2;
    }

    .mutasi-table tbody tr {
        cursor: pointer;
        transition: background .15s;
    }

    .mutasi-table tbody tr:hover {
        background: #f1f3f5;
    }

    .mutasi-table tbody tr.active {
        background: #e7f1ff;
    }

    .table-wrapper {
        overflow-y: auto;
        max-height: calc(100vh - 220px);
    }

    @media (max-width: 768px) {
        .crm-wrapper {
            max-width: 100%;
        }

        .frame-a {
            flex: 0 0 260px;
        }
    }
</style>

<div class="container-fluid px-2">
    <div class="crm-wrapper">

        <div class="crm-row">

            {{-- ================= FRAME A ================= --}}
            <div class="frame-a">
                <div class="card">
                    <div class="card-body p-2">

                        <!-- <div class="frame-title">
                            Mutasi Showroom
                        </div> -->

                        <div class="mutasi-tabs">
                            <div class="tab-btn active" data-tab="aktif">
                                Aktif (<span id="count-aktif">{{ $mutasiAktif->total() }}</span>)
                            </div>
                        
                            <div class="tab-btn" data-tab="belum-diambil">
                                Belum Diambil (<span id="count-belum">{{ $mutasiBelumDiambil->total() }}</span>)
                            </div>
                        
                            <div class="tab-btn" data-tab="selesai">
                                Selesai (<span id="count-selesai">{{ $mutasiSelesai->total() }}</span>)
                            </div>
                        </div>


                        {{-- TAB AKTIF --}}
                        <div id="tab-aktif" class="mutasi-tab-content">
                            <div class="table-wrapper">
                                <table class="table table-sm mutasi-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Tanggal - Kode</th>
                                            <th>Customer</th>
                                            <th>Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($mutasiAktif as $row)
                                           
                                            <tr data-id="{{ $row->id }}">
                                                <td>
                                                    {{ $loop->iteration + $mutasiAktif->firstItem() - 1 }}
                                                </td>
                                                <td>
                                                    {{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/y') }}<br> 
                                                    <span><strong>{{ $row->kode }}{{ optional($row->so)->code ? ' / '.optional($row->so)->code : '' }}</strong></span>
                                                </td>
                                                <td>
                                                    {{ 
                                                        optional($row->customer_other_address)->name
                                                            ? optional($row->customer_other_address)->name . '  ' . optional($row->customer_other_address)->text_kota
                                                            : 'SHOWROOM'
                                                    }}
                                                </td>
                                                <td>
                                                    @if($row->type == 5)
                                                        PROMOSI
                                                    @else
                                                        {{ $row->type() }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-muted text-center">
                                                    Tidak ada data
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-1">
                                {{ $mutasiAktif->links() }}
                            </div>
                        </div>
                        
                        {{-- TAB BELUM DIAMBIL --}}
                        <div id="tab-belum-diambil" class="mutasi-tab-content d-none">
                            <div class="table-wrapper">
                                <table class="table table-sm mutasi-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Tanggal - Kode</th>
                                            <th>Customer</th>
                                            <th>Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($mutasiBelumDiambil as $row)
                                            <tr data-id="{{ $row->id }}">
                                                <td>
                                                    {{ $loop->iteration + $mutasiBelumDiambil->firstItem() - 1 }}
                                                </td>
                                                <td>
                                                    {{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/y') }}<br> 
                                                    <span><strong>{{ $row->kode }}{{ optional($row->so)->code ? ' / '.optional($row->so)->code : '' }}</strong></span>
                                                </td>
                                                <td>
                                                    {{ 
                                                        optional($row->customer_other_address)->name
                                                            ? optional($row->customer_other_address)->name . '  ' . optional($row->customer_other_address)->text_kota
                                                            : 'SHOWROOM'
                                                    }}
                                                </td>
                                                <td>
                                                    {{ $row->type == 5 ? 'PROMOSI' : $row->type() }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-muted text-center">
                                                    Tidak ada data
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        
                            <div class="mt-1">
                                {{ $mutasiBelumDiambil->links() }}
                            </div>
                        </div>

                        {{-- TAB SELESAI --}}
                        <div id="tab-selesai" class="mutasi-tab-content d-none">
                            <div class="table-wrapper">
                                <table class="table table-sm mutasi-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Tanggal - Kode</th>
                                            <th>Customer</th>
                                            <th>Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($mutasiSelesai as $row)
                                            <tr class="text-muted" data-id="{{ $row->id }}">
                                                <td>
                                                    {{ $loop->iteration + $mutasiSelesai->firstItem() - 1 }}
                                                </td>
                                                <td>
                                                    {{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/y') }}<br> 
                                                    <span><strong>{{ $row->kode }}{{ optional($row->so)->code ? ' / '.optional($row->so)->code : '' }}</strong></span>
                                                </td>
                                                <td>
                                                    {{ 
                                                        optional($row->customer_other_address)->name
                                                            ? optional($row->customer_other_address)->name . '  ' . optional($row->customer_other_address)->text_kota
                                                            : 'SHOWROOM'
                                                    }}
                                                </td>
                                                <td>
                                                    @if($row->type == 5)
                                                        PROMOSI
                                                    @else
                                                        {{ $row->type() }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-muted text-center">
                                                    Tidak ada data
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-1">
                                {{ $mutasiSelesai->links() }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ================= FRAME B ================= --}}
            <div class="frame-b">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center text-muted mt-5">
                            <i class="bi bi-inbox" style="font-size:32px;"></i>
                            <h6 class="mt-2">Pilih mutasi dari tabel kiri</h6>
                            <p class="mb-0">Detail akan ditampilkan di sini</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $('.tab-btn').on('click', function () {
        $('.tab-btn').removeClass('active');
        $(this).addClass('active');

        const tab = $(this).data('tab');
        $('.mutasi-tab-content').addClass('d-none');
        $('#tab-' + tab).removeClass('d-none');

        resetFrameB(); // ⬅️ INI KUNCI UX
    });

    $(document).on('click', '.mutasi-table tbody tr', function () {
        $('.mutasi-table tbody tr').removeClass('active');
        $(this).addClass('active');

        const id = $(this).data('id');

        $('.frame-b .card-body').html(`
            <div class="text-center text-muted mt-5">
                <div class="spinner-border spinner-border-sm"></div>
                <p class="mt-2 mb-0">Memuat detail...</p>
            </div>
        `);

        $.get(
            "{{ route('superuser.gudang.sj_mutasi_internal.show', ':id') }}"
                .replace(':id', id),
            function (html) {
                $('.frame-b .card-body').html(html);
            }
        );
    });

    // SAVE STEP 1
    $(document).on('submit', '#formStep1', function(e){
        e.preventDefault();

        let checkedCount = $('#formStep1 input[type="checkbox"]:checked').length;
        let totalCount = $('#formStep1 input[type="checkbox"]').length;

        if (checkedCount !== totalCount) {
            Swal.fire({
                icon: 'warning',
                title: 'Checklist belum lengkap',
                text: 'Semua produk dalam mutasi harus dicentang untuk melanjutkan.'
            });
            return;
        }


        Swal.fire({
            icon: 'question',
            title: 'Konfirmasi',
            text: `Anda akan memproses ${checkedCount} produk. Lanjutkan?`,
            showCancelButton: true,
            confirmButtonText: 'Ya, simpan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(
                    '{{ route("superuser.gudang.sj_mutasi_internal.step1Save") }}',
                    $('#formStep1').serialize(),
                    function(res){
                        if(res.success){
                            $('.frame-b .card-body').html(res.html);
                        }
                    }
                );
            }
        });
    });

    $(document).on('click', '#cancelStep1', function(){
        Swal.fire({
            icon: 'warning',
            title: 'Batalkan proses?',
            text: 'Checklist akan dikosongkan.',
            showCancelButton: true,
            confirmButtonText: 'Ya, batalkan',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(
                    '{{ route("superuser.gudang.sj_mutasi_internal.step1Cancel") }}',
                    {
                        _token: '{{ csrf_token() }}',
                        mutasi_id: $('input[name=mutasi_id]').val()
                    },
                    function(res){
                        if(res.success){
                            $('.frame-b .card-body').html(res.html);
                        }
                    }
                );
            }
        });
    });


    $(document).on('click', '#cancelStep2', function () {
        Swal.fire({
            icon: 'warning',
            title: 'Batalkan Step 2?',
            text: 'Proses akan kembali ke Step 1.',
            showCancelButton: true,
            confirmButtonText: 'Ya, batalkan',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(
                    '{{ route("superuser.gudang.sj_mutasi_internal.step2Cancel") }}',
                    {
                        _token: '{{ csrf_token() }}',
                        mutasi_id: $('input[name=mutasi_id]').val()
                    },
                    function () {
                        $('.mutasi-table tr.active').click();
                    }
                );
            }
        });
    });

    // NEXT STEP 2
    $(document).on('click', '#nextStep2', function () {
        Swal.fire({
            icon: 'question',
            title: 'Lanjut ke Step 3?',
            text: 'Pastikan data sudah benar sebelum melanjutkan.',
            showCancelButton: true,
            confirmButtonText: 'Ya, lanjut',
            cancelButtonText: 'Batal'
        }).then((result) => {
    
            if (!result.isConfirmed) return;
    
            $.post(
                '{{ route("superuser.gudang.sj_mutasi_internal.step2Next") }}',
                {
                    _token: '{{ csrf_token() }}',
                    mutasi_id: $('input[name=mutasi_id]').val()
                }
            )
            .done(function (res) {
                if (res.success) {
                    // reload detail (masuk step 3)
                    $('.mutasi-table tr.active').click();
                }
            })
            .fail(function (xhr) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak bisa lanjut',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                });
            });
    
        });
    });


    // SAVE STEP 3
    $(document).on('click', '#saveStep3', function () {
        let status = $('#status_barang').val();

        if (!status) {
            Swal.fire({
                icon: 'warning',
                title: 'Status belum dipilih',
                text: 'Silakan pilih status barang terlebih dahulu.'
            });
            return;
        }

        Swal.fire({
            icon: 'warning',
            title: 'Proses seluruh mutasi?',
            text: 'Aksi ini akan memproses SEMUA barang dalam satu kode mutasi.',
            showCancelButton: true,
            confirmButtonText: 'Ya, proses',
            cancelButtonText: 'Batal'
        }).then((result) => {

            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Memproses...',
                text: 'Mohon tunggu',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.post('{{ route("superuser.gudang.sj_mutasi_internal.step3Update") }}', {
                _token: '{{ csrf_token() }}',
                mutasi_id: $('input[name=mutasi_id]').val(),
                status_barang: status
            }, function (res) {

                Swal.close();

                if(res.success && res.to_selesai){
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Mutasi telah selesai diproses.'
                    });
                
                    refreshMutasiTabs(); // 🔥 INI
                
                    $('.tab-btn').removeClass('active');
                    $('.tab-btn[data-tab="selesai"]').addClass('active');
                    $('.mutasi-tab-content').addClass('d-none');
                    $('#tab-selesai').removeClass('d-none');
                
                    $('#tab-selesai').load(location.href + ' #tab-selesai > *');
                
                    resetFrameB();
                }

            }).fail(function (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: err.responseJSON?.message || 'Terjadi kesalahan'
                });
            });
        });
    });

    function toggleStep1Button() {
        let checkedCount = $('#formStep1 input[type="checkbox"]:checked').length;
        $('#btnStep1Submit').prop('disabled', checkedCount === 0);
    }

    // Saat halaman step 1 pertama kali dimuat
    $(document).on('change', '#formStep1 input[type="checkbox"]', function () {
        toggleStep1Button();
    });

    // Trigger juga saat step1 pertama kali di-render via ajax
    $(document).on('shownStep1', function () {
        toggleStep1Button();
    });

    function resetFrameB() {
        $('.mutasi-table tbody tr').removeClass('active');

        $('.frame-b .card-body').html(`
            <div class="text-center text-muted mt-5">
                <i class="bi bi-inbox" style="font-size:32px;"></i>
                <h6 class="mt-2">Pilih mutasi dari tabel kiri</h6>
                <p class="mb-0">Detail akan ditampilkan di sini</p>
            </div>
        `);
    }

    $(document).on('click', '.pagination a', function () {
        resetFrameB();
    });
    
    function refreshMutasiTabs() {
        $.get('{{ route("superuser.gudang.sj_mutasi_internal.refreshTabs") }}', function (res) {
            $('#count-aktif').text(res.aktif);
            $('#count-belum').text(res.belum);
            $('#count-selesai').text(res.selesai);
        });
    }
</script>
@endpush