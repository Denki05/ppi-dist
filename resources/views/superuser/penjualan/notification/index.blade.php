@extends('superuser.app')

@section('content')

<div class="row mb-30">
    <div class="col-md-6">
        <h4 style="font-weight: bold;">
            <i class="fa fa-bell mr-2"></i> Semua Notifikasi
        </h4>
        <p class="text-muted mb-0" style="font-size:13px;">
            {{ $stats['unread'] }} belum dibaca
        </p>
    </div>
    <div class="col-md-6 text-right">
        @if($stats['unread'] > 0)
        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnMarkAllRead">
            <i class="fa fa-check-double mr-1"></i> Tandai Semua Dibaca
        </button>
        @endif
    </div>
</div>

<!-- Filter Tabs -->
<div class="mb-3">
    <div class="btn-group btn-group-sm" role="group">
        <a href="{{ route('superuser.penjualan.notification.index') }}" 
           class="btn {{ !$typeFilter ? 'btn-primary' : 'btn-outline-primary' }}">
            Semua
        </a>
        <a href="{{ route('superuser.penjualan.notification.index', ['type' => 'App\\Notifications\\DoNotification']) }}" 
           class="btn {{ $typeFilter === 'App\\Notifications\\DoNotification' ? 'btn-info' : 'btn-outline-info' }}">
            <i class="fa fa-truck"></i> DO
        </a>
        <a href="{{ route('superuser.penjualan.notification.index', ['type' => 'App\\Notifications\\SoNotification']) }}" 
           class="btn {{ $typeFilter === 'App\\Notifications\\SoNotification' ? 'btn-success' : 'btn-outline-success' }}">
            <i class="fa fa-shopping-cart"></i> SO
        </a>
        <a href="{{ route('superuser.penjualan.notification.index', ['type' => 'App\\Notifications\\PayableNotification']) }}" 
           class="btn {{ $typeFilter === 'App\\Notifications\\PayableNotification' ? 'btn-secondary' : 'btn-outline-secondary' }}">
            <i class="fa fa-credit-card"></i> Payment
        </a>
    </div>
</div>

<!-- Notifications List -->
<div class="block">
    <div class="block-content block-content-full">
        @forelse($notifications as $notif)
            @php
                $data = json_decode($notif->data, true);
                $isUnread = is_null($notif->read_at);
                $typeLabel = match(true) {
                    str_contains($notif->type ?? '', 'DoNotification') => ['DO', 'fa-truck', 'info'],
                    str_contains($notif->type ?? '', 'SoNotification') => ['SO', 'fa-shopping-cart', 'success'],
                    str_contains($notif->type ?? '', 'PayableNotification') => ['Payment', 'fa-credit-card', 'secondary'],
                    default => ['Notif', 'fa-bell', 'primary'],
                };
            @endphp

            <div class="d-flex align-items-start p-3 {{ $isUnread ? 'bg-light' : '' }}">
                
                <!-- Icon -->
                <div class="mr-3">
                    <span class="btn btn-{{ $typeLabel[2] }} btn-sm" style="border-radius:50%; width:40px; height:40px; display:flex; align-items:center; justify-content:center;">
                        <i class="fa {{ $typeLabel[1] }}"></i>
                    </span>
                </div>

                <!-- Content -->
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="badge badge-{{ $typeLabel[2] }} mr-2">{{ $typeLabel[0] }}</span>
                            @if($isUnread)
                                <span class="badge badge-primary">New</span>
                            @endif
                        </div>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}</small>
                    </div>

                    <!-- Regular Notification -->
                    <div class="mt-1">
                        @if(isset($data['code']))
                            <strong>{{ $data['code'] }}</strong>
                        @endif
                        @if(isset($data['customer']))
                            <span class="text-muted ml-2">{{ $data['customer'] }}</span>
                        @endif
                        @if(isset($data['customer_kota']))
                            <span class="text-muted">({{ $data['customer_kota'] }})</span>
                        @endif
                    </div>
                    <div class="mt-1">
                        @if(str_contains($notif->type ?? '', 'DoNotification'))
                            @if(isset($data['status']) && $data['status'] == 2)
                                <a href="{{ route('superuser.penjualan.delivery_order.detail', $data['id'] ?? 0) }}" 
                                   class="text-primary" style="font-size:13px;">
                                    Lihat Detail DO →
                                </a>
                            @endif
                        @elseif(str_contains($notif->type ?? '', 'SoNotification'))
                            <a href="{{ route('superuser.penjualan.sales_order.index_lanjutan') }}" 
                               class="text-primary" style="font-size:13px;">
                                Lihat SO →
                            </a>
                        @elseif(str_contains($notif->type ?? '', 'PayableNotification'))
                            <a href="{{ route('superuser.finance.payable.index') }}" 
                               class="text-primary" style="font-size:13px;">
                                Lihat Payable →
                            </a>
                        @endif
                    </div>
                    @if($isUnread)
                    <div class="mt-1">
                        @if(str_contains($notif->type ?? '', 'DoNotification'))
                            <form action="{{ route('superuser.penjualan.notification.mark_as_read_do', ['id' => $notif->id, 'do' => $data['id'] ?? 0]) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-outline-secondary">
                                    <i class="fa fa-check"></i> Dibaca
                                </button>
                            </form>
                        @elseif(str_contains($notif->type ?? '', 'SoNotification'))
                            <form action="{{ route('superuser.penjualan.notification.mark_as_read_so', $notif->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-outline-secondary">
                                    <i class="fa fa-check"></i> Dibaca
                                </button>
                            </form>
                        @elseif(str_contains($notif->type ?? '', 'PayableNotification'))
                            <form action="{{ route('superuser.penjualan.notification.mark_as_read_payable', $notif->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-outline-secondary">
                                    <i class="fa fa-check"></i> Dibaca
                                </button>
                            </form>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            <hr class="my-0" style="border-color: #f1f3f5;">
        @empty
            <div class="text-center py-5 text-muted">
                <i class="fa fa-bell-slash fa-3x mb-3 d-block"></i>
                <p>Tidak ada notifikasi.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Pagination -->
@if($notifications->hasPages())
<div class="d-flex justify-content-center">
    {{ $notifications->links() }}
</div>
@endif

@endsection

@push('scripts')
<script>
$('#btnMarkAllRead').on('click', function() {
    if (!confirm('Tandai semua notifikasi sebagai sudah dibaca?')) return;
    
    $.post('{{ route("superuser.penjualan.notification.unread_all_notif") }}', {
        _token: '{{ csrf_token() }}'
    })
    .done(function() {
        window.location.reload();
    })
    .fail(function() {
        alert('Gagal menandai notifikasi.');
    });
});
</script>
@endpush
