@extends('superuser.app')

@section('content')
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

@if(session()->has('success'))
<div class="alert alert-success alert-dismissable" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
    <h3 class="alert-heading font-size-h4 font-w400">Success</h3>
    <p class="mb-0">{{ session()->get('success') }}</p>
</div>
@endif

<div class="block finance-tabs">
    <div class="block-content block-content-full">

        {{-- Tab Header --}}
        <main class="tab-header">
            <input type="radio" id="tab1" name="tabs" checked hidden>
            <label for="tab1" class="tab-label tab-list" data-tab="content1">Refund</label>
        </main>

        {{-- Tab Content: Refund --}}
        <div class="tab-content active" id="content1">
            <div class="row">
                {{-- Dropdown Code --}}
                <div class="col-md-2">
                    <select class="form-control js-select2" id="selectCode">
                        <option value="">Pilih Kode</option>
                        @foreach($retur as $p)
                            @if($p->fat_status == 2)
                                <option value="{{ $p->id }}"
                                        data-customer="{{ $p->customer->name ?? '' }}  {{ $p->customer->text_kota ?? '' }}"
                                        data-cost_refund="{{ $p->cost->purchase_total_idr ?? 0 }}"
                                        data-type="{{ $p->type() }}"
                                        data-status="{{ $p->payment_status() }}">
                                    {{ $p->code }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <form id="form_bukti_refund" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-2">
                            {{-- Left --}}
                            <div class="col-md-4">
                                <label for="customer_name" class="form-label">Customer</label>
                                <input type="text" class="form-control form-control-sm" id="customer_name" readonly>

                                <label for="refund_value" class="form-label fw-bold">Refund</label>
                                <input type="text" class="form-control form-control-sm text-end fw-bold" 
                                    id="refund_value" placeholder="Rp 0" readonly>
                            </div>

                            {{-- Right --}}
                            <div class="col-md-6">
                                <div class="upload-area">
                                    {{-- Ganti name jadi "bukti_refund" agar cocok dengan controller --}}
                                    <input type="file" id="bukti_refund" name="bukti_refund" accept=".png, .jpg, .jpeg"/>
                                    <input type="hidden" name="retur_id" id="retur_id">
                                    <label for="bukti_refund" class="upload-label">
                                        <div id="imagePreview" style="background-image: url('https://via.placeholder.com/250x150?text=Upload+Bukti');"></div>
                                        <div class="upload-text">
                                            <i class="fas fa-file-upload fa-2x"></i>
                                            <p class="mb-0">Upload Bukti</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                {{-- type harus "submit" agar form trigger --}}
                                <button type="submit" class="btn btn-primary w-100">Submit</button>
                            </div>  
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.select2')

@push('scripts')
<style>
/* CSS Anda sebelumnya */
.finance-tabs .tab-label::before {
    font-family: "Font Awesome 6 Free","Font Awesome 5 Free";
    font-weight: 900;
    margin-right: 6px;
}
.finance-tabs .tab-payment::before { content: "\f571"; }
.finance-tabs .tab-list::before { content: "\f03a"; }
.finance-tabs .tab-label {
    background-color: #f0f0f0;
    border: 1px solid #ddd;
    border-bottom: none;
    padding: 10px 20px;
    cursor: pointer;
    display: inline-block;
    border-radius: 5px 5px 0 0;
    margin-right: 5px;
    transition: background-color 0.3s ease;
    font-weight: bold;
    color: #555;
}
.finance-tabs .tab-label:hover { background-color: #e0e0e0; }
.finance-tabs .tab-label.active-tab-label {
    background-color: #fff;
    border-color: #ccc;
    border-bottom: 1px solid #fff;
    color: #000;
}
.finance-tabs .tab-content {
    display: none;
    border: 0px solid #ccc;
    padding: 20px;
    border-radius: 0 5px 5px 5px;
    background-color: #fff;
}
.finance-tabs .tab-content.active { display: block; }

#invoice_table {
    table-layout: fixed;
    width: 100% !important;
}
#invoice_table th, 
#invoice_table td {
    text-align: center;
    vertical-align: middle;
    white-space: nowrap;
}

#invoice_table tbody tr {
    cursor: pointer;
}
#invoice_table tbody tr.selected-row {
    background-color: #d1ecf1; /* biru muda */
}

.retur-row { cursor: pointer; }
.active-row { background-color: #fff3cd; }

.table-fixed-height {
    height: 270px;
    overflow-y: auto;
}
.custom-modal {
    max-width: 95% !important;   /* hampir full lebar */
    width: 95% !important;
    height: 95vh;               /* tinggi hampir penuh viewport */
}

.custom-modal .modal-content {
    height: 95vh;               /* isi modal penuh */
}

.modal-body {
    height: calc(100% - 100px); /* sisakan ruang untuk header+footer */
    overflow: hidden;           /* buang scrollbar */
}

.iframe-full {
    width: 100%;
    height: 100%;
    border: none;
}

.table-fixed-height {
    max-height: 400px; /* atur sesuai kebutuhan */
    overflow-y: auto;
}

.upload-area {
    position: relative;
    width: 100%;
    height: 100%;
    min-height: 150px;
    border: 2px dashed #ccc;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}
.upload-area:hover {
    border-color: #007bff;
    background-color: #f7f7f7;
}
.upload-area input[type="file"] {
    display: none;
}
.upload-area .upload-label {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
    cursor: pointer;
    position: relative; /* Tambahkan ini */
}
.upload-area .upload-text {
    text-align: center;
    color: #777;
    transition: opacity 0.3s ease;
}
.upload-area .upload-text p {
    font-weight: 500;
}
#imagePreview {
    display: none; /* Sembunyikan default */
    position: absolute; /* Posisikan di atas */
    width: 100%;
    height: 100%;
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    border-radius: 6px;
}
</style>

<script type="text/javascript">
$(document).ready(function(){
    $('.js-select2').select2();

    // Preview gambar
    $("#bukti_refund").on("change", function() {
        if (this.files && this.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview')
                    .css('background-image', 'url('+ e.target.result +')')
                    .show();
                $('.upload-text').hide();
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Select kode retur
    $('#selectCode').on('change', function() {
        let returId   = $(this).val();
        let customer  = $(this).find(':selected').data('customer');
        let costRefund = $(this).find(':selected').data('cost_refund') ?? 0;

        $('#customer_name').val(customer ?? '');
        $('#refund_value').val(
            parseFloat(costRefund).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' })
        );
        $('#retur_id').val(returId);
    });

    // Submit form upload refund
    $('#form_bukti_refund').on('submit', function(e) {
        e.preventDefault();

        let form = $(this);
        let formData = new FormData(form[0]);

        $.ajax({
            url: "{{ route('superuser.finance.nota_kredit_finance.upload_bukti_refund') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: { 
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') 
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: response.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Gagal!', response.message, 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error!', 'Terjadi kesalahan saat mengirim data. Silakan coba lagi.', 'error');
            }
        });
    });
});
</script>
@endpush