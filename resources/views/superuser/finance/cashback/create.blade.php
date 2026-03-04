@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Finance</span>
  <span class="breadcrumb-item">Cashback</span>
  <span class="breadcrumb-item active">Create</span>
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

<div class="block">
  <div class="block-content">
  <form class="ajax" data-action="{{ route('superuser.finance.cashback.store') }}" data-type="POST" enctype="multipart/form-data">
        <input type="hidden" value="{{ $invoice->id }}" name="do_id">
        <div class="row">
                <div class="col-6">
                        <div class="block">
                            <div class="block-header block-header-default">
                                <h3 class="block-title">#Detail Nota</h3>
                            </div>
                        <div class="block-content">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="so_date">Invoice</label>
                                    <input type="text" name="so_date" class="form-control" value="{{ $invoice->do_code }} - {{ $invoice->id }}" readonly>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="type_transaction">Type Transaksi</label>
                                    <input type="text" name="type_transaction" class="form-control" value="{{ $invoice->type_transaction }}" readonly>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="type_transaction">Tanggal Nota <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" value="{{ $invoice->so->so_date ?? '-' }}" readonly>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="warehouse_id">Gudang <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" value="{{ $invoice->warehouse->name ?? '-' }}" readonly>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="type_transaction">Kurs <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="idr_rate" id="idr_rate" value="{{ $invoice->idr_rate }}" readonly>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="type_transaction">Note <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="note" id="note">
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
                                        <input class="form-control" type="text" value="{{ $invoice->member->name }}" readonly>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="note">Alamat Kirim</label>
                                        <input class="form-control" type="text" value="{{ $invoice->member->address }}" readonly>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="customer_city">Kota</label>
                                        <input class="form-control" type="text" value="{{ $invoice->member->text_kota }}" readonly>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="customer_area">Provinsi</label>
                                        <input class="form-control" type="text" value="{{ $invoice->member->text_provinsi }}" readonly>
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
                                    <th>#</th>
                                    <th>Category</th>
                                    <th>Kode Bahan</th>
                                    <th>Nama Barang</th>
                                    <th>Fee</th>
                                    <th>Acuan</th>
                                    <th>Qty</th>
                                    <th>Item Jual Nett</th>
                                    <th>Total Jual</th>
                                    <th>Item Beli Nett</th>
                                    <th>Total Beli</th>
                                    <th>Selisih</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->do_detail AS $key)
                                    <tr>
                                        
                                        <input type="hidden" name="disc_usd[]" id="disc_usd" value="{{ $key->usd_disc }}">
                                        <input type="hidden" name="disc_percent[]" id="disc_percent" value="{{ $invoice->do_detail_cost->discount_1 ?? 0 }}">
                                        <input type="hidden" name="disc_kemasan[]" id="disc_kemasan" value="{{ $invoice->do_detail_cost->discount_2 }}">
                                        <input type="hidden" name="disc_tambahan[]" id="disc_tambahan" value="{{ $invoice->do_detail_cost->discount_idr }}">
                                        <input type="hidden" name="product[]" value="{{ $key->product_packaging_id }}">
                                        <input type="hidden" name="free[]" value="{{ $key->so_item->free_product }}">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $key->product_pack->product->category->brand_name ?? '-' }} - {{ $key->product_pack->product->category->name ?? '-' }}</td>
                                        <td>{{ $key->product_pack->material_code ?? '-' }}</td>
                                        <td>{{ $key->product_pack->name ?? '-' }}</td>
                                        <td style="width:8%">
                                            <input class="form-control text-center" type="number" name="cashback[]" value="{{ $key->product_pack->cashback[0]->fee }}" readonly>
                                        </td>
                                        <td style="width:8%">
                                            <input class="form-control text-center" type="number" name="item_price[]" value="{{ $key->price }}" readonly>
                                        </td>
                                        <td style="width:8%">
                                            <input class="form-control text-center" type="number" name="item_qty[]" id="item_qty" value="{{ $key->qty }}" readonly>
                                        </td>
                                        <td style="width:15%">
                                            <input class="form-control text-center" type="number" name="item_price_nett[]" id="item_qty" readonly>
                                        </td>
                                        <td style="width:15%">
                                            <input class="form-control text-center" type="number" name="item_purchase_total[]" id="item_purchase_total" readonly>
                                        </td>
                                        <td style="width:15%">
                                            <input class="form-control text-center" type="number" name="item_price_cashback[]" id="item_purchase_total" readonly>
                                        </td>
                                        <td style="width:15%">
                                            <input class="form-control text-center" type="number" name="item_grand_total[]" id="item_grand_total" readonly>
                                        </td>
                                        <td style="width:15%">
                                            <input class="form-control text-center" type="number" name="selisih_cashback[]" id="selisih_cashback" readonly>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="row-footer-subtotal">
                                    <td colspan="7" class="text-right">
                                        <b>TOTAL :</b>
                                    <td></td>
                                    </td>
                                    <td class="text-center">
                                        <input type="text" name="subtotal_nett" id="subtotal_nett" class="form-control text-center" readonly step="any">
                                    </td>
                                    <td></td>
                                    <td class="text-center">
                                        <input type="text" name="subtotal_cashback" id="subtotal_cashback" class="form-control text-center" readonly step="any">
                                    </td>
                                    <td class="text-center">
                                        <input type="text" name="subtotal_selisih" id="subtotal_selisih" class="form-control text-center" readonly step="any">
                                    </td>
                              </tr>
                            </tfoot>
                        </table>
                        <!-- <a class="btn btn-danger" href="{{ route('superuser.finance.cashback.index') }}" role="button"><i class="fa fa-arrow-left" aria-hidden="true"></i> Back</a>
                        <button type="button" style="position: absolute; right: 0;" class="btn btn-warning" id="btn_call"><i class="fas fa-calculator pr-2" aria-hidden="true"></i>calculated</button> -->
                        <div class="row pt-30 mb-15">
                            <div class="col-md-6">
                                <a href="{{ route('superuser.finance.cashback.index') }}">
                                    <button type="button" class="btn bg-gd-cherry border-0 text-white">
                                        <i class="fa fa-arrow-left mr-10"></i> Back
                                    </button>
                                </a>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="button"class="btn btn-warning" id="btn_call"><i class="fas fa-calculator pr-2" aria-hidden="true"></i>calculated</button>
                                <!-- <a class="btn btn-success" href="#" role="button" id="saveBtn" disabled>Save <i class="fa fa-check ml-10"></i></a> -->
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
        $('#datatable').DataTable({
            paging: false,
            searching: false,
            info: false,
            order: [
                [3, 'asc']
            ],
        });

        $('#btn_call').on('click', function (e) {
            let total_qty = 0;
            let isFirstDiscountApplied = false;

            // Calculate total quantity
            $('#datatable tbody tr').each(function () {
                let qty = parseFloat($(this).find('input[name="item_qty[]"]').val()) || 0;
                total_qty += qty;
            });

            // Loop through each table row to calculate discounts and totals
            $('#datatable tbody tr').each(function () {
                let disc_usd = parseFloat($(this).find('input[name="disc_usd[]"]').val()) || 0;
                let kurs = parseFloat($("#idr_rate").val()) || 1;
                let qty = parseFloat($(this).find('input[name="item_qty[]"]').val()) || 0;
                let price = parseFloat($(this).find('input[name="item_price[]"]').val()) || 0;
                let discount_percent = parseFloat($(this).find('input[name="disc_percent[]"]').val()) || 0;
                let discount_kemasan = parseFloat($(this).find('input[name="disc_kemasan[]"]').val()) || 0;
                let discount_tambahan = parseFloat($(this).find('input[name="disc_tambahan[]"]').val()) || 0;
                let cashback = parseFloat($(this).find('input[name="cashback[]"]').val()) || 0;
                let free = parseFloat($(this).find('input[name="free[]"]').val()) || 0;

                // Determine if the additional discount should be applied
                let disc_tambahan_idr_item = 0;
                if (!isFirstDiscountApplied && free != 1) {
                    disc_tambahan_idr_item = discount_tambahan;
                    isFirstDiscountApplied = true;
                }

                let unit_price_before = (price - disc_usd) * kurs;
                let amount_total_before = unit_price_before * qty;

                // Calculate discounts
                let disc_percent_idr = amount_total_before * discount_percent / 100;
                let disc_kemasan_idr = (amount_total_before - disc_percent_idr) * discount_kemasan / 100;
                let amount_total_after = amount_total_before - disc_percent_idr - disc_kemasan_idr - disc_tambahan_idr_item;

                let amount_sub_item = amount_total_after / qty;
                let cashback_idr = cashback * kurs;

                if (free == 1) {
                    cashback_idr = 0;
                }

                let amount_after_cashback = amount_sub_item - cashback_idr;
                let final_amount_after_cashback = amount_after_cashback * qty;

                if (free == 1) {
                    final_amount_after_cashback = 0;
                }

                let selisih_cashback = amount_total_after - final_amount_after_cashback;

                // Set the calculated values
                $(this).find('input[name="item_price_nett[]"]').val(amount_sub_item.toFixed(2));
                $(this).find('input[name="item_price_cashback[]"]').val(amount_after_cashback.toFixed(2));
                $(this).find('input[name="item_purchase_total[]"]').val(amount_total_after.toFixed(2));
                $(this).find('input[name="item_grand_total[]"]').val(final_amount_after_cashback.toFixed(2));
                $(this).find('input[name="selisih_cashback[]"]').val(selisih_cashback.toFixed(2));
            });

            // Calculate the subtotal
            let subtotal_cashback = 0;
            let subtotal_nett = 0;
            let subtotal_selisih_cashback = 0;
            $('#datatable tbody tr').each(function () {
                let row_total_1 = parseFloat($(this).find('input[name="item_purchase_total[]"]').val()) || 0;
                let row_total = parseFloat($(this).find('input[name="item_grand_total[]"]').val()) || 0;
                let row_total_2 = parseFloat($(this).find('input[name="selisih_cashback[]"]').val()) || 0;
                subtotal_cashback += row_total;
                subtotal_nett += row_total_1;
                subtotal_selisih_cashback += row_total_2;
            });

            // Set the subtotal values
            $('#subtotal_nett').val(subtotal_nett.toFixed(2));
            $('#subtotal_cashback').val(subtotal_cashback.toFixed(2));
            $('#subtotal_selisih').val(subtotal_selisih_cashback.toFixed(2));

            // Check if all `item_grand_total` fields are calculated correctly
            let allCalculated = true;
            $('#datatable tbody tr').each(function () {
                let grand_total = parseFloat($(this).find('input[name="item_grand_total[]"]').val()) || 0;
                let free = parseFloat($(this).find('input[name="free[]"]').val()) || 0;

                // For non-free products, grand_total should be greater than 0
                if (free != 1 && grand_total <= 0) {
                    allCalculated = false;
                    return false; // Break out of the loop if condition fails
                }
            });

            // Enable or disable the save button based on the calculation check
            $('#saveBtn').prop('disabled', !allCalculated);
        });

        // Initial disable state of the save button
        $('#saveBtn').prop('disabled', true);
    });
</script>
@endpush