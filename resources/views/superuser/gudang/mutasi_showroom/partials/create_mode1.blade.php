<style>
    .item-card { border: 1px solid #edf2f9; border-radius: 12px !important; background: #fff; margin-bottom: 10px; }
    .qty-input { background: #f1f4f9 !important; border: 1px solid #dce3f1 !important; font-weight: 800 !important; border-radius: 8px !important; height: 38px !important; }

    /* Custom Select2 Minimalis */
    .select2-container--bootstrap4 .select2-selection--single {
        height: 38px !important;
        border-radius: 8px !important;
        background-color: #f8fafc !important;
        border: 1px solid #dce3f1 !important;
    }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        font-size: 13px;
        font-weight: 600;
    }

    .btn-action-sm { border-radius: 10px; font-weight: 700; font-size: 13px; padding: 10px; }
    .form-control-compact { height: 38px !important; border-radius: 8px !important; font-size: 13px !important; }
    
    /* Chrome, Safari, Edge, Opera - Remove arrows from number input */
    input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
</style>

<div class="container-fluid p-2 text-left">
    <div class="card border-0 shadow-sm mb-3" style="border-radius: 16px;">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center" data-toggle="collapse" href="#collapseStep1" role="button" id="headerStep1">
            <div class="text-truncate mr-2">
                <span class="badge badge-primary mr-1">1</span>
                <small id="summary-info" class="text-dark font-weight-bold">
                    {{ $kode }} - {{ date('d/m/Y') }} | <span class="text-muted">Pilih Tipe...</span>
                </small>
            </div>
            <i class="fa fa-chevron-down text-muted small" id="icon-step1"></i>
        </div>
        
        <div class="collapse show" id="collapseStep1">
            <div class="card-body p-3">
                <div class="form-row align-items-center">
                    <div class="col-4">
                        <input type="text" class="form-control form-control-compact bg-light border-0 font-weight-bold text-primary" value="{{ $kode }}" readonly placeholder="Kode">
                    </div>
                    <div class="col-4">
                        <input type="date" name="tanggal" class="form-control form-control-compact border-light" value="{{ date('Y-m-d') }}" disabled>
                    </div>
                    <div class="col-4">
                        <select name="type" id="type_selector_mode1" class="form-control select2-generic">
                            <option value="">-- TIPE --</option>
                            @foreach($types as $key => $value)
                                <option value="{{ $value }}">{{ $key }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-3 d-flex justify-content-end">
                    <button type="button" class="btn btn-link text-danger btn-sm p-0 font-weight-bold" id="btnBackToList">
                        <i class="fa fa-times-circle mr-1"></i> Batalkan Proses
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="product-section" style="display:none;">
        <div class="card border-0 shadow-sm mb-3" style="border-radius: 16px; border: 2px solid #4e73df !important;">
            <div class="card-body p-2">
                <select name="brand_name" id="brand_selector_mode1" class="form-control select2-brand">
                    <option value="">-- PILIH BRAND DULU --</option>
                    @foreach($brands as $b)
                        <option value="{{ $b->brand_name }}">{{ $b->brand_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div id="list-container" style="display:none;">
            <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                <h6 class="font-weight-bold text-dark mb-0 small text-uppercase">Daftar Barang</h6>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" id="btnAddRow">
                    <i class="fa fa-plus mr-1"></i> Tambah Item
                </button>
            </div>

            <div id="card-container"></div>

            <div class="row no-gutters mt-4 mb-5">
                <div class="col-2 pr-1">
                    <button type="button" class="btn btn-light btn-block btn-action-sm text-muted border shadow-sm" id="btnToggleStep1">
                        <i class="fa fa-info-circle mr-1"></i> <span id="textToggle">Info</span>
                    </button>
                </div>
                <div class="col-2 pl-1">
                    <button type="submit" class="btn btn-success btn-block btn-action-sm shadow">
                        <i class="fa fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    const $productSection = $('#product-section');
    const $listContainer = $('#list-container');
    const $cardContainer = $('#card-container');
    const $collapse1 = $('#collapseStep1');
    const $brandSelector = $('#brand_selector_mode1');
    const $typeSelector = $('#type_selector_mode1');
    const $textToggle = $('#textToggle');
    
    let lastBrand = "";

    // Init Select2
    $('.select2-brand, .select2-generic').select2({ theme: 'bootstrap4', width: '100%' });

    function initSelectProduct(el) {
        $(el).select2({
            theme: 'bootstrap4',
            placeholder: 'Cari Produk...',
            ajax: {
                url: '{{ route("superuser.gudang.mutasi_showroom.get_product_pack") }}',
                data: params => ({ id: params.term, brand_name: $brandSelector.val() }),
                processResults: res => res
            }
        });
    }

    // Alur: Pilih Tipe -> Munculkan Pemilihan Brand
    $typeSelector.on('change', function() {
        const val = $(this).val();
        if(val) {
            const typeText = $(this).find('option:selected').text();
            $('#summary-info').html(`{{ $kode }} - {{ date('d/m/Y') }} | <span class="text-primary">${typeText}</span>`);
            $productSection.fadeIn();
            $collapse1.collapse('hide');
        }
    });

    // Alur: Pilih Brand -> Munculkan List Produk
    $brandSelector.on('change', function() {
        const val = $(this).val();
        if(val) {
            if(lastBrand !== "" && lastBrand !== val && $('.item-card').length > 0) {
                Swal.fire({
                    title: 'Ganti Brand?',
                    text: "Item terpilih akan direset.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Reset'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $cardContainer.empty();
                        lastBrand = val;
                        showList();
                    } else {
                        $brandSelector.val(lastBrand).trigger('change.select2');
                    }
                });
            } else {
                lastBrand = val;
                showList();
            }
        }
    });

    function showList() {
        $listContainer.fadeIn();
        if($('.item-card').length === 0) $('#btnAddRow').click();
    }

    $('#btnAddRow').on('click', function () {
        const index = $('.item-card').length;
        const cardHtml = `
            <div class="item-card animated fadeIn p-2">
                <div class="row no-gutters align-items-center mb-1">
                    <div class="col-8 pr-1">
                         <select name="items[${index}][product_id]" class="form-control select-product-mobile"></select>
                    </div>
                    <div class="col-3">
                        <input type="number" step="0.01" name="items[${index}][qty]" class="form-control qty-input text-center" placeholder="QTY" inputmode="decimal">
                    </div>
                    <div class="col-1 text-right">
                        <button type="button" class="btn btn-link text-danger btnRemoveRow p-0"><i class="fa fa-times-circle fa-lg"></i></button>
                    </div>
                </div>
            </div>`;
        $cardContainer.append(cardHtml);
        initSelectProduct($cardContainer.find('.select-product-mobile').last());
    });

    $(document).on('click', '.btnRemoveRow', function () {
        if ($('.item-card').length > 1) $(this).closest('.item-card').remove();
        else Swal.fire('Info', 'Minimal 1 barang', 'info');
    });

    $('#btnToggleStep1').on('click', () => $collapse1.collapse('toggle'));
    $collapse1.on('shown.bs.collapse', () => $textToggle.text('Tutup'));
    $collapse1.on('hidden.bs.collapse', () => $textToggle.text('Info'));

    $(document).on('click', '#btnBackToList', function () {
        exitLockedMode();
        loadFrameB('{{ route("superuser.gudang.mutasi_showroom.list_partial") }}');
    });
});
</script>