@extends('superuser.app')

@section('content')

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
            <span class="vfa-canvas-icon"><i class="fas fa-history"></i></span>
            <div>
                <h4>History Void DO</h4>
                <small>Pengajuan void yang sudah di-approve atau di-reject</small>
            </div>
        </div>
        <a href="{{ route('superuser.finance.void.index') }}" class="vfa-btn vfa-btn-ghost">
            <i class="fa fa-clock"></i> Lihat Pending
        </a>
    </div>

    <div class="vfa-canvas-body">
        @if($requests->count() === 0)
            <div class="vfa-empty">
                <i class="fa fa-inbox"></i>
                <p>Belum ada history void yang diproses.</p>
            </div>
        @else
            <div class="vfa-list">
                @foreach($requests as $r)
                <div class="vfa-card vfa-card-{{ $r->status == 2 ? 'approved' : 'rejected' }}">
                    <div class="vfa-card-main">
                        <div class="vfa-card-topline">
                            <span class="vfa-code">{{ $r->do_code ?? '-' }}</span>
                            @if($r->invoice_code)
                                <span class="vfa-sep">&middot;</span>
                                <span class="vfa-invoice">{{ $r->invoice_code }}</span>
                            @endif
                            @if($r->status == 2)
                                <span class="vfa-badge-approved"><i class="fa fa-check"></i> Approved</span>
                            @else
                                <span class="vfa-badge-rejected"><i class="fa fa-times"></i> Rejected</span>
                            @endif
                        </div>

                        <div class="vfa-card-customer">
                            <i class="fa fa-user"></i> {{ $r->customer_name }}
                        </div>

                        <div class="vfa-card-reason">
                            <i class="fa fa-quote-left vfa-quote-icon"></i>
                            <b>Alasan pengajuan:</b> {{ $r->request_reason }}
                        </div>

                        <div class="vfa-card-reason">
                            <i class="fa fa-quote-left vfa-quote-icon"></i>
                            <b>Alasan {{ $r->status == 2 ? 'approval' : 'penolakan' }}:</b> {{ $r->approval_reason }}
                        </div>

                        <div class="vfa-card-meta">
                            <span><i class="fa fa-user-edit"></i> Diajukan <b>{{ $r->requested_by_name ?? 'Unknown' }}</b> - {{ \Carbon\Carbon::parse($r->requested_at)->format('d M Y, H:i') }}</span>
                            <span><i class="fa fa-user-check"></i> Diproses <b>{{ $r->approved_by_name ?? 'Unknown' }}</b> - {{ $r->approved_at ? \Carbon\Carbon::parse($r->approved_at)->format('d M Y, H:i') : '-' }}</span>
                        </div>
                    </div>

                    <div class="vfa-card-actions">
                        <span class="vfa-amount">Rp {{ number_format($r->grand_total_idr ?? 0, 0, ',', '.') }}</span>

                        @if($r->status == 2 && $r->invoice_id)
                            <a href="{{ route('superuser.finance.void.print_invoice', $r->do_id) }}" target="_blank" class="vfa-btn vfa-btn-download">
                                <i class="fa fa-file-pdf"></i> Invoice (Void)
                            </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
</div>

<style>
.vfa-page-wrap { max-width: 1100px; margin: 20px auto 32px; }
.vfa-canvas { background: #fff; border-radius: 14px; box-shadow: 0 1px 6px rgba(0,0,0,.06); overflow: hidden; }
.vfa-canvas-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; padding: 16px 20px; border-bottom: 1px solid #f1f3f5; }
.vfa-canvas-title { display: flex; align-items: center; gap: 12px; }
.vfa-canvas-icon { width: 38px; height: 38px; border-radius: 10px; background: #eef2ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
.vfa-canvas-title h4 { margin: 0; font-size: 17px; font-weight: 700; color: #212529; }
.vfa-canvas-title small { display: block; font-size: 12px; color: #adb5bd; margin-top: 1px; }
.vfa-canvas-body { padding: 18px 20px; }

.vfa-empty { text-align: center; padding: 56px 20px; color: #adb5bd; }
.vfa-empty i { font-size: 32px; margin-bottom: 12px; display: block; opacity: .5; }
.vfa-empty p { margin: 0; font-size: 13.5px; }

.vfa-list { display: flex; flex-direction: column; gap: 12px; }

.vfa-card { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; background: #fff; border: 1px solid #f1f3f5; border-radius: 12px; padding: 14px 16px; transition: box-shadow .15s ease; }
.vfa-card:hover { box-shadow: 0 2px 10px rgba(0,0,0,.06); }
.vfa-card-approved { border-left: 3px solid #2b8a3e; }
.vfa-card-rejected { border-left: 3px solid #adb5bd; }

.vfa-card-main { flex: 1; min-width: 0; }
.vfa-card-topline { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 6px; }
.vfa-code { font-size: 14px; font-weight: 700; color: #212529; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }
.vfa-sep { color: #ced4da; }
.vfa-invoice { font-size: 12.5px; color: #495057; background: #f1f3f5; padding: 2px 9px; border-radius: 20px; }

.vfa-badge-approved { font-size: 11.5px; font-weight: 700; color: #2b8a3e; background: #ebfbee; padding: 3px 10px; border-radius: 20px; }
.vfa-badge-rejected { font-size: 11.5px; font-weight: 700; color: #495057; background: #f1f3f5; padding: 3px 10px; border-radius: 20px; }

.vfa-card-customer { font-size: 13px; color: #495057; margin-bottom: 8px; }
.vfa-card-customer i { color: #adb5bd; margin-right: 5px; }

.vfa-card-reason { font-size: 12.5px; color: #6c757d; background: #f8f9fb; border-radius: 8px; padding: 8px 11px; margin-bottom: 6px; line-height: 1.5; }
.vfa-quote-icon { color: #ced4da; margin-right: 6px; font-size: 10px; }

.vfa-card-meta { display: flex; flex-direction: column; gap: 4px; font-size: 11.5px; color: #adb5bd; margin-top: 6px; }
.vfa-card-meta i { margin-right: 4px; }
.vfa-card-meta b { color: #868e96; }

.vfa-card-actions { display: flex; flex-direction: column; align-items: flex-end; gap: 8px; flex-shrink: 0; }
.vfa-amount { font-size: 14px; font-weight: 700; color: #212529; }

.vfa-btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; border: none; border-radius: 8px; padding: 8px 16px; font-size: 13px; font-weight: 600; cursor: pointer; transition: .15s; white-space: nowrap; text-decoration: none; }
.vfa-btn-ghost { background: #f1f3f5; color: #495057; }
.vfa-btn-ghost:hover { background: #e9ecef; color: #212529; text-decoration: none; }
.vfa-btn-download { background: #eef2ff; color: #4f46e5; }
.vfa-btn-download:hover { background: #e0e7ff; color: #4338ca; text-decoration: none; }

@media (max-width: 768px) {
    .vfa-page-wrap { margin: 12px auto 20px; }
    .vfa-canvas-header { padding: 14px 16px; }
    .vfa-canvas-body { padding: 14px; }
    .vfa-card { flex-direction: column; align-items: stretch; }
    .vfa-card-actions { flex-direction: row; align-items: center; margin-top: 8px; }
}
</style>

@endsection