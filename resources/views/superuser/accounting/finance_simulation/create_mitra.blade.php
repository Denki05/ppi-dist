@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">UV</span>
    <span class="breadcrumb-item">CV</span>
    <span class="breadcrumb-item active">Create Mitra</span>
</nav>

<div id="alert-block"></div>

<div class="block">
    <div class="block-content">
        <form class="ajax" data-action="{{ route('superuser.accounting.finance_simulation.store_mitra') }}" data-type="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" value="{{ $mitra->id }}" name="mitra_id">
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
                                    <label for="addInvoice">Invoice</label>
                                    <div class="form-control-plaintext">{{ $do_uv->code }}</div>
                                    <input type="hidden" name="do_uv_id" class="form-control" value="{{ $do_uv->id }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="type_transaction">Type Transaksi</label>
                                    <div class="form-control-plaintext">{{ $do_uv->transaksi }}</div>
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
                                    <label for="customer_name">Customer</label>
                                    <div class="form-control-plaintext">{{ $do_uv->customer->name }} {{ $do_uv->customer->text_kota }}</div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="customer_address">Alamat Kirim</label>
                                    <div class="form-control-plaintext">{{ $do_uv->customer->address }}</div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="customer_kota">Kota</label>
                                    <div class="form-control-plaintext">{{ $do_uv->customer->text_kota }}</div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="customer_provinsi">Provinsi</label>
                                    <div class="form-control-plaintext">{{ $do_uv->customer->text_provinsi }}</div>
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
                                                <input type="hidden" name="invoice_type" value="jual">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Kode</th>
                                                        <th>Nama</th>
                                                        <th>Kemasan</th>
                                                        <th>Acuan</th>
                                                        <th>Qty</th>
                                                        <th>Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($do_uv->simulation_item as $item => $key)
                                                        <tr>
                                                            <td style="width: 5%;">{{ $item + 1 }}</td>
                                                            <td style="width: 10%;">{{ $key->product->code }}</td>
                                                            <td style="width: 15%;">{{ $key->product->name }}</td>
                                                            <td style="width: 10%;">
                                                                {{ $key->product->packaging->pack_name }}
                                                                <input type="hidden" name="product_name_jual[]" class="product_name_jual" value="{{ $key->product->id }}">
                                                            </td>
                                                            <td style="width: 10%;">
                                                                <input type="text" class="form-control price_jual" style="text-align: center;" name="price_jual[]" 
                                                                    value="{{ optional($key->product->latest_price_uv())->selling_price_usd_unit }}" readonly>
                                                            </td>
                                                            <td style="width: 10%;">
                                                                <input type="text" class="form-control qty_jual" style="text-align: center;" name="qty_jual[]" value="{{ $key->qty }}" readonly>
                                                            </td>
                                                            <td style="width: 15%;">
                                                                <input type="text" class="form-control subtotal_item_jual" style="text-align: center;" name="subtotal_item_jual[]" readonly>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr class="row-footer-subtotal">
                                                        <td colspan="6" class="text-right">
                                                            <b>Subtotal :</b>
                                                        </td>
                                                        <td>
                                                            <input type="text" name="grand_total_jual" id="grand_total_jual" style="text-align: right;" class="form-control text-center" readonly>
                                                        </td>
                                                    </tr>
                                                    <tr class="row-footer-subtotal">
                                                        <td colspan="6" class="text-right">
                                                            <b>PPN (%)</b>
                                                        </td>
                                                        <td>
                                                            <div class="row">
                                                                <div class="col">
                                                                    <input type="text" name="ppn_percent_jual" id="ppn_percent_jual" class="form-control text-center">
                                                                </div>
                                                                <div class="col">
                                                                    <input type="text" name="ppn_idr_jual" id="ppn_idr_jual" class="form-control text-center" readonly>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="row-footer-subtotal">
                                                        <td colspan="6" class="text-right">
                                                            <b>Grand Total</b>
                                                        </td>
                                                        <td>
                                                            <input type="text" name="all_grand_total_jual" id="all_grand_total_jual" class="form-control text-center" readonly>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>

                                            <div class="row pt-30 mb-15">
                                                <div class="col text-right">
                                                    <button type="button" class="btn btn-warning" id="btn_call_jual">
                                                        <i class="fas fa-calculator pr-2" aria-hidden="true"></i>Hitung
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <a href="{{ route('superuser.accounting.finance_simulation.index_mitra') }}">
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
                                                <input type="hidden" name="invoice_type" value="beli">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Kode</th>
                                                        <th>Nama</th>
                                                        <th>Kemasan</th>
                                                        <th>Acuan</th>
                                                        <th>Qty</th>
                                                        <th>Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($do_uv->simulation_item as $key => $row)
                                                        <tr>
                                                            <td style="width: 5%;">{{ $key + 1 }}</td>
                                                            <td style="width: 10%;">{{ $row->product->code }}</td>
                                                            <td style="width: 15%;">{{ $row->product->name }}</td>
                                                            <td style="width: 10%;">
                                                                {{ $row->product->packaging->pack_name }}
                                                                <input type="hidden" name="product_name_beli[]" class="product_name_beli" value="{{ $row->product->id }}">
                                                            </td>
                                                            <td style="width: 10%;">
                                                                <input type="text" class="form-control price_beli" style="text-align: center;" name="price_beli[]" 
                                                                    value="{{ optional($row->product->latest_price_uv())->buying_price_usd_unit }}" readonly>
                                                            </td>
                                                            <td style="width: 10%;">
                                                                <input type="text" class="form-control qty_beli" style="text-align: center;" name="qty_beli[]" value="{{ $row->qty }}" readonly>
                                                            </td>
                                                            <td style="width: 15%;">
                                                                <input type="text" class="form-control subtotal_item_beli" style="text-align: center;" name="subtotal_item_beli[]" readonly>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr class="row-footer-subtotal">
                                                        <td colspan="6" class="text-right"><b>Subtotal :</b></td>
                                                        <td>
                                                            <input type="text" name="grand_total_beli" id="grand_total_beli" class="form-control text-center" readonly>
                                                        </td>
                                                    </tr>
                                                    <tr class="row-footer-subtotal">
                                                        <td colspan="6" class="text-right"><b>PPN (%)</b></td>
                                                        <td>
                                                            <div class="row">
                                                                <div class="col">
                                                                    <input type="text" name="ppn_percent_beli" id="ppn_percent_beli" class="form-control text-center">
                                                                </div>
                                                                <div class="col">
                                                                    <input type="text" name="ppn_idr_beli" id="ppn_idr_beli" class="form-control text-center" readonly>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="row-footer-subtotal">
                                                        <td colspan="6" class="text-right"><b>Grand Total</b></td>
                                                        <td>
                                                            <input type="text" name="all_grand_total_beli" id="all_grand_total_beli" class="form-control text-center" readonly>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>

                                            <div class="row pt-30 mb-15">
                                                <div class="col text-right">
                                                    <button type="button" class="btn btn-warning" id="btn_call_beli">
                                                        <i class="fas fa-calculator pr-2"></i> Hitung
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
                            <button class="btn btn-success float-right" id="btn_submit" type="submit">
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

        $(".js-select2-mitra").select2();
        
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
            order: [[3, 'asc']],
        });

        function hitungTotal() {
            let hasZeroPrice = false;
            let subtotal_item = 0;
            let zeroPriceProducts = [];

            $('#datatable_jual tbody tr').each(function () {
                let qty = parseFloat($(this).find('.qty_jual').val()) || 0;
                let price = parseFloat($(this).find('.price_jual').val()) || 0;
                let productName = $(this).find('td:nth-child(3)').text().trim(); // Ambil nama produk

                if (price === 0) {
                    hasZeroPrice = true;
                    zeroPriceProducts.push(productName);
                }

                let subtotal = qty * price;
                subtotal_item += subtotal;

                // Perbarui nilai subtotal di setiap baris
                $(this).find('.subtotal_item_jual').val(subtotal.toFixed(2));
            });

            if (hasZeroPrice) {
                alert('Produk berikut memiliki harga 0!\n' + zeroPriceProducts.join("\n"));
                return;
            }

            $('#grand_total_jual').val(formatRupiahManual(subtotal_item));

            // Hitung ulang PPN jika sudah diisi
            hitungPPN();

            jualCalculated = true;
            $(".nextBtn").prop("disabled", false);
        }

        function hitungPPN() {
            let ppnPercent = parseFloat($('#ppn_percent_jual').val()) || 0;
            let subtotal_item = parseRupiah($('#grand_total_jual').val()) || 0;

            let ppnIdr = (ppnPercent / 100) * subtotal_item;
            let grandTotal = subtotal_item + ppnIdr;

            $('#ppn_idr_jual').val(formatRupiahManual(ppnIdr));
            $('#all_grand_total_jual').val(formatRupiahManual(grandTotal));

            // Aktifkan tombol next
            $(".nextBtn").prop("disabled", false);
        }

        // Event listener saat tombol "Hitung" diklik
        $('#btn_call_jual').on('click', function () {
            hitungTotal();
        });

        // Event listener untuk menghitung ulang saat qty atau price berubah
        $(document).on('keyup change', '.price_jual, .qty_jual', function() {
            hitungTotal();
        });

        // Event listener untuk menghitung ulang PPN saat nilainya berubah
        $('#ppn_percent_jual').on('keyup', function () {
            hitungPPN();
        });

        function hitungTotalBeli() {
            let hasZeroPrice = false;
            let subtotal_item = 0;
            let zeroPriceProducts = [];

            $('#datatable_beli tbody tr').each(function () {
                let qty = parseFloat($(this).find('.qty_beli').val()) || 0;
                let price = parseFloat($(this).find('.price_beli').val()) || 0;
                let productName = $(this).find('td:nth-child(3)').text().trim(); // Ambil nama produk

                if (price === 0) {
                    hasZeroPrice = true;
                    zeroPriceProducts.push(productName);
                }

                let subtotal = qty * price;
                subtotal_item += subtotal;

                // Perbarui nilai subtotal di setiap baris
                $(this).find('.subtotal_item_beli').val(formatRupiahManual(subtotal));
            });

            if (hasZeroPrice) {
                alert('Produk berikut memiliki harga 0!\n' + zeroPriceProducts.join("\n"));
                return;
            }

            $('#grand_total_beli').val(formatRupiahManual(subtotal_item));

            // Hitung ulang PPN jika sudah diisi
            hitungPPNBeli();

            // Aktifkan tombol Submit
            beliCalculated = true;
            $("#btn_submit").prop("disabled", false);
        }

        function hitungPPNBeli() {
            let ppnPercent = parseFloat($('#ppn_percent_beli').val()) || 0;
            let subtotal_item = parseRupiah($('#grand_total_beli').val()) || 0;

            let ppnIdr = (ppnPercent / 100) * subtotal_item;
            let grandTotal = subtotal_item + ppnIdr;

            $('#ppn_idr_beli').val(formatRupiahManual(ppnIdr));
            $('#all_grand_total_beli').val(formatRupiahManual(grandTotal));

            // Aktifkan tombol submit
            $("#btn_submit").prop("disabled", false);
        }

        // Event listener saat tombol "Hitung" diklik
        $('#btn_call_beli').on('click', function () {
            hitungTotalBeli();
        });

        // Event listener untuk perubahan harga atau qty
        $(document).on('keyup change', '.price_beli, .qty_beli', function() {
            hitungTotalBeli();
        });

        // Event listener untuk perubahan PPN
        $('#ppn_percent_beli').on('keyup', function () {
            hitungPPNBeli();
        });

        let jualCalculated = false;
        let beliCalculated = false;

        // Fungsi update step navigation
        let currentStep = 1;
        function updateStepNavigation() {
            $(".stepwizard-step a").removeClass("btn-primary").addClass("btn-secondary");
            $(".stepwizard-step a[href='#step-" + currentStep + "']").removeClass("btn-secondary").addClass("btn-primary");
        }

        // Event untuk tombol Next
        $(".nextBtn").click(function () {
            if (!jualCalculated) {
                alert("Silahkan isi Nilai PPN dahulu!");
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

        $("#btn_submit").click(function () {
            if (!beliCalculated) {
                alert("Silakan lakukan kalkulasi pembelian terlebih dahulu sebelum menyimpan!");
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