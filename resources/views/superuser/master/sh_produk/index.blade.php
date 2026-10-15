@extends('superuser.app')

@push('styles')
<style>
.crm-wrapper { max-width: 992px; margin: auto; }
.page-head { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 10px; }
.page-head .titles { flex: 1; min-width: 200px; }
.stepper { display: flex; gap: 6px; margin: 0 0 10px; flex-wrap: wrap; }
.step { flex: 1; min-width: 150px; background: #fff; border: 1px solid #e3e6eb; border-radius: 8px; padding: 6px 10px; display: flex; gap: 8px; align-items: center; }
.step .num { width: 24px; height: 24px; border-radius: 50%; background: #4e73df; color: #fff; font-weight: 700; font-size: .75rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.step.done .num { background: #1cc88a; }
.step .txt { font-size: .78rem; line-height: 1.25; }
.step .txt strong { display: block; font-size: .82rem; }
.step .txt span { color: #858796; }
.src-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 16px; }
.src-cell { padding: 6px 0; border-bottom: 1px dashed #e3e6eb; }
.src-cell:last-child { border-bottom: none; }
@media (max-width: 640px) { .src-grid { grid-template-columns: 1fr; } }
.toolbar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 12px; }
.workflow-tabs { display: flex; flex-wrap: wrap; gap: 6px; flex-shrink: 0; }
.menu-tab { border: 1px solid #dce1e7; background: #fff; padding: 5px 14px; border-radius: 20px; font-size: .83rem; font-weight: 500; cursor: pointer; transition: all .15s; white-space: nowrap; text-decoration: none; color: #5a5c69; }
.menu-tab:hover { background: #f0f2f8; text-decoration: none; }
.menu-tab.active { background: #4e73df; border-color: #4e73df; color: #fff; }
.toolbar-search { flex: 1; min-width: 200px; max-width: 280px; }
.table th { font-size: .75rem; text-transform: uppercase; letter-spacing: .4px; color: #858796; }
.table td { vertical-align: middle; font-size: .875rem; }
.sh-table td { padding: 4px 8px !important; font-size: 12.5px; line-height: 1.4; }
.sh-table .sub { color: #9aa0ab; font-size: 11.5px; }
.sh-table .badge { font-size: 11px; }
@media (max-width: 640px) { .toolbar-search { max-width: 100%; flex-basis: 100%; } }
</style>
@endpush

@section('content')
<div class="crm-wrapper">

    <div class="page-head">
        <div class="titles">
            <h5 class="mb-0 font-weight-bold">SH Produk <small class="font-weight-normal text-muted">— Sertifikasi Halal</small></h5>
        </div>
        <form action="{{ route('superuser.master.sh_produk.run') }}" method="POST" class="mb-0">
            @csrf
            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Jalankan pencocokan ulang memakai dokumen terbaru?')">
                <i class="fa fa-sync mr-1"></i>Jalankan Pencocokan
            </button>
        </form>
    </div>

    <div class="stepper">
        <div class="step {{ $docList && $docPdf ? 'done' : '' }}">
            <div class="num">1</div>
            <div class="txt"><strong>Upload sumber</strong><span>{{ $docList && $docPdf ? ($docList->total_baris . ' list • ' . $docPdf->total_baris . ' PDF') : 'Excel list + PDF resmi' }}</span></div>
        </div>
        <div class="step {{ ($stats['Cocok'] ?? 0) + ($stats['Perlu ditinjau'] ?? 0) > 0 ? 'done' : '' }}">
            <div class="num">2</div>
            <div class="txt"><strong>Pencocokan</strong><span>{{ ($stats['Cocok'] ?? 0) + ($stats['Perlu ditinjau'] ?? 0) + ($stats['Tidak cocok'] ?? 0) }} baris • kunci kode→nama</span></div>
        </div>
        <div class="step">
            <div class="num">3</div>
            <div class="txt"><strong>Tinjau</strong><span>{{ $stats['Perlu ditinjau'] ?? 0 }} menunggu • {{ $stats['Tidak cocok'] ?? 0 }} tak cocok</span></div>
        </div>
    </div>

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
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if(!$petaConfirmed)
        <div class="alert alert-warning py-2 mb-2">
            <small><strong>Aturan 5 belum dikonfirmasi:</strong> {{ $petaCount }} produk PPI FF / PPI NON FF dicocokkan ke IVA
            hanya berdasar pola kode — ditahan sebagai <em>Perlu ditinjau</em>.</small>
            <form action="{{ route('superuser.master.sh_produk.confirm_brand_map') }}" method="POST" class="d-inline ml-2">
                @csrf
                <button type="submit" class="btn btn-sm btn-warning py-0" onclick="return confirm('Pastikan produk PPI FF / PPI NON FF memang sama dengan produk IVA di sertifikat, lalu jalankan pencocokan ulang. Lanjut?')">Saya Konfirmasi PPI = IVA</button>
            </form>
        </div>
    @endif

    <div class="card mb-2">
        <div class="card-body py-1">
            <div class="src-grid">
                <div class="src-cell">
                    <form action="{{ route('superuser.master.sh_produk.upload_list') }}" method="POST" enctype="multipart/form-data" class="form-inline">
                        @csrf
                        <span class="badge badge-info mr-1">List ID</span>
                        <input type="file" name="file_list" accept=".xlsx,.xls" required class="form-control form-control-sm mr-1" style="max-width:190px;">
                        <input type="date" name="tanggal_list" value="{{ date('Y-m-d') }}" required class="form-control form-control-sm mr-1" style="max-width:140px;">
                        <button type="submit" class="btn btn-sm btn-outline-primary py-0">Upload</button>
                    </form>
                    <small class="text-muted">{{ $docList ? $docList->original_name . ' • ' . $docList->total_baris . ' baris • ' . $docList->tanggal_versi : 'belum ada' }}</small>
                </div>
                <div class="src-cell">
                    <form action="{{ route('superuser.master.sh_produk.upload_pdf') }}" method="POST" enctype="multipart/form-data" class="form-inline">
                        @csrf
                        <span class="badge badge-danger mr-1">PDF ID</span>
                        <input type="file" name="file_pdf" accept=".pdf" required class="form-control form-control-sm mr-1" style="max-width:190px;">
                        <input type="date" name="tanggal_pdf" value="{{ date('Y-m-d') }}" required class="form-control form-control-sm mr-1" style="max-width:140px;">
                        <button type="submit" class="btn btn-sm btn-outline-primary py-0">Upload</button>
                    </form>
                    <small class="text-muted">{{ $docPdf ? $docPdf->original_name . ' • ' . $docPdf->total_baris . ' produk • ' . $docPdf->nomor_sertifikat : 'belum ada' }}</small>
                </div>
                <div class="src-cell">
                    <form action="{{ route('superuser.master.sh_produk.import_qr_sg') }}" method="POST" class="form-inline">
                        @csrf
                        <span class="badge badge-primary mr-1">QR SG</span>
                        <input type="url" name="qr_url" value="https://halal.muis.gov.sg/qr?n=DF7ag2NswkfuxRHTDo%2bQyz33Kq9RpbHpzJPKoNE5Dmw%3d" required class="form-control form-control-sm mr-1" style="max-width:260px;">
                        <input type="date" name="tanggal_list_sg" value="{{ date('Y-m-d') }}" required class="form-control form-control-sm mr-1" style="max-width:140px;">
                        <button type="submit" class="btn btn-sm btn-outline-primary py-0">Import + Cocokkan</button>
                    </form>
                    <small class="text-muted">{{ isset($docListSg) && $docListSg ? $docListSg->nomor_sertifikat . ' • ' . $docListSg->total_baris . ' produk • exp ' . ($docListSg->tanggal_kedaluwarsa ?? '-') : 'belum ada' }}</small>
                </div>
                <div class="src-cell">
                    <form action="{{ route('superuser.master.sh_produk.upload_pdf_sg') }}" method="POST" enctype="multipart/form-data" class="form-inline">
                        @csrf
                        <span class="badge badge-warning mr-1">PDF SG</span>
                        <input type="file" name="file_pdf_sg" accept=".pdf" required class="form-control form-control-sm mr-1" style="max-width:190px;">
                        <input type="date" name="tanggal_pdf_sg" value="{{ date('Y-m-d') }}" required class="form-control form-control-sm mr-1" style="max-width:140px;">
                        <button type="submit" class="btn btn-sm btn-outline-primary py-0">Upload</button>
                    </form>
                    <small class="text-muted">{{ isset($docPdfSg) && $docPdfSg ? $docPdfSg->original_name . ' • ' . $docPdfSg->total_baris . ' produk • ' . ($docPdfSg->nomor_sertifikat ?? '-') : 'belum ada (opsional)' }}</small>
                    <form action="{{ route('superuser.master.sh_produk.run_sg') }}" method="POST" class="d-inline ml-2">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-success py-0" onclick="return confirm('Jalankan pencocokan SG ulang?')">Run SG</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @php
                $st = request('status', '');
                $sb = request('sumber', '');
                $cAll = ($stats['Cocok'] ?? 0) + ($stats['Perlu ditinjau'] ?? 0) + ($stats['Tidak cocok'] ?? 0);
                $tabUrl = function ($s) { $p = ['status' => $s]; if (request('cari')) $p['cari'] = request('cari'); if (request('sumber')) $p['sumber'] = request('sumber'); return route('superuser.master.sh_produk.index', $p); };
                $srcUrl = function ($s) { $p = []; if (request('status')) $p['status'] = request('status'); if (request('cari')) $p['cari'] = request('cari'); if ($s) $p['sumber'] = $s; return route('superuser.master.sh_produk.index', $p); };
            @endphp
            <div class="toolbar">
                <div class="workflow-tabs" id="sh-tabs">
                    <a class="menu-tab {{ $sb === '' ? 'active' : '' }}" href="{{ $srcUrl('') }}">ID+SG</a>
                    <a class="menu-tab {{ $sb === 'ID' ? 'active' : '' }}" href="{{ $srcUrl('ID') }}">ID ({{ ($statsId['Cocok'] ?? 0) + ($statsId['Perlu ditinjau'] ?? 0) + ($statsId['Tidak cocok'] ?? 0) }})</a>
                    <a class="menu-tab {{ $sb === 'SG' ? 'active' : '' }}" href="{{ $srcUrl('SG') }}">SG ({{ ($statsSg['Cocok'] ?? 0) + ($statsSg['Perlu ditinjau'] ?? 0) + ($statsSg['Tidak cocok'] ?? 0) }})</a>
                </div>
            </div>
            <div class="toolbar">
                <div class="workflow-tabs" id="sh-tabs2">
                    <a class="menu-tab {{ $st === '' ? 'active' : '' }}" data-status="" href="{{ $tabUrl('') }}">Semua <span class="badge badge-{{ $st === '' ? 'light text-dark' : 'secondary' }} ml-1" data-count="all">{{ $cAll }}</span></a>
                    <a class="menu-tab {{ $st === 'Cocok' ? 'active' : '' }}" data-status="Cocok" href="{{ $tabUrl('Cocok') }}">Cocok <span class="badge badge-{{ $st === 'Cocok' ? 'light text-dark' : 'success' }} ml-1" data-count="Cocok">{{ $stats['Cocok'] ?? 0 }}</span></a>
                    <a class="menu-tab {{ $st === 'Perlu ditinjau' ? 'active' : '' }}" data-status="Perlu ditinjau" href="{{ $tabUrl('Perlu ditinjau') }}">Tinjau <span class="badge badge-{{ $st === 'Perlu ditinjau' ? 'light text-dark' : 'warning' }} ml-1" data-count="Perlu ditinjau">{{ $stats['Perlu ditinjau'] ?? 0 }}</span></a>
                    <a class="menu-tab {{ $st === 'Tidak cocok' ? 'active' : '' }}" data-status="Tidak cocok" href="{{ $tabUrl('Tidak cocok') }}">Tidak cocok <span class="badge badge-{{ $st === 'Tidak cocok' ? 'light text-dark' : 'danger' }} ml-1" data-count="Tidak cocok">{{ $stats['Tidak cocok'] ?? 0 }}</span></a>
                </div>
                <form method="GET" action="{{ route('superuser.master.sh_produk.index') }}" class="toolbar-search mb-0" id="sh-search">
                    <input type="hidden" name="status" value="{{ $st }}" id="sh-search-status">
                    <input type="hidden" name="sumber" value="{{ $sb }}">
                    <div class="input-group input-group-sm">
                        <input type="text" name="cari" id="sh-search-q" value="{{ request('cari') }}" class="form-control" placeholder="Kode / nama produk...">
                        <div class="input-group-append">
                            <button class="btn btn-primary px-3" type="submit"><i class="fa fa-search"></i></button>
                            <a href="{{ $tabUrl($st) }}" class="btn btn-outline-secondary px-2" id="sh-search-clear" style="{{ request('cari') ? '' : 'display:none;' }}">×</a>
                        </div>
                    </div>
                </form>
            </div>

            <div id="sh-bulkbar" class="alert alert-info py-1 px-2 mb-2" style="display:none; font-size:.82rem;">
                <strong><span id="sh-selcount">0</span> dipilih</strong>
                <span class="ml-2">Ubah ke:</span>
                <button type="button" class="btn btn-sm btn-success py-0 ml-1" data-bulk="Cocok">Cocok</button>
                <button type="button" class="btn btn-sm btn-warning py-0" data-bulk="Perlu ditinjau">Tinjau</button>
                <button type="button" class="btn btn-sm btn-danger py-0" data-bulk="Tidak cocok">Tidak</button>
                <input type="text" id="sh-bulk-note" class="form-control form-control-sm d-inline-block ml-2" style="width:200px;" placeholder="catatan (opsional)">
                <button type="button" class="btn btn-sm btn-link py-0" id="sh-selclear">batalkan pilihan</button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-sm sh-table mb-0">
                    <thead class="sticky-top" style="background:#fff;">
                        <tr>
                            <th style="width:32px;"><input type="checkbox" id="sh-checkall" title="Pilih semua di halaman ini"></th>
                            <th>Produk Master</th>
                            <th>List Halal</th>
                            <th>PDF</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="sh-tbody">
                        @forelse($rows as $r)
                        <tr data-id="{{ $r->id }}">
                            <td><input type="checkbox" class="sh-check" value="{{ $r->id }}"></td>
                            <td>
                                @if($r->sumber === 'SG')
                                <span class="badge badge-primary">SG</span>
                                @else
                                <span class="badge badge-success">ID</span>
                                @endif
                                <strong>{{ $r->nama_produk }}</strong>
                                <span class="sub"> — {{ $r->merek }} • {{ $r->kode_produk }}</span>
                                <div><a href="{{ url('/halal/produk/' . $r->id . '/preview') }}" target="_blank" style="font-size:11px;">Preview PDF</a></div>
                            </td>
                            <td>
                                @if($r->nama_di_list_halal)
                                    <span title="{{ $r->nama_di_list_halal }}">{{ $r->nama_di_list_halal }}</span>
                                    <span class="badge badge-light">#{{ $r->nomor_urut_list_halal }}</span>
                                    <span class="sub">({{ $r->kunci_yang_dipakai ?? '-' }})</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($r->halaman_pdf)
                                    <span title="Nomor urut PDF #{{ $r->nomor_urut_pdf }}">Hal. {{ $r->halaman_pdf }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($r->status_pencocokan == 'Cocok')
                                <span class="badge badge-success" @if($r->catatan_tinjauan)title="{{ $r->catatan_tinjauan }}"@endif>Cocok</span>
                                @elseif($r->status_pencocokan == 'Perlu ditinjau')
                                <span class="badge badge-warning" @if($r->catatan_tinjauan)title="{{ $r->catatan_tinjauan }}"@endif>Tinjau</span>
                                @else
                                <span class="badge badge-danger" @if($r->catatan_tinjauan)title="{{ $r->catatan_tinjauan }}"@endif>Tidak</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline-primary py-0 px-2" style="font-size:11.5px;" data-toggle="modal" data-target="#modal-tinjau"
                                    data-id="{{ $r->id }}" data-nama="{{ $r->nama_produk }}" data-kode="{{ $r->merek }} • {{ $r->kode_produk }}"
                                    data-status="{{ $r->status_pencocokan }}" data-catatan="{{ $r->catatan_tinjauan }}">
                                    Tinjau
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">Belum ada data. Upload sumber lalu jalankan pencocokan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-2 d-flex align-items-center" id="sh-pager-wrap">
                <div id="sh-pager">{{ $rows->links() }}</div>
                <small class="text-muted ml-2" id="sh-total"></small>
            </div>

            <div class="mt-2">
                <a data-toggle="collapse" href="#riwayat-dok" role="button" aria-expanded="false" class="small">Riwayat dokumen sumber (klik untuk buka)</a>
                <div id="riwayat-dok" class="collapse mt-1">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr><th>Jenis</th><th>Nama File</th><th>Tgl Versi</th><th>Isi</th><th>Sertifikat</th><th>Diunggah</th></tr>
                        </thead>
                        <tbody>
                            @forelse($docs as $d)
                            <tr>
                                <td><span class="badge badge-{{ $d->jenis == 'list' ? 'info' : 'danger' }}">{{ $d->jenis }}</span></td>
                                <td>{{ $d->original_name }}</td>
                                <td>{{ $d->tanggal_versi }}</td>
                                <td>{{ $d->total_baris }}</td>
                                <td><small>{{ $d->nomor_sertifikat ?? '-' }}</small></td>
                                <td><small>{{ $d->created_at }}</small></td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted">Belum ada dokumen.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection

@section('modal')
<div class="modal fade" id="modal-tinjau" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <form id="form-tinjau" method="POST" action="">
      @csrf
      <div class="modal-content">
        <div class="modal-header py-2">
          <h5 class="modal-title" style="font-size:.95rem;">Tinjau Produk</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <p class="mb-2"><strong id="tinjau-nama"></strong><br><small class="text-muted" id="tinjau-kode"></small></p>
          <div class="form-group mb-2">
            <label class="mb-1">Status</label>
            <select name="status_pencocokan" id="tinjau-status" class="form-control form-control-sm">
              <option value="Cocok">Cocok — tampil di landing</option>
              <option value="Perlu ditinjau">Perlu ditinjau — tombol ditahan</option>
              <option value="Tidak cocok">Tidak cocok — disembunyikan</option>
            </select>
          </div>
          <div class="form-group mb-0">
            <label class="mb-1">Catatan</label>
            <input type="text" name="catatan_tinjauan" id="tinjau-catatan" class="form-control form-control-sm" placeholder="alasan / keputusan">
          </div>
        </div>
        <div class="modal-footer py-2">
          <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  $('#modal-tinjau').on('show.bs.modal', function (e) {
    var b = $(e.relatedTarget);
    $('#tinjau-nama').text(b.data('nama'));
    $('#tinjau-kode').text(b.data('kode'));
    $('#tinjau-status').val(b.data('status'));
    $('#tinjau-catatan').val(b.data('catatan'));
    $('#form-tinjau').attr('action', '{{ url('superuser/master/sh_produk/review') }}/' + b.data('id'));
  });
</script>
@endpush
