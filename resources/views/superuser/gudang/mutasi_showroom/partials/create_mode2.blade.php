<style>
    /* 1. Header dengan Gradient Biru */
    .brand-header { 
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); 
        border-radius: 0 0 25px 25px; 
        margin-bottom: 20px; 
        padding: 15px;
    }

    /* 2. Container Putih Informasi Utama */
    .header-info-card {
        background: #ffffff;
        border-radius: 15px;
        padding: 12px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .info-header-text { 
        font-size: 16px; 
        color: #000; 
        display: flex;
        align-items: center;
    }

    /* 3. PERBAIKAN SELECT TIPE & BRAND (Background Putih & Border) */
    /* Target khusus untuk select2 di dalam area brand-header */
    .brand-header .select2-container--bootstrap4 .select2-selection--single {
        height: 42px !important; /* Sedikit lebih tinggi agar nyaman di mobile */
        line-height: 40px !important;
        border-radius: 10px !important;
        background-color: #ffffff !important; /* Background Putih solid */
        border: 1px solid #d1d3e2 !important; /* Border 1px solid */
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    /* Memastikan teks di dalam select terlihat hitam bold */
    .brand-header .select2-container--bootstrap4 .select2-selection__rendered {
        color: #2e59d9 !important; /* Warna biru pekat agar kontras dengan putih */
        font-weight: 800 !important;
        padding-left: 15px !important;
    }

    /* Mengatur warna placeholder select2 */
    .brand-header .select2-container--bootstrap4 .select2-selection--single .select2-selection__placeholder {
        color: #858796 !important;
        font-weight: 500;
    }

    /* Styling lainnya tetap sama */
    .cart-item { border-radius: 12px !important; border: 1px solid #f1f1f1 !important; margin-bottom: 10px; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .quick-search-box { border-radius: 12px !important; background: #fff; border: 2px solid #4e73df; }
    .qty-input { background: #f1f4f9; border: 1px solid #d1d3e2; border-radius: 8px !important; font-weight: 800; color: #4e73df; height: 38px; }
    .btn-action-mode2 { border-radius: 10px; font-weight: 700; font-size: 12px; padding: 12px; text-transform: uppercase; }
</style>

<div class="container-fluid p-0 pb-5 text-left">
    <div class="brand-header shadow">
        <div class="header-info-card mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 32px; height: 32px;">
                        <i class="fa fa-bolt" style="font-size: 14px;"></i>
                    </div>
                    <div class="info-header-text font-weight-bold">
                        <span class="text-uppercase">{{ $kode }}</span>
                        <span class="mx-2 text-muted" style="font-weight: 300;">|</span>
                        <span>{{ date('d/m/Y') }}</span>
                    </div>
                    <input type="hidden" name="tanggal" value="{{ date('Y-m-d') }}">
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger rounded-circle border-0 btnBackToList">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        </div>
        
        <div class="row no-gutters">
            <div class="col-6 pr-1">
                <select name="type" id="type_selector_mode2" class="form-control select2-interaktif">
                    <option value="">-- TIPE --</option>
                    @foreach($types as $key => $value)
                        <option value="{{ $value }}">{{ $key }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 pl-1">
                <select name="brand_name" id="brand_selector_mode2" class="form-control select2-interaktif">
                    <option value="">-- BRAND --</option>
                    @foreach($brands as $b)
                        <option value="{{ $b->brand_name }}">{{ $b->brand_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="px-3">
        <div class="card quick-search-box shadow-sm mb-3 border-0" id="search-container" style="opacity: 0.5; pointer-events: none;">
            <div class="card-body p-2">
                <select id="quick-add-product" class="form-control select-product-mobile"></select>
            </div>
        </div>

        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center px-1 mb-2">
                <h6 class="text-xs font-weight-bold text-muted mb-0">ITEM TERPILIH</h6>
                <span class="badge badge-primary px-2" id="count-items">0</span>
            </div>
            <div id="cart-items"></div>
        </div>

        <div id="submit-section" style="display:none;">
            <div class="row no-gutters mb-5">
                <div class="col-2 pr-1">
                    <button type="button" class="btn btn-light btn-block btn-action-mode2 border shadow-sm btnBackToList">
                        Batal
                    </button>
                </div>
                <div class="col-2 pl-1">
                    <button type="submit" class="btn btn-success btn-block btn-action-mode2 shadow">
                        <i class="fa fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    const $cartItems = $('#cart-items');
    const $quickAdd = $('#quick-add-product');
    const $brandSelector = $('#brand_selector_mode2');
    let lastBrandMode2 = $brandSelector.val();

    $('.select2-interaktif').select2({ theme: 'bootstrap4', width: '100%' });

    // Tombol Batal & Close dengan Alert
    $(document).off('click', '.btnBackToList').on('click', '.btnBackToList', function () {
        if ($('.cart-item').length > 0) {
            Swal.fire({
                title: 'Batalkan Proses?',
                text: "Yakin membatalkan proses, data akan di reset!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Batalkan'
            }).then((result) => {
                if (result.isConfirmed) {
                    exitLockedMode();
                    loadFrameB('{{ route("superuser.gudang.mutasi_showroom.list_partial") }}');
                }
            });
        } else {
            exitLockedMode();
            loadFrameB('{{ route("superuser.gudang.mutasi_showroom.list_partial") }}');
        }
    });

    // Proteksi Ubah Brand
    $brandSelector.on('select2:selecting', function(e) {
        const nextBrand = e.params.args.data.id;
        if ($('.cart-item').length > 0 && lastBrandMode2 !== "" && lastBrandMode2 !== nextBrand) {
            e.preventDefault();
            Swal.fire({
                title: 'Ubah Brand?',
                text: "List barang terpilih akan dihapus otomatis!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Reset'
            }).then((result) => {
                if (result.isConfirmed) {
                    $cartItems.empty();
                    updateState();
                    lastBrandMode2 = nextBrand;
                    $brandSelector.val(nextBrand).trigger('change');
                }
            });
        }
    });

    $brandSelector.on('change', function() {
        const val = $(this).val();
        if(val) {
            lastBrandMode2 = val;
            $('#search-container').css({'opacity': '1', 'pointer-events': 'auto'});
        } else {
            $('#search-container').css({'opacity': '0.5', 'pointer-events': 'none'});
        }
    });

    $quickAdd.select2({
        theme: 'bootstrap4',
        placeholder: 'Cari nama produk...',
        ajax: {
            url: '{{ route("superuser.gudang.mutasi_showroom.get_product_pack") }}',
            data: params => ({ id: params.term, brand_name: $brandSelector.val() }),
            processResults: res => res
        }
    });

    $quickAdd.on('select2:select', function (e) {
        const data = e.params.data;
        const index = $('.cart-item').length;

        if ($(`input[value="${data.id}"]`).length > 0) {
            Swal.fire('Info', 'Produk sudah ada', 'warning');
            $quickAdd.val(null).trigger('change');
            return;
        }

        const itemHtml = `
            <div class="cart-item animated slideInRight p-2">
                <input type="hidden" name="items[${index}][product_id]" value="${data.id}">
                <div class="d-flex justify-content-between align-items-start">
                    <div style="max-width: 85%;">
                        <small class="text-primary font-weight-bold">ID: ${data.id}</small>
                        <h6 class="mb-0 font-weight-bold text-dark" style="font-size: 13.5px;">${data.text}</h6>
                    </div>
                    <button type="button" class="btn btn-sm text-danger btnRemoveCart p-0">
                        <i class="fa fa-times-circle fa-lg"></i>
                    </button>
                </div>
                <div class="row no-gutters mt-2">
                    <div class="col-4">
                        <input type="number" name="items[${index}][qty]" class="form-control qty-input text-center" value="1" step="0.01" inputmode="decimal">
                    </div>
                    <div class="col-8 pl-2">
                        <input type="text" name="items[${index}][note]" class="form-control border-0 bg-light" style="height: 38px; border-radius: 8px; font-size: 12px;" placeholder="Catatan...">
                    </div>
                </div>
            </div>`;

        $cartItems.prepend(itemHtml);
        $quickAdd.val(null).trigger('change');
        updateState();
        // Select2 akan menutup otomatis (default), user bisa fokus ke input QTY.
    });

    $(document).on('click', '.btnRemoveCart', function () {
        $(this).closest('.cart-item').remove();
        updateState();
    });

    function updateState() {
        const count = $('.cart-item').length;
        $('#count-items').text(count);
        if(count > 0) $('#submit-section').fadeIn(); else $('#submit-section').fadeOut();
    }
});
</script>