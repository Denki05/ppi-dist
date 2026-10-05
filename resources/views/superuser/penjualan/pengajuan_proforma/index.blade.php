@extends('superuser.app')

@push('styles')
<style>
.crm-wrapper { max-width: 992px; margin: auto; }

/* Tab + search bar sejajar */
.toolbar {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 14px;
}
.workflow-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    flex-shrink: 0;
}
.menu-tab {
    border: 1px solid #dce1e7;
    background: #fff;
    padding: 5px 14px;
    border-radius: 20px;
    font-size: .83rem;
    font-weight: 500;
    cursor: pointer;
    transition: all .15s;
    white-space: nowrap;
}
.menu-tab:hover  { background: #f0f2f8; }
.menu-tab.active { background: #4e73df; border-color: #4e73df; color: #fff; }

/* Search sejajar, dorong ke kiri sisa ruang */
.toolbar-search { flex: 1; min-width: 200px; max-width: 280px; }

/* Tabel */
.table th { font-size: .75rem; text-transform: uppercase; letter-spacing: .4px; color: #858796; }
.table td { vertical-align: middle; font-size: .875rem; }

/* Tombol aksi inline */
.btn-verif  { font-size:.75rem; padding:3px 9px; border-radius:5px; }
.btn-tolak  { font-size:.75rem; padding:3px 9px; border-radius:5px; }
</style>
@endpush

@section('content')
<div class="crm-wrapper">

    <div class="mb-3">
        <h5 class="mb-0 font-weight-bold">Antrian Pengajuan Proforma</h5>
        <small class="text-muted">Verifikasi data calon customer dari modul AO</small>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa fa-check-circle mr-1"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fa fa-exclamation-circle mr-1"></i>{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">

            {{-- ── Toolbar: tab kiri + search kanan ── --}}
            <div class="toolbar">

                <div class="workflow-tabs">
                    <button class="menu-tab {{ request('status','menunggu') === 'menunggu' ? 'active' : '' }}"
                        onclick="gotoTab('menunggu')">
                        ⏳ Menunggu
                        @if($counts['menunggu'] ?? 0)
                            <span class="badge badge-{{ request('status','menunggu')==='menunggu' ? 'light text-dark' : 'warning' }} ml-1">
                                {{ $counts['menunggu'] }}
                            </span>
                        @endif
                    </button>
                    <button class="menu-tab {{ request('status') === 'disetujui' ? 'active' : '' }}"
                        onclick="gotoTab('disetujui')">✅ Disetujui</button>
                    <button class="menu-tab {{ request('status') === 'ditolak' ? 'active' : '' }}"
                        onclick="gotoTab('ditolak')">❌ Ditolak</button>
                    <button class="menu-tab {{ request('status') === 'dibatalkan' ? 'active' : '' }}"
                        onclick="gotoTab('dibatalkan')">🚫 Dibatalkan</button>
                </div>

                {{-- Search sejajar kiri setelah tab --}}
                <form method="GET" class="toolbar-search mb-0" id="frmSearch">
                    <input type="hidden" name="status" value="{{ request('status','menunggu') }}" id="searchStatus">
                    <div class="input-group input-group-sm">
                        <input type="text" name="q" id="searchQ" value="{{ request('q') }}"
                            class="form-control" placeholder="Nama / HP / no. estimate...">
                        <div class="input-group-append">
                            <button class="btn btn-primary px-3" type="submit"><i class="fa fa-search"></i></button>
                            @if(request('q'))
                                <a href="{{ route('superuser.penjualan.pengajuan_proforma.index', ['status'=>request('status','menunggu')]) }}"
                                   class="btn btn-outline-secondary px-2">×</a>
                            @endif
                        </div>
                    </div>
                </form>

            </div>{{-- /toolbar --}}

            {{-- ── Tabel ── --}}
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th width="36">#</th>
                            <th>No. Estimate</th>
                            <th>Prospek</th>
                            <th>No. HP</th>
                            <th>AO PIC</th>
                            <th>Tgl Masuk</th>
                            <th width="160" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td class="text-muted">
                                {{ $loop->iteration + ($items->currentPage()-1) * $items->perPage() }}
                            </td>
                            <td>
                                <span class="font-weight-bold text-primary">{{ $item->estimate_number }}</span>
                            </td>
                            <td>
                                <div class="font-weight-bold" style="line-height:1.2">{{ $item->prospect_name }}</div>
                                @if($item->perusahaan && $item->perusahaan !== $item->prospect_name)
                                    <small class="text-muted">{{ $item->perusahaan }}</small><br>
                                @endif
                                @if($item->zone || $item->kategori)
                                    <small class="text-muted">{{ $item->zone ?: '-' }} &middot; {{ $item->kategori ?: '-' }}</small>
                                @endif
                            </td>
                            <td>{{ $item->phone ?: '-' }}</td>
                            <td>
                                @if($item->ao_pic)
                                    <span class="badge badge-light border" style="font-size:.75rem">{{ $item->ao_pic }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-' }}
                                </small>
                            </td>
                            <td class="text-center">
                                {{-- Detail --}}
                                <a href="{{ route('superuser.penjualan.pengajuan_proforma.show', $item->id) }}"
                                   class="btn btn-verif btn-outline-secondary" title="Lihat Detail">
                                    <i class="fa fa-eye"></i>
                                </a>

                                @if($item->status === 'menunggu')

                                {{-- Mutasi langsung --}}
                                <form method="POST"
                                      action="{{ route('superuser.penjualan.pengajuan_proforma.verifikasi', $item->id) }}"
                                      class="d-inline" id="frmV{{ $item->id }}">
                                    @csrf
                                    <button type="button"
                                        class="btn btn-verif btn-success ml-1"
                                        title="Verifikasi & Mutasi"
                                        onclick="doMutasi({{ $item->id }}, '{{ addslashes($item->prospect_name) }}')">
                                        <i class="fa fa-check"></i> Mutasi
                                    </button>
                                </form>

                                {{-- Tolak langsung --}}
                                <form method="POST"
                                      action="{{ route('superuser.penjualan.pengajuan_proforma.tolak', $item->id) }}"
                                      class="d-inline" id="frmT{{ $item->id }}">
                                    @csrf
                                    <input type="hidden" name="catatan" id="alasanT{{ $item->id }}">
                                    <button type="button"
                                        class="btn btn-tolak btn-outline-danger ml-1"
                                        title="Tolak"
                                        onclick="doTolak({{ $item->id }}, '{{ addslashes($item->prospect_name) }}')">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </form>

                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                Tidak ada pengajuan dengan status
                                <strong>{{ request('status','menunggu') }}</strong>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($items->total() > $items->perPage())
            <div class="mt-3">
                {{ $items->appends(request()->query())->links() }}
            </div>
            @endif

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function gotoTab(status) {
    var q = document.getElementById('searchQ') ? document.getElementById('searchQ').value : '';
    var base = '{{ route('superuser.penjualan.pengajuan_proforma.index') }}';
    window.location = base + '?status=' + status + (q ? '&q=' + encodeURIComponent(q) : '');
}

function doMutasi(id, nama) {
    if (!confirm('Verifikasi & mutasi customer "' + nama + '"?')) return;
    document.getElementById('frmV' + id).submit();
}

function doTolak(id, nama) {
    var alasan = prompt('Alasan penolakan untuk "' + nama + '":');
    if (alasan === null) return;
    if (!alasan.trim()) { alert('Alasan tidak boleh kosong.'); return; }
    document.getElementById('alasanT' + id).value = alasan;
    document.getElementById('frmT' + id).submit();
}
</script>
@endpush
