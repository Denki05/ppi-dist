@extends('superuser.app')

@section('content')

@if($errors->any())
<div class="alert alert-danger alert-dismissable" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
    <h3 class="alert-heading font-size-h4 font-w400">Error</h3>
    @foreach ($errors->all() as $error)
    <p class="mb-0">{{ $error }}</p>
    @endforeach
</div>
@endif

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

<div class="vfa-page-wrap">
<div class="vfa-canvas">

    <div class="vfa-canvas-header">
        <div class="vfa-canvas-title">
            <span class="vfa-canvas-icon"><i class="fas fa-ban"></i></span>
            <div>
                <h4>Approval Void DO</h4>
                <small>Pengajuan void yang menunggu persetujuan Finance</small>
            </div>
        </div>
        <span class="vfa-count-chip"><i class="fa fa-clock"></i> {{ $requests->count() }} menunggu</span>
        <a href="{{ route('superuser.finance.void.history') }}" class="vfa-btn vfa-btn-ghost" style="margin-left:8px;">
            <i class="fa fa-history"></i> History
        </a>
    </div>

    <div class="vfa-canvas-body">
        @if($requests->count() === 0)
            <div class="vfa-empty">
                <i class="fa fa-inbox"></i>
                <p>Tidak ada pengajuan void yang pending saat ini.</p>
            </div>
        @else
            <div class="vfa-list">
                @foreach($requests as $r)
                <div class="vfa-card">
                    <div class="vfa-card-main">
                        <div class="vfa-card-topline">
                            <span class="vfa-code">{{ $r->do_code ?? '-' }}</span>
                        </div>

                        <div class="vfa-card-customer">
                            <i class="fa fa-user"></i>
                            {{ $r->customer_name ?? '-' }}
                            @if($r->customer_city)
                                <span class="vfa-muted">&nbsp;&middot; {{ $r->customer_city }}</span>
                            @endif
                        </div>

                        <div class="vfa-card-reason">
                            <i class="fa fa-quote-left vfa-quote-icon"></i>
                            {{ $r->request_reason }}
                        </div>

                        <div class="vfa-card-meta">
                            <span><i class="fa fa-user-edit"></i> Diajukan oleh <b>{{ $r->requested_by_name ?? 'Unknown' }}</b></span>
                            <span><i class="fa fa-calendar"></i> {{ \Carbon\Carbon::parse($r->requested_at)->format('d M Y, H:i') }}</span>
                        </div>

                        @if($r->kurs_warning || $r->payment_warning)
                        <div class="vfa-card-meta">
                            @if($r->kurs_warning)
                                <span class="vfa-badge-danger"><i class="fa fa-exclamation-triangle"></i> Kurs belum valid pada DO ini</span>
                            @endif
                            @if($r->payment_warning)
                                <span class="vfa-badge-danger"><i class="fa fa-exclamation-triangle"></i> Pembayaran CASH belum dikonfirmasi</span>
                            @endif
                        </div>
                        @endif
                    </div>

                    <div class="vfa-card-actions">
                        {{-- TAMBAHKAN di sini --}}
                        <span class="vfa-amount">Rp {{ number_format($r->grand_total_idr ?? 0, 0, ',', '.') }}</span>

                        <button type="button" class="vfa-btn vfa-btn-approve btn-approve-void" data-id="{{ $r->id }}" data-code="{{ $r->do_code }}">
                            <i class="fa fa-check"></i> Approve
                        </button>
                        <button type="button" class="vfa-btn vfa-btn-reject btn-reject-void" data-id="{{ $r->id }}" data-code="{{ $r->do_code }}">
                            <i class="fa fa-times"></i> Reject
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
</div>

{{-- ============ MODAL: APPROVE / REJECT VOID ============ --}}
<div class="modal fade" id="modalVoidAction" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalVoidActionTitle">Approve Void</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p id="modalVoidActionInfo" class="mb-2"></p>
                <input type="hidden" id="void_action_id">
                <input type="hidden" id="void_action_type">
                <div class="form-group">
                    <label id="void_action_reason_label">Alasan</label>
                    <textarea class="form-control" id="void_action_reason" rows="4" placeholder="Tulis alasan..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btn-submit-void-action">Kirim</button>
            </div>
        </div>
    </div>
</div>

<style>
.vfa-page-wrap {
    max-width: 1100px;
    margin: 20px auto 32px;
}
.vfa-canvas {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 1px 6px rgba(0,0,0,.06);
    overflow: hidden;
}
.vfa-canvas-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    padding: 16px 20px;
    border-bottom: 1px solid #f1f3f5;
}
.vfa-canvas-title {
    display: flex;
    align-items: center;
    gap: 12px;
}
.vfa-canvas-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: #fef2f2;
    color: #dc2626;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}
.vfa-canvas-title h4 {
    margin: 0;
    font-size: 17px;
    font-weight: 700;
    color: #212529;
}
.vfa-canvas-title small {
    display: block;
    font-size: 12px;
    color: #adb5bd;
    margin-top: 1px;
}
.vfa-count-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    font-weight: 600;
    color: #d97706;
    background: #fffbeb;
    border: 1px solid #fde8bd;
    padding: 6px 13px;
    border-radius: 999px;
    white-space: nowrap;
}
.vfa-canvas-body {
    padding: 18px 20px;
}

.vfa-empty {
    text-align: center;
    padding: 56px 20px;
    color: #adb5bd;
}
.vfa-empty i {
    font-size: 32px;
    margin-bottom: 12px;
    display: block;
    opacity: .5;
}
.vfa-empty p {
    margin: 0;
    font-size: 13.5px;
}

.vfa-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.vfa-card {
    display: flex;
    align-items: flex-start; /* Sebelumnya center, diubah agar baris pertama sejajar */
    justify-content: space-between;
    gap: 16px;
    background: #fff;
    border: 1px solid #f1f3f5;
    border-left: 3px solid #dc2626;
    border-radius: 12px;
    padding: 14px 16px;
    transition: box-shadow .15s ease;
}
.vfa-card:hover {
    box-shadow: 0 2px 10px rgba(0,0,0,.06);
}

.vfa-card-main {
    flex: 1;
    min-width: 0;
}

.vfa-card-topline {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 6px;
}
.vfa-code {
    font-size: 14px;
    font-weight: 700;
    color: #212529;
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
}
.vfa-sep {
    color: #ced4da;
}
.vfa-invoice {
    font-size: 12.5px;
    color: #495057;
    background: #f1f3f5;
    padding: 2px 9px;
    border-radius: 20px;
}
.vfa-badge-danger {
    font-size: 12.5px;
    font-weight: 600;
    color: #991b1b;
    background: #fee2e2;
    border: 1px solid #fca5a5;
    padding: 3px 10px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-right: 6px;
}
.vfa-amount {
    font-size: 14px;
    font-weight: 700;
    color: #dc2626;
    margin-bottom: 4px; /* Memberi jarak sedikit antara nominal dan tombol */
}

.vfa-card-customer {
    font-size: 13px;
    color: #495057;
    margin-bottom: 8px;
}
.vfa-card-customer i {
    color: #adb5bd;
    margin-right: 5px;
}
.vfa-muted {
    color: #adb5bd;
}

.vfa-card-reason {
    font-size: 12.5px;
    color: #6c757d;
    background: #f8f9fb;
    border-radius: 8px;
    padding: 8px 11px;
    margin-bottom: 8px;
    line-height: 1.5;
}
.vfa-quote-icon {
    color: #ced4da;
    margin-right: 6px;
    font-size: 10px;
}

.vfa-card-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    font-size: 11.5px;
    color: #adb5bd;
}
.vfa-card-meta i {
    margin-right: 4px;
}
.vfa-card-meta b {
    color: #868e96;
}

.vfa-card-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-end; /* Tambahan: agar tombol & nominal rata kanan */
    gap: 8px;
    flex-shrink: 0;
}
.vfa-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    border: none;
    border-radius: 8px;
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: .15s;
    white-space: nowrap;
}
.vfa-btn-approve {
    background: #ebfbee;
    color: #2b8a3e;
}
.vfa-btn-approve:hover {
    background: #d3f9d8;
}
.vfa-btn-reject {
    background: #fff5f5;
    color: #c92a2a;
}
.vfa-btn-reject:hover {
    background: #ffe3e3;
}
.vfa-btn-ghost {
    background: #f1f3f5;
    color: #495057;
    text-decoration: none;
}
.vfa-btn-ghost:hover {
    background: #e9ecef;
    color: #212529;
    text-decoration: none;
}

/* ===== Mobile ===== */
@media (max-width: 768px) {
    .vfa-page-wrap { margin: 12px auto 20px; }
    .vfa-canvas-header { padding: 14px 16px; }
    .vfa-canvas-body { padding: 14px; }

    .vfa-card {
        flex-direction: column;
        align-items: stretch;
        border-left: none;
        border-top: 3px solid #dc2626;
    }
    .vfa-amount {
        width: 100%;
        text-align: left;
        margin-bottom: 6px;
    }
    .vfa-card-actions {
        flex-direction: row;
        flex-wrap: wrap; /* Tambahan agar responsif jika meluber */
        margin-top: 4px;
        align-items: center; 
    }
    .vfa-card-actions .vfa-btn {
        flex: 1;
    }
}
</style>

@endsection

@include('superuser.asset.plugin.swal2')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript">
    $(document).ready(function () {

        let approveUrlTpl = "{{ route('superuser.finance.void.approve', ['id' => '__ID__']) }}";
        let rejectUrlTpl  = "{{ route('superuser.finance.void.reject', ['id' => '__ID__']) }}";

        $(document).on('click', '.btn-approve-void', function () {
            $('#void_action_id').val($(this).data('id'));
            $('#void_action_type').val('approve');
            $('#modalVoidActionTitle').text('Approve Void - ' + $(this).data('code'));
            $('#modalVoidActionInfo').html('DO ini akan di-<b>void permanen</b>: stok fisik dikembalikan, invoice dibatalkan, SO ditandai batal. Aksi ini tidak bisa dibatalkan.');
            $('#void_action_reason_label').text('Alasan Approval');
            $('#void_action_reason').val('');
            $('#btn-submit-void-action').removeClass('btn-danger').addClass('btn-success').text('Ya, Approve Void');
            $('#modalVoidAction').modal('show');
        });

        $(document).on('click', '.btn-reject-void', function () {
            $('#void_action_id').val($(this).data('id'));
            $('#void_action_type').val('reject');
            $('#modalVoidActionTitle').text('Reject Void - ' + $(this).data('code'));
            $('#modalVoidActionInfo').html('DO akan <b>dikembalikan ke kondisi normal</b> (status Delivering seperti semula), tidak ada perubahan stok/invoice/SO.');
            $('#void_action_reason_label').text('Alasan Penolakan');
            $('#void_action_reason').val('');
            $('#btn-submit-void-action').removeClass('btn-success').addClass('btn-danger').text('Ya, Reject');
            $('#modalVoidAction').modal('show');
        });

        $('#btn-submit-void-action').on('click', function () {
            var id = $('#void_action_id').val();
            var type = $('#void_action_type').val();
            var reason = $('#void_action_reason').val().trim();

            if (!reason || reason.length < 5) {
                Swal.fire('Error', 'Alasan wajib diisi (minimal 5 karakter).', 'error');
                return;
            }

            var url = (type === 'approve' ? approveUrlTpl : rejectUrlTpl).replace('__ID__', id);

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    approval_reason: reason
                },
                success: function (res) {
                    Swal.fire({
                        icon: res.status === 'success' ? 'success' : 'error',
                        title: res.status === 'success' ? 'Berhasil' : 'Gagal',
                        text: res.message,
                        timer: 2500,
                        showConfirmButton: false
                    }).then(function () {
                        if (res.status === 'success') {
                            $('#modalVoidAction').modal('hide');
                            window.location.reload();
                        }
                    });
                },
                error: function () {
                    Swal.fire('Error', 'Terjadi kesalahan saat memproses request!', 'error');
                }
            });
        });
    });
</script>
@endpush