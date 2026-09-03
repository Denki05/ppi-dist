@extends('superuser.app')

@section('content')

<div class="row mb-30">
    <div class="col-md-8">
        <h4 style="font-weight: bold;"><i class="fa fa-clipboard-check mr-2"></i>QA CHECKLIST - FLOW SALES ORDER</h4>
        <p class="text-muted mb-0" style="font-size:13px;">
            Test sebelum upload ke trial program dan di coba user
        </p>
    </div>
    <div class="col-md-4 text-right">
        <a href="{{ route('superuser.penjualan.qa_checklist_excel') }}" class="btn btn-sm btn-outline-success">
            <i class="fa fa-file-excel mr-1"></i> Download Excel
        </a>
        <button type="button" class="btn btn-sm btn-outline-primary" id="btnExportChecklist">
            <i class="fa fa-print mr-1"></i> Print Page
        </button>
        <button type="button" class="btn btn-sm btn-outline-danger" id="btnResetChecklist">
            <i class="fa fa-refresh mr-1"></i> Reset
        </button>
    </div>
</div>

<!-- Progress Bar -->
<div class="block mb-20">
    <div class="block-content block-content-full" style="padding: 15px;">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="font-weight-bold">Progress Test</span>
            <span id="progressText">0 / 0 selesai</span>
        </div>
        <div class="progress" style="height: 25px;">
            <div id="progressBar" class="progress-bar bg-success" role="progressbar" style="width: 0%"></div>
        </div>
    </div>
</div>

<!-- ================================================================ -->
<!-- PHASE 1: SALES ORDER AWAL -->
<!-- ================================================================ -->
<div class="block mb-20">
    <div class="block-header block-header-default">
        <h3 class="block-title">
            <span class="badge badge-primary mr-2">PHASE 1</span>
            <b>SALES ORDER AWAL (Cash & Tempo)</b>
        </h3>
    </div>
    <div class="block-content">
        <table class="table table-sm table-vcenter mb-0">
            <thead class="thead-light">
                <tr>
                    <th style="width:5%">✓</th>
                    <th style="width:5%">#</th>
                    <th style="width:45%">Check Item</th>
                    <th style="width:25%">Catatan / Hasil</th>
                    <th style="width:20%">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr class="table-light"><td colspan="5"><b>CASH</b></td></tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>1.1</td>
                    <td>Buka modal Add SO → Pilih Customer</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>1.2</td>
                    <td>Pilih Type: <b>CASH</b> → checkbox "Estimate?" otomatis centang</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>1.3</td>
                    <td>Input Kurs, Disc %, Disc Kemasan, Disc IDR</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>1.4</td>
                    <td>Pilih Brand, Kemasan, Indent → Klik "Add"</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>1.5</td>
                    <td>Redirect ke halaman Create SO → Item muncul</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>

                <tr class="table-light"><td colspan="5"><b>TEMPO</b></td></tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>1.6</td>
                    <td>Buka modal Add SO → Pilih Customer → Type: <b>TEMPO</b></td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>1.7</td>
                    <td>Checkbox "Estimate?" tidak otomatis centang (sesuai UX Cash vs Tempo)</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>1.8</td>
                    <td>Input data lengkap → Klik "Add" → Redirect ke Create SO</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ================================================================ -->
<!-- PHASE 2: ESTIMATE -->
<!-- ================================================================ -->
<div class="block mb-20">
    <div class="block-header block-header-default">
        <h3 class="block-title">
            <span class="badge badge-info mr-2">PHASE 2</span>
            <b>ESTIMATE / PDF ESTIMATE</b>
        </h3>
    </div>
    <div class="block-content">
        <table class="table table-sm table-vcenter mb-0">
            <thead class="thead-light">
                <tr>
                    <th style="width:5%">✓</th>
                    <th style="width:5%">#</th>
                    <th style="width:45%">Check Item</th>
                    <th style="width:25%">Catatan / Hasil</th>
                    <th style="width:20%">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>2.1</td>
                    <td>Klik "Estimate PDF" → PDF terbuka 1 halaman</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>2.2</td>
                    <td>PDF: Header "SALES ESTIMATE" + Customer + Tanggal SO + AO/Sales tampil</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>2.3</td>
                    <td>PDF: Note merah (estimasi harga, belum termasuk ongkir, stock & kurs)</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>2.4</td>
                    <td>PDF: Tabel produk (No, Produk, Qty, Kemasan, Harga, Disc, Netto, Jumlah)</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>2.5</td>
                    <td>PDF: Terbilang, Kurs USD, Sub total, Disc %, Grand Total</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>2.6</td>
                    <td>PDF: Tanda tangan "Hormat kami" di posisi kanan bawah</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ================================================================ -->
<!-- PHASE 3: LANJUTKAN SO -->
<!-- ================================================================ -->
<div class="block mb-20">
    <div class="block-header block-header-default">
        <h3 class="block-title">
            <span class="badge badge-warning mr-2">PHASE 3</span>
            <b>LANJUTKAN SO → SO LANJUTAN</b>
        </h3>
    </div>
    <div class="block-content">
        <table class="table table-sm table-vcenter mb-0">
            <thead class="thead-light">
                <tr>
                    <th style="width:5%">✓</th>
                    <th style="width:5%">#</th>
                    <th style="width:45%">Check Item</th>
                    <th style="width:25%">Catatan / Hasil</th>
                    <th style="width:20%">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>3.1</td>
                    <td>Klik "Lanjutkan" di SO Awal → Konfirmasi SweetAlert muncul</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>3.2</td>
                    <td>SO berpindah ke tab "SO LANJUTAN" dengan benar</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>3.3</td>
                    <td>SO muncul di tabel "SO Lanjutan" dengan status benar</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ================================================================ -->
<!-- PHASE 4: TUTUP SO → LIST QUEUE -->
<!-- ================================================================ -->
<div class="block mb-20">
    <div class="block-header block-header-default">
        <h3 class="block-title">
            <span class="badge badge-success mr-2">PHASE 4</span>
            <b>TUTUP SO → LIST QUEUE</b>
        </h3>
    </div>
    <div class="block-content">
        <table class="table table-sm table-vcenter mb-0">
            <thead class="thead-light">
                <tr>
                    <th style="width:5%">✓</th>
                    <th style="width:5%">#</th>
                    <th style="width:45%">Check Item</th>
                    <th style="width:25%">Catatan / Hasil</th>
                    <th style="width:20%">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>4.1</td>
                    <td>Klik "Tutup SO" → SweetAlert konfirmasi</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>4.2</td>
                    <td>SO berpindah ke tab "LIST QUEUE" (Packing Order)</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>4.3</td>
                    <td>Stok di-reserve (reserved_quantity bertambah)</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>4.4</td>
                    <td>Kurs valid (idr_rate > 1) → Invoice terbuat</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ================================================================ -->
<!-- PHASE 5: TEST REVISI SO -->
<!-- ================================================================ -->
<div class="block mb-20">
    <div class="block-header block-header-default">
        <h3 class="block-title">
            <span class="badge badge-secondary mr-2">PHASE 5</span>
            <b>TEST REVISI SO</b>
        </h3>
    </div>
    <div class="block-content">
        <table class="table table-sm table-vcenter mb-0">
            <thead class="thead-light">
                <tr>
                    <th style="width:5%">✓</th>
                    <th style="width:5%">#</th>
                    <th style="width:45%">Check Item</th>
                    <th style="width:25%">Catatan / Hasil</th>
                    <th style="width:20%">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>5.1</td>
                    <td>Klik "Revisi" di List Queue → Form revisi terbuka</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>5.2</td>
                    <td>Ubah qty/harga/disc → Simpan revisi</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>5.3</td>
                    <td>Revisi tercatat di history (count_rev bertambah)</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ================================================================ -->
<!-- PHASE 6: DO - PICKER -->
<!-- ================================================================ -->
<div class="block mb-20">
    <div class="block-header block-header-default">
        <h3 class="block-title">
            <span class="badge badge-danger mr-2">PHASE 6</span>
            <b>DO - PICKER</b>
        </h3>
    </div>
    <div class="block-content">
        <table class="table table-sm table-vcenter mb-0">
            <thead class="thead-light">
                <tr>
                    <th style="width:5%">✓</th>
                    <th style="width:5%">#</th>
                    <th style="width:45%">Check Item</th>
                    <th style="width:25%">Catatan / Hasil</th>
                    <th style="width:20%">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>6.1</td>
                    <td>DO terbuat dari SO → Status Packing Order = Ready</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>6.2</td>
                    <td>Packing Order items sesuai dengan SO items</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ================================================================ -->
<!-- PHASE 7: DO - CHECKER -->
<!-- ================================================================ -->
<div class="block mb-20">
    <div class="block-header block-header-default">
        <h3 class="block-title">
            <span class="badge badge-dark mr-2">PHASE 7</span>
            <b>DO - CHECKER (Status 3 → 4)</b>
        </h3>
    </div>
    <div class="block-content">
        <table class="table table-sm table-vcenter mb-0">
            <thead class="thead-light">
                <tr>
                    <th style="width:5%">✓</th>
                    <th style="width:5%">#</th>
                    <th style="width:45%">Check Item</th>
                    <th style="width:25%">Catatan / Hasil</th>
                    <th style="width:20%">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>7.1</td>
                    <td>Buka DO di halaman Checker → Item terlist dengan benar</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>7.2</td>
                    <td>Checklist semua item (checkbox harus centang semua)</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>7.3</td>
                    <td>Jika belum centang semua → tombol Save tidak bisa diklik</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>7.4</td>
                    <td>Klik "Save" → Status DO berubah ke "Siap Kirim" (Status 4)</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ================================================================ -->
<!-- PHASE 8: TEST REVISI ULANG -->
<!-- ================================================================ -->
<div class="block mb-20">
    <div class="block-header block-header-default">
        <h3 class="block-title">
            <span class="badge badge-info mr-2">PHASE 8</span>
            <b>TEST REVISI ULANG</b>
        </h3>
    </div>
    <div class="block-content">
        <table class="table table-sm table-vcenter mb-0">
            <thead class="thead-light">
                <tr>
                    <th style="width:5%">✓</th>
                    <th style="width:5%">#</th>
                    <th style="width:45%">Check Item</th>
                    <th style="width:25%">Catatan / Hasil</th>
                    <th style="width:20%">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>8.1</td>
                    <td>Setelah DO Status 4, cek apakah bisa revisi lagi</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>8.2</td>
                    <td>Jika sudah pernah di-revisi → tombol "Ajukan Revisi" disabled</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ================================================================ -->
<!-- PHASE 9: DO - DELIVERING -->
<!-- ================================================================ -->
<div class="block mb-20">
    <div class="block-header block-header-default">
        <h3 class="block-title">
            <span class="badge badge-primary mr-2">PHASE 9</span>
            <b>DO - DELIVERING (Status 4 → 5)</b>
        </h3>
    </div>
    <div class="block-content">
        <table class="table table-sm table-vcenter mb-0">
            <thead class="thead-light">
                <tr>
                    <th style="width:5%">✓</th>
                    <th style="width:5%">#</th>
                    <th style="width:45%">Check Item</th>
                    <th style="width:25%">Catatan / Hasil</th>
                    <th style="width:20%">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>9.1</td>
                    <td>Cetak Surat Jalan (SJ) → PDF SJ terbuka</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>9.2</td>
                    <td>Klik "Delivering" → Konfirmasi → Status berubah ke 5</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>9.3</td>
                    <td>Stok fisik terpotong (quantity berkurang di ProductMinStock)</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>9.4</td>
                    <td>Kurs guard: Jika kurs ≤ 1 → DIBLOKIR, tidak bisa lanjut</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ================================================================ -->
<!-- PHASE 10: UPDATE RESI -->
<!-- ================================================================ -->
<div class="block mb-20">
    <div class="block-header block-header-default">
        <h3 class="block-title">
            <span class="badge badge-success mr-2">PHASE 10</span>
            <b>UPDATE RESI (Status 5 → 6)</b>
        </h3>
    </div>
    <div class="block-content">
        <table class="table table-sm table-vcenter mb-0">
            <thead class="thead-light">
                <tr>
                    <th style="width:5%">✓</th>
                    <th style="width:5%">#</th>
                    <th style="width:45%">Check Item</th>
                    <th style="width:25%">Catatan / Hasil</th>
                    <th style="width:20%">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>10.1</td>
                    <td>Upload foto bukti kirim (1-2 foto)</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>10.2</td>
                    <td>Input Ongkir + Resi (Ekspedisi + Nominal)</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>10.3</td>
                    <td>Klik "Selesaikan" → Status DO berubah ke "Delivered" (6)</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>10.4</td>
                    <td>Stock Move tercatat di kartu stok (StockMove)</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>10.5</td>
                    <td>Invoice terupdate (grand_total_idr sesuai)</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ================================================================ -->
<!-- PHASE 11: REVISI INTERNAL -->
<!-- ================================================================ -->
<div class="block mb-20">
    <div class="block-header block-header-default">
        <h3 class="block-title">
            <span class="badge badge-warning mr-2">PHASE 11</span>
            <b>REVISI INTERNAL DO</b>
        </h3>
    </div>
    <div class="block-content">
        <table class="table table-sm table-vcenter mb-0">
            <thead class="thead-light">
                <tr>
                    <th style="width:5%">✓</th>
                    <th style="width:5%">#</th>
                    <th style="width:45%">Check Item</th>
                    <th style="width:25%">Catatan / Hasil</th>
                    <th style="width:20%">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr class="table-light"><td colspan="5"><b>PENGAJUAN</b></td></tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>11.1</td>
                    <td>Klik "Ajukan Revisi" di DO Status 5/6 → Form terbuka</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>11.2</td>
                    <td>Ubah qty/harga/disc → Kirim pengajuan (status = Pending)</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>11.3</td>
                    <td>DO terkunci (internal_revision_status = 1) → tidak bisa diajukan ulang</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>

                <tr class="table-light"><td colspan="5"><b>APPROVAL</b></td></tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>11.4</td>
                    <td>Buka halaman "Revisi Internal" (tab SO Lanjutan) → Data muncul</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>11.5</td>
                    <td>Klik "Lihat Detail" → Modal terbuka dengan data before/after</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>11.6</td>
                    <td>Modal close button (×) berfungsi dengan benar</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>

                <tr class="table-light"><td colspan="5"><b>OTP & APPROVE</b></td></tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>11.7</td>
                    <td>Klik "Kirim Kode OTP" → SweetAlert konfirmasi</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>11.8</td>
                    <td>OTP muncul di modal (kotak kuning) + countdown 5 menit</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>11.9</td>
                    <td>Input OTP manual + Alasan → Klik "Approve Sekarang"</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>11.10</td>
                    <td>SweetAlert konfirmasi approve → Proses → Success → Reload</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>

                <tr class="table-light"><td colspan="5"><b>HASIL APPROVE</b></td></tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>11.11</td>
                    <td>DO items terupdate sesuai revisi</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>11.12</td>
                    <td>PackingOrderDetail terupdate (disc, ongkir, grand total)</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>11.13</td>
                    <td>Invoice terupdate (grand_total_idr sesuai)</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>11.14</td>
                    <td>Stok terkoreksi sesuai delta qty</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input type="checkbox" class="checklist-item"></td>
                    <td>11.15</td>
                    <td>internal_revision_count bertambah → DO tidak bisa diajukan lagi</td>
                    <td><input type="text" class="form-control form-control-sm note-input" placeholder="catatan..."></td>
                    <td>
                        <select class="form-control form-control-sm status-input">
                            <option value="">-</option>
                            <option value="pass">✅ Pass</option>
                            <option value="fail">❌ Fail</option>
                            <option value="skip">⏭ Skip</option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Summary -->
<div class="block mb-20">
    <div class="block-header block-header-default">
        <h3 class="block-title"><b>SUMMARY</b></h3>
    </div>
    <div class="block-content">
        <div class="row">
            <div class="col-md-4">
                <div class="card text-center p-3" style="background:#d4edda;">
                    <h3 id="countPass" class="mb-0">0</h3>
                    <small class="text-success">✅ Pass</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center p-3" style="background:#f8d7da;">
                    <h3 id="countFail" class="mb-0">0</h3>
                    <small class="text-danger">❌ Fail</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center p-3" style="background:#fff3cd;">
                    <h3 id="countSkip" class="mb-0">0</h3>
                    <small class="text-warning">⏭ Skip</small>
                </div>
            </div>
        </div>
        <div class="form-group mt-3">
            <label><b>Catatan Tester:</b></label>
            <textarea class="form-control" id="testerNotes" rows="4" placeholder="Tulis catatan, bug ditemukan, atau rekomendasi..."></textarea>
        </div>
        <div class="row mt-2">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Nama Tester:</label>
                    <input type="text" class="form-control" id="testerName" placeholder="Nama tester">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Tanggal Test:</label>
                    <input type="date" class="form-control" id="testDate" value="{{ date('Y-m-d') }}">
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Load saved state from localStorage
    var savedState = JSON.parse(localStorage.getItem('qa_checklist_state') || '{}');
    
    // Restore checkboxes and inputs
    $('.checklist-item').each(function(i) {
        var key = 'item_' + i;
        if (savedState[key]) {
            $(this).prop('checked', true);
        }
    });
    
    $('.note-input').each(function(i) {
        var key = 'note_' + i;
        if (savedState[key]) {
            $(this).val(savedState[key]);
        }
    });
    
    $('.status-input').each(function(i) {
        var key = 'status_' + i;
        if (savedState[key]) {
            $(this).val(savedState[key]);
        }
    });
    
    if (savedState.testerName) $('#testerName').val(savedState.testerName);
    if (savedState.testerNotes) $('#testerNotes').val(savedState.testerNotes);
    
    // Update progress on change
    function updateProgress() {
        var total = $('.checklist-item').length;
        var checked = $('.checklist-item:checked').length;
        var pct = total > 0 ? Math.round((checked / total) * 100) : 0;
        
        $('#progressBar').css('width', pct + '%').text(pct + '%');
        $('#progressText').text(checked + ' / ' + total + ' selesai (' + pct + '%)');
        
        // Count by status
        var pass = 0, fail = 0, skip = 0;
        $('.status-input').each(function() {
            if ($(this).val() === 'pass') pass++;
            else if ($(this).val() === 'fail') fail++;
            else if ($(this).val() === 'skip') skip++;
        });
        $('#countPass').text(pass);
        $('#countFail').text(fail);
        $('#countSkip').text(skip);
        
        // Save state
        saveState();
    }
    
    function saveState() {
        var state = {};
        $('.checklist-item').each(function(i) {
            state['item_' + i] = $(this).prop('checked');
        });
        $('.note-input').each(function(i) {
            state['note_' + i] = $(this).val();
        });
        $('.status-input').each(function(i) {
            state['status_' + i] = $(this).val();
        });
        state.testerName = $('#testerName').val();
        state.testerNotes = $('#testerNotes').val();
        localStorage.setItem('qa_checklist_state', JSON.stringify(state));
    }
    
    // Event listeners
    $(document).on('change', '.checklist-item, .status-input', updateProgress);
    $(document).on('input', '.note-input', function() { saveState(); });
    $(document).on('input', '#testerName, #testerNotes', function() { saveState(); });
    
    // Reset
    $('#btnResetChecklist').on('click', function() {
        if (!confirm('Yakin ingin reset semua checklist?')) return;
        localStorage.removeItem('qa_checklist_state');
        $('.checklist-item').prop('checked', false);
        $('.note-input').val('');
        $('.status-input').val('');
        $('#testerName').val('');
        $('#testerNotes').val('');
        updateProgress();
    });
    
    // Print
    $('#btnExportChecklist').on('click', function() {
        window.print();
    });
    
    // Initial count
    updateProgress();
});
</script>
@endpush
