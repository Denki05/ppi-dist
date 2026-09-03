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
                        <input type="text" class="form-control" readonly
                            value="{{ $result->member->name ?? '-' }} {{ $result->member->text_kota ?? '' }}">
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
                        <input type="text" class="form-control" readonly value="{{ $result->brand_name ?? '-' }}">
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
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Alamat Kirim</label>
                        <textarea class="form-control" rows="1" readonly>{{ $result->member->address ?? '-' }}</textarea>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Kota</label>
                        <input type="text" class="form-control" readonly value="{{ $result->member->text_kota ?? '-' }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Sales Senior <span class="text-danger">*</span></label>
                        <select class="form-control js-select2" name="sales_senior_id" required>
                            <option value="">Pilih Sales Senior</option>
                            @foreach(\App\Entities\Penjualan\SalesOrder::SALES_SENIOR as $sales_senior => $senior_value)
                            <option value="{{ $senior_value }}" @if(isset($result->so->sales_senior_id) && $result->so->sales_senior_id == $senior_value) selected @endif>{{ $sales_senior }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Sales <span class="text-danger">*</span></label>
                        <select class="form-control js-select2" name="sales_id" required>
                            <option value="">Pilih Sales</option>
                            @foreach(\App\Entities\Penjualan\SalesOrder::SALES as $sales => $sales_value)
                            <option value="{{ $sales_value }}" @if(isset($result->so->sales_id) && $result->so->sales_id == $sales_value) selected @endif>{{ $sales }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>No Rek Admin <span class="text-danger">*</span></label>
                        <select class="form-control js-select2" name="rekening_id" required>
                            <option value="">Pilih Rekening</option>
                            @foreach($rekening as $rek)
                            <option value="{{ $rek->id }}" @if(isset($result->so->rekening) && $result->so->rekening == $rek->id) selected @endif>{{ $rek->name }} - {{ $rek->number_card }}</option>
                            @endforeach
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
                            <th class="block" style="width:15%">Harga (USD)</th>
                            <th class="block" style="width:auto">Kemasan</th>
                            <th class="block" style="width:13%">Disc (USD)</th>
                            <th class="block" style="width:15%">Total (IDR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($result->do_detail as $index => $detail)
                        @php
                            $priceRupiah = round((float) $detail->price * (float) $result->idr_rate);
                            $discRupiah = round((float) ($detail->usd_disc ?? 0) * (float) $result->idr_rate);
                        @endphp
                        <tr class="index{{ $index }} row-item" data-index="{{ $index }}">
                            <input type="hidden" name="items[{{ $index }}][do_item_id]" value="{{ $detail->id }}">
                            <input type="hidden" name="items[{{ $index }}][product_packaging_id]" value="{{ $detail->product_packaging_id }}">
                            <input type="hidden" name="items[{{ $index }}][qty_asal]" value="{{ $detail->qty }}">
                            <input type="hidden" name="items[{{ $index }}][price]" class="hidden-price-usd" value="{{ $detail->price }}">
                            <input type="hidden" name="items[{{ $index }}][usd_disc]" class="hidden-disc-usd" value="{{ $detail->usd_disc ?? 0 }}">

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
                                <input type="text" class="form-control text-center price-usd-input" data-index="{{ $index }}" value="{{ number_format((float)$detail->price, 2, ',', '.') }}">
                            </td>
                            <td>
                                <input type="text" class="form-control text-center" readonly value="{{ $detail->product_pack->packaging->pack_name ?? '' }}">
                            </td>
                            <td>
                                <input type="text" class="form-control text-center disc-usd-input" data-index="{{ $index }}" value="{{ number_format((float)($detail->usd_disc ?? 0), 2, ',', '.') }}">
                            </td>
                            <td>
                                <input type="text" name="items[{{ $index }}][total]" class="form-control text-center" readonly>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="row-footer-subtotal">
                            <td colspan="8" class="text-right"><b>Subtotal</b></td>
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
        </td>
        <td>0 <span class="text-muted">(baru)</span></td>
        <td><input type="number" step="any" class="form-control count" data-index="__INDEX__" name="items[__INDEX__][qty]" value="1" required></td>
        <td><input type="text" class="form-control text-center price-usd-input" data-index="__INDEX__" value="0.00"></td>
        <td><input type="text" class="form-control text-center" readonly value="-"></td>
        <td><input type="text" class="form-control text-center disc-usd-input" data-index="__INDEX__" value="0.00"></td>
        <td><input type="text" class="form-control" name="items[__INDEX__][total]" readonly></td>
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

    // ==========================================
    // AUTO-FORMAT INPUT
    // ==========================================
    $(document).on('input', '.price-usd-input, .disc-usd-input', function () {
      var cursorFromEnd = this.value.length - this.selectionStart;
      this.value = formatInputKurs(this.value);
      var newPos = this.value.length - cursorFromEnd;
      if (this.selectionStart) this.setSelectionRange(newPos, newPos);
    });

    $(document).on('input', '#disc_tambahan_idr, #voucher_idr, #delivery_cost_idr', function () {
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
      let priceUsd = parseFormattedNumber($row.find('.price-usd-input').val());
      let discUsd = parseFormattedNumber($row.find('.disc-usd-input').val());
      let kurs = parseFloat($('#idr_rate').val()) || 0;

      // Sync ke hidden field
      $row.find('.hidden-price-usd').val(priceUsd);
      $row.find('.hidden-disc-usd').val(discUsd);

      // Hitung total dalam IDR
      let sub_total_usd = (priceUsd - discUsd) * qty;
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
            // Set harga dari produk yang dipilih
            let selectedPrice = e.params.data.price || 0;
            $row.find('.price-usd-input').val(parseFloat(selectedPrice).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, '.'));
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
