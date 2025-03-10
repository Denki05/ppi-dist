@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Accounting</span>
    <span class="breadcrumb-item">Araya</span>
    <span class="breadcrumb-item active">Show</span>
</nav>

<div id="alert-block"></div>

<div class="block">
    <div class="block-content">
        <div class="row">
            <div class="col-6">
                <div class="block">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">#Detail Nota</h3> 
                        <a class="btn btn-secondary btn-sm" data-toggle="collapse" href="#detailNota" role="button" aria-expanded="false" aria-controls="detailNota" id="toggleDetailNota">
                            <i class="fas fa-chevron-down"></i> <!-- Ikon awal -->
                        </a>
                    </div>

                    <div class="collapse" id="detailNota">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="so_date">Invoice</label>
                                <input type="text" name="code_jual" class="form-control-plaintext" value="{{ $invoice->cashbackUv->code }}" readonly>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="type_transaction">Type Transaksi</label>
                                <input type="text" name="type_transaction" class="form-control-plaintext" value="{{ $invoice->transaksi }}" readonly>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="type_transaction">Tanggal Nota <span class="text-danger">*</span></label>
                                <input type="date" class="form-control-plaintext" value="" readonly>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="type_transaction">Note <span class="text-danger">*</span></label>
                                <input type="text" class="form-control-plaintext" name="note_jual" id="note_jual">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="type_transaction">Status Pembayaran <span class="text-danger">*</span></label>
                                <input type="text" class="form-control-plaintext" name="payable_status" value="{{ $invoice->payment_status }}" readonly>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="type_transaction">Tanggal Pembayaran <span class="text-danger">*</span></label>
                                <input type="date" class="form-control-plaintext" name="payable_date" id="payable_date" value="{{ $invoice->payment_date ?? '-' }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6">
                <div class="block">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">#Customer Info</h3>
                        <a class="btn btn-secondary btn-sm" data-toggle="collapse" href="#detailCustomer" role="button" aria-expanded="false" aria-controls="detailCustomer" id="toggleDetailCustomer">
                            <i class="fas fa-chevron-down"></i> <!-- Ikon awal -->
                        </a>
                    </div>

                    <div class="collapse" id="detailCustomer">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="type_transaction">Customer</label>
                                <input class="form-control-plaintext" type="text" value="{{ $invoice->customer->name }}" readonly>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="note">Alamat Kirim</label>
                                <input class="form-control-plaintext" type="text" value="{{ $invoice->customer->address }}" readonly>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="customer_city">Kota</label>
                                <input class="form-control-plaintext" type="text" value="{{ $invoice->customer->text_kota }}" readonly>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="customer_area">Provinsi</label>
                                <input class="form-control-plaintext" type="text" value="{{ $invoice->customer->text_provinsi }}" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 1 -->
        <div class="setup-content" id="step-1">
            <div class="col-12">
                <div class="card border-0 shadow-none">
                    <div class="card-body">
                        <h2 class="card-title">INVOICE JUAL</h2>

                        <div class="row">
                            <div class="col">
                                <div class="block">
                                    <div class="block-content">
                                        <table class="table table-hover" id="datatable_jual">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Kode Bahan</th>
                                                    <th>Nama Barang</th>
                                                    <th>Kemasan</th>
                                                    <th>Acuan</th>
                                                    <th>Qty</th>
                                                    <th>Total Price Nett</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($invoice->simulation_item as $detail)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $detail->product->code }}</td>
                                                    <td>{{ $detail->product->name }}</td>
                                                    <td>{{ $detail->product->packaging->pack_name }}</td>
                                                    <td style="width:10%">
                                                        <input class="form-control text-center item_price_jual" type="number" name="item_price_jual[]" value="{{ $detail->price_jual }}" readonly>
                                                    </td>
                                                    <td style="width:8%">
                                                        <input class="form-control text-center item_qty_jual" type="number" name="item_qty_jual[]" id="item_qty_jual" value="{{ $detail->qty }}" readonly>
                                                    </td>
                                                    
                                                    <td style="width:15%">
                                                        <input class="form-control text-center item_purchase_total_jual" type="number" name="item_purchase_total_jual[]" id="item_purchase_total_jual" value="{{ $detail->total }}" readonly>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="row-footer-subtotal">
                                                    <td colspan="6" class="text-right">
                                                        <b>TOTAL :</b>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="text" name="grand_total_jual" id="grand_total_jual" class="form-control text-center grand_total_jual" readonly>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('superuser.accounting.finance_simulation.index_araya') }}">
                            <button type="button" class="btn bg-gd-cherry border-0 text-white">
                                <i class="fa fa-arrow-left mr-10"></i> Back
                            </button>
                        </a>

                        <button class="btn btn-primary nextBtn float-right" type="button">
                            Next <i class="fa fa-arrow-right mr-10"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2 -->
        <div class="setup-content" id="step-2" style="display: none;">
            <div class="col-12">
                <div class="card border-0 shadow-none">
                    <div class="card-body">
                        <h2 class="card-title">INVOICE BELI</h2>

                        <div class="row">
                            <div class="col">
                                <div class="block">
                                    <div class="block-content">
                                        <table class="table table-hover" id="datatable_beli">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Kode Bahan</th>
                                                    <th>Nama Barang</th>
                                                    <th>Fee</th>
                                                    <th>Acuan</th>
                                                    <th>Qty</th>
                                                    <th>Total CashBack</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $cashback = $invoice->cashbackUv; // Objek tunggal
                                                @endphp

                                                @if($cashback) <!-- Cek apakah cashbackUv ada -->
                                                @foreach($cashback->detail as $detail)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $detail->product->code }}</td>
                                                    <td>{{ $detail->product->name }}</td>
                                                    <td style="width:8%">
                                                        <input class="form-control text-center rupiah" type="number" name="cashback_beli[]" value="{{ $detail->price_cashback }}" readonly>
                                                    </td>
                                                    <td style="width:10%">
                                                        <input class="form-control text-center" type="text" name="item_price_beli[]" value="{{ number_format($detail->amount_cashback_idr / $detail->qty, 2, ',', '.') }}" readonly>
                                                    </td>
                                                    <td style="width:8%">
                                                        <input class="form-control text-center" type="number" name="item_qty_beli[]" value="{{ $detail->qty }}" readonly>
                                                    </td>
                                                    <td style="width:15%">
                                                        <input class="form-control text-center item_grand_total_beli rupiah" type="number" name="item_grand_total_beli[]" value="{{ $detail->amount_cashback_idr }}" readonly>
                                                    </td>
                                                </tr>
                                                @endforeach
                                                @endif
                                            </tbody>
                                            <tfoot>
                                                <tr class="row-footer-subtotal">
                                                    <td colspan="6" class="text-right"><b>TOTAL :</b></td>
                                                    <td class="text-center">
                                                        <input type="text" name="subtotal_cashback_beli" id="subtotal_cashback_beli" class="form-control text-center" readonly>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn bg-gd-cherry border-0 text-white prevBtn float-left">
                            <i class="fa fa-arrow-left mr-10"></i> Previous
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.datatables-button')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#toggleDetailNota').on('click', function() {
            var $this = $(this);
            var icon = $this.find('i');

            if ($this.attr('aria-expanded') === 'true') {
                icon.removeClass('fa-chevron-up').addClass('fa-chevron-down'); // Ganti ikon ke panah bawah
            } else {
                icon.removeClass('fa-chevron-down').addClass('fa-chevron-up'); // Ganti ikon ke panah atas
            }
        });

        $('#toggleDetailCustomer').on('click', function() {
            var $this = $(this);
            var icon = $this.find('i');

            if ($this.attr('aria-expanded') === 'true') {
                icon.removeClass('fa-chevron-up').addClass('fa-chevron-down'); // Ganti ikon ke panah bawah
            } else {
                icon.removeClass('fa-chevron-down').addClass('fa-chevron-up'); // Ganti ikon ke panah atas
            }
        });

        calculateGrandTotal();

        $('.js-select2').select2();

        $('#datatable_jual, #datatable_beli').DataTable({
            paging: false,
            searching: false,
            info: false,
            order: [[3, 'asc']],
        });

        // calculated grand total jual & beli
        function calculateGrandTotal() {
            let grandTotalJual = 0;
            let grandTotalBeli = 0;

            $('.item_purchase_total_jual').each(function () {
                let value = parseFloat($(this).val()) || 0; // Pastikan nilai kosong menjadi 0
                grandTotalJual += value;
            });

            $('.item_grand_total_beli').each(function () {
                let value = parseFloat($(this).val()) || 0; // Pastikan nilai kosong menjadi 0
                grandTotalBeli += value;
            });

            $('#grand_total_jual').val(new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(grandTotalJual));
            $('#subtotal_cashback_beli').val(new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(grandTotalBeli));
        }

        // Fungsi update step navigation
        let currentStep = 1;
        function updateStepNavigation() {
            $(".stepwizard-step a").removeClass("btn-primary").addClass("btn-secondary");
            $(".stepwizard-step a[href='#step-" + currentStep + "']").removeClass("btn-secondary").addClass("btn-primary");
        }

        // Event untuk tombol Next
        $(".nextBtn").click(function () {
            let curStep = $("#step-" + currentStep);
            let nextStep = $("#step-" + (currentStep + 1));
            let isValid = curStep.find(".required").toArray().every(input => $(input).val().trim() !== "");

            if (isValid) {
                curStep.hide();
                nextStep.show();
                currentStep++;
                updateStepNavigation();
            }
        });

        // Event untuk tombol Previous
        $(".prevBtn").click(function () {
            $("#step-" + currentStep).hide();
            currentStep--;
            $("#step-" + currentStep).show();
            updateStepNavigation();
        });
    });
</script>
@endpush
