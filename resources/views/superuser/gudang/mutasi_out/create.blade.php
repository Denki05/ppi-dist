@extends('superuser.gudang.mutasi_out.layouts._wrapper')

@section('page-header')
<h5 class="fw-semibold mb-3">Buat Mutasi Gudang</h5>
@endsection

@section('page-content')
<form method="POST"
      action="{{ route('superuser.gudang.mutasi_out.store') }}">
    @csrf

    {{-- HIDDEN HEADER --}}
    <input type="hidden" name="warehouse_from" value="{{ request('warehouse_from') }}">
    <input type="hidden" name="warehouse_to" value="{{ request('warehouse_to') }}">
    <input type="hidden" name="brand_name" value="{{ request('brand_name') }}">
    <input type="hidden" name="note" value="{{ request('note') }}">

    {{-- HEADER INFO (READONLY) --}}
    <!-- <div class="card mb-3">
        <div class="card-body">
            <h6 class="fw-semibold mb-2">Informasi Mutasi</h6>
            <div class="row g-2 small">
                <div class="col-md-6">
                    <strong>Gudang Asal:</strong>
                    {{ $warehouseFrom->name ?? '-' }}
                </div>
                <div class="col-md-6">
                    <strong>Gudang Tujuan:</strong>
                    {{ $warehouseTo->name ?? '-' }}
                </div>
                <div class="col-md-6">
                    <strong>Brand:</strong>
                    {{ $brandSelected ?? '-' }}
                </div>
                @if($note)
                <div class="col-12">
                    <strong>Catatan:</strong> {{ $note }}
                </div>
                @endif
            </div>
        </div>
    </div> -->

    {{-- DETAIL BARANG SAJA --}}
    @include('superuser.gudang.mutasi_out.partials._form_detail')


    <div class="text-end mt-3">
        <a href="{{ route('superuser.gudang.mutasi_out.index') }}"
           class="btn btn-secondary btn-sm">
            Batal
        </a>
        <button type="submit" class="btn btn-primary btn-sm" id="btn-submit">
            Simpan
        </button>
    </div>
</form>
@endsection

@include('superuser.asset.plugin.select2')

@push('scripts')
<script>
let rowIndex = 0;

$('#addRow').on('click', function () {
    let html = $('#rowTemplate').html()
        .replace(/__INDEX__/g, rowIndex++);

    $('#detailTable tbody').append(html);
    initProductSelect();
});

$(document).on('click', '.removeRow', function () {
    $(this).closest('tr').remove();
});

function initProductSelect() {
    $('.product-select').select2({
        placeholder: 'Cari Produk',
        width: '100%',
        ajax: {
            url: "{{ route('superuser.gudang.mutasi_out.search_sku') }}",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term,
                    warehouse: $('[name="warehouse_from"]').val(),
                    brand_name: $('[name="brand_name"]').val()
                };
            },
            processResults: function (data) {
                return data;
            }
        }
    }).on('select2:select', function (e) {
        $(this).closest('tr')
            .find('.stock-view')
            .val(e.params.data.stock || 0);
    });
}

$(document).on('submit', 'form', function (e) {
    e.preventDefault();

    let form = this;

    Swal.fire({
        title: 'Simpan Mutasi?',
        text: 'Pastikan data mutasi sudah benar.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Simpan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
});
</script>
@endpush
