@extends('superuser.app')

@section('content')

@php
    $aktifTipe = request('type');
    $aktifStatus = request('status');

    $tipeChips = [
        '' => ['label' => 'Semua', 'icon' => 'bi-collection'],
        'App\\Notifications\\SoNotification' => ['label' => 'SO', 'icon' => 'bi-cart'],
        'App\\Notifications\\DoNotification' => ['label' => 'DO', 'icon' => 'bi-truck'],
        'App\\Notifications\\PayableNotification' => ['label' => 'Payment', 'icon' => 'bi-credit-card'],
        'App\\Notifications\\InternalRevisionOtpNotification' => ['label' => 'OTP Revisi', 'icon' => 'bi-key'],
    ];

    $judulNotif = function ($n) {
        $data = $n['data'] ?? [];
        if (strpos($n['type'], 'SoNotification') !== false) {
            $label = (($data['kind'] ?? '') === 'proforma') ? 'SO Proforma' : 'SO Lanjutan';
        } elseif (strpos($n['type'], 'DoNotification') !== false) {
            $label = 'Delivery Order';
        } elseif (strpos($n['type'], 'PayableNotification') !== false) {
            $label = 'Payment';
        } elseif (strpos($n['type'], 'ReceivingNotification') !== false) {
            $label = 'Receiving';
        } elseif (strpos($n['type'], 'InternalRevisionOtp') !== false) {
            $label = 'OTP Revisi Internal';
        } else {
            $label = 'Notifikasi';
        }
        $code = $data['code'] ?? null;
        return $code ? $label . ' · ' . $code : $label;
    };

    $subjudulNotif = function ($n) {
        $data = $n['data'] ?? [];
        if (strpos($n['type'], 'InternalRevisionOtp') !== false) {
            return trim(($data['otp'] ?? '') . '  ' . ($data['message'] ?? ''));
        }
        $parts = [];
        if (!empty($data['customer'])) $parts[] = $data['customer'];
        if (!empty($data['customer_kota'])) $parts[] = $data['customer_kota'];
        if (!empty($data['type_transaction'])) $parts[] = $data['type_transaction'];
        return implode(' · ', $parts);
    };
@endphp

<style>
    .notif-page .notif-head { display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:10px; }
    .notif-page .notif-title { font-size:18px; font-weight:700; margin:0; }
    .notif-page .notif-sub { font-size:12px; color:#8a939d; margin-top:2px; }
    .notif-page .notif-chips { display:flex; flex-wrap:wrap; gap:6px; }
    .notif-page .chip { display:inline-flex; align-items:center; gap:5px; padding:5px 10px; border:1px solid #e3e7ec; border-radius:20px; font-size:12px; color:#5b6470; background:#fff; text-decoration:none; }
    .notif-page .chip:hover { border-color:#b9c6d6; color:#2b3a4b; }
    .notif-page .chip.on { background:#0d6efd; border-color:#0d6efd; color:#fff; }
    .notif-page .chip.on-warn { background:#f59f00; border-color:#f59f00; color:#fff; }
    .notif-page .notif-list { border-top:1px solid #eef1f4; }
    .notif-page .notif-date { font-size:11px; text-transform:uppercase; letter-spacing:.4px; color:#98a2ad; padding:14px 8px 6px; background:#fafbfc; }
    .notif-page .notif-row { display:flex; align-items:flex-start; gap:10px; padding:12px 8px; border-bottom:1px solid #f1f3f5; background:#fff; cursor:pointer; transition:background .15s ease; }
    .notif-page .notif-row:hover { background:#f7f9fc; }
    .notif-page .notif-row.unread { background:#f2f7ff; box-shadow:inset 3px 0 0 #0d6efd; }
    .notif-page .notif-row .ico { width:34px; height:34px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex:0 0 34px; background:#eef2f7; color:#4a5563; }
    .notif-page .notif-row.unread .ico { background:#e0edff; color:#0d6efd; }
    .notif-page .notif-row .ttl { font-size:13.5px; font-weight:600; color:#222d3a; }
    .notif-page .notif-row .desc { font-size:12px; color:#78828d; margin-top:2px; }
    .notif-page .notif-row .when { font-size:11px; color:#9aa4af; margin-top:4px; }
    .notif-page .notif-row .right { margin-left:auto; display:flex; align-items:center; gap:8px; padding-top:2px; }
    .notif-page .dot { width:8px; height:8px; border-radius:50%; background:#0d6efd; }
    .notif-page .read-btn { border:1px solid #dfe4ea; background:#fff; color:#6c7681; border-radius:6px; width:26px; height:26px; display:inline-flex; align-items:center; justify-content:center; }
    .notif-page .read-btn:hover { background:#eef2f7; color:#0d6efd; }
    .notif-page .pager { display:flex; justify-content:space-between; align-items:center; gap:10px; padding:12px 4px; flex-wrap:wrap; font-size:12px; color:#8a939d; }
    .notif-page .pager a, .notif-page .pager span.pg { padding:4px 9px; border:1px solid #e3e7ec; border-radius:6px; color:#4a5563; text-decoration:none; background:#fff; }
    .notif-page .pager a:hover { border-color:#b9c6d6; }
    .notif-page .pager .off { opacity:.45; }
    .notif-page .empty { text-align:center; padding:56px 10px; color:#98a2ad; }
    .notif-page .empty i { font-size:34px; display:block; margin-bottom:8px; }
</style>

<div class="notif-page">

    <div class="notif-head mb-3">
        <div>
            <h4 class="notif-title"><i class="bi bi-bell me-1"></i> Notifikasi</h4>
            <div class="notif-sub">
                {{ $stats['unread'] }} belum dibaca · {{ $stats['total'] }} total
            </div>
        </div>
        @if($stats['unread'] > 0)
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnMarkAllRead">
            <i class="bi bi-check2-all me-1"></i> Tandai semua dibaca
        </button>
        @endif
    </div>

    <div class="notif-chips mb-3">
        @foreach($tipeChips as $tipe => $chip)
            @php
                $params = [];
                if ($tipe !== '') $params['type'] = $tipe;
                if ($aktifStatus) $params['status'] = $aktifStatus;
            @endphp
            <a href="{{ route('superuser.penjualan.notification.index', $params) }}"
               class="chip {{ $aktifTipe === $tipe ? 'on' : '' }}">
                <i class="bi {{ $chip['icon'] }}"></i> {{ $chip['label'] }}
            </a>
        @endforeach

        @php
            $unreadParams = ['status' => 'unread'];
            if ($aktifTipe) $unreadParams['type'] = $aktifTipe;
        @endphp
        <a href="{{ route('superuser.penjualan.notification.index', $unreadParams) }}"
           class="chip {{ $aktifStatus === 'unread' ? 'on-warn' : '' }}">
            <i class="bi bi-dot"></i> Belum dibaca
        </a>
    </div>

    <div class="notif-list">
        @php $lastDate = null; @endphp
        @forelse($notifications as $n)
            @php
                $created = $n['created_at'] ? \Illuminate\Support\Carbon::parse($n['created_at']) : null;
                if (!$created) {
                    $dateLabel = 'Tanpa tanggal';
                } elseif ($created->isToday()) {
                    $dateLabel = 'Hari ini';
                } elseif ($created->isYesterday()) {
                    $dateLabel = 'Kemarin';
                } else {
                    $dateLabel = $created->format('d M Y');
                }
            @endphp

            @if($dateLabel !== $lastDate)
                <div class="notif-date">{{ $dateLabel }}</div>
                @php $lastDate = $dateLabel; @endphp
            @endif

            <div class="notif-row {{ $n['is_unread'] ? 'unread' : '' }}"
                 data-href="{{ route('superuser.penjualan.notification.read', $n['id']) }}"
                 role="link" tabindex="0">
                <div class="ico"><i class="bi {{ $n['icon'] }}"></i></div>
                <div class="flex-grow-1">
                    <div class="ttl">{{ $judulNotif($n) }}</div>
                    @php $sub = $subjudulNotif($n); @endphp
                    @if($sub !== '')
                        <div class="desc">{{ $sub }}</div>
                    @endif
                    <div class="when" title="{{ $created ? $created->format('d M Y H:i') : '' }}">
                        {{ $n['human'] ?: ($created ? $created->format('d M Y H:i') : '') }}
                    </div>
                </div>
                <div class="right">
                    @if($n['is_unread'])
                        <span class="dot"></span>
                        <form method="POST" action="{{ route('superuser.penjualan.notification.mark_as_read_only', $n['id']) }}" class="m-0">
                            @csrf
                            <button type="submit" class="read-btn" title="Tandai dibaca">
                                <i class="bi bi-check2"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="empty">
                <i class="bi bi-bell-slash"></i>
                <div>Belum ada notifikasi.</div>
            </div>
        @endforelse
    </div>

    @if($notifications->lastPage() > 1)
    <div class="pager">
        <div>
            Menampilkan {{ $notifications->firstItem() }}–{{ $notifications->lastItem() }}
            dari {{ $notifications->total() }}
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($notifications->onFirstPage())
                <span class="pg off">‹ Sebelumnya</span>
            @else
                <a href="{{ $notifications->previousPageUrl() }}">‹ Sebelumnya</a>
            @endif
            <span class="pg">{{ $notifications->currentPage() }} / {{ $notifications->lastPage() }}</span>
            @if($notifications->hasMorePages())
                <a href="{{ $notifications->nextPageUrl() }}">Berikutnya ›</a>
            @else
                <span class="pg off">Berikutnya ›</span>
            @endif
        </div>
    </div>
    @endif

</div>

@endsection

@push('scripts')
<script>
(function () {
    document.querySelectorAll('.notif-row[data-href]').forEach(function (row) {
        row.addEventListener('click', function (e) {
            if (e.target.closest('form')) return;
            window.location.href = row.dataset.href;
        });
        row.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                window.location.href = row.dataset.href;
            }
        });
    });

    var markAll = document.getElementById('btnMarkAllRead');
    if (markAll) {
        markAll.addEventListener('click', function () {
            if (!confirm('Tandai semua notifikasi sebagai sudah dibaca?')) return;

            fetch('{{ route("superuser.penjualan.notification.unread_all_notif") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            }).then(function (res) {
                if (!res.ok) throw new Error('Gagal');
                window.location.reload();
            }).catch(function () {
                alert('Gagal menandai notifikasi.');
            });
        });
    }
})();
</script>
@endpush
