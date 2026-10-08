@extends('superuser.app')

@push('styles')
<style>
/* ── Canvas ── */
.crm-wrapper { max-width: 992px; margin: auto; }

/* ── Page header ── */
.pp-no { font-size: 1.1rem; font-weight: 700; color: #2d3748; }
.pp-meta { font-size: .8rem; color: #64748b; margin-top: 2px; }
.pp-crumb { font-size: .8rem; color: #64748b; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
.pp-crumb:hover { color: #4e73df; }

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
.info-col { padding:12px 14px; }
.info-col-title { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.6px; color:#64748b; margin-bottom:10px; }

.info-row { display:flex; flex-direction:column; padding:5px 0; border-bottom:1px solid #f8fafc; }
.info-row:last-child { border-bottom:none; }
.info-lbl { font-size:.72rem; color:#64748b; margin-bottom:1px; }
.info-val { font-size:.875rem; color:#2d3748; font-weight:500; word-break:break-word; }
.info-val.big { font-size:1rem; font-weight:700; color:#1a202c; }

/* ── Identitas kepala ── */
.prospect-head { padding:10px 14px 0; display:flex; align-items:center; gap:10px; }
.prospect-avatar {
    width:44px; height:44px; border-radius:50%; background:#e8f0fe;
    display:flex; align-items:center; justify-content:center;
    font-size:1.1rem; font-weight:700; color:#4e73df; flex-shrink:0;
}
.prospect-name { font-size:1.05rem; font-weight:700; color:#1a202c; line-height:1.2; }
.prospect-sub { font-size:.8rem; color:#718096; }

/* ── Dokumen inline (rapat) ── */
.doc-row { display:flex; align-items:center; gap:8px; padding:4px 0; border-bottom:1px solid #f8fafc; }
.doc-row:last-child { border-bottom:none; }
.doc-lbl { font-size:.72rem; color:#64748b; width:70px; flex-shrink:0; }
.doc-row .form-control-sm { height:28px; font-size:.8rem; padding:2px 8px; }
/* Dropzone foto: klik/seret/tempel */
.doc-drop { flex:1; border:1.5px dashed #cbd5e0; border-radius:8px; padding:6px 8px; text-align:center; font-size:.72rem; color:#718096; cursor:pointer; background:#f8fafc; transition:all .12s; min-width:0; }
.doc-drop:hover, .doc-drop.over { border-color:#4e73df; color:#4e73df; background:#eef4ff; }
.doc-drop.has-file { border-style:solid; border-color:#16a34a; color:#16a34a; background:#f0fdf4; }
.doc-prev { display:flex; gap:4px; margin-top:4px; flex-wrap:wrap; }
.doc-prev img { width:40px; height:40px; object-fit:cover; border-radius:6px; border:1px solid #e2e8f0; }
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

    {{-- Page header ringkas: konteks EST tetap terlihat (scent), status ikut di kepala identitas --}}
    @php
        $badgeMap   = ['menunggu'=>['warning','#f6c23e','#333'], 'disetujui'=>['success','#1cc88a','#fff'], 'ditolak'=>['danger','#e74a3b','#fff'], 'dibatalkan'=>['secondary','#858796','#fff'], 'revisi'=>['info','#36b9cc','#fff']];
        $bStyle     = $badgeMap[$pengajuan->status] ?? ['secondary','#858796','#fff'];
    @endphp
    <div class="d-flex justify-content-between align-items-center mb-2">
        <a href="{{ route('superuser.penjualan.pengajuan_proforma.index') }}" class="pp-crumb">
            <i class="fa fa-arrow-left"></i> Antrian
        </a>
        <div class="text-right" style="min-width:0;">
            <span class="pp-no">{{ $pengajuan->estimate_number ?: 'EST-?' }}</span>
            <span class="pp-meta ml-2">AO: <strong>{{ $pengajuan->ao_pic ?: '-' }}</strong>@if($pengajuan->created_at) &bull; {{ $pengajuan->created_at->format('d/m/Y H:i') }} @endif</span>
        </div>
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

    {{-- Paket B poin 11: pilihan member — gandeng existing / buat baru di store ini --}}
    @if($pengajuan->status === 'menunggu' && !empty($candidateStore['parent']) && $candidateStore['members']->count())
    @php $csParent = $candidateStore['parent']; @endphp
    <div class="alert alert-info rounded-lg" id="memberPickAlert">
        <strong><i class="fa fa-link mr-1"></i>Store cocok: {{ $csParent->name }}</strong>
        <span class="text-muted small">(ID: {{ $csParent->id }})</span>
        <div class="mt-2 small">
            <div class="form-check">
                <input class="form-check-input member-action-radio" type="radio" name="member_action_dup" value="auto" id="maAuto" checked>
                <label class="form-check-label" for="maAuto">Otomatis — ikut KTP (perilaku lama)</label>
            </div>
            @foreach($candidateStore['members'] as $m)
            <div class="form-check">
                <input class="form-check-input member-action-radio" type="radio" name="member_action_dup" value="gandeng:{{ $m->id }}" id="ma{{ $loop->index }}">
                <label class="form-check-label" for="ma{{ $loop->index }}">
                    Gandeng — <strong>{{ $m->name }}</strong> ({{ $m->phone ?: '-' }}, {{ $m->kota ?: '-' }})
                    @if((int) $m->member_default === 1) <span class="badge badge-success">default</span> @endif
                    <span class="text-muted">{{ $m->id }}</span>
                </label>
            </div>
            @endforeach
            <div class="form-check">
                <input class="form-check-input member-action-radio" type="radio" name="member_action_dup" value="baru" id="maBaru">
                <label class="form-check-label" for="maBaru">Buat member baru di store ini <span class="text-muted">({{ $csParent->id }}.+1, tercatat siapa membuat)</span></label>
            </div>
        </div>
        <small class="d-block mt-1 text-muted">Pilihan ikut terkirim saat tekan <strong>Proses Mutasi / Paksa Mutasi</strong>.</small>
    </div>
    <script>
    (function () {
        function val() {
            var c = document.querySelector('input[name="member_action_dup"]:checked');
            return c ? c.value : 'auto';
        }
        ['frmPaksa', 'frmVerif'].forEach(function (fid) {
            var f = document.getElementById(fid);
            if (!f) return;
            var h = document.createElement('input');
            h.type = 'hidden'; h.name = 'member_action'; h.value = 'auto';
            f.appendChild(h);
            f.addEventListener('submit', function () { h.value = val(); });
        });
        var radios = document.querySelectorAll('input[name="member_action_dup"]');
        for (var i = 0; i < radios.length; i++) {
            radios[i].addEventListener('change', function () {
                var v = val();
                ['frmPaksa', 'frmVerif'].forEach(function (fid) {
                    var f = document.getElementById(fid);
                    if (!f) return;
                    var h = f.querySelector('input[name="member_action"]');
                    if (h) h.value = v;
                });
            });
        }
    })();
    </script>
    @endif

    {{-- ═════════════════════════════════════════
         CARD IDENTITAS — 3 kolom
    ═════════════════════════════════════════ --}}
    <div class="pp-card">

        {{-- Nama besar di atas + badge status menempel kanan --}}
        <div class="prospect-head">
            <div class="prospect-avatar">{{ strtoupper(substr($pengajuan->prospect_name,0,1)) }}</div>
            <div style="min-width:0;">
                <div class="prospect-name">{{ $pengajuan->prospect_name }}</div>
                @if($pengajuan->perusahaan && $pengajuan->perusahaan !== $pengajuan->prospect_name)
                    <div class="prospect-sub"><i class="fa fa-store mr-1" style="font-size:.7rem"></i>{{ $pengajuan->perusahaan }}</div>
                @endif
                @if($pengajuan->owner)
                    <div class="prospect-sub"><i class="fa fa-user mr-1" style="font-size:.7rem"></i>CP: {{ $pengajuan->owner }}</div>
                @endif
            </div>
            <span class="status-pill" style="margin-left:auto;background:{{ $bStyle[1] }};color:{{ $bStyle[2] }}">
                {{ ucfirst($pengajuan->status) }}
            </span>
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

            {{-- Kolom 3: Dokumen (bisa diisi admin sales sebelum/sesudah mutasi) --}}
            <div class="info-col">
                <div class="info-col-title"><i class="fa fa-file-alt mr-1"></i>Dokumen</div>
                @if(in_array($pengajuan->status, ['menunggu', 'revisi', 'disetujui'], true))
                <form method="POST" action="{{ route('superuser.penjualan.pengajuan_proforma.dokumen-simpan', $pengajuan->id) }}" enctype="multipart/form-data" id="frmDokumen">
                    @csrf
                    <div class="doc-row">
                        <span class="doc-lbl">No. HP</span>
                        <input type="text" name="phone" value="{{ $pengajuan->phone }}" class="form-control form-control-sm">
                    </div>
                    <div class="doc-row">
                        <span class="doc-lbl">No. KTP</span>
                        <input type="text" name="ktp" value="{{ $pengajuan->ktp }}" maxlength="16" inputmode="numeric" class="form-control form-control-sm" placeholder="16 digit (opsional)">
                    </div>
                    <div class="doc-row">
                        <span class="doc-lbl">NPWP</span>
                        <input type="text" name="npwp" value="{{ $pengajuan->npwp }}" maxlength="15" inputmode="numeric" class="form-control form-control-sm" placeholder="15 digit (opsional)">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                        <div>
                            <div class="doc-lbl" style="margin-bottom:2px;">Foto KTP</div>
                            <div class="doc-drop" id="docKtpDrop" title="Klik / seret file / tempel (Ctrl+V)">
                                <span id="docKtpTxt">{{ $pengajuan->ktp_photo_path ? 'Ada — ganti?' : 'Klik / seret' }}</span>
                                @if($pengajuan->ktp_photo_path)
                                <a href="{{ route('superuser.penjualan.pengajuan_proforma.dokumen', ['id' => $pengajuan->id, 'jenis' => 'ktp']) }}" target="_blank"
                                   class="btn btn-xs btn-outline-secondary ml-1" style="font-size:.7rem;padding:1px 7px;" onclick="event.stopPropagation();">
                                    <i class="fa fa-eye"></i>
                                </a>
                                @endif
                            </div>
                            <input type="file" name="ktp_photo" id="docKtpFile" accept="image/*" style="display:none;">
                            <div class="doc-prev" id="docKtpPrev"></div>
                        </div>
                        <div>
                            <div class="doc-lbl" style="margin-bottom:2px;">Foto NPWP</div>
                            <div class="doc-drop" id="docNpwpDrop" title="Klik / seret file / tempel (Ctrl+V)">
                                <span id="docNpwpTxt">{{ $pengajuan->npwp_photo_path ? 'Ada — ganti?' : 'Klik / seret' }}</span>
                                @if($pengajuan->npwp_photo_path)
                                <a href="{{ route('superuser.penjualan.pengajuan_proforma.dokumen', ['id' => $pengajuan->id, 'jenis' => 'npwp']) }}" target="_blank"
                                   class="btn btn-xs btn-outline-secondary ml-1" style="font-size:.7rem;padding:1px 7px;" onclick="event.stopPropagation();">
                                    <i class="fa fa-eye"></i>
                                </a>
                                @endif
                            </div>
                            <input type="file" name="npwp_photo" id="docNpwpFile" accept="image/*" style="display:none;">
                            <div class="doc-prev" id="docNpwpPrev"></div>
                        </div>
                    </div>
                    <div class="doc-row">
                        <span class="doc-lbl"></span>
                        <button type="submit" class="btn btn-xs btn-primary font-weight-bold">
                            <i class="fa fa-save"></i> Simpan Dokumen
                        </button>
                    </div>
                    <small class="text-muted d-block mt-1">Sesudah mutasi: tersimpan langsung ke data customer.</small>
                </form>
                @else
                {{-- Read-only: pengajuan sudah final (disetujui/ditolak/dibatalkan) --}}
                <div class="doc-row">
                    <span class="doc-lbl">No. HP</span>
                    <span class="info-val">{{ $pengajuan->phone ?: '—' }}</span>
                </div>
                <div class="doc-row">
                    <span class="doc-lbl">No. KTP</span>
                    <span class="info-val">{{ $pengajuan->ktp ?: '—' }}</span>
                </div>
                <div class="doc-row">
                    <span class="doc-lbl">NPWP</span>
                    <span class="info-val">{{ $pengajuan->npwp ?: '—' }}</span>
                </div>
                <div class="doc-row">
                    <span class="doc-lbl">Foto KTP</span>
                    @if($pengajuan->ktp_photo_path)
                    <a href="{{ route('superuser.penjualan.pengajuan_proforma.dokumen', ['id' => $pengajuan->id, 'jenis' => 'ktp']) }}" target="_blank"
                       class="btn btn-xs btn-outline-secondary" style="font-size:.75rem;padding:2px 8px;">
                        <i class="fa fa-eye"></i> Lihat
                    </a>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                </div>
                <div class="doc-row">
                    <span class="doc-lbl">Foto NPWP</span>
                    @if($pengajuan->npwp_photo_path)
                    <a href="{{ route('superuser.penjualan.pengajuan_proforma.dokumen', ['id' => $pengajuan->id, 'jenis' => 'npwp']) }}" target="_blank"
                       class="btn btn-xs btn-outline-secondary" style="font-size:.75rem;padding:2px 8px;">
                        <i class="fa fa-eye"></i> Lihat
                    </a>
                    @else
                    <span class="text-muted">—</span>
                    @endif
                </div>
                @endif
                @if(in_array($pengajuan->status, ['menunggu', 'revisi', 'disetujui'], true))
                <script>
                (function () {
                    function docAssign(kind, file) {
                        if (!file || (file.type && file.type.indexOf('image/') !== 0)) { alert('File harus berupa gambar.'); return; }
                        if (file.size > 5 * 1024 * 1024) { alert('Maksimal 5MB per foto.'); return; }
                        var input = document.getElementById(kind === 'ktp' ? 'docKtpFile' : 'docNpwpFile');
                        var drop = document.getElementById(kind === 'ktp' ? 'docKtpDrop' : 'docNpwpDrop');
                        var prev = document.getElementById(kind === 'ktp' ? 'docKtpPrev' : 'docNpwpPrev');
                        var txt = document.getElementById(kind === 'ktp' ? 'docKtpTxt' : 'docNpwpTxt');
                        if (!input || !drop) return;
                        var dt = new DataTransfer();
                        dt.items.add(file);
                        input.files = dt.files;
                        drop.classList.add('has-file');
                        if (prev) prev.innerHTML = '<img src="' + URL.createObjectURL(file) + '" title="' + file.name + '">';
                        if (txt) txt.textContent = file.name;
                        lastDoc = kind === 'ktp' ? 'docKtpDrop' : 'docNpwpDrop';
                    }
                    function bindDoc(dropId, kind) {
                        var drop = document.getElementById(dropId);
                        if (!drop) return;
                        drop.addEventListener('click', function(e) {
                            if (e.target.closest('a')) return;
                            lastDoc = dropId;
                            document.getElementById(kind === 'ktp' ? 'docKtpFile' : 'docNpwpFile').click();
                        });
                        ['dragover', 'dragenter'].forEach(function(ev) {
                            drop.addEventListener(ev, function(e) { e.preventDefault(); drop.classList.add('over'); });
                        });
                        ['dragleave', 'drop'].forEach(function(ev) {
                            drop.addEventListener(ev, function(e) { e.preventDefault(); drop.classList.remove('over'); });
                        });
                        drop.addEventListener('drop', function(e) {
                            if (e.dataTransfer && e.dataTransfer.files[0]) docAssign(kind, e.dataTransfer.files[0]);
                        });
                    }
                    var lastDoc = 'docKtpDrop';
                    bindDoc('docKtpDrop', 'ktp');
                    bindDoc('docNpwpDrop', 'npwp');
                    document.getElementById('docKtpFile').addEventListener('change', function() {
                        if (this.files[0]) docAssign('ktp', this.files[0]);
                    });
                    document.getElementById('docNpwpFile').addEventListener('change', function() {
                        if (this.files[0]) docAssign('npwp', this.files[0]);
                    });
                    // Tempel (Ctrl+V): masuk ke dropzone terakhir disentuh (default KTP).
                    // Abaikan bila fokus sedang di input/textarea agar tidak salah sasaran.
                    document.addEventListener('paste', function(e) {
                        var tag = (e.target && e.target.tagName) || '';
                        if (/^(INPUT|TEXTAREA|SELECT)$/.test(tag)) return;
                        if (!document.getElementById('frmDokumen')) return;
                        var files = (e.clipboardData && e.clipboardData.files) || [];
                        if (!files.length) return;
                        docAssign(lastDoc === 'docNpwpDrop' ? 'npwp' : 'ktp', files[0]);
                    });
                    // Cegah dobel-klik: kunci tombol saat submit dokumen / mutasi
                    ['frmDokumen', 'frmPaksa', 'frmVerif'].forEach(function(fid) {
                        var f = document.getElementById(fid);
                        if (!f) return;
                        f.addEventListener('submit', function() {
                            var b = f.querySelector('button[type="submit"]');
                            if (b) { b.disabled = true; b.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Memproses...'; }
                        });
                    });
                })();
                </script>
                @endif

                <div class="doc-row">
                    <span class="doc-lbl">Bukti Capture</span>
                    @php $buktiList = json_decode((string) $pengajuan->bukti_list, true) ?: []; @endphp
                    @if(!is_null($pengajuan->bukti_ada) && $pengajuan->bukti_ada)
                        <span class="doc-pill ada"><i class="fa fa-check"></i> Ada ({{ count($buktiList) }})</span>
                        @foreach($buktiList as $bi => $bp)
                        <a href="{{ route('superuser.penjualan.pengajuan_proforma.bukti', ['id' => $pengajuan->id, 'index' => $bi]) }}" target="_blank" rel="noopener"
                           class="btn btn-xs btn-outline-secondary" style="font-size:.75rem;padding:4px 10px;margin-left:4px;min-height:32px;"
                           title="Lihat bukti capture {{ $bi + 1 }} (tab baru)" aria-label="Lihat bukti {{ $bi + 1 }}">
                            <i class="fa fa-eye"></i> {{ $bi + 1 }}
                        </a>
                        @endforeach
                        <small class="text-muted ml-1">klik untuk lihat</small>
                    @elseif(is_null($pengajuan->bukti_ada))
                        <span class="doc-pill belum" title="Pengajuan lama, status bukti tak diketahui"><i class="fa fa-question"></i> Tak diketahui</span>
                    @else
                        <span class="doc-pill belum"><i class="fa fa-times"></i> Belum — mutasi diblokir</span>
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
            <a href="{{ route('superuser.penjualan.pengajuan_proforma.index') }}" class="btn btn-outline-secondary font-weight-bold" style="margin-right:auto;">
                <i class="fa fa-arrow-left mr-1"></i> Kembali
            </a>

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

            {{-- Kembalikan = revisi, baris + file DIPERTAHANKAN --}}
            <button type="button" class="btn btn-outline-warning font-weight-bold" onclick="submitKembalikan()"
                title="Kembalikan (revisi): baris dipertahankan, AO perbaiki lalu ajukan ulang">
                <i class="fa fa-undo mr-1"></i> Kembalikan (Revisi)
            </button>

            {{-- Tolak = cabut total (pengganti Hapus/Revisi yang lama) --}}
            <button type="button" class="btn btn-outline-danger font-weight-bold" onclick="submitTolak()"
                title="Tolak: baris + file dihapus total, AO mulai dari draft">
                <i class="fa fa-times mr-1"></i> Tolak (Cabut Total)
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

        {{-- Hidden form kembalikan (revisi, baris dipertahankan) --}}
        <form method="POST" action="{{ route('superuser.penjualan.pengajuan_proforma.kembalikan', $pengajuan->id) }}" id="frmKembalikan" class="d-none">
            @csrf
            <input type="hidden" name="catatan" id="inputAlasanKembalikan">
        </form>
        @endif

        {{-- Terminal state --}}
        @if(in_array($pengajuan->status, ['ditolak', 'dibatalkan']))
        <div class="action-row justify-content-center text-muted">
            <a href="{{ route('superuser.penjualan.pengajuan_proforma.index') }}" class="btn btn-outline-secondary font-weight-bold">
                <i class="fa fa-arrow-left mr-1"></i> Kembali
            </a>
            <i class="fa fa-lock mr-2 ml-2"></i>
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

        {{-- Cancel proforma + kembalikan ke AO (revisi pasca-mutasi) --}}
        @if($pengajuan->status === 'disetujui')
        <div class="action-row">
            <a href="{{ route('superuser.penjualan.pengajuan_proforma.index') }}" class="btn btn-outline-secondary font-weight-bold" style="margin-right:auto;">
                <i class="fa fa-arrow-left mr-1"></i> Kembali
            </a>
            <button type="button" class="btn btn-outline-info font-weight-bold" onclick="submitKembalikanAo()"
                title="Kembalikan ke AO: ubah data di sana, proforma diperbarui otomatis tanpa pengajuan/mutasi ulang">
                <i class="fa fa-reply mr-1"></i> Kembalikan ke AO
            </button>
            <button type="button" class="btn btn-outline-warning font-weight-bold" onclick="submitCancel()">
                <i class="fa fa-ban mr-1"></i> Batalkan Proforma
            </button>
            <!--<small class="text-muted">Customer akan dihapus dari existing dan log audit dicatat.</small>-->
        </div>
        {{-- Retry SO + Proforma (push AO gagal): sumber lokal items_json, duplikat ke-2 = free.
             Dua blok if terpisah tanpa nesting agar aman di semua versi compiler Blade. --}}
        @if(isset($retryInfo) && ($retryInfo['canRetry'] ?? false) && empty($retryInfo['proforma']))
        <div class="action-row" style="background:#fffbeb;border-top-color:#fde68a;">
            <span class="small text-muted" style="margin-right:auto;">
                <i class="fa fa-redo mr-1"></i>Proforma belum terbentuk (push AO gagal).
                @if(!empty($retryInfo['brand'] ?? null)) Brand: <strong>{{ $retryInfo['brand'] }}</strong> &bull; @endif
                    {{ $retryInfo['itemCount'] ?? 0 }} item @if(!empty($retryInfo['freeCount'] ?? null)) ({{ $retryInfo['freeCount'] }} free) @endif
            </span>
            <button type="button" class="btn btn-success font-weight-bold" onclick="submitRetrySo()"
                title="Buat SO Awal + proforma Aktif dari data pengajuan lokal">
                <i class="fa fa-redo mr-1"></i> Retry Proforma
            </button>
        </div>
        <form method="POST" action="{{ route('superuser.penjualan.pengajuan_proforma.retry-so', $pengajuan->id) }}" id="frmRetrySo" class="d-none">
            @csrf
            <input type="hidden" name="kurs" id="inputKursRetry">
        </form>
        @endif
        @if(isset($retryInfo) && !($retryInfo['canRetry'] ?? false) && empty($retryInfo['proforma']) && !empty($retryInfo['reason'] ?? null))
        <div class="action-row justify-content-center">
            <small class="text-muted"><i class="fa fa-info-circle mr-1"></i>{{ $retryInfo['reason'] }}</small>
        </div>
        @endif
        <form method="POST" action="{{ route('superuser.penjualan.pengajuan_proforma.cancel', $pengajuan->id) }}" id="frmCancel" class="d-none">
            @csrf
            <input type="hidden" name="alasan" id="inputAlasanCancel">
            <input type="hidden" name="rollback_customer" value="1">
        </form>
        <form method="POST" action="{{ route('superuser.penjualan.pengajuan_proforma.kembalikanAo', $pengajuan->id) }}" id="frmKembalikanAo" class="d-none">
            @csrf
            <input type="hidden" name="catatan" id="inputAlasanKembalikanAo">
        </form>
        @endif
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
function ppAskAlasan(judul, teks, danger) {
    return Swal.fire({
        title: judul,
        html: '<div style="text-align:left;">' + teks + '<textarea id="ppAlasanSw" class="form-control" rows="3" placeholder="Wajib diisi..."></textarea></div>',
        icon: danger ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Lanjut',
        cancelButtonText: 'Batal',
        confirmButtonColor: danger ? '#dc3545' : '#4e73df',
        preConfirm: function() {
            var v = document.getElementById('ppAlasanSw').value.trim();
            if (!v) { Swal.showValidationMessage('Alasan tidak boleh kosong.'); return false; }
            return v;
        }
    });
}
function submitTolak() {
    ppAskAlasan('Tolak pengajuan?', 'Pengajuan akan <b>DIHAPUS total</b> (baris + file). Alasan wajib diisi:', true)
    .then(function(r) {
        if (!r.isConfirmed) return;
        document.getElementById('inputAlasanTolak').value = r.value;
        document.getElementById('frmTolak').submit();
    });
}
function submitKembalikan() {
    ppAskAlasan('Kembalikan untuk revisi?', 'Baris dipertahankan. Catatan untuk AO (wajib diisi):', false)
    .then(function(r) {
        if (!r.isConfirmed) return;
        document.getElementById('inputAlasanKembalikan').value = r.value;
        document.getElementById('frmKembalikan').submit();
    });
}

function submitCancel() {
    ppAskAlasan('Batalkan proforma?', 'Customer dihapus dari existing + log audit dicatat. Alasan (wajib diisi):', true)
    .then(function(r) {
        if (!r.isConfirmed) return;
        document.getElementById('inputAlasanCancel').value = r.value;
        document.getElementById('frmCancel').submit();
    });
}
function submitKembalikanAo() {
    ppAskAlasan('Kembalikan ke AO?', 'Ubah data di AO, proforma diperbarui otomatis. Catatan revisi (wajib diisi, misF. tambah/kurangi produk atau ubah diskon):', false)
    .then(function(r) {
        if (!r.isConfirmed) return;
        document.getElementById('inputAlasanKembalikanAo').value = r.value;
        document.getElementById('frmKembalikanAo').submit();
    });
}

function submitHapusTerminal() {
    ppAskAlasan('Hapus pengajuan?', 'AO harus perbaiki + ajukan ulang. Alasan (wajib diisi, diteruskan ke AO):', true)
    .then(function(r) {F
        if (!r.isConfirmed) return;
        document.getElementById('inputAlasanHapusTerminal').value = r.value;
        document.getElementById('frmHapusTerminal').submit();
    });
}

function submitRetrySo() {
    Swal.fire({
        title: 'Retry Proforma',
        html: '<div style="text-align:left;">Sumber <b>lokal</b> (items pengajuan, duplikat ke-2 = free).'
            + '<br>SO Tutup (status 4) tidak diproses otomatis.'
            + '<br><br><label style="font-size:.8rem;">Kurs (wajib &ge; 1000)</label>'
            + '<input id="ppKursRetry" type="number" min="1000" step="1" value="19000" class="form-control" placeholder="cth: 19000"></div>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Buatkan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#16a34a',
        preConfirm: function() {
            var v = parseFloat(document.getElementById('ppKursRetry').value);
            if (!v || v < 1000) { Swal.showValidationMessage('Kurs wajib angka &ge; 1000.'); return false; }
            return v;
        }
    }).then(function(r) {
        if (!r.isConfirmed) return;
        document.getElementById('inputKursRetry').value = r.value;
        document.getElementById('frmRetrySo').submit();
    });
}
</script>
@endpush