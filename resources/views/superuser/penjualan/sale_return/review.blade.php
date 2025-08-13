@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <span class="breadcrumb-item">Retur</span>
  <span class="breadcrumb-item active">Review</span>
</nav>

@if($errors->any())
<div class="alert alert-danger alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Error</h3>
  @foreach ($errors->all() as $error)
  <p class="mb-0">{{ $error }}</p>
  @endforeach
</div>
@endif

<div id="alert-block"></div>

@if(session()->has('message'))
<div class="alert alert-success alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Success</h3>
  <p class="mb-0">{{ session()->get('message') }}</p>
</div>
@endif

@if (session('success'))
    <div id="alert-message" class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div id="alert-message" class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="block">
  <div class="block-content">
  <form class="ajax" data-action="{{ route('superuser.penjualan.sale_return.acc') }}" data-type="POST" enctype="multipart/form-data">
        <input type="hidden" value="{{ $sale_return->id }}" name="retur_id">
        <div class="row">
                <div class="col-6">
                        <div class="block">
                            <div class="block-header block-header-default">
                                <h3 class="block-title">#Detail</h3>
                            </div>
                        <div class="block-content">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="so_date">Code | DO</label>
                                    <input type="text" name="code" class="form-control" value="{{ $sale_return->code }} | {{ $sale_return->invoice->do_code }}" readonly>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="type_transaction">Tanggal Retur <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" value="{{ $sale_return->retur_date }}" readonly>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="warehouse_id">Gudang <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" value="{{ $sale_return->warehouse->name }}" readonly>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="type_transaction">Kurs <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="idr_rate" id="idr_rate" value="{{ $sale_return->invoice->idr_rate }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-6">
                    <div class="row">
                        <div class="col">
                            <div class="block">
                                <div class="block-header block-header-default">
                                <h3 class="block-title">#Customer Info</h3>
                            </div>
                            <div class="block-content">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="type_transaction">Customer</label>
                                        <input class="form-control" type="text" value="{{ $sale_return->invoice->member->name }}" readonly>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="note">Alamat Kirim</label>
                                        <input class="form-control" type="text" value="{{ $sale_return->invoice->member->address }}" readonly>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="customer_city">Kota</label>
                                        <input class="form-control" type="text" value="{{ $sale_return->invoice->member->text_kota }}" readonly>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="customer_area">Provinsi</label>
                                        <input class="form-control" type="text" value="{{ $sale_return->invoice->member->text_provinsi }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="block">
                    <div class="block-content">
                        <table class="table table-hover" id="datatable">
                            <thead>
                                <tr>
                                    <th style="width: 2%;">#</th>
                                    <th style="width: 15%;">Product</th>
                                    <th style="width: 10%;">Kemasan</th>
                                    <th class="text-right" style="width: 5%;">Acuan</th>
                                    <th class="text-right" style="width: 5%;">Qty</th>
                                    <th class="text-right" style="width: 5%;">Disc (USD)</th>
                                    <th class="text-center" style="width: 10%;">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale_return->sale_return_details as $index => $item)
                                    @php
                                        $getDoDetail = DB::table('penjualan_do_item')
                                            ->where('do_id', $item->sale_return->do_id)
                                            ->where('product_packaging_id', $item->product_packaging_id)
                                            ->first();
                                    @endphp
                                    <tr>
                                        <input type="hidden" name="retur_detail_id[]" value="{{ $item->id }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item->product->code }} - {{ $item->product->name }}</td>
                                        <td>{{ $item->product->packaging->pack_name }}</td>
                                        <td class="text-right">
                                            <input type="text" name="price[]" id="price" class="form-control text-right" value="{{ number_format($getDoDetail->price ?? 0, 2) }}" readonly>
                                        </td>
                                        <td class="text-right">
                                            <input type="number" name="qty[]" id="qty" class="form-control text-right" value="{{ $item->qty }}" readonly>
                                        </td>
                                        <td class="text-right">
                                            <input type="text" name="disc[]" id="disc" class="form-control text-right" value="{{ number_format($getDoDetail->usd_disc ?? 0, 2) }}" readonly>
                                        </td>
                                        <td class="text-center">
                                            <input type="text" name="totalItem[]" id="totalItem" class="form-control text-right" value="" readonly>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                @php
                                    $getDoDetail = DB::table('penjualan_do_details')
                                        ->where('do_id', $sale_return->do_id)
                                        ->first();
                                @endphp
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
                                            <input type="text" name="disc_percent" id="disc_percent" value="{{ $getDoDetail->discount_1 }}" class="form-control text-center" readonly step="any">
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
                                            <input type="text" name="disc_percent_2" id="disc_percent_2" value="{{ $getDoDetail->discount_2 }}" class="form-control text-center" readonly step="any">
                                        </td>
                                        <td class="text-end">
                                            <input type="text" name="disc_amount_2" id="disc_amount_2" value="{{ number_format($getDoDetail->discount_idr ?? 0, 2) }}" class="form-control text-end" readonly step="any">
                                        </td>
                                </tr>
                                <tr class="row-footer-subtotal">
                                        <td colspan="6" class="text-end">
                                            <b>Disc IDR</b>
                                        </td>
                                        <td class="text-end">
                                            <input type="text" name="disc_idr" id="disc_idr" class="form-control text-end" value="{{ number_format($getDoDetail->discount_idr ?? 0, 2) }}" readonly step="any">
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
                        <div class="row pt-30 mb-15">
                            <div class="col-md-6">
                                <a href="{{ route('superuser.penjualan.sale_return.index') }}">
                                    <button type="button" class="btn bg-gd-cherry border-0 text-white">
                                        <i class="fa fa-arrow-left mr-10"></i> Back
                                    </button>
                                </a>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="button"class="btn btn-warning" id="btn_call"><i class="fas fa-calculator pr-2" aria-hidden="true"></i>calculated</button>
                                <button type="submit"class="btn btn-success" id="saveBtn">Save <i class="fa fa-check ml-10"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function () {
  $('#btn_call').on('click', function () {
    let kurs = parseFloat($('#idr_rate').val()) || 0;
    let subtotal = 0;

    $('#datatable tbody tr').each(function () {
      let $row = $(this);

      let price = parseFloat($row.find('input[name="price[]"]').val().replace(/,/g, '')) || 0;
      let disc = parseFloat($row.find('input[name="disc[]"]').val().replace(/,/g, '')) || 0;
      let qty = parseFloat($row.find('input[name="qty[]"]').val().replace(/,/g, '')) || 0;

      let totalItem = ((price - disc) * qty) * kurs;
      subtotal += totalItem;

      $row.find('input[name="totalItem[]"]').val(totalItem.toFixed(2));
    });

    $('#subtotal_item').val(subtotal.toFixed(2));

    // Diskon 1
    let disc_percent = parseFloat($('#disc_percent').val()) || 0;
    let disc_amount_1 = subtotal * (disc_percent / 100);
    $('#disc_amount_1').val(disc_amount_1.toFixed(2));

    // Diskon 2
    let disc_percent_2 = parseFloat($('#disc_percent_2').val()) || 0;
    let disc_amount_2 = (subtotal - disc_amount_1) * (disc_percent_2 / 100);
    $('#disc_amount_2').val(disc_amount_2.toFixed(2));

    // Disc IDR (input hidden/readonly)
    let disc_idr = parseFloat($('#disc_idr').val().replace(/,/g, '')) || 0;

    // Grand Total
    let grand_total = subtotal - disc_amount_1 - disc_amount_2 - disc_idr;
    $('#grand_total').val(grand_total.toFixed(2));
  });

  $('#saveBtn').on('click', function (e) {
    let grandTotal = parseFloat($('#grand_total').val().replace(/,/g, '')) || 0;

    if (grandTotal === 0) {
      e.preventDefault();

      Swal.fire({
        icon: 'warning',
        title: 'Perhatian',
        text: 'Silakan klik tombol "calculated" terlebih dahulu sebelum menyimpan.',
        confirmButtonText: 'OK'
      });

      return false;
    }
  });
  
  $('form.ajax').on('submit', function(e) {
        e.preventDefault();

        let $form = $(this);
        let action = $form.data('action');
        let type = $form.data('type') || 'POST';
        let formData = new FormData(this);

        $.ajax({
            url: action,
            type: type,
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = response.redirect;
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Gagal',
                        text: response.message || 'Terjadi kesalahan.',
                    });
                }
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan sistem.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: message,
                });
            }
        });
    });
});
</script>
@endpush