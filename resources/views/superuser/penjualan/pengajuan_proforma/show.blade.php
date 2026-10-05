@extends('superuser.app')

@push('styles')
<style>
/* ── Canvas ── */
.crm-wrapper { max-width: 992px; margin: auto; }

/* ── Page header ── */
.pp-header { margin-bottom: 20px; }
.pp-no { font-size: 1.35rem; font-weight: 700; color: #2d3748; }
.pp-meta { font-size: .82rem; color: #a0aec0; margin-top: 3px; }

/* ── Card ── */
.pp-card { background:#fff; border:1px solid #e8ecf0; border-radius:10px; overflow:hidden; margin-bottom:16px; }
.pp-card-hd {
    padding:10px 18px; background:#f8fafc; border-bottom:1px solid #e8ecf0;
    font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.8px; color:#718096;
    display:flex; align-items:center; gap:6px;
}

/* ── 3-column info grid ── */
.info-3col { display:grid; grid-template-columns:1fr 1fr 1fr; gap:0; }
.info-3col .col-divider { border-right:1px solid #f1f5f9; }
.info-3col .col-divider:last-child { border-right:none; }
.info-col { padding:16px 18px; }
.info-col-title { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#a0aec0; margin-bottom:10px; }

.info-row { display:flex; flex-direction:column; padding:5px 0; border-bottom:1px solid #f8fafc; }
.info-row:last-child { border-bottom:none; }
.info-lbl { font-size:.72rem; color:#a0aec0; margin-bottom:1px; }
.info-val { font-size:.875rem; color:#2d3748; font-weight:500; word-break:break-word; }
.info-val.big { font-size:1rem; font-weight:700; color:#1a202c; }

/* ── Identitas kepala ── */
.prospect-head { padding:14px 18px 0; display:flex; align-items:center; gap:12px; }
.prospect-avatar {
    width:44px; height:44px; border-radius:50%; background:#e8f0fe;
    display:flex; align-items:center; justify-content:center;
    font-size:1.1rem; font-weight:700; color:#4e73df; flex-shrink:0;
}
.prospect-name { font-size:1.05rem; font-weight:700; color:#1a202c; line-height:1.2; }
.prospect-sub { font-size:.8rem; color:#718096; }

/* ── Dokumen inline ── */
.doc-row { display:flex; align-items:center; gap:8px; padding:6px 0; border-bottom:1px solid #f8fafc; }
.doc-row:last-child { border-bottom:none; }
.doc-lbl { font-size:.72rem; color:#a0aec0; width:70px; flex-shrink:0; }
.doc-pill {
    display:inline-flex; align-items:center; gap:4px; font-size:.75rem; font-weight:600;
    padding:3px 10px; border-radius:20px;
}
.doc-pill.ada   { background:#dcfce7; color:#16a34a; }
.doc-pill.belum { background:#f1f5f9; color:#94a3b8; }

/* ── Action row ── */
.action-row { padding:14px 18px; background:#f8fafc; border-top:1px solid #e8ecf0; display:flex; gap:10px; align-items:center; flex-wrap:wrap; }

/* ── Hasil mutasi ── */
.mutasi-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; padding:16px 18px; }
.mutasi-box { background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:10px 14px; }
.mutasi-box .m-lbl { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#16a34a; margin-bottom:2px; }
.mutasi-box .m-val { font-size:.95rem; font-weight:700; color:#166534; }

/* ── Status badge ── */
.status-pill { padding:5px 16px; border-radius:50px; font-size:.8rem; font-weight:600; white-space:nowrap; }
</style>
@endpush

@section('content')
<div class="crm-wrapper">

    {{-- Breadcrumb --}}
    <a href="{{ route('superuser.penjualan.pengajuan_proforma.index') }}" class="text-muted small d-inline-block mb-3">
        <i class="fa fa-arrow-left mr-1"></i>Kembali ke Antrian
    </a>

    {{-- Page header --}}
    @php
        $badgeMap   = ['menunggu'=>['warning','#f6c23e','#333'], 'disetujui'=>['success','#1cc88a','#fff'], 'ditolak'=>['danger','#e74a3b','#fff'], 'dibatalkan'=>['secondary','#858796','#fff']];
        $bStyle     = $badgeMap[$pengajuan->status] ?? ['secondary','#858796','#fff'];
    @endphp
    <div class="pp-header d-flex justify-content-between align-items-start">
        <div>
            <div class="pp-no">{{ $pengajuan->estimate_number }}</div>
            <div class="pp-meta">
                AO: <strong>{{ $pengajuan->ao_pic ?: '-' }}</strong>
                @if($pengajuan->created_at) &bull; {{ $pengajuan->created_at->format('d/m/Y H:i') }} @endif
            </div>
        </div>
        <span class="status-pill" style="background:{{ $bStyle[1] }};color:{{ $bStyle[2] }}">
            {{ ucfirst($pengajuan->status) }}
        </span>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-lg">
            <i class="fa fa-check-circle mr-1"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-lg">
            <i class="fa fa-exclamation-circle mr-1"></i>{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- Warning field tidak lengkap --}}
    @if(!empty($fieldErrors))
        <div class="alert alert-warning rounded-lg">
            <strong><i class="fa fa-exclamation-triangle mr-1"></i>Data belum lengkap:</strong>
            <ul class="mb-0 mt-1 pl-4 small">
                @foreach($fieldErrors as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Warning duplikat --}}
    @if(!empty($duplikat))
        <div class="alert alert-danger rounded-lg" id="duplikatAlert">
            <strong><i class="fa fa-exclamation-circle mr-1"></i>Duplikat di data existing:</strong>
            <ul class="mb-0 mt-1 pl-4 small">
                @foreach($duplikat as $dup)
                    <li>Field <strong>{{ $dup['field'] }}</strong> cocok dengan
                        <strong>{{ $dup['name'] }}</strong>
                        <span class="text-muted">({{ $dup['table'] }}, ID: {{ $dup['id'] }})</span>
                    </li>
                @endforeach
            </ul>
            <small class="d-block mt-1">Klik <strong>Paksa Mutasi</strong> jika ingin tetap dilanjutkan.</small>
        </div>
    @endif

    {{-- ═════════════════════════════════════════
         CARD IDENTITAS — 3 kolom
    ═════════════════════════════════════════ --}}
    <div class="pp-card">

        {{-- Nama besar di atas --}}
        <div class="prospect-head">
            <div class="prospect-avatar">{{ strtoupper(substr($pengajuan->prospect_name,0,1)) }}</div>
            <div>
                <div class="prospect-name">{{ $pengajuan->prospect_name }}</div>
                @if($pengajuan->perusahaan && $pengajuan->perusahaan !== $pengajuan->prospect_name)
                    <div class="prospect-sub"><i class="fa fa-store mr-1" style="font-size:.7rem"></i>{{ $pengajuan->perusahaan }}</div>
                @endif
                @if($pengajuan->owner)
                    <div class="prospect-sub"><i class="fa fa-user mr-1" style="font-size:.7rem"></i>CP: {{ $pengajuan->owner }}</div>
                @endif
            </div>
        </div>

        <div class="pp-card-hd mt-2">
            <i class="fa fa-address-card"></i> Detail Identitas
        </div>

        {{-- 3 kolom --}}
        <div class="info-3col">

            {{-- Kolom 1: Kontak --}}
            <div class="info-col col-divider">
                <div class="info-col-title"><i class="fa fa-phone mr-1"></i>Kontak</div>
                <div class="info-row">
                    <span class="info-lbl">No. HP</span>
                    <span class="info-val">{{ $pengajuan->phone ?: '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-lbl">No. KTP</span>
                    <span class="info-val">{{ $pengajuan->ktp ?: '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-lbl">NPWP</span>
                    <span class="info-val">{{ $pengajuan->npwp ?: '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-lbl">Zone</span>
                    <span class="info-val">{{ $pengajuan->zone ?: '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-lbl">Kategori</span>
                    <span class="info-val">{{ $pengajuan->kategori ?: '—' }}</span>
                </div>
            </div>

            {{-- Kolom 2: Alamat --}}
            <div class="info-col col-divider">
                <div class="info-col-title"><i class="fa fa-map-marker-alt mr-1"></i>Alamat</div>
                <div class="info-row">
                    <span class="info-lbl">Jalan</span>
                    <span class="info-val">{{ $pengajuan->address ?: '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-lbl">Kel / Kec</span>
                    <span class="info-val">
                        {{ $pengajuan->kelurahan ?: '—' }}
                        @if($pengajuan->kecamatan) / {{ $pengajuan->kecamatan }} @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-lbl">Kota</span>
                    <span class="info-val">{{ $pengajuan->city ?: '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-lbl">Provinsi</span>
                    <span class="info-val">{{ $pengajuan->provinsi ?: '—' }}</span>
                </div>
            </div>

            {{-- Kolom 3: Dokumen --}}
            <div class="info-col">
                <div class="info-col-title"><i class="fa fa-file-alt mr-1"></i>Dokumen</div>

                <div class="doc-row">
                    <span class="doc-lbl">Foto KTP</span>
                    @if($pengajuan->foto_ktp_ada)
                        <span class="doc-pill ada"><i class="fa fa-check"></i> Ada</span>
                        @if($pengajuan->ktp_photo_path)
                            <a href="{{ route('superuser.penjualan.pengajuan_proforma.dokumen', ['id' => $pengajuan->id, 'jenis' => 'ktp']) }}" target="_blank"
                               class="btn btn-xs btn-outline-secondary" style="font-size:.75rem;padding:2px 8px;margin-left:4px">
                                <i class="fa fa-eye"></i> Lihat
                            </a>
                        @endif
                    @else
                        <span class="doc-pill belum"><i class="fa fa-times"></i> Belum</span>
                    @endif
                </div>

                <div class="doc-row">
                    <span class="doc-lbl">Foto NPWP</span>
                    @if($pengajuan->foto_npwp_ada)
                        <span class="doc-pill ada"><i class="fa fa-check"></i> Ada</span>
                        @if($pengajuan->npwp_photo_path)
                            <a href="{{ route('superuser.penjualan.pengajuan_proforma.dokumen', ['id' => $pengajuan->id, 'jenis' => 'npwp']) }}" target="_blank"
                               class="btn btn-xs btn-outline-secondary" style="font-size:.75rem;padding:2px 8px;margin-left:4px">
                                <i class="fa fa-eye"></i> Lihat
                            </a>
                        @endif
                    @else
                        <span class="doc-pill belum"><i class="fa fa-times"></i> Belum</span>
                    @endif
                </div>

                <div class="doc-row">
                    <span class="doc-lbl">AO PIC</span>
                    <span class="info-val">{{ $pengajuan->ao_pic ?: '—' }}</span>
                </div>

                @if($pengajuan->verified_by)
                <div class="doc-row">
                    <span class="doc-lbl">Verifikator</span>
                    <span class="info-val small">{{ $pengajuan->verified_by }}<br>
                        <span class="text-muted">{{ $pengajuan->verified_at ? $pengajuan->verified_at->format('d/m/Y H:i') : '' }}</span>
                    </span>
                </div>
                @endif
            </div>

        </div>{{-- /info-3col --}}

        {{-- ── Action Row ── --}}
        @if($pengajuan->status === 'menunggu')
        <div class="action-row">

            {{-- Tombol Verifikasi --}}
            @if(!empty($duplikat))
                {{-- Ada duplikat: tawarkan paksa --}}
                <form method="POST" action="{{ route('superuser.penjualan.pengajuan_proforma.verifikasi', $pengajuan->id) }}" class="d-inline" id="frmPaksa">
                    @csrf
                    <input type="hidden" name="paksa" value="1">
                    <button type="submit" class="btn btn-warning font-weight-bold"
                        @if(!empty($fieldErrors)) disabled @endif
                        onclick="return confirm('Ada duplikat. Tetap mutasi paksa?')">
                        <i class="fa fa-exclamation-triangle mr-1"></i> Paksa Mutasi
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('superuser.penjualan.pengajuan_proforma.verifikasi', $pengajuan->id) }}" class="d-inline" id="frmVerif">
                    @csrf
                    <button type="submit" class="btn btn-success font-weight-bold"
                        @if(!empty($fieldErrors)) disabled title="Lengkapi data dulu" @endif
                        onclick="return confirm('Verifikasi dan mutasi customer {{ addslashes($pengajuan->prospect_name) }}?')">
                        <i class="fa fa-check mr-1"></i> Proses Mutasi
                    </button>
                </form>
            @endif

            {{-- Tombol Tolak --}}
            <button type="button" class="btn btn-outline-danger font-weight-bold" onclick="submitTolak()">
                <i class="fa fa-times mr-1"></i> Tolak
            </button>

            {{-- Tombol Hapus / Minta Revisi (input AO keliru) --}}
            <button type="button" class="btn btn-outline-secondary font-weight-bold" onclick="submitHapus()"
                title="Hapus pengajuan ini agar AO bisa perbaiki + ajukan ulang">
                <i class="fa fa-trash mr-1"></i> Hapus / Revisi
            </button>

            @if(!empty($fieldErrors))
                <small class="text-danger"><i class="fa fa-lock mr-1"></i>Data belum lengkap — verifikasi dinonaktifkan.</small>
            @endif

        </div>

        {{-- Hidden form tolak --}}
        <form method="POST" action="{{ route('superuser.penjualan.pengajuan_proforma.tolak', $pengajuan->id) }}" id="frmTolak" class="d-none">
            @csrf
            <input type="hidden" name="catatan" id="inputAlasanTolak">
        </form>

        {{-- Hidden form hapus / revisi --}}
        <form method="POST" action="{{ route('superuser.penjualan.pengajuan_proforma.hapus', $pengajuan->id) }}" id="frmHapus" class="d-none">
            @csrf
            <input type="hidden" name="alasan" id="inputAlasanHapus">
        </form>
        @endif

        {{-- Terminal state --}}
        @if(in_array($pengajuan->status, ['ditolak', 'dibatalkan']))
        <div class="action-row justify-content-center text-muted">
            <i class="fa fa-lock mr-2"></i>
            Pengajuan sudah <strong>{{ $pengajuan->status }}</strong> — tidak dapat diproses ulang.
            @if($pengajuan->catatan)
                &bull; <em>{{ $pengajuan->catatan }}</em>
            @endif
            @if(empty($pengajuan->customer_id_hasil) && empty($pengajuan->member_id_hasil))
            <form method="POST" action="{{ route('superuser.penjualan.pengajuan_proforma.hapus', $pengajuan->id) }}" id="frmHapusTerminal" class="d-inline ml-2">
                @csrf
                <input type="hidden" name="alasan" id="inputAlasanHapusTerminal">
                <button type="button" class="btn btn-sm btn-outline-danger font-weight-bold" onclick="submitHapusTerminal()"
                    title="Hapus agar AO bisa perbaiki + ajukan ulang">
                    <i class="fa fa-trash mr-1"></i> Hapus / Revisi
                </button>
            </form>
            @endif
        </div>
        @endif

    </div>{{-- /pp-card --}}

    {{-- ═════════════════════════════════════════
         HASIL MUTASI
    ═════════════════════════════════════════ --}}
    @if($pengajuan->customer_id_hasil)
    <div class="pp-card">
        <div class="pp-card-hd" style="background:#f0fdf4;border-bottom-color:#bbf7d0;color:#16a34a">
            <i class="fa fa-link"></i> Hasil Mutasi ke Customer Existing
        </div>
        <div class="mutasi-grid">
            <div class="mutasi-box">
                <div class="m-lbl">Customer ID</div>
                <div class="m-val">{{ $pengajuan->customer_id_hasil }}</div>
            </div>
            <div class="mutasi-box">
                <div class="m-lbl">Member ID</div>
                <div class="m-val">{{ $pengajuan->member_id_hasil ?: '—' }}</div>
            </div>
            <div class="mutasi-box" style="background:#f0f9ff;border-color:#bae6fd">
                <div class="m-lbl" style="color:#0369a1">Notif AO</div>
                <div class="m-val" style="color:#0c4a6e;font-size:.85rem">
                    <span class="badge badge-{{ $pengajuan->notif_ao_status === 'terkirim' ? 'success' : 'warning' }}">
                        {{ $pengajuan->notif_ao_status ?: 'belum terkirim' }}
                    </span>
                    @if($pengajuan->member_id_hasil)
                    <form method="POST" action="{{ route('superuser.penjualan.pengajuan_proforma.notif-ulang', $pengajuan->id) }}" class="d-inline ml-1">
                        @csrf
                        <button type="submit" class="btn btn-xs btn-info" title="Kirim ulang notifikasi ke AO"
                            onclick="return confirm('Kirim ulang notifikasi ke AO?')">
                            <i class="fa fa-bell"></i> Kirim ulang
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            <div class="mutasi-box" style="background:#f0f9ff;border-color:#bae6fd">
                <div class="m-lbl" style="color:#0369a1">Diverifikasi</div>
                <div class="m-val" style="color:#0c4a6e;font-size:.85rem">
                    {{ $pengajuan->verified_by ?: '—' }}
                    <span class="text-muted" style="font-size:.75rem;font-weight:400">
                        {{ $pengajuan->verified_at ? '· '.$pengajuan->verified_at->format('d/m/Y H:i') : '' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Cancel proforma --}}
        @if($pengajuan->status === 'disetujui')
        <div class="action-row">
            <button type="button" class="btn btn-outline-warning font-weight-bold" onclick="submitCancel()">
                <i class="fa fa-ban mr-1"></i> Batalkan Proforma
            </button>
            <small class="text-muted">Customer akan dihapus dari existing dan log audit dicatat.</small>
        </div>
        <form method="POST" action="{{ route('superuser.penjualan.pengajuan_proforma.cancel', $pengajuan->id) }}" id="frmCancel" class="d-none">
            @csrf
            <input type="hidden" name="alasan" id="inputAlasanCancel">
            <input type="hidden" name="rollback_customer" value="1">
        </form>
        @endif
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
function submitTolak() {
    var alasan = prompt('Alasan penolakan (wajib diisi):');
    if (alasan === null) return; // user cancel
    if (!alasan.trim()) { alert('Alasan tidak boleh kosong.'); return; }
    document.getElementById('inputAlasanTolak').value = alasan;
    document.getElementById('frmTolak').submit();
}

function submitCancel() {
    var alasan = prompt('Alasan pembatalan proforma (wajib diisi):');
    if (alasan === null) return;
    if (!alasan.trim()) { alert('Alasan tidak boleh kosong.'); return; }
    document.getElementById('inputAlasanCancel').value = alasan;
    document.getElementById('frmCancel').submit();
}

function submitHapus() {
    var alasan = prompt('Alasan hapus / minta revisi (wajib diisi, diteruskan ke AO):');
    if (alasan === null) return; // user cancel
    if (!alasan.trim()) { alert('Alasan tidak boleh kosong.'); return; }
    if (!confirm('Hapus pengajuan {{ addslashes($pengajuan->estimate_number) }}? AO harus perbaiki + ajukan ulang.')) return;
    document.getElementById('inputAlasanHapus').value = alasan;
    document.getElementById('frmHapus').submit();
}

function submitHapusTerminal() {
    var alasan = prompt('Alasan hapus / minta revisi (wajib diisi, diteruskan ke AO):');
    if (alasan === null) return;
    if (!alasan.trim()) { alert('Alasan tidak boleh kosong.'); return; }
    if (!confirm('Hapus pengajuan {{ addslashes($pengajuan->estimate_number) }}? AO harus perbaiki + ajukan ulang.')) return;
    document.getElementById('inputAlasanHapusTerminal').value = alasan;
    document.getElementById('frmHapusTerminal').submit();
}
</script>
@endpush
