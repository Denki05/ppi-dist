@extends('superuser.app')

{{-- ================= CUSTOM STYLE ================= --}}
@push('styles')
<style>
    /* rapatkan breadcrumb */
    .breadcrumb.push {
        margin-bottom: 6px !important;
    }

    /* rapatkan block */
    .block {
        margin-top: 0 !important;
    }

    .block-content {
        padding-top: 6px !important;
    }

    /* modern tabs */
    .nav-tabs {
        border-bottom: none;
        margin-bottom: 8px;
    }

    .nav-tabs .nav-link {
        border: none;
        border-radius: 6px 6px 0 0;
        padding: 8px 16px;
        font-weight: 500;
        background: #f1f3f5;
        color: #6c757d;
        margin-right: 6px;
    }

    .nav-tabs .nav-link.active {
        background: #5c80d1;
        color: #fff;
        box-shadow: 0 2px 6px rgba(0,0,0,.15);
    }

    .tab-content {
        background: #fff;
        padding: 10px;
        border-radius: 0 6px 6px 6px;
    }
</style>
@endpush

@section('content')
<!-- <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Gudang</span>
    <span class="breadcrumb-item active">Receiving - Komplain</span>
</nav> -->

<div class="block">
    <div class="block-content pt-0">

        {{-- ================= TAB NAV ================= --}}
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#tab-input" role="tab">
                    Input
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tab-list" role="tab">
                    List
                </a>
            </li>
        </ul>

        <div class="tab-content">

            {{-- ================================================= --}}
            {{-- TAB INPUT --}}
            {{-- ================================================= --}}
            <div class="tab-pane fade show active" id="tab-input" role="tabpanel">
                <form method="POST"
                    action="{{ route('superuser.gudang.quality_control_2.store') }}">
                  @csrf

                    <div class="row">

                        {{-- ============ FRAME A ============ --}}
                        <div class="col-md-3">
                            <div class="card h-100">
                                <div class="card-header py-2">
                                    <strong>Informasi</strong>
                                </div>
                                <div class="card-body">

                                    <div class="form-group">
                                        <input type="text" name="code" class="form-control" Readonly value="{{ $code }}">
                                    </div>

                                    <div class="form-group">
                                        <select name="warehouse_id" class="form-control js-select2" required>
                                            <option value="">Pilih Gudang</option>
                                            @foreach($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}">
                                                    {{ $warehouse->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <select name="customer_id" class="form-control js-select2" required>
                                            <option value="">Pilih Customer</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->customer_id }}">
                                                    {{ $customer->full_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <input type="date" name="date" class="form-control"
                                               value="{{ date('Y-m-d') }}">
                                    </div>

                                    <div class="form-group">
                                        <textarea name="note" rows="2" class="form-control" placeholder="Catatan"></textarea>
                                    </div>

                                </div>
                            </div>
                        </div>

                        {{-- ============ FRAME B ============ --}}
                        <div class="col-md-9">
                            <div class="card h-100">
                                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                                    <strong>Detail Barang Komplain</strong>
                                    <button type="button" class="btn btn-sm btn-primary" id="btn-add-row">
                                        <i class="fa fa-plus"></i> Add
                                    </button>
                                </div>

                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm" id="table-items">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="25%">Brand</th>
                                                    <th width="30%">Product</th>
                                                    <th width="15%">Qty</th>
                                                    <th width="10%"></th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>

                                    <div class="text-right mt-2">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fa fa-save"></i> Simpan
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </form>
            </div>

            {{-- ================================================= --}}
            {{-- TAB LIST --}}
            {{-- ================================================= --}}
            <div class="tab-pane fade" id="tab-list" role="tabpanel">
                <div class="card">
                    <div class="card-body p-2">
                        <table class="table table-bordered table-sm" id="table-list">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Code</th>
                                    <th>Tanggal</th>
                                    <th>Customer</th>
                                    <th>Warehouse</th>
                                    <th>Qty</th>
                                    <th>Status</th>
                                    <th width="15%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($komplain as $qc)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $qc->code }}</td>
                                    <td>{{ date('d M Y', strtotime($qc->tanggal)) }}</td>
                                    <td>{{ $qc->customer->name }} {{ $qc->customer->text_kota }}</td>
                                    <td>{{ $qc->warehouse->name }}</td>
                                    <td class="text-center">{{ $qc->details->sum('qty') }}</td>
                                    <td>{{ $qc->status() }}</td>
                                    
                                    <td>
                                        @if( $qc->status == 1 )
                                        <a href="javascript:saveConfirmation('{{ route('superuser.gudang.quality_control_2.acc', $qc->id) }}')">
                                            <button type="button" class="btn btn-sm btn-circle btn-alt-secondary" title="Approve">
                                                <i class="fa fa-check"></i>
                                            </button>
                                        </a>
                                        @endif

                                        @if( $qc->status == 2 )
                                        <a href="{{ route('superuser.gudang.quality_control_2.pdf_sj_komplain', ['data' => $qc->id, 'protect' => 'no']) }}" target="_blank">
                                            <button type="button" class="btn btn-sm btn-circle btn-alt-secondary" title="Surat Jalan">
                                                <i class="fa fa-file-pdf"></i>
                                            </button>
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ================= TEMPLATE ROW ================= --}}
<script type="text/template" id="row-template">
<tr>
    <td>
        <select class="form-control select2 brand-select" name="items[__index__][brand_name]">
            <option value="">Pilih Brand</option>
        </select>
    </td>
    <td>
        <select class="form-control select2 product-select" name="items[__index__][product_id]">
            <option value="">Pilih Product</option>
        </select>
</td>
    <td>
        <input type="number" step="0.01" name="items[__index__][qty]" class="form-control" required>
    </td>
    <td class="text-center">
        <button type="button" class="btn btn-sm btn-danger btn-remove">
            <i class="fa fa-trash"></i>
        </button>
    </td>
</tr>
</script>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')

{{-- ================= SCRIPT ================= --}}
@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
$(function () {

    function saveConfirmation(url, message) {
        Swal.fire({
            title: 'Konfirmasi',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, lanjutkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    beforeSend: function () {
                        Swal.fire({
                            title: 'Proses...',
                            text: 'Mohon tunggu',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function (res) {
                        if (res.status === true) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: res.message,
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload(); // 🔄 RELOAD HALAMAN
                            });
                        } else {
                            Swal.fire('Gagal', res.message, 'error');
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                    }
                });

            }
        });
    }

    function initSelect2(element = null) {
        let target = element ? element.find('.select2') : $('.select2');

        target.each(function () {
            if (!$(this).hasClass('select2-hidden-accessible')) {
                $(this).select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    allowClear: true,
                    minimumResultsForSearch: 0   // <-- PAKSA SEARCH MUNCUL
                });
            }
        });
    }

    $('.js-select2').select2()

    $('#table-list').DataTable({});

    let rowIndex = 0;

    // Add row
    $('#btn-add-row').click(function () {

        let template = $('#row-template').html();
        template = template.replace(/__index__/g, rowIndex);

        $('#table-items tbody').append(template);

        let row = $('#table-items tbody tr:last');

        loadBrands(row);
        initSelect2(row);

        rowIndex++;
    });


    // Remove row
    $(document).on('click', '.btn-remove', function () {
        $(this).closest('tr').remove();
    });

    // Load brands
    function loadBrands(row) {
        $.get("{{ route('superuser.gudang.quality_control_2.brands') }}", function (res) {
            let select = row.find('.brand-select');
            select.empty().append('<option value="">Pilih Brand</option>');
            $.each(res, function (i, v) {
                select.append(`<option value="${v.brand_name}">${v.brand_name}</option>`);
            });
        });
    }

    // Brand change → Product
    $(document).on('change', '.brand-select', function () {
        let brand = $(this).val();
        let row = $(this).closest('tr');

        row.find('.product-select').empty().append('<option>Loading...</option>');
        row.find('.pack-select').empty().append('<option value="">Pilih Kemasan</option>');

        $.get("{{ route('superuser.gudang.quality_control_2.products') }}", { brand_name: brand }, function (res) {
            let product = row.find('.product-select');
            product.empty().append('<option value="">Pilih Product</option>');
            $.each(res, function (i, v) {
                product.append(`<option value="${v.id}">${v.code} - ${v.name} / ${v.packaging_name}</option>`);
            });
        });
    });
});
</script>
@endpush