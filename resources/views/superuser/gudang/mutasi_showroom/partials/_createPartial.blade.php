<style>
.select2-container--bootstrap4 .select2-selection { height: 38px; }
.select2-container--bootstrap4 .select2-selection__rendered { line-height: 36px; }
.select2-container--bootstrap4 .select2-selection__arrow { height: 36px; }
.select2-container { width: 100% !important; }
</style>

<form id="formCreateMutasi">
    @csrf

    {{-- =====================================================
        HEADER DATA (DIKIRIM DARI POPUP → HIDDEN INPUT)
        JANGAN TAMPILKAN DI UI
    ===================================================== --}}
    <input type="hidden" name="type">
    <input type="hidden" name="brand_name">
    <input type="hidden" name="gudang_id">
    <input type="hidden" name="vendor_id">
    <input type="hidden" name="customer_id">

    {{-- =====================================================
        PRODUCT SECTION
    ===================================================== --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0">Add Product</h6>
        <button type="button" class="btn btn-sm btn-success" id="btnAddRow">
            <i class="fa fa-plus"></i> Add Row
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-sm" id="tableProduct">
            <thead>
                <tr class="text-center">
                    <th width="40">#</th>
                    <th width="260">Product</th>
                    <th width="120">Qty</th>
                    <th width="80">Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">1</td>
                    <td>
                        <select name="items[0][product_id]"
                                class="form-control select-product"
                                data-index="0">
                            <option value="">-- Pilih Product --</option>
                        </select>
                    </td>
                    <td>
                        <input type="number"
                               step="0.01"
                               name="items[0][qty]"
                               class="form-control text-end"
                               placeholder="0.00">
                    </td>   
                    <td class="text-center">
                        <button type="button"
                                class="btn btn-sm btn-danger btnRemoveRow">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="text-end mt-3">
        <button type="button" class="btn btn-warning me-2" id="btnBackToList">
            Cancel
        </button>
        <button type="submit" class="btn btn-primary">
            Simpan
        </button>
    </div>
</form>

<script>
function backToMutasiList() {
    // PRIORITAS 1: fungsi global lama (jika ada)
    if (typeof loadFrameA === 'function') {
        loadFrameA();
        return;
    }

    // PRIORITAS 2: pola frame (jika ada)
    if (typeof uiState !== 'undefined') {
        uiState.mode = 'list';
    }

    // PRIORITAS 3: fallback load langsung
    $('#frameBContent').load(
        '{{ route("superuser.gudang.mutasi_showroom.list_partial") }}'
    );
}

(function () {

    let rowIndex = 0;

    /* =====================================================
        INIT PRODUCT SELECT2
    ===================================================== */
    function initProductSelect(element = null) {
        const $el = element ? $(element) : $('.select-product');

        $el.select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: '-- Pilih Product --',
            allowClear: true,
            minimumInputLength: 1,
            ajax: {
                url: '{{ route("superuser.gudang.mutasi_showroom.get_product_pack") }}',
                dataType: 'json',
                delay: 300,
                data: function (params) {
                    return {
                        id: params.term,
                        // Perbaikan di sini: ambil dari input hidden name="brand_name"
                        brand_name: $('input[name="brand_name"]').val() 
                    };
                },
                processResults: response => response
            }
        });
    }

    /* =====================================================
        ADD ROW
    ===================================================== */
    $(document)
        .off('click', '#btnAddRow')
        .on('click', '#btnAddRow', function () {

        // ❗ Guard: cegah double click cepat
        if ($(this).data('loading')) return;
        $(this).data('loading', true);

        const index = $('#tableProduct tbody tr').length;

        const row = `
        <tr>
            <td class="text-center">${index + 1}</td>

            <td>
                <select name="items[${index}][product_id]"
                        class="form-control select-product"
                        data-index="${index}">
                    <option value="">-- Pilih Product --</option>
                </select>
            </td>


            <td>
                <input type="number"
                       step="0.01"
                       name="items[${index}][qty]"
                       class="form-control text-right"
                       placeholder="0.00">
            </td>

            <td class="text-center">
                <button type="button"
                        class="btn btn-sm btn-danger btnRemoveRow">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        </tr>
        `;

        $('#tableProduct tbody').append(row);

        // init select2 hanya row terakhir
        initProductSelect(
            $('#tableProduct tbody tr:last .select-product')
        );

        // release lock
        setTimeout(() => {
            $('#btnAddRow').data('loading', false);
        }, 150);
    });

    /* =====================================================
        REMOVE ROW + REINDEX
    ===================================================== */
     $(document)
        .off('click', '.btnRemoveRow')
        .on('click', '.btnRemoveRow', function () {
    
            if ($('#tableProduct tbody tr').length === 1) {
                Swal.fire('Info', 'Minimal 1 product harus ada', 'info');
                return;
            }
    
            $(this).closest('tr').remove();
            reindexTable();
        });

    /* ================= DUPLICATE VALIDATION ================= */
    $(document)
        .off('select2:select', '.select-product')
        .on('select2:select', '.select-product', function (e) {
    
            const selectedId = e.params.data.id;
            const current    = this;
    
            let count = 0;
            $('.select-product').each(function () {
                if ($(this).val() == selectedId) count++;
            });
    
            if (count > 1) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Duplicate Product',
                    text: 'Product ini sudah dipilih di baris lain'
                });
    
                $(current).val(null).trigger('change');
                $(current).closest('tr').find('.kemasan').val('');
                return;
            }
    
            $(current)
                .closest('tr')
                .find('.kemasan')
                .val(e.params.data.packName ?? '');
        });

    // Setup CSRF untuk AJAX Laravel
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() }
    });    

    $(document).off('submit', '#formCreateMutasi').on('submit', '#formCreateMutasi', function (e) {
        e.preventDefault();
        
        // Simpan referensi form
        const formElement = this; 
        const $form = $(this);

        // Gunakan sintaks Swal.fire yang paling stabil
        Swal.fire({
            title: 'Simpan Mutasi Showroom?',
            text: "Pastikan data item sudah benar.",
            icon: 'question', // Jika error 'icon' muncul lagi, ganti baris ini menjadi --> type: 'question'
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            // Cek apakah user menekan tombol confirm
            if (result.isConfirmed || result.value) {
                
                // 1. Ambil data (termasuk yang disabled)
                const $disabledItems = $form.find(':disabled');
                $disabledItems.prop('disabled', false); // Aktifkan sementara
                const payload = $form.serialize();
                $disabledItems.prop('disabled', true); // Matikan kembali

                $.ajax({
                    url: '{{ route("superuser.gudang.mutasi_showroom.store") }}',
                    method: 'POST',
                    data: payload,
                    beforeSend: function() {
                        // Disable tombol submit agar tidak double post
                        $form.find('button[type="submit"]').prop('disabled', true);
                        
                        // Tampilkan loading yang tidak bisa ditutup user
                        Swal.fire({
                            title: 'Memproses...',
                            text: 'Sedang menyimpan data ke server',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            backToMutasiList();
                        });
                    },
                    error: function(xhr) {
                        $form.find('button[type="submit"]').prop('disabled', false);
                        
                        let errorMessage = 'Terjadi kesalahan saat menyimpan.';
                        
                        // Jika error validasi (422)
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            errorMessage = '';
                            $.each(errors, function(key, value) {
                                errorMessage += value[0] + '<br>'; 
                            });
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Simpan',
                            html: errorMessage
                        });
                    }
                });
            }
        });
    });

    /* =====================================================
        BACK BUTTON
    ===================================================== */
    $(document).off('click', '#btnBackToList').on('click', '#btnBackToList', function() {
        // Ambil filter yang ada di session global
        let stored = window.mutasiFilterStore.load();
        let url = '{{ route("superuser.gudang.mutasi_showroom.list_partial") }}';
        
        if(stored) {
            url += '?' + $.param({
                start_date: stored.start_date,
                end_date: stored.end_date,
                status: stored.status
            });
        }
        
        loadFrameB(url);
    });

    /* =====================================================
        INIT FIRST LOAD
    ===================================================== */
    initProductSelect();

})();
</script>