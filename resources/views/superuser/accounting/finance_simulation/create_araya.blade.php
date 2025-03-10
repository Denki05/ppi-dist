@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">UV</span>
    <span class="breadcrumb-item">Araya</span>
    <span class="breadcrumb-item active">Create</span>
</nav>

<div id="alert-block"></div>

<div class="block">
    <div class="block-content">
        <form class="ajax" data-action="{{ route('superuser.accounting.finance_simulation.store_araya') }}" data-type="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" value="{{ $invoice->id }}" name="invoice_id">
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
                                                    <input type="text" name="code_jual" class="form-control-plaintext" value="UV-{{ $invoice->do_code }}" readonly>
                                                    <input type="hidden" name="code_beli" class="form-control-plaintext" value="UV-C{{ $invoice->do_code }}" readonly>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="type_transaction">Type Transaksi</label>
                                                    <input type="text" name="type_transaction" class="form-control-plaintext" value="{{ $invoice->type_transaction }}" readonly>
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label for="type_transaction">Tanggal Nota <span class="text-danger">*</span></label>
                                                    <input type="date" class="form-control-plaintext" value="{{ $invoice->so->so_date }}" readonly>
                                                    <input type="hidden" name="invoice_date" value="{{ $invoice->so->so_date }}">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="type_transaction">Kurs <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control-plaintext" value="{{ $invoice->idr_rate }}" readonly>
                                                    <input type="hidden" id="invoice_kurs" value="{{ $invoice->idr_rate }}">
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label for="type_transaction">Status Pembayaran <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control-plaintext" name="payable_status" value="{{ $pembayaran->status_pembayaran }}" readonly>
                                                    <input type="hidden" name="payable_proses" value="{{ $pembayaran->proses_by }}">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="type_transaction">Tanggal Pembayaran <span class="text-danger">*</span></label>
                                                    <input type="date" class="form-control-plaintext" name="payable_date" id="payable_date" value="{{ $pembayaran->tangal_pembayaran ?? '-' }}" readonly>
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
                                                    <input class="form-control-plaintext" type="text" value="{{ $invoice->member->name }}" readonly>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="note">Alamat Kirim</label>
                                                    <input class="form-control-plaintext" type="text" value="{{ $invoice->member->address }}" readonly>
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label for="customer_city">Kota</label>
                                                    <input class="form-control-plaintext" type="text" value="{{ $invoice->member->text_kota }}" readonly>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="customer_area">Provinsi</label>
                                                    <input class="form-control-plaintext" type="text" value="{{ $invoice->member->text_provinsi }}" readonly>
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
                                                        <th>Kode Barang</th>
                                                        <th>Nama Barang</th>
                                                        <th>Kemasan</th>
                                                        <th>nett / kg</th>
                                                        <th>Qty</th>
                                                        <th>Total price Nett</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($get_product as $key)
                                                    <tr>
                                                        <input type="hidden" name="disc_usd_jual[]" class="disc_usd_jual" value="{{ $key->usd_disc }}">
                                                        <input type="hidden" name="disc_percent_jual" class="disc_percent_jual" value="{{ $key->discount_1 }}">
                                                        <input type="hidden" name="disc_kemasan_jual" class="disc_kemasan_jual" value="{{ $key->discount_2 }}">
                                                        <input type="hidden" name="disc_percent_idr_jual" class="disc_percent_idr_jual">
                                                        <input type="hidden" name="disc_kemasan_idr_jual" class="disc_kemasan_idr_jual">
                                                        <input type="hidden" name="disc_tambahan_jual" class="disc_tambahan_jual" value="{{ $key->discount_idr }}">
                                                        <input type="hidden" name="voucher_jual" class="voucher_jual" value="{{ $key->voucher_idr }}">
                                                        <input type="hidden" name="ppn_percent" class="ppn_percent" value="{{ $key->ppn_percent }}">
                                                        <input type="hidden" name="ppn_idr" class="ppn_idr" value="{{ $key->ppn_idr }}">
                                                        <input type="hidden" name="product_jual[]" value="{{ $key->id_produk }}">
                                                        <input type="hidden" name="free_jual[]" value="{{ $key->free_product }}">
                                                        <input type="hidden" name="item_name_jual[]" class="item_name_jual" value="{{ $key->nama_produk }}">
                                                        <td style="width: 5%;">{{ $loop->iteration }}</td>
                                                        <td style="width: 10%;">{{ $key->kode_produk }}</td>
                                                        <td style="width: 20%;">{{ $key->nama_produk }}</td>
                                                        <td style="width: 10%;">{{ $key->kemasan }}</td>
                                                        <td style="width: 10%;">
                                                            <input class="form-control text-center item_price_jual" type="text" name="item_price_jual[]" value="{{ $key->harga_jual_tax ?? 00 }}" readonly>
                                                            <input type="hidden" class="item_price_beli" name="item_price_beli[]" value="{{ $key->harga_beli_tax ?? 0 }}">
                                                        </td>
                                                        <td style="width: 10%;">
                                                            <input class="form-control text-center item_qty_jual" type="text" name="item_qty_jual[]" id="item_qty_jual" value="{{ $key->qty }}" readonly>
                                                        </td>
                                                        <td style="width: 15%;">
                                                            <input class="form-control text-center item_price_nett_jual" type="text" name="item_price_nett_jual[]" id="item_price_nett_jual" readonly>
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

                                            <div class="row pt-30 mb-15">
                                                <div class="col text-right">
                                                    <button type="button"class="btn btn-warning" id="btn_call_jual"><i class="fas fa-calculator pr-2" aria-hidden="true"></i>calculated</button>
                                                </div>
                                            </div>
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
                                                        <th>Kode Barang</th>
                                                        <th>Nama Barang</th>
                                                        <th>Fee</th>
                                                        <th>Nett / KG</th>
                                                        <th>Qty</th>
                                                        <th>Item Price Cashback</th>
                                                        <th>Total CashBack</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($get_product as $key)
                                                    <tr>
                                                        <input type="hidden" name="disc_usd_beli[]" value="{{ $key->usd_disc }}">
                                                        <input type="hidden" name="disc_percent_beli[]" value="{{ $key->discount_1 }}">
                                                        <input type="hidden" name="disc_kemasan_beli[]" value="{{ $key->discount_2 }}">
                                                        <input type="hidden" name="disc_tambahan_beli[]" value="{{ $key->discount_idr }}">
                                                        <input type="hidden" name="voucher_beli[]" value="{{ $key->voucher_idr }}">
                                                        <input type="hidden" name="product_beli[]" value="{{ $key->id_produk }}">
                                                        <input type="hidden" name="free_beli[]" value="{{ $key->free_product }}">
                                                        <input type="hidden" name="item_name_beli[]" class="item_name_beli" value="{{ $key->nama_produk }}">
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $key->kode_produk }}</td>
                                                        <td>{{ $key->nama_produk }}</td>
                                                        <td style="width:8%">
                                                            <input class="form-control text-center" type="number" name="cashback_beli[]" value="{{ $key->cashback }}" readonly>
                                                        </td>
                                                        <td style="width:10%">
                                                            <input class="form-control text-center" type="number" name="item_price_beli[]" value="{{ $key->harga_jual_tax }}" readonly>
                                                        </td>
                                                        <td style="width:8%">
                                                            <input class="form-control text-center" type="number" name="item_qty_beli[]" value="{{ $key->qty }}" readonly>
                                                        </td>
                                                        <td style="width:15%">
                                                            <input class="form-control text-center item_price_cashback_beli" type="text" name="item_price_cashback_beli[]" readonly>
                                                        </td>
                                                        <td style="width:15%">
                                                            <input class="form-control text-center item_grand_total_beli" type="text" name="item_grand_total_beli[]" readonly>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr class="row-footer-subtotal">
                                                        <td colspan="7" class="text-right"><b>TOTAL :</b></td>
                                                        <td class="text-center">
                                                            <input type="text" name="subtotal_cashback_beli" id="subtotal_cashback_beli" class="form-control text-center" readonly>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>

                                            <div class="row pt-30 mb-15">
                                                <div class="col text-right">
                                                    <button type="button" class="btn btn-warning" id="btn_call_beli">
                                                        <i class="fas fa-calculator pr-2"></i> Calculate
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn bg-gd-cherry border-0 text-white prevBtn float-left">
                                <i class="fa fa-arrow-left mr-10"></i> Previous
                            </button>
                            <button class="btn btn-success float-right" type="submit">
                            <i class="fa fa-save mr-10"></i> Submit
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
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
        $('.js-select2').select2();

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

        $('#datatable_jual, #datatable_beli').DataTable({
            paging: false,
            searching: false,
            info: false,
            order: [[2, 'asc']],
        });

        // Nonaktifkan tombol Next dan Submit saat pertama kali dimuat
        $(".nextBtn").prop("disabled", true);
        $("#btn_submit").prop("disabled", true);

        let jualCalculated = false;
        let beliCalculated = false;

        // Event listener untuk Invoice Jual
        $('#btn_call_jual').on('click', function () {
            let kurs = 15500;
            let cashback = 0;
            let isFirstDiscountApplied = false;
            let hasZeroPrice = false;
            let zeroPriceProduct = ''; // Simpan nama produk yang harga kosong

            $('#datatable_jual tbody tr').each(function () {
                let disc_usd = parseFloat($(this).find('.disc_usd_jual').val()) || 0;
                let disc_usd_idr = disc_usd * kurs;
                let qty = parseFloat($(this).find('.item_qty_jual').val()) || 0;
                let price = parseFloat($(this).find('.item_price_jual').val()) || 0;
                let discount_percent = parseFloat($(this).find('.disc_percent_jual').val()) || 0;
                let discount_kemasan = parseFloat($(this).find('.disc_kemasan_jual').val()) || 0;
                let discount_tambahan = parseFloat($(this).find('.disc_tambahan_jual').val()) || 0;
                let free = parseFloat($(this).find('.free_jual').val()) || 0;
                let productName = $(this).find('.item_name_jual').val(); // Ambil nama produk dari input hidden

                let disc_tambahan_idr_item = 0;
                if (!isFirstDiscountApplied && free !== 1) {
                    disc_tambahan_idr_item = discount_tambahan;
                    isFirstDiscountApplied = true;
                }

                if (price === 0) {
                    hasZeroPrice = true;
                    zeroPriceProduct = productName;
                    return false; // Hentikan loop jika ada harga 0
                }

                let unit_price_before = price - disc_usd_idr;
                let amount_total_before = unit_price_before * qty;

                let disc_percent_idr = amount_total_before * (discount_percent / 100);
                let disc_kemasan_idr = (amount_total_before - disc_percent_idr) * (discount_kemasan / 100);
                let amount_total_after = amount_total_before - disc_percent_idr - disc_kemasan_idr - disc_tambahan_idr_item;

                $(this).find('.disc_percent_idr_jual').val(disc_percent_idr);
                $(this).find('.disc_kemasan_idr_jual').val(disc_kemasan_idr);

                // Perbaikan: gunakan `amount_total_after` bukan `amount_total_before`
                $(this).find('.item_price_nett_jual').val(formatRupiahManual(amount_total_after));
            });

            if (hasZeroPrice) {
                alert('Produk "' + zeroPriceProduct + '" memiliki harga 0! Silakan periksa kembali.');
                return; // Hentikan eksekusi selanjutnya
            }

            let subtotal_nett = 0;
            $('#datatable_jual tbody tr').each(function () {
                let row_total = parseRupiah($(this).find('.item_price_nett_jual').val()) || 0;
                subtotal_nett += row_total;
            });

            $('#grand_total_jual').val(formatRupiahManual(subtotal_nett));

            // Aktifkan tombol Next setelah berhasil kalkulasi jual
            jualCalculated = true;
            $(".nextBtn").prop("disabled", false);
        });

        // Event listener untuk Invoice Beli
        $('#btn_call_beli').on('click', function (e) {
            let total_qty = 0;
            let isFirstDiscountApplied = false;
            let hasZeroPrice = false;
            let zeroPriceProduct = ''; // Simpan nama produk yang harga kosong

            // Calculate total quantity
            $('#datatable_beli tbody tr').each(function () {
                let qty = parseFloat($(this).find('input[name="item_qty[]"]').val()) || 0;
                total_qty += qty;
            });

            // Loop through each table row to calculate discounts and totals
            $('#datatable_beli tbody tr').each(function () {
                let disc_usd = parseFloat($(this).find('input[name="disc_usd_beli[]"]').val()) || 0;
                let kurs = 15500;
                let usd_disc_idr = disc_usd * kurs;
                let qty = parseFloat($(this).find('input[name="item_qty_beli[]"]').val()) || 0;
                let price = parseFloat($(this).find('input[name="item_price_beli[]"]').val()) || 0;
                let discount_percent = parseFloat($(this).find('input[name="disc_percent_beli[]"]').val()) || 0;
                let discount_kemasan = parseFloat($(this).find('input[name="disc_kemasan_beli[]"]').val()) || 0;
                let discount_tambahan = parseFloat($(this).find('input[name="disc_tambahan_beli[]"]').val()) || 0;
                let cashback = parseFloat($(this).find('input[name="cashback_beli[]"]').val()) || 0;
                let free = parseFloat($(this).find('input[name="free_beli[]"]').val()) || 0;
                let productName = $(this).find('.item_name_beli').val(); // Ambil nama produk dari input hidde

                // Determine if the additional discount should be applied
                let disc_tambahan_idr_item = 0;
                if (!isFirstDiscountApplied && free != 1) {
                    disc_tambahan_idr_item = discount_tambahan;
                    isFirstDiscountApplied = true;
                }

                if (price == 0) {
                    hasZeroPrice = true;
                    zeroPriceProduct = productName;
                    return false; // Hentikan loop jika ada harga 0
                }

                let unit_price_before = price - usd_disc_idr;
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

                // Set the calculated values
                $(this).find('input[name="item_price_cashback_beli[]"]').val(formatRupiahManual(amount_after_cashback));
                $(this).find('input[name="item_grand_total_beli[]"]').val(formatRupiahManual(final_amount_after_cashback));
            });

            if (hasZeroPrice) {
                alert('Produk "' + zeroPriceProduct + '" memiliki harga 0! Silakan periksa kembali.');
                return; // Hentikan eksekusi selanjutnya
            }

            // Calculate the subtotal
            let subtotal_cashback = 0;
           
            $('#datatable_beli tbody tr').each(function () {
                let row_total = parseRupiah($(this).find('input[name="item_grand_total_beli[]"]').val()) || 0;
                subtotal_cashback += row_total;
            });

            // Set the subtotal values
            $('#subtotal_cashback_beli').val(formatRupiahManual(subtotal_cashback));

            // Aktifkan tombol Submit setelah berhasil kalkulasi beli
            beliCalculated = true;
            $("#btn_submit").prop("disabled", false);

            // Check if all `item_grand_total` fields are calculated correctly
            let allCalculated = true;
            $('#datatable_beli tbody tr').each(function () {
                let grand_total = parseFloat($(this).find('input[name="item_grand_total_beli[]"]').val()) || 0;
                if (grand_total === 0) {
                    allCalculated = false;
                    return false; // Break out of the loop
                }
            });
        });

        // Fungsi update step navigation
        let currentStep = 1;
        function updateStepNavigation() {
            $(".stepwizard-step a").removeClass("btn-primary").addClass("btn-secondary");
            $(".stepwizard-step a[href='#step-" + currentStep + "']").removeClass("btn-secondary").addClass("btn-primary");
        }

        // Event untuk tombol Next
        $(".nextBtn").click(function () {
            if (!jualCalculated) {
                alert("Silakan lakukan kalkulasi terlebih dahulu!");
                return;
            }

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

        // Fungsi untuk mengonversi angka ke format Rupiah
        function formatRupiahManual(number) {
            return number.toFixed(2) // Pastikan selalu ada 2 desimal
                .replace('.', '#') // Ganti sementara titik desimal agar tidak tertukar
                .replace(/\B(?=(\d{3})+(?!\d))/g, '.') // Tambahkan titik untuk ribuan
                .replace('#', ','); // Ubah titik desimal kembali menjadi koma
        }

        // Fungsi untuk menghapus format Rupiah dan mengembalikan angka
        function parseRupiah(rupiah) {
            let cleanNumber = rupiah.replace(/[^0-9,]/g, '') // Hapus semua kecuali angka dan koma
                .replace(/\./g, '') // Hapus semua titik (pemisan ribuan)
                .replace(',', '.'); // Ubah koma desimal menjadi titik

            return parseFloat(cleanNumber) || 0;
        }
    });
</script>
@endpush