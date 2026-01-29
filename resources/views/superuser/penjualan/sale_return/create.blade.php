@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.sale_return.index') }}">Nota Kredit</a>
  <span class="breadcrumb-item active">Create</span>
</nav>
<div id="alert-block"></div>

<form class="ajax" data-action="{{ route('superuser.penjualan.sale_return.store') }}" data-type="POST" enctype="multipart/form-data">
  <div class="row">
    <div class="col-md-12">
      <div class="block">
        <div class="block-header block-header-default">
          <h3 class="block-title">Create Nota Kredit</h3>
        </div>
        <div class="block-content">
          <div class="row mb-3">
            <label class="col-md-3 col-form-label text-right" for="code">Code <span class="text-danger">*</span></label>
            <div class="col-md-7">
              <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)" value="{{ App\Repositories\CodeRepo::generateReturCode() }}" readonly>
            </div>
          </div>
          <div class="row mb-3">
            <label class="col-md-3 col-form-label text-right" for="type">Type <span class="text-danger">*</span></label>
            <div class="col-md-7">
              <select class="js-select2 form-control" id="type" name="type" data-placeholder="Select Type" require>
                <option></option>
                @foreach(\App\Entities\Penjualan\SaleReturn::TYPE as $key => $value)
                <option value="{{ $value }}">{{ $key }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <label class="col-md-3 col-form-label text-right" for="delivery_order">Invoice <span class="text-danger">*</span></label>
            <div class="col-md-7">
              <select class="js-select2 form-control js-select2-do" id="delivery_order" name="delivery_order" data-placeholder="Search Invoice" require>
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <label class="col-md-3 col-form-label text-right" for="retur_date">Tanggal <span class="text-danger">*</span></label>
            <div class="col-md-7">
              <input type="date" class="form-control" id="retur_date" name="retur_date" required>
            </div>
          </div>
          <div class="row mb-3">
            <label class="col-md-3 col-form-label text-right">
              Komplain?
            </label>
            <div class="col-md-7 d-flex align-items-center">
              <div class="form-check m-0">
                <input class="form-check-input"
                      type="checkbox"
                      id="flag_qc"
                      name="flag_qc"
                      value="1">
              </div>
            </div>
          </div>
          <div class="row pt-30">
            <div class="col-md-6">
              <a href="javascript:history.back()">
                <button type="button" class="btn bg-gd-cherry border-0 text-white">
                  <i class="fa fa-arrow-left mr-10"></i> Back
                </button>
              </a>
            </div>
            <div class="col-md-6 text-right">
              <button type="button"
                      class="btn btn-warning text-black me-2"
                      id="btn-qc"
                      style="display:none">
                <i class="fa fa-warehouse me-1"></i> Receiving – Komplain
              </button>

              <button type="submit" class="btn bg-gd-corporate border-0 text-white" id="submit-table" disabled>
                Submit <i class="fa fa-arrow-right ml-10"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="block">
    <div class="block-header">
      <h3 class="block-title">Add Product</h3>
      <a href="#" class="row-add">
        <button type="button" class="btn bg-gd-sea border-0 text-white">
          <i class="fa fa-plus mr-10"></i> Row
        </button>
      </a>
    </div>
    <div class="block-content">
      <table id="datatable" class="table table-striped">
        <thead>
          <tr>
            <th style="width: 2%;">#</th>
            <th style="width: 15%;">Product</th>
            <th style="width: 10%;">Kemasan</th>
            <th class="text-right" style="width: 5%;">Acuan</th>
            <th class="text-right" style="width: 5%;">Qty</th>
            <th class="text-right" style="width: 5%;">Disc (USD)</th>
            <th class="text-center" style="width: 10%;">Jumlah</th>
            <th class="text-center" style="width: 10%;">Action</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
          <tr class="row-footer-subtotal">
            <td colspan="6" class="text-end">
              <b>Subtotal :</b>
            </td>
            <td class="text-end">
              <input type="text" name="subtotal_item" id="subtotal_item" class="form-control text-end" readonly step="any">
            </td>
          </tr>
          <tr class="row-footer-subtotal">
            <td colspan="5" class="text-end">
              <b>Disc (%)</b>
            </td>
            <td class="text-center" style="width: 100px;">
              <input type="text" name="disc_percent" id="disc_percent" class="form-control text-center" readonly step="any">
            </td>
            <td class="text-end">
              <input type="text" name="disc_amount_1" id="disc_amount_1" class="form-control text-end" readonly step="any">
             </td>
          </tr>
          <tr class="row-footer-subtotal">
            <td colspan="5" class="text-end">
              <b>Disc Kemasan</b>
            </td>
            <td class="text-center">
              <input type="text" name="disc_percent_2" id="disc_percent_2" class="form-control text-center" readonly step="any">
            </td>
            <td class="text-end">
              <input type="text" name="disc_amount_2" id="disc_amount_2" class="form-control text-end" readonly step="any">
            </td>
          </tr>
          <tr class="row-footer-subtotal">
            <td colspan="6" class="text-end">
              <b>Disc IDR</b>
            </td>
            <td class="text-end">
                <input type="text" name="disc_idr" id="disc_idr" class="form-control text-end" readonly step="any">
            </td>
          </tr>
          <tr class="row-footer-subtotal">
            <td colspan="6" class="text-end">
              <b>Grand Total</b>
            </td>
            <td class="text-end">
              <input type="text" name="grand_total" id="grand_total" class="form-control text-end" readonly step="any">
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</form>

<!-- MODAL -->
<div class="modal fade" id="modalQc" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-l modal-dialog-scrollable" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Receiving – Komplain</h5>
        <!-- <button type="button" class="close no-ajax" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button> -->
      </div>

      <div class="modal-body">
        <table class="table table-bordered table-sm" id="table-qc">
          <thead>
            <tr>
              <th style="width:15%">Kode</th>
              <th>Produk</th>
              <th style="width:10%" class="text-end">Qty</th>
              <th style="width:10%" class="text-center">Action</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>

      <div class="modal-footer">
        <!-- <button type="button" class="btn btn-warning no-ajax" data-dismiss="modal">
          Tutup
        </button> -->
      </div>

    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
$(document).ready(function () {

  /* ==========================
     INIT SELECT2 (JANGAN DIUBAH)
  ========================== */
  $('.js-select2').select2();

  $(".js-select2-do").select2({
    ajax: {
      url: '{{ route("superuser.penjualan.sale_return.search_do") }}',
      dataType: 'json',
      delay: 250,
      data: function (params) {
        return { q: params.term, _token: "{{ csrf_token() }}" };
      },
      cache: true
    }
  });

  let product_data = [];
  let invoiceQty = {};
  let usedQty = {};
  let counter = 1;

  let table = $('#datatable').DataTable({
    paging: false,
    bInfo: false,
    searching: false,
    columns: [
      { visible: false },
      null, null, null, null, null, null, null
    ],
    order: [[0, 'desc']]
  });

  /* ==========================
     LOAD PRODUCT FROM INVOICE
  ========================== */
  $('#delivery_order').on('select2:select', function () {

    table.clear().draw();
    counter = 1;
    invoiceQty = {};
    usedQty = {};

    $('input[name^="disc"], #subtotal_item, #grand_total').val('');

    $.post('{{ route("superuser.penjualan.sale_return.get_product") }}', {
      id: $(this).val(),
      _token: "{{ csrf_token() }}"
    }, function (res) {

      if (res.code !== 200) return;

      product_data = res.data;

      product_data.forEach(p => {
        invoiceQty[p.id] = parseFloat(p.quantity);
        usedQty[p.id] = 0;
      });

    }, 'json');
  });

  /* ==========================
     ADD ROW MANUAL
  ========================== */
  $('.row-add').on('click', function (e) {
    e.preventDefault();
    if (!$('#delivery_order').val()) return;

    let selected = $('.product-select').map(function () {
      return $(this).val();
    }).get();

    let select = `<select class="js-select2 form-control product-select" name="sku[]" required><option>Pilih Produk</option>`;

    product_data.forEach(p => {
      let sisa = invoiceQty[p.id] - (usedQty[p.id] || 0);
      if (sisa > 0 && !selected.includes(String(p.id))) {
        select += `
          <option value="${p.id}"
            data-quantity="${sisa}"
            data-kemasan="${p.kemasan}"
            data-acuan="${p.acuan}"
            data-disc_usd="${p.disc_usd}"
            data-disc_1="${p.discount_percent}"
            data-disc_2="${p.discount_kemasan}"
            data-disc_idr="${p.discount_idr}"
            data-idr_rate="${p.idr_rate}">
            ${p.sku} - ${p.name}
          </option>`;
      }
    });

    select += '</select>';

    let row = table.row.add([
      counter++,
      select,
      '<span class="packaging"></span>',
      '<input type="number" class="form-control text-right" name="acuan[]" readonly><input type="hidden" name="kurs[]"><input type="hidden" name="row_type[]" value="manual">',
      '<input type="number" class="form-control qty text-right" name="quantity[]" min="0" step="0.01">',
      '<input type="number" class="form-control text-right" name="disc_usd[]" readonly>',
      '<input type="number" class="form-control text-right" name="jumlah[]" readonly>',
      '<a href="#" class="row-delete btn btn-sm btn-danger"><i class="fa fa-trash"></i></a>'
    ]).draw(false).node();

    $(row).addClass('row-manual');
    $('.product-select').select2();
    toggleQcButton();
    toggleSubmitButton();
  });

  /* ==========================
     SELECT PRODUCT
  ========================== */
  $('#datatable').on('select2:select', '.product-select', function () {

    let opt = $(this).find(':selected');
    let row = $(this).closest('tr');

    row.find('.packaging').text(opt.data('kemasan'));
    row.find('input[name="acuan[]"]').val(opt.data('acuan'));
    row.find('input[name="disc_usd[]"]').val(opt.data('disc_usd'));
    row.find('input[name="kurs[]"]').val(opt.data('idr_rate'));

    $('#disc_percent').val(opt.data('disc_1'));
    $('#disc_percent_2').val(opt.data('disc_2'));
    $('#disc_idr').val(opt.data('disc_idr'));

    let max = opt.data('quantity');
    row.find('.qty').attr('max', max).attr('placeholder', max);
  });

  /* ==========================
     QTY INPUT (AKUMULATIF)
  ========================== */
  $('#datatable').on('keyup change', '.qty', function () {

    let row = $(this).closest('tr');
    let productId = row.find('.product-select').val();
    let qty = parseFloat($(this).val()) || 0;

    let totalUsed = 0;
    $('.product-select').each(function (i) {
      if ($(this).val() === productId) {
        totalUsed += parseFloat($('input[name="quantity[]"]').eq(i).val()) || 0;
      }
    });

    if (totalUsed > invoiceQty[productId]) {
      alert('Qty melebihi qty nota');
      $(this).val('');
      return;
    }

    usedQty[productId] = totalUsed;

    let acuan = parseFloat(row.find('input[name="acuan[]"]').val()) || 0;
    let disc = parseFloat(row.find('input[name="disc_usd[]"]').val()) || 0;
    let kurs = parseFloat(row.find('input[name="kurs[]"]').val()) || 1;

    let jumlah = ((acuan - disc) * qty) * kurs;
    row.find('input[name="jumlah[]"]').val(jumlah.toFixed(2));

    calculateTotal();
  });

  /* ==========================
     DELETE ROW
  ========================== */
  $('#datatable').on('click', '.row-delete', function (e) {
    e.preventDefault();
    table.row($(this).closest('tr')).remove().draw();
    recalcUsedQty();
    calculateTotal();
    toggleQcButton();
    toggleSubmitButton();
  });

  function recalcUsedQty() {
    usedQty = {};
    $('.product-select').each(function (i) {
      let pid = $(this).val();
      let qty = parseFloat($('input[name="quantity[]"]').eq(i).val()) || 0;
      usedQty[pid] = (usedQty[pid] || 0) + qty;
    });
  }

  /* ==========================
     RECEIVING – KOMPLAIN (QC)
  ========================== */
  $('#btn-qc').on('click', function () {

    $('#modalQc').modal('show');
    $('#table-qc tbody').html('<tr><td colspan="4">Loading...</td></tr>');

    $.post('{{ route("superuser.penjualan.sale_return.get_qc_by_do") }}', {
      delivery_order: $('#delivery_order').val(),
      _token: "{{ csrf_token() }}"
    }, function (res) {

      let html = '';

      res.forEach(qc => {
        qc.details.forEach(d => {
          html += `
            <tr>
              <td>${qc.code}</td>
              <td>${d.product_pack.code} - ${d.product_pack.name}</td>
              <td class="text-end">${d.qty}</td>
              <td class="text-center">
                <button type="button"
                  class="btn btn-success btn-sm btn-pilih-qc"
                  data-product="${d.product_pack.id}"
                  data-qty="${d.qty}"
                  data-komplain_id="${qc.id}">
                  Pilih
                </button>
              </td>
            </tr>`;
        });
      });

      $('#table-qc tbody').html(html || '<tr><td colspan="4">Tidak ada data</td></tr>');
    });
  });

  $('#table-qc').on('click', '.btn-pilih-qc', function () {

    let komplainId = String($(this).data('komplain_id'));
    let productId = String($(this).data('product'));
    let qtyQc = parseFloat($(this).data('qty')) || 0;
    if (qtyQc <= 0) return;

    let product = product_data.find(p => String(p.id) === productId);
    if (!product) return;

    let sisa = invoiceQty[productId] - (usedQty[productId] || 0);
    if (qtyQc > sisa) {
      alert('Qty QC melebihi sisa qty invoice');
      return;
    }

    usedQty[productId] = (usedQty[productId] || 0) + qtyQc;

    let jumlahQc = ((product.acuan - product.disc_usd) * qtyQc) * product.idr_rate;

    let row = table.row.add([
      counter++,
      `<span>${product.sku} - ${product.name} <span class="badge bg-warning">QC</span></span>
      <input type="hidden" name="sku[]" value="${product.id}"><input type="hidden" name="row_type[]" value="qc"><input type="hidden" name="komplain_id[]" value="${komplainId}">`,
      `<span class="packaging">${product.kemasan}</span>`,
      `<input type="number" class="form-control text-right" name="acuan[]" value="${product.acuan}" readonly>
      <input type="hidden" name="kurs[]" value="${product.idr_rate}">`,
      `<input type="number" class="form-control text-right" name="quantity[]" value="${qtyQc}" readonly>`,
      `<input type="number" class="form-control text-right" name="disc_usd[]" value="${product.disc_usd}" readonly>`,
      `<input type="number" class="form-control text-right" name="jumlah[]" value="${jumlahQc.toFixed(0)}" readonly>`,
      `<a href="#" class="row-delete btn btn-sm btn-danger"><i class="fa fa-trash"></i></a>`
    ]).draw(false).node();

    $(row).addClass('row-qc');

    calculateTotal();
    toggleQcButton();
    toggleSubmitButton();
    $('#modalQc').modal('hide');
  });

  /* ==========================
     TOTAL
  ========================== */
  function calculateTotal() {
    let subtotal = 0;

    $('input[name="jumlah[]"]').each(function () {
      let val = parseFloat($(this).val());
      if (!isNaN(val)) subtotal += val;
    });

    // simpan subtotal asli (desimal)
    $('#subtotal_item').val(subtotal.toFixed(2));

    let disc_percent   = parseFloat($('#disc_percent').val()) || 0;
    let disc_percent_2 = parseFloat($('#disc_percent_2').val()) || 0;
    let disc_idr       = parseFloat($('#disc_idr').val()) || 0;

    let disc_amount_1 = subtotal * (disc_percent / 100);
    let after_disc_1  = subtotal - disc_amount_1;

    let disc_amount_2 = after_disc_1 * (disc_percent_2 / 100);

    $('#disc_amount_1').val(disc_amount_1.toFixed(2));
    $('#disc_amount_2').val(disc_amount_2.toFixed(2));

    // ⬇️ BULATKAN SEKALI DI SINI
    let grand_total = subtotal - disc_amount_1 - disc_amount_2 - disc_idr;
    $('#grand_total').val(Math.round(grand_total));
  }

  /* ==========================
     TOGGLE QC BUTTON
  ========================== */
  $('#btn-qc').prop('disabled', true);

  function toggleQcButton() {
    let type = $('#type').val();
    let invoice = $('#delivery_order').val();
    let manualRow = $('.row-manual').length;

    $('#btn-qc').prop('disabled', !(type && invoice && manualRow > 0));
  }

  $('#type, #delivery_order').on('change select2:select', toggleQcButton);
  $('#datatable').on('draw.dt', toggleQcButton);

  function toggleSubmitButton() {
    let rowCount = $('#datatable tbody tr').length;

    if (rowCount > 0) {
      $('#submit-table').prop('disabled', false);
    } else {
      $('#submit-table').prop('disabled', true);
    }
  }

  function toggleQcVisibility() {
    if ($('#flag_qc').is(':checked')) {
      $('#btn-qc').show();
    } else {
      $('#btn-qc').hide();
    }
  }

  $('#flag_qc').on('change', function () {
    toggleQcVisibility();
    toggleQcButton(); // pakai validasi lama (type, invoice, manual row)
  });

});
</script>
@endpush