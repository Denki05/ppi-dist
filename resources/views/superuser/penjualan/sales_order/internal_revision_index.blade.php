@extends('superuser.app')

@section('content')

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>Error!</strong> {{ session('error') }}
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <strong>Berhasil!</strong> {{ session('success') }}
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

<div class="row mb-30">
    <div class="col-md-6">
        <h4 style="font-weight: bold;"><i class="fa fa-history mr-2"></i>REVISI INTERNAL</h4>
        <!-- <p class="text-muted mb-0" style="font-size:13px;">
            <span class="badge badge-warning">{{ $stats['pending'] }} Pending</span>
            <span class="badge badge-success">{{ $stats['approved'] }} Approved</span>
            <span class="badge badge-danger">{{ $stats['rejected'] }} Rejected</span>
        </p> -->
    </div>
    <div class="col-md-6 text-right">
        <a href="{{ route('superuser.penjualan.sales_order.index_lanjutan') }}" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back to SO Lanjutan
        </a>
    </div>
</div>

<!-- Filter Tabs -->
<div class="mb-3">
    <div class="btn-group btn-group-sm" role="group">
        <a href="{{ route('superuser.penjualan.internal_revision.index') }}" 
           class="btn {{ !$statusFilter ? 'btn-primary' : 'btn-outline-primary' }}">
            Semua
        </a>
        <a href="{{ route('superuser.penjualan.internal_revision.index', ['status' => 1]) }}" 
           class="btn {{ $statusFilter == 1 ? 'btn-warning' : 'btn-outline-warning' }}">
            <i class="fa fa-clock-o"></i> Pending
            @if($stats['pending'] > 0)
                <span class="badge badge-light">{{ $stats['pending'] }}</span>
            @endif
        </a>
        <a href="{{ route('superuser.penjualan.internal_revision.index', ['status' => 2]) }}" 
           class="btn {{ $statusFilter == 2 ? 'btn-success' : 'btn-outline-success' }}">
            <i class="fa fa-check"></i> Approved
        </a>
        <a href="{{ route('superuser.penjualan.internal_revision.index', ['status' => 3]) }}" 
           class="btn {{ $statusFilter == 3 ? 'btn-danger' : 'btn-outline-danger' }}">
            <i class="fa fa-times"></i> Rejected
        </a>
    </div>
</div>

<div class="block">
    <div class="block-content block-content-full">
        <table class="table table-hover table-striped" id="dtRevisions" style="width:100%">
            <thead>
                <tr class="small text-uppercase">
                    <th>#</th>
                    <th>DO Code</th>
                    <th>SO Ref</th>
                    <th>Customer</th>
                    <th>Status Revisi</th>
                    <th>Alasan Revisi</th>
                    <th>Diajukan Oleh</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($revisions as $index => $rev)
                @php
                    $customerName = optional($rev->packingOrder->member ?? null)->name
                        ?? optional(optional($rev->packingOrder->so ?? null)->member)->name
                        ?? optional($rev->packingOrder->customer ?? null)->name
                        ?? '-';
                    $revStatusMap = [
                        1 => ['label' => 'Pending', 'class' => 'warning', 'icon' => 'fa-clock-o'],
                        2 => ['label' => 'Approved', 'class' => 'success', 'icon' => 'fa-check'],
                        3 => ['label' => 'Rejected', 'class' => 'danger', 'icon' => 'fa-times'],
                    ];
                    $revSt = $revStatusMap[$rev->status] ?? $revStatusMap[1];
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><b>{{ $rev->packingOrder->do_code ?? '-' }}</b></td>
                    <td>{{ $rev->packingOrder->so->code ?? '-' }}</td>
                    <td>{{ $customerName }}</td>
                    <td>
                        <span class="badge badge-{{ $revSt['class'] }}">
                            <i class="fa {{ $revSt['icon'] }} mr-1"></i>{{ $revSt['label'] }}
                        </span>
                        @if($rev->status == 2 && $rev->approvedBy)
                            <br><small class="text-muted">oleh {{ $rev->approvedBy->name }}</small>
                        @endif
                        @if($rev->status == 3 && $rev->approval_reason)
                            <br><small class="text-danger" title="{{ $rev->approval_reason }}">{{ \Illuminate\Support\Str::limit($rev->approval_reason, 40) }}</small>
                        @endif
                    </td>
                    <td style="max-width:250px;">{{ $rev->request_reason }}</td>
                    <td>{{ $rev->requestedBy->name ?? $rev->requested_by }}</td>
                    <td>{{ $rev->requested_at ? \Illuminate\Support\Carbon::parse($rev->requested_at)->format('d/m/Y H:i') : '-' }}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-lihat-detail" data-id="{{ $rev->id }}">
                            <i class="fa fa-eye"></i> Lihat Detail
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted">Tidak ada data revisi internal.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Detail + Approve/Reject -->
<div class="modal fade" id="modalDetailRevisi" tabindex="-1" role="dialog">
    <!-- Menggunakan class modal-xl (bawaan bootstrap) agar tampilan rapi tanpa perlu inline style width -->
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold">Detail Revisi - <span id="detailDoCode"></span></h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal_revision_id">

                <!-- Legend -->
                <div class="alert alert-info py-2 px-3 mb-4" style="font-size: 13px;">
                    <i class="fa fa-info-circle mr-2"></i>
                    <span class="text-muted">nilai lama</span>
                    <i class="fa fa-arrow-right text-warning mx-2"></i>
                    <b class="text-success">nilai baru</b>
                    <span class="mx-2">|</span> 
                    <span>tanpa panah = tidak berubah</span>
                </div>

                <!-- Section Info DO & Alasan Revisi -->
                <div class="row mb-4">
                    <div class="col-md-7 mb-3 mb-md-0">
                        <div class="block block-bordered mb-0 h-100">
                            <div class="block-header block-header-default">
                                <h3 class="block-title font-size-sm font-weight-bold">#Info DO</h3>
                            </div>
                            <div class="block-content py-3">
                                <div class="row font-size-sm">
                                    <div class="col-sm-4 mb-2">
                                        <div class="text-muted mb-1">DO Code</div>
                                        <b id="detailDoCode2"></b>
                                    </div>
                                    <div class="col-sm-4 mb-2">
                                        <div class="text-muted mb-1">Customer</div>
                                        <b id="detailCustomer"></b>
                                    </div>
                                    <div class="col-sm-4 mb-2">
                                        <div class="text-muted mb-1">Kurs IDR</div>
                                        <span id="detailKursDiff"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="block block-bordered mb-0 h-100">
                            <div class="block-header block-header-default">
                                <h3 class="block-title font-size-sm font-weight-bold">#Alasan Revisi</h3>
                            </div>
                            <div class="block-content py-3 font-size-sm" id="detailReason"></div>
                        </div>
                    </div>
                </div>

                <!-- Section Item Produk & Kalkulasi -->
                <div class="row mb-4">
                    <div class="col-lg-7 mb-3 mb-lg-0">
                        <div class="block block-bordered mb-0 h-100">
                            <div class="block-header block-header-default">
                                <h3 class="block-title font-size-sm font-weight-bold">#Perubahan Item Produk</h3>
                            </div>
                            <div class="block-content block-content-full p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm table-vcenter mb-0 font-size-sm">
                                        <thead class="thead-light">
                                            <tr class="text-uppercase">
                                                <th>Produk</th>
                                                <th class="text-center">Qty</th>
                                                <th class="text-right">Harga (Rp)</th>
                                                <th class="text-right">Disc (Rp)</th>
                                                <th class="text-right">Total (Rp)</th>
                                            </tr>
                                        </thead>
                                        <tbody id="detailItemsBody"></tbody>
                                        <tfoot>
                                            <tr class="table-light">
                                                <td colspan="4" class="text-right"><b>Subtotal Item</b></td>
                                                <td id="subtotalItemValue" class="text-right"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="block block-bordered mb-0 h-100">
                            <div class="block-header block-header-default">
                                <h3 class="block-title font-size-sm font-weight-bold">#Kalkulasi Kurs &amp; Biaya</h3>
                            </div>
                            <div class="block-content block-content-full p-0">
                                <table class="table table-sm table-vcenter mb-0 font-size-sm">
                                    <thead class="thead-light" style="visibility: hidden; height: 0;">
                                        <!-- Agar lebar kolom konsisten -->
                                        <tr><th width="35%"></th><th width="25%"></th><th width="40%"></th></tr>
                                    </thead>
                                    <tbody id="detailCostBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Aksi -->
                <div id="actionSection">
                <div class="row">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <button type="button" class="btn btn-outline-danger btn-block" id="btnToggleReject">
                            <i class="fa fa-times-circle mr-1"></i> Tolak Pengajuan
                        </button>
                        <div id="rejectPanel" style="display:none;" class="border border-danger rounded p-3 mt-2">
                            <div class="form-group mb-2">
                                <textarea class="form-control" id="reject_reason" rows="2" minlength="5" placeholder="Alasan penolakan, minimal 5 karakter"></textarea>
                            </div>
                            <button type="button" class="btn btn-danger btn-block" id="btnConfirmReject">
                                <i class="fa fa-times mr-1"></i> Konfirmasi Tolak
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-outline-success btn-block" id="btnToggleApprove">
                            <i class="fa fa-check-circle mr-1"></i> Setujui Pengajuan
                        </button>
                        <div id="approvePanel" style="display:none;" class="border border-success rounded p-3 mt-2">
                            <button type="button" class="btn btn-warning btn-block mb-3" id="btnRequestOtp">
                                <i class="fa fa-key mr-1"></i> Kirim Kode OTP
                            </button>
                            <div id="otpSection" style="display:none;">
                                <!-- OTP Code Display - Large & Copyable -->
                                <div id="otpDisplayBox" class="text-center p-3 mb-3" style="background: #fff3bf; border: 2px dashed #fcc419; border-radius: 10px; display:none;">
                                    <div class="text-muted mb-1" style="font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Kode OTP Anda</div>
                                    <div id="otpDisplayCode" style="font-size:2.2em; font-weight:800; letter-spacing:8px; color:#e67700; cursor:pointer;" title="Klik untuk copy" onclick="copyOtp()"></div>
                                    <div class="text-muted mt-1" style="font-size:11px;">
                                        <i class="fa fa-clock-o"></i> Berlaku <span id="otpCountdown">5:00</span> menit &middot; <small>klik kode untuk copy</small>
                                    </div>
                                </div>
                                <!-- Manual Input -->
                                <div class="form-group mb-2">
                                    <label class="font-weight-bold" style="font-size:12px;">Masukkan OTP di bawah ini:</label>
                                    <input type="text" class="form-control text-center" id="otp_code" placeholder="6 digit OTP" maxlength="6" style="font-size:1.3em; letter-spacing:5px; font-weight:700;">
                                </div>
                                <div class="form-group mb-2">
                                    <textarea class="form-control" id="approval_reason" rows="2" minlength="5" placeholder="Alasan approval, minimal 5 karakter"></textarea>
                                </div>
                                <button type="button" class="btn btn-success btn-block" id="btnConfirmApprove">
                                    <i class="fa fa-check mr-1"></i> Approve Sekarang
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                </div><!-- /actionSection -->

                <!-- Info untuk already processed -->
                <div id="processedInfo" class="d-none mt-3 p-3 rounded" style="background:#f8f9fa; border:1px solid #dee2e6;">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-info-circle text-muted mr-2"></i>
                        <span id="processedInfoText"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
$(document).ready(function () {
    $('#dtRevisions').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 25,
    });

    function formatRp(v) {
        v = parseFloat(v) || 0;
        return 'Rp ' + Math.round(v).toLocaleString('id-ID');
    }

    function cell(before, after) {
        if (before == after) return after;
        return '<span class="text-muted">' + before + '</span> ' +
               '<i class="fa fa-arrow-right text-warning mx-2"></i> ' +
               '<b class="text-success">' + after + '</b>';
    }

    function resetActionPanels() {
        $('#rejectPanel, #approvePanel, #otpSection, #otpDisplayBox').hide();
        $('#reject_reason, #otp_code, #approval_reason').val('');
        $('#otpDisplayCode').text('');
        $('#otpCountdown').text('5:00').removeClass('text-danger font-weight-bold');
        $('#btnRequestOtp').show().prop('disabled', false).html('<i class="fa fa-key mr-1"></i> Kirim Kode OTP');
        if (otpCountdownInterval) {
            clearInterval(otpCountdownInterval);
            otpCountdownInterval = null;
        }
    }

    $('#btnToggleReject').on('click', function () {
        $('#approvePanel').slideUp(150);
        $('#rejectPanel').slideToggle(150);
    });
    
    $('#btnToggleApprove').on('click', function () {
        $('#rejectPanel').slideUp(150);
        $('#approvePanel').slideToggle(150);
    });

    $(document).on('click', '.btn-lihat-detail', function () {
        var id = $(this).data('id');
        $('#modal_revision_id').val(id);
        resetActionPanels();

        $.get('{{ route("superuser.penjualan.internal_revision.detail", ["id" => "__ID__"]) }}'.replace('__ID__', id), function (res) {
            $('#detailDoCode').text(res.do_code);
            $('#detailDoCode2').text(res.do_code);
            $('#detailCustomer').text(res.customer || '-');
            $('#detailReason').text(res.request_reason);
            $('#detailKursDiff').html(cell(res.before.idr_rate, res.after.idr_rate));

            // Show/hide action buttons based on status
            if (res.status == 1) {
                // Pending - show action buttons
                $('#actionSection').show();
                $('#processedInfo').addClass('d-none');
            } else {
                // Approved/Rejected - hide action buttons, show info
                $('#actionSection').hide();
                $('#processedInfo').removeClass('d-none');
                if (res.status == 2) {
                    $('#processedInfo').css('background', '#d4edda').css('border-color', '#c3e6cb');
                    $('#processedInfoText').html(
                        '<i class="fa fa-check-circle text-success mr-2"></i>' +
                        '<b class="text-success">Approved</b> oleh ' + (res.approved_by || '-') +
                        ' pada ' + (res.approved_at || '-')
                    );
                } else if (res.status == 3) {
                    $('#processedInfo').css('background', '#f8d7da').css('border-color', '#f5c6cb');
                    $('#processedInfoText').html(
                        '<i class="fa fa-times-circle text-danger mr-2"></i>' +
                        '<b class="text-danger">Rejected</b>' +
                        (res.approval_reason ? ' - ' + res.approval_reason : '')
                    );
                }
            }

            var afterMap = {};
            res.after.items.forEach(function (i) { afterMap[i.product_packaging_id] = i; });
            var beforeIds = res.before.items.map(function (i) { return i.product_packaging_id; });

            var rows = '';
            res.before.items.forEach(function (b) {
                var a = afterMap[b.product_packaging_id];
                if (a) {
                    rows += '<tr>' +
                            '<td>' + b.product_code + ' - ' + b.product_name + '</td>' +
                            '<td class="text-center">' + cell(b.qty, a.qty) + '</td>' +
                            '<td class="text-right">' + cell(formatRp(b.price_idr), formatRp(a.price_idr)) + '</td>' +
                            '<td class="text-right">' + cell(formatRp(b.usd_disc_idr), formatRp(a.usd_disc_idr)) + '</td>' +
                            '<td class="text-right">' + cell(formatRp(b.total_idr), formatRp(a.total_idr)) + '</td>' +
                            '</tr>';
                } else {
                    rows += '<tr class="table-danger">' +
                            '<td>' + b.product_code + ' - ' + b.product_name + ' <span class="badge badge-danger ml-1">Dihapus</span></td>' +
                            '<td class="text-center">' + b.qty + '</td>' +
                            '<td class="text-center" colspan="2">-</td>' +
                            '<td class="text-right">' + formatRp(b.total_idr) + '</td>' +
                            '</tr>';
                }
            });
            res.after.items.forEach(function (a) {
                if (beforeIds.indexOf(a.product_packaging_id) === -1) {
                    rows += '<tr class="table-success">' +
                            '<td>' + a.product_code + ' - ' + a.product_name + ' <span class="badge badge-success ml-1">Produk Baru</span></td>' +
                            '<td class="text-center">' + a.qty + '</td>' +
                            '<td class="text-right">' + formatRp(a.price_idr) + '</td>' +
                            '<td class="text-right">' + formatRp(a.usd_disc_idr) + '</td>' +
                            '<td class="text-right">' + formatRp(a.total_idr) + '</td>' +
                            '</tr>';
                }
            });
            $('#detailItemsBody').html(rows);

            var bTotals = res.before.calculated_totals;
            var aTotals = res.after.calculated_totals;

            $('#subtotalItemValue').html(
                (bTotals && aTotals) ? cell(formatRp(bTotals.sub_total_item), formatRp(aTotals.sub_total_item)) : '-'
            );

            var costRows = '';
            if (bTotals && aTotals) {
                costRows += '<tr><td>Disc Agen</td><td class="text-center text-muted">' + cell(res.before.disc_agen_percent + '%', res.after.disc_agen_percent + '%') + '</td><td class="text-right">' + cell(formatRp(bTotals.disc_agen_idr), formatRp(aTotals.disc_agen_idr)) + '</td></tr>';
                costRows += '<tr><td>Disc Kemasan</td><td class="text-center text-muted">' + cell(res.before.disc_kemasan_percent + '%', res.after.disc_kemasan_percent + '%') + '</td><td class="text-right">' + cell(formatRp(bTotals.disc_kemasan_idr), formatRp(aTotals.disc_kemasan_idr)) + '</td></tr>';
            } else {
                costRows += '<tr><td colspan="3" class="text-danger text-center">Kalkulasi tidak valid (lihat validasi qty/disc pada pengajuan ini).</td></tr>';
            }

            costRows += '<tr><td>Disc IDR</td><td class="text-center text-muted">-</td><td class="text-right">' + cell(formatRp(res.before.disc_tambahan_idr), formatRp(res.after.disc_tambahan_idr)) + '</td></tr>';
            costRows += '<tr><td>Voucher</td><td class="text-center text-muted">-</td><td class="text-right">' + cell(formatRp(res.before.voucher_idr), formatRp(res.after.voucher_idr)) + '</td></tr>';
            costRows += '<tr><td>Ongkir</td><td class="text-center text-muted">-</td><td class="text-right">' + cell(formatRp(res.before.delivery_cost_idr), formatRp(res.after.delivery_cost_idr)) + '</td></tr>';
            costRows += '<tr><td>Biaya Lain</td><td class="text-center text-muted">-</td><td class="text-right">' + cell(formatRp(res.before.other_cost_idr), formatRp(res.after.other_cost_idr)) + '</td></tr>';

            if (bTotals && aTotals) {
                costRows += '<tr class="table-warning"><td colspan="2"><b>GRAND TOTAL</b></td><td class="text-right"><b>' +
                    cell(formatRp(bTotals.grand_total_idr), formatRp(aTotals.grand_total_idr)) +
                    '</b></td></tr>';
            }
            $('#detailCostBody').html(costRows);

            $('#modalDetailRevisi').modal('show');
        });
    });

    var otpCountdownInterval = null;

    function copyOtp() {
        var code = $('#otpDisplayCode').text().trim();
        if (navigator.clipboard) {
            navigator.clipboard.writeText(code).then(function() {
                var el = $('#otpDisplayCode');
                el.effect('highlight', {color: '#b2f2bb'}, 600);
            });
        } else {
            // Fallback
            var tmp = document.createElement('input');
            document.body.appendChild(tmp);
            tmp.value = code;
            tmp.select();
            document.execCommand('copy');
            document.body.removeChild(tmp);
        }
    }

    function startOtpCountdown(minutes) {
        if (otpCountdownInterval) clearInterval(otpCountdownInterval);
        var totalSeconds = minutes * 60;
        var display = $('#otpCountdown');

        otpCountdownInterval = setInterval(function() {
            totalSeconds--;
            if (totalSeconds <= 0) {
                clearInterval(otpCountdownInterval);
                display.text('0:00').addClass('text-danger font-weight-bold');
                $('#otpDisplayBox').css('border-color', '#fa5252').css('background', '#fff5f5');
                $('#otpDisplayCode').css('color', '#fa5252');
                return;
            }
            var m = Math.floor(totalSeconds / 60);
            var s = totalSeconds % 60;
            display.text(m + ':' + (s < 10 ? '0' : '') + s);

            // Warning when < 60 seconds
            if (totalSeconds <= 60) {
                display.addClass('text-danger font-weight-bold');
            }
        }, 1000);
    }

    $('#btnRequestOtp').on('click', function () {
        var id = $('#modal_revision_id').val();
        var btn = $(this);

        Swal.fire({
            title: 'Kirim Kode OTP?',
            text: 'Kode OTP akan dikirim dan ditampilkan di bawah ini.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#fcc419',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fa fa-key mr-1"></i> Ya, Kirim OTP',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Mengirim...');

                $.ajax({
                    url: '{{ route("superuser.penjualan.internal_revision.request_otp", ["id" => "__ID__"]) }}'.replace('__ID__', id),
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        if (res.status === 'success') {
                            if (res.otp) {
                                $('#otpDisplayCode').text(res.otp);
                                $('#otpDisplayBox').show().css({
                                    'border-color': '#fcc419',
                                    'background': '#fff3bf'
                                });
                                $('#otpDisplayCode').css('color', '#e67700');
                                $('#otpCountdown').removeClass('text-danger font-weight-bold');
                                startOtpCountdown(res.expires_in || 5);
                            }
                            $('#otpSection').show();
                            btn.hide();

                            Swal.fire({
                                title: 'OTP Terkirim!',
                                text: 'Masukkan kode OTP di bawah ini untuk melanjutkan approval.',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Gagal', res.message || 'Gagal mengirim OTP', 'error');
                            btn.prop('disabled', false).html('<i class="fa fa-key mr-1"></i> Kirim Kode OTP');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Terjadi kesalahan saat mengirim OTP', 'error');
                        btn.prop('disabled', false).html('<i class="fa fa-key mr-1"></i> Kirim Kode OTP');
                    }
                });
            }
        });
    });

    $('#btnConfirmApprove').on('click', function () {
        var id = $('#modal_revision_id').val();
        var otp = $('#otp_code').val();
        var reason = $('#approval_reason').val();

        if (!otp || otp.length < 6) {
            Swal.fire('Perhatian', 'Kode OTP harus 6 digit', 'warning');
            return;
        }
        if (!reason || reason.length < 5) {
            Swal.fire('Perhatian', 'Alasan approval minimal 5 karakter', 'warning');
            return;
        }

        Swal.fire({
            title: 'Approve Revisi Internal?',
            html: '<div class="text-left">' +
                  '<p>Anda akan menyetujui pengajuan revisi ini.</p>' +
                  '<p><b>OTP:</b> <code>' + otp + '</code></p>' +
                  '<p><b>Alasan:</b> ' + reason + '</p>' +
                  '</div>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fa fa-check mr-1"></i> Ya, Approve!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                var btn = $('#btnConfirmApprove');
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memproses...');

                $.ajax({
                    url: '{{ route("superuser.penjualan.internal_revision.approve", ["id" => "__ID__"]) }}'.replace('__ID__', id),
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', otp: otp, approval_reason: reason },
                    success: function (res) {
                        if (res.status === 'success') {
                            $('#modalDetailRevisi').modal('hide');
                            Swal.fire({
                                title: 'Berhasil!',
                                text: res.message,
                                icon: 'success',
                                confirmButtonColor: '#28a745'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', res.message || 'Gagal approve', 'error');
                            btn.prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Approve Sekarang');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Terjadi kesalahan saat approve', 'error');
                        btn.prop('disabled', false).html('<i class="fa fa-check mr-1"></i> Approve Sekarang');
                    }
                });
            }
        });
    });

    $('#btnConfirmReject').on('click', function () {
        var id = $('#modal_revision_id').val();
        var reason = $('#reject_reason').val();

        if (!reason || reason.length < 5) {
            Swal.fire('Perhatian', 'Alasan penolakan minimal 5 karakter', 'warning');
            return;
        }

        Swal.fire({
            title: 'Tolak Pengajuan Revisi?',
            html: '<div class="text-left">' +
                  '<p>Anda akan menolak pengajuan revisi ini.</p>' +
                  '<p><b>Alasan:</b> ' + reason + '</p>' +
                  '</div>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fa fa-times mr-1"></i> Ya, Tolak!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                var btn = $('#btnConfirmReject');
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Memproses...');

                $.ajax({
                    url: '{{ route("superuser.penjualan.internal_revision.reject", ["id" => "__ID__"]) }}'.replace('__ID__', id),
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', approval_reason: reason },
                    success: function (res) {
                        if (res.status === 'success') {
                            $('#modalDetailRevisi').modal('hide');
                            Swal.fire({
                                title: 'Berhasil!',
                                text: res.message,
                                icon: 'success',
                                confirmButtonColor: '#28a745'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', res.message || 'Gagal reject', 'error');
                            btn.prop('disabled', false).html('<i class="fa fa-times mr-1"></i> Reject Pengajuan Ini');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Terjadi kesalahan saat reject', 'error');
                        btn.prop('disabled', false).html('<i class="fa fa-times mr-1"></i> Reject Pengajuan Ini');
                    }
                });
            }
        });
    });
});
</script>
@endpush