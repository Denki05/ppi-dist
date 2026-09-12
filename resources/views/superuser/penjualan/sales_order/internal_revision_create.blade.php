@extends('superuser.app')

@section('content')

@php
    $allErrors = collect([]);
    if($errors->any()){
        $allErrors = $allErrors->merge($errors->all());
    }
    if(session('errors') && count(session('errors')) > 0){
        $allErrors = $allErrors->merge(session('errors'));
    }
@endphp

@if($allErrors->count() > 0)
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <h5 class="alert-heading">Error</h5>
    <ul class="mb-0">
        @foreach($allErrors->unique() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('warnings') && count(session('warnings')) > 0)
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <h5 class="alert-heading">Warning</h5>
    <ul class="mb-0">
        @foreach(session('warnings') as $warning)
            <li>{{ $warning }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session()->has('message'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <h5 class="alert-heading">Success</h5>
    <p class="mb-0">{{ session('message') }}</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div id="alert-block"></div>

<div id="page-loading-overlay" style="position:fixed;inset:0;background:rgba(255,255,255,0.85);z-index:9999;display:flex;flex-direction:column;align-items:center;justify-content:center;">
  <div class="spinner-border text-primary" role="status" style="width:3rem;height:3rem;"></div>
  <p class="mt-15" style="font-weight:600;">Harap tunggu sebentar, sedang memuat data revisi...</p>
</div>

<div class="alert alert-info">
    <b>Perhatian:</b> Perubahan qty/produk akan memicu cetak ulang Surat Jalan setelah disetujui.
    Pengajuan ini akan menahan (hold) invoice sampai disetujui/ditolak oleh Management/Developer.
</div>

<form method="POST" action="{{ route('superuser.penjualan.internal_revision.store') }}" id="frmInternalRevision">
@csrf
<input type="hidden" name="do_id" value="{{ $result->id }}">

<div class="row">
    <div class="col-6">
        <div class="block">
            <div class="block-header block-header-default">
                <h3 class="block-title">#Info DO</h3>
            </div>
            <div class="block-content">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>DO Code</label>
                        <input type="text" class="form-control" readonly value="{{ $result->do_code }}">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Status DO Saat Ini</label>
                        <input type="text" class="form-control" readonly
                            value="{{ $result->do_status()->msg ?? '-' }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Customer</label>
                        @php
                            // Pakai relasi yang sudah ada (seperti sebelumnya): DO member -> SO member -> master customer
                            $custName = $result->member->name ?? $result->so->member->name ?? $result->customer->name ?? $result->so->customer->name ?? '-';
                            $custKota = $result->member->text_kota ?? $result->so->member->text_kota ?? '';
                        @endphp
                        <input type="text" class="form-control" readonly
                            value="{{ $custName }} {{ $custKota }}">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Kurs IDR</label>
                        <input type="text" id="idr_rate_display" class="form-control"
                            value="{{ number_format((float) $result->idr_rate, 0, ',', '.') }}" placeholder="cth: 18.050">
                        <input type="hidden" name="idr_rate" id="idr_rate" value="{{ $result->idr_rate }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Type Transaksi</label>
                        <input type="text" class="form-control" readonly value="{{ $result->type_transaction }}">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Brand</label>
                        <input type="text" class="form-control" readonly value="{{ $result->so->brand_name ?? '-' }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6">
        <div class="block">
            <div class="block-header block-header-default">
                <h3 class="block-title">#Customer Info</h3>
            </div>
            <div class="block-content">
                {{-- Hanya Sales + No Rek + Disc USD sejajar (sales senior tidak perlu).
                     Disc USD = set massal kolom Disc (USD) per item, kecuali produk Free. --}}
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="sales_id">Sales <span class="text-danger">*</span></label>
                        <select class="form-control js-select2" name="sales_id" required>
                            <option value="">Pilih Sales</option>
                            @foreach(\App\Entities\Penjualan\SalesOrder::SALES as $sales => $sales_value)
                            <option value="{{ $sales_value }}" @if(isset($result->so->sales_id) && $result->so->sales_id == $sales_value) selected @endif>{{ $sales }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="rekening_id">No Rek Admin <span class="text-danger">*</span></label>
                        <select class="form-control js-select2" name="rekening_id" required>
                            <option value="">Pilih Rekening</option>
                            @foreach($rekening as $rek)
                            <option value="{{ $rek->id }}" @if(isset($result->so->rekening) && $result->so->rekening == $rek->id) selected @endif>{{ $rek->name }} - {{ $rek->number_card }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="base_id">Disc USD</label>
                        @php
                            // Preselect sesuai nilai sebelumnya: samakan pola create_lanjutan ($result->disc_usd).
                            // DO tidak punya field disc_usd, jadi ambil dari SO, fallback ke usd_disc item DO bila seragam.
                            $baseDiscDefault = 0;
                            $soDisc = $result->so->disc_usd ?? null;
                            if (in_array((float) ($soDisc ?? -1), [0, 2, 4], true)) {
                                $baseDiscDefault = (float) $soDisc;
                            } else {
                                $itemDiscs = $result->do_detail->map(function ($d) { return (float) ($d->usd_disc ?? 0); })->unique()->values();
                                if ($itemDiscs->count() === 1 && in_array($itemDiscs->first(), [0.0, 2.0, 4.0], true)) {
                                    $baseDiscDefault = $itemDiscs->first();
                                }
                            }
                        @endphp
                        <select class="form-control js-select2 base_disc" id="base_id">
                            <option value="0" @if($baseDiscDefault == 0) selected @endif>$0</option>
                            <option value="2" @if($baseDiscDefault == 2) selected @endif>$2</option>
                            <option value="4" @if($baseDiscDefault == 4) selected @endif>$4</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label>Alasan Revisi <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="request_reason" rows="3" minlength="10" required
                            placeholder="Jelaskan kesalahan/perubahan yang terjadi, minimal 10 karakter"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <aside class="col-lg-9">
        <div class="card border-0">
            <div class="table-responsive">
                <table class="table table-hover" id="datatables" style="white-space:nowrap;width:100%;">
                    <thead class="text-muted">
                        <tr class="small text-uppercase">
                            <th class="block" style="width:auto"></th>
                            <th class="block" style="width:auto">#</th>
                            <th class="block" style="width:18%">Product</th>
                            <th class="block" style="width:auto">Qty Asal</th>
                            <th class="block" style="width:7%">Qty Baru</th>
                            <th class="block" style="width:10%">Harga (USD)</th>
                            <th class="block" style="width:auto">Free</th>
                            <th class="block" style="width:17%">Kemasan</th>
                            <th class="block" style="width:13%">Disc (USD)</th>
                            <th class="block" style="width:15%">Total (IDR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($result->do_detail as $index => $detail)
                        @php
                            $priceRupiah = round((float) $detail->price * (float) $result->idr_rate);
                            $discRupiah = round((float) ($detail->usd_disc ?? 0) * (float) $result->idr_rate);
                            // Samakan create_lanjutan: status Free dibaca dari SO item terkait (DO tidak punya flag free sendiri)
                            $isFree = (int) (optional($detail->so_item)->free_product ?? 0) === 1;
                        @endphp
                        <tr class="index{{ $index }} row-item" data-index="{{ $index }}">
                            <input type="hidden" name="items[{{ $index }}][do_item_id]" value="{{ $detail->id }}">
                            <input type="hidden" name="items[{{ $index }}][product_packaging_id]" value="{{ $detail->product_packaging_id }}">
                            <input type="hidden" name="items[{{ $index }}][qty_asal]" value="{{ $detail->qty }}">
                            <input type="hidden" name="items[{{ $index }}][price]" class="hidden-price-usd" value="{{ $detail->price }}">
                            <input type="hidden" name="items[{{ $index }}][usd_disc]" class="hidden-disc-usd" value="{{ $detail->usd_disc ?? 0 }}">
                            <input type="hidden" name="items[{{ $index }}][percent_disc]" value="{{ $detail->percent_disc ?? 0 }}">

                            <td>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" title="Hapus produk ini dari DO">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $detail->product_pack->code ?? '' }} - <b>{{ $detail->product_pack->name ?? '' }}</b></td>
                            <td class="text-center">{{ $detail->qty }}</td>
                            <td>
                                <input type="number" name="items[{{ $index }}][qty]" class="form-control text-center count" data-index="{{ $index }}" value="{{ $detail->qty }}" step="any" required>
                            </td>
                            <td>
                                <input type="text" class="form-control text-center price-usd-input" data-index="{{ $index }}" value="{{ number_format((float)($isFree ? 0 : $detail->price), 2, ',', '.') }}" @if($isFree) readonly @endif>
                            </td>
                            <td class="text-center">
                                {{-- Samakan create_lanjutan: tampilkan status Free, disabled (info saja, tidak dikirim) --}}
                                <input class="form-check-input free-count" type="checkbox" value="{{ $isFree ? 1 : 0 }}" @if($isFree) checked @endif disabled>
                            </td>
                            <td>
                                <input type="text" class="form-control text-center" readonly value="{{ $detail->product_pack->packaging->pack_name ?? '' }}">
                            </td>
                            <td>
                                <input type="text" class="form-control text-center disc-usd-input count-disc" data-index="{{ $index }}" value="{{ number_format((float)($isFree ? 0 : ($detail->usd_disc ?? 0)), 2, ',', '.') }}" @if($isFree) readonly @endif>
                            </td>
                            <td>
                                <input type="text" name="items[{{ $index }}][total]" class="form-control text-center" readonly>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="row-footer-subtotal">
                            <td colspan="9" class="text-right"><b>Subtotal</b></td>
                            <td class="text-center">
                                <input type="text" class="form-control sub-total-item-display" readonly>
                            </td>
                        </tr>
                    </tfoot>
                </table>
                <div class="p-15">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddProduct">
                        <i class="fa fa-plus"></i> Tambah Produk
                    </button>
                </div>
            </div>
        </div>
    </aside>

    <aside class="col-lg-3">
        <div class="card border-0">
            <div class="card-body">
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Disc %</label>
                    <div class="col-sm-3">
                        <input type="text" class="form-control" id="disc_agen_percent" name="disc_agen_percent" value="{{ $result->do_detail_cost->discount_1 ?? 0 }}">
                    </div>
                    <div class="col-sm-5">
                        <input type="text" readonly class="form-control" id="disc_agen_idr" name="disc_agen_idr">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Disc Kemasan</label>
                    <div class="col-sm-3">
                        <input type="text" class="form-control" id="disc_kemasan_percent" name="disc_kemasan_percent" value="{{ $result->do_detail_cost->discount_2 ?? 0 }}">
                    </div>
                    <div class="col-sm-5">
                        <input type="text" readonly class="form-control" id="disc_kemasan_idr" name="disc_kemasan_idr">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Disc IDR</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="disc_tambahan_idr" name="disc_tambahan_idr" value="{{ $result->do_detail_cost->discount_idr ?? 0 }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Voucher</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="voucher_idr" name="voucher_idr" value="{{ $result->do_detail_cost->voucher_idr ?? 0 }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Ongkir</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="delivery_cost_idr" name="delivery_cost_idr"
                            value="{{ optional($result->do_detail_cost)->delivery_cost_idr ?? 0 }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Biaya Lain</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="other_cost_idr" name="other_cost_idr"
                            value="{{ optional($result->do_detail_cost)->other_cost_idr ?? 0 }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Grand Total</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="grand_total_idr" readonly>
                        <input type="hidden" id="subtotal_2">
                    </div>
                </div>

                <div class="alert alert-info mt-10 mb-15" style="font-size:12px;">
                    <i class="fa fa-info-circle"></i> Preview saja — total akhir dihitung ulang oleh sistem saat disetujui.
                </div>

                <div class="mt-3">
                    <button type="button" class="btn btn-warning mb-2" id="btn_call">
                        <i class="fas fa-calculator pr-2"></i> Calculated
                    </button>
                    <button type="submit" class="btn btn-primary mb-2" id="btn_submit">
                        <i class="fa fa-paper-plane pr-2"></i> Kirim Pengajuan
                    </button>
                </div>
            </div>
        </div>
    </aside>
</div>

<div class="row pt-30 mb-15">
    <div class="col-md-6">
        <a href="{{ route('superuser.penjualan.sales_order.index_lanjutan') }}">
            <button type="button" class="btn bg-gd-cherry border-0 text-white">
                <i class="fa fa-arrow-left mr-10"></i> Back
            </button>
        </a>
    </div>
</div>

</form>

<template id="tplNewProductRow">
    <tr class="row-item row-new-product">
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="fa fa-trash"></i></button>
        </td>
        <td>-</td>
        <td>
            <select class="form-control select2-new-product" style="width:100%;"></select>
            <input type="hidden" name="items[__INDEX__][do_item_id]" value="">
            <input type="hidden" name="items[__INDEX__][product_packaging_id]" class="input-product-id" value="">
            <input type="hidden" name="items[__INDEX__][qty_asal]" value="0">
            <input type="hidden" name="items[__INDEX__][price]" class="hidden-price-usd" value="0">
            <input type="hidden" name="items[__INDEX__][usd_disc]" class="hidden-disc-usd" value="0">
            <input type="hidden" name="items[__INDEX__][percent_disc]" value="0">
        </td>
        <td>0 <span class="text-muted">(baru)</span></td>
        <td><input type="number" step="any" class="form-control text-center count" data-index="__INDEX__" name="items[__INDEX__][qty]" value="1" required></td>
        <td><input type="text" class="form-control text-center price-usd-input" data-index="__INDEX__" value="0.00"></td>
        <td class="text-center"><input class="form-check-input" type="checkbox" disabled></td>
        <td><input type="text" class="form-control text-center" readonly value="-"></td>
        <td><input type="text" class="form-control text-center disc-usd-input count-disc" data-index="__INDEX__" value="0.00"></td>
        <td><input type="text" class="form-control text-center" name="items[__INDEX__][total]" readonly></td>
    </tr>
</template>

@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
$(document).ready(function () {
    var rowIndex = {{ count($result->do_detail) }};

    // Samakan create_lanjutan: aktifkan select2 untuk Sales Senior / Sales / Rekening / Disc Cash
    $('.js-select2').select2();

    // Hide loading overlay
    setTimeout(function () {
      $('#page-loading-overlay').fadeOut(200);
    }, 300);

    $('#datatables').DataTable({
        paging: false,
        searching: false,
        info: false,
        ordering: false,
        scrollY: '430px',
        scrollCollapse: true,
    });

    // ==========================================
    // FORMAT FUNCTIONS
    // ==========================================
    function formatNumber(angka) {
      var rounded = Math.round(parseFloat(String(angka)));
      var numberString = String(rounded).replace(/[^\d]/g, '');
      if (!numberString) return '';
      return numberString.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function formatInputKurs(inputValue) {
      var numberString = inputValue.replace(/[^\d]/g, '');
      if (!numberString) return '';
      return numberString.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function parseFormattedNumber(val) {
      if (!val) return 0;
      return parseFloat(String(val).split('.').join('')) || 0;
    }

    // Harga/disc USD memakai koma desimal (cth "49,50"): titik ribuan dibuang,
    // koma jadi titik desimal. Sama dengan parseCurrency di backend.
    function parseUsd(val) {
      if (val === null || val === undefined || val === '') return 0;
      var s = String(val).replace(/\./g, '').replace(',', '.');
      var n = parseFloat(s);
      return isNaN(n) ? 0 : n;
    }

    function formatUsd(inputValue) {
      var clean = String(inputValue).replace(/[^\d,]/g, '');
      var parts = clean.split(',');
      var intPart = (parts[0] || '').replace(/^0+(?=\d)/, '');
      var decPart = (parts.slice(1).join('')).substring(0, 2);
      if (intPart === '') intPart = '0';
      intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
      return decPart.length ? intPart + ',' + decPart : intPart;
    }

    // ==========================================
    // AUTO-FORMAT INPUT
    // ==========================================
    $(document).on('input', '.price-usd-input, .disc-usd-input', function () {
      var cursorFromEnd = this.value.length - this.selectionStart;
      this.value = formatUsd(this.value);
      var newPos = this.value.length - cursorFromEnd;
      if (this.selectionStart) this.setSelectionRange(newPos, newPos);
    });

    $(document).on('input', '#disc_tambahan_idr, #voucher_idr, #delivery_cost_idr, #other_cost_idr', function () {
      var cursorFromEnd = this.value.length - this.selectionStart;
      this.value = formatInputKurs(this.value);
      var newPos = this.value.length - cursorFromEnd;
      if (this.selectionStart) this.setSelectionRange(newPos, newPos);
      hitungGrandTotal();
    });

    $(document).on('input', '#idr_rate_display', function () {
      var cursorFromEnd = this.value.length - this.selectionStart;
      this.value = formatInputKurs(this.value);
      var newPos = this.value.length - cursorFromEnd;
      this.setSelectionRange(newPos, newPos);
      $('#idr_rate').val(this.value.replace(/\./g, ''));
      $('tbody tr.row-item').each(function () {
        count_per_item($(this).data('index'));
      });
    });

    // ==========================================
    // KALKULASI PER ITEM
    // ==========================================
    function count_per_item(index) {
      let $row = $('tr.index' + index);
      let qty = parseFloat($row.find('input[name="items[' + index + '][qty]"]').val()) || 0;
      let priceUsd = parseUsd($row.find('.price-usd-input').val());
      let discUsd = parseUsd($row.find('.disc-usd-input').val());
      let pctDisc = parseFloat($row.find('input[name="items[' + index + '][percent_disc]"]').val()) || 0;
      let kurs = parseFloat($('#idr_rate').val()) || 0;

      // Sync ke hidden field
      $row.find('.hidden-price-usd').val(priceUsd);
      $row.find('.hidden-disc-usd').val(discUsd);

      // Rumus sama dengan calculateTotals backend (termasuk percent_disc)
      let totalDiscItem = (discUsd + ((priceUsd - discUsd) * (pctDisc / 100))) * qty;
      let sub_total_usd = (qty * priceUsd) - totalDiscItem;
      let total_idr = sub_total_usd * kurs;
      if (isNaN(total_idr)) total_idr = 0;

      $row.find('input[name="items[' + index + '][total]"]').val(formatNumber(total_idr));
      sub_total_item();
    }

    function sub_total_item() {
      let total = 0;
      $('tbody tr.row-item').each(function () {
        let val = $(this).find('input[name$="[total]"]').val();
        val = val ? parseFloat(val.split('.').join('')) : 0;
        if (!isNaN(val)) total += val;
      });
      $('.sub-total-item-display').val(formatNumber(total));
      hitungDiscAgen();
    }

    // ==========================================
    // KALKULASI DISC AGEN
    // ==========================================
    function hitungDiscAgen() {
      let discPercent = parseFloat($('#disc_agen_percent').val()) || 0;
      let subTotal = parseFormattedNumber($('.sub-total-item-display').first().val());
      let result = (subTotal * discPercent) / 100;
      $('#disc_agen_idr').val(formatNumber(result));
      hitungDiscKemasan();
    }

    // ==========================================
    // KALKULASI DISC KEMASAN
    // ==========================================
    function hitungDiscKemasan() {
      let subTotal = parseFormattedNumber($('.sub-total-item-display').first().val());
      let discAgenIdr = parseFormattedNumber($('#disc_agen_idr').val());
      let discKemasanPercent = parseFloat($('#disc_kemasan_percent').val()) || 0;
      let amount = ((subTotal - discAgenIdr) * discKemasanPercent) / 100;
      $('#disc_kemasan_idr').val(formatNumber(amount));
      subtotal2();
    }

    // ==========================================
    // KALKULASI SUBTOTAL 2
    // ==========================================
    function subtotal2() {
      let subTotal = parseFormattedNumber($('.sub-total-item-display').first().val());
      let discAgen = parseFormattedNumber($('#disc_agen_idr').val());
      let discKemasan = parseFormattedNumber($('#disc_kemasan_idr').val());
      $('#subtotal_2').val(formatNumber(subTotal - discAgen - discKemasan));
      hitungGrandTotal();
    }

    // ==========================================
    // KALKULASI GRAND TOTAL
    // ==========================================
    function hitungGrandTotal() {
      let subtotalBefore = parseFormattedNumber($('#subtotal_2').val());
      let discTambahan = parseFormattedNumber($('#disc_tambahan_idr').val());
      let voucher = parseFormattedNumber($('#voucher_idr').val());
      let ongkir = parseFormattedNumber($('#delivery_cost_idr').val());
      let otherCost = parseFormattedNumber($('#other_cost_idr').val());
      let grandTotal = subtotalBefore - discTambahan - voucher + ongkir + otherCost;
      $('#grand_total_idr').val(formatNumber(grandTotal));
    }

    // ==========================================
    // EVENT LISTENERS
    // ==========================================
    $(document).on('input change', '.count', function () {
      count_per_item($(this).data('index'));
    });

    $(document).on('input change', '.price-usd-input, .disc-usd-input', function () {
      count_per_item($(this).data('index'));
    });

    $('#disc_agen_percent').on('keyup change', hitungDiscAgen);
    $('#disc_kemasan_percent').on('keyup change input', hitungDiscKemasan);
    $('#btn_call').on('click', hitungGrandTotal);

    // Samakan create_lanjutan.blade.php: Disc Cash global set massal Disc USD,
    // kecuali produk Free (price 0 & disc terkunci).
    $('.base_disc').on('change', function () {
        var baseDisc = $(this).val();
        $('tbody tr.row-item').each(function () {
            var idx = $(this).data('index');
            var isFree = $(this).find('.free-count').is(':checked');
            if (isFree) {
                $(this).find('.disc-usd-input').val('0');
            } else {
                $(this).find('.disc-usd-input').val(baseDisc);
            }
            count_per_item(idx);
        });
        hitungDiscAgen();
    });

    // Load awal
    $('tbody tr.row-item').each(function () {
      count_per_item($(this).data('index'));
    });

    // ==========================================
    // ADD PRODUCT
    // ==========================================
    $('#btnAddProduct').on('click', function () {
        var tpl = $('#tplNewProductRow').html().replace(/__INDEX__/g, rowIndex);
        var $row = $(tpl).addClass('index' + rowIndex).attr('data-index', rowIndex);
        $('#datatables tbody').append($row);

        $row.find('.select2-new-product').select2({
            ajax: {
                url: "{{ route('superuser.penjualan.sales_order.ajax_product_detail') }}",
                type: 'POST',
                data: function (params) { return { search: params.term, _token: '{{ csrf_token() }}' }; },
                processResults: function (data) {
                    return { results: data.map(function (item) {
                        return { id: item.product_packaging_id, text: item.code + ' - ' + item.name, price: item.price };
                    })};
                }
            },
            placeholder: 'Cari produk...'
        }).on('select2:select', function (e) {
            $row.find('.input-product-id').val(e.params.data.id);
            // Set harga dari produk yang dipilih (format Indonesia: ribuan titik, desimal koma)
            let selectedPrice = parseFloat(e.params.data.price) || 0;
            let intPart = String(Math.floor(selectedPrice)).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            let decPart = (selectedPrice % 1).toFixed(2).split('.')[1];
            $row.find('.price-usd-input').val(intPart + ',' + decPart);
        });

        rowIndex++;
        count_per_item(rowIndex - 1);
    });

    // ==========================================
    // REMOVE PRODUCT
    // ==========================================
    $(document).on('click', '.btn-remove-row', function () {
        if ($('#datatables tbody tr').length <= 1) {
            Swal.fire('Perhatian', 'Minimal harus ada 1 produk di DO.', 'warning');
            return;
        }
        $(this).closest('tr').remove();
        sub_total_item();
    });

    // ==========================================
    // FORM SUBMISSION
    // ==========================================
    $('#frmInternalRevision').on('submit', function (e) {
        var incomplete = false;
        $('.row-new-product').each(function () {
            if ($(this).find('.input-product-id').val() == '') incomplete = true;
        });
        if (incomplete) {
            e.preventDefault();
            Swal.fire('Perhatian', 'Ada produk baru yang belum dipilih dari dropdown pencarian.', 'warning');
            return;
        }

        e.preventDefault();
        var _form = $(this);

        Swal.fire({
            title: 'Kirim Pengajuan Revisi?',
            text: "Pastikan semua data sudah benar sebelum mengirim.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Kirim!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#btn_submit').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Mengirim...');
                $.ajax({
                    url: _form.attr('action'),
                    type: 'POST',
                    data: _form.serialize(),
                    dataType: 'JSON',
                    success: function(resp){
                        if(resp.IsError == true){
                            Swal.fire('Error!', resp.Notification?.content || 'Terjadi kesalahan.', 'error');
                        } else {
                            Swal.fire(
                                'Berhasil!',
                                resp.Notification?.content || 'Pengajuan revisi berhasil dikirim.',
                                'success'
                            ).then(() => {
                                window.location.href = resp.redirect_to || '{{ route("superuser.penjualan.internal_revision.index") }}';
                            });
                        }
                    },
                    error: function (jqXHR) {
                        let errorMessage = "Cek Koneksi Internet";
                        if (jqXHR.responseJSON) {
                            if (jqXHR.responseJSON.errors) {
                                errorMessage = Object.values(jqXHR.responseJSON.errors).flat().join('<br>');
                            } else if (jqXHR.responseJSON.Notification) {
                                errorMessage = jqXHR.responseJSON.Notification.content;
                            } else if (jqXHR.responseJSON.message) {
                                errorMessage = jqXHR.responseJSON.message;
                            }
                        }
                        Swal.fire('Error!', errorMessage, 'error');
                    },
                    complete: function(){
                        $('#btn_submit').prop('disabled', false).html('<i class="fa fa-paper-plane pr-2"></i> Kirim Pengajuan');
                    }
                });
            }
        });
    });
});
</script>
@endpush
