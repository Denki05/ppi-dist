@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Accounting</span>
    <span class="breadcrumb-item">Finance Simulation Price</span>
    <span class="breadcrumb-item active">Create</span>
</nav>

<div class="block">
    <div class="block-content">
        <h3>#Create Simulation Price</h3>
    </div>
    <div class="block-content block-content-full">
        <form id="simulation-form" action="{{ route('superuser.accounting.finance_simulation.store') }}" method="POST">
            @csrf
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="month">Bulan</label>
                    <select id="month" class="form-control js-select2" style="width: 100%;">
                        <option value="" selected disabled>Pilih Bulan</option>
                        @foreach($months as $month)
                            <option value="{{ $month['id'] }}">{{ $month['monthName'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Invoices</label>
                <div id="invoices-list">
                    <p>Select a month to view invoices.</p>
                </div>
                <table class="table table-bordered table-striped" id="invoice-table" style="display:none; width: 100%;">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="select-all">
                            </th>
                            <th>Invoice Code</th>
                            <th>Customer Name</th>
                            <th>City</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <!-- Hidden field to store selected invoices -->
            <input type="hidden" name="selected_invoices" id="selected-invoices">

            <div class="d-flex justify-content-between">
                <a href="{{ route('superuser.accounting.finance_simulation.index') }}" class="btn btn-danger">Back</a>
                <button type="submit" class="btn btn-success">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
$(document).ready(function () {
    // Initialize Select2
    $('.js-select2').select2();

    let selectedRows = new Set();  // Untuk menyimpan ID yang dipilih
    let table = $('#invoice-table').DataTable({
        processing: true,
        serverSide: false,
        paging: true,
        ordering: true,
        searching: true,
        order: [[1, 'asc']],
        pageLength: 10,
        lengthMenu: [[10, 20, 50, 100, 200], [10, 20, 50, 100, 200]],
        columns: [
            { data: 'select', orderable: false },
            { data: 'invoice_code' },
            { data: 'customer_name' },
            { data: 'customer_city' },
        ],
        rowCallback: function (row, data) {
            let checkbox = $(row).find('.row-checkbox');
            checkbox.prop('checked', selectedRows.has(data.do_id)); // Periksa apakah ID ada di selectedRows
        },
    });

    let currentMonth = null;

    // Ketika bulan dipilih, request invoice berdasarkan bulan
    $('#month').change(function () {
        let month = $(this).val();
        if (month !== currentMonth) {
            currentMonth = month;
            let url = "{{ route('superuser.accounting.finance_simulation.getInvoice') }}";

            $.ajax({
                url: url,
                type: "GET",
                data: { month: month },
                success: function (response) {
                    table.clear().draw();
                    if (response.length > 0) {
                        response.forEach(function (invoice) {
                            table.row.add({
                                select: `<input type="checkbox" class="row-checkbox" value="${invoice.do_id}">`,
                                invoice_code: invoice.invoice_code,
                                customer_name: invoice.customer_name,
                                customer_city: invoice.customer_city,
                            }).draw();
                        });
                        $('#invoices-list').empty();
                        $('#invoice-table').show();
                    } else {
                        $('#invoices-list').html('<p>No invoices found for the selected month.</p>');
                        $('#invoice-table').hide();
                    }
                },
                error: function () {
                    alert('Failed to fetch invoices. Please try again.');
                },
            });
        }
    });

    // Fungsi untuk memilih semua checkbox
    $('#select-all').on('click', function () {
        let isChecked = $(this).is(':checked');
        table.rows().every(function () {
            let data = this.data();
            if (isChecked) {
                selectedRows.add(data.do_id);  // Tambahkan ID invoice ke selectedRows
            } else {
                selectedRows.delete(data.do_id);  // Hapus ID invoice dari selectedRows
            }
        });
        updateSelectAllCheckbox();  // Perbarui status checkbox header
        updateSelectedInvoicesField();  // Update field selected_invoices
    });

    // Fungsi untuk memilih checkbox individu
    $('#invoice-table').on('change', '.row-checkbox', function () {
        let value = $(this).val();
        if ($(this).is(':checked')) {
            selectedRows.add(value);  // Tambahkan ID ke selectedRows
        } else {
            selectedRows.delete(value);  // Hapus ID dari selectedRows
        }
        updateSelectAllCheckbox();  // Perbarui status checkbox header
        updateSelectedInvoicesField();  // Update field selected_invoices
    });

    // Perbarui status checkbox header
    function updateSelectAllCheckbox() {
        let totalCheckboxes = $('.row-checkbox').length;
        let checkedCheckboxes = $('.row-checkbox:checked').length;
        $('#select-all').prop('checked', totalCheckboxes === checkedCheckboxes);
    }

    // Update field selected_invoices
    function updateSelectedInvoicesField() {
        $('#selected-invoices').val(Array.from(selectedRows));  // Kirimkan array langsung
    }

    // On form submit, pastikan selected invoices terisi
    $('#simulation-form').submit(function (e) {
        if (selectedRows.size === 0) {
            alert('Please select at least one invoice.');
            e.preventDefault(); // Mencegah pengiriman form jika tidak ada yang dipilih
        }
    });
});
</script>
@endpush