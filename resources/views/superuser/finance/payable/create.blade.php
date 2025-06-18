@extends('superuser.app')
@section('content')

<nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">FAT</span>
    <span class="breadcrumb-item">Finance</span>
    <span class="breadcrumb-item">Pembayaran</span>
    <span class="breadcrumb-item active">Create</span>
</nav>

@if(session('error') || session('success'))
<div class="alert alert-{{ session('error') ? 'danger' : 'success' }} alert-dismissible fade show" role="alert">
    <strong>{{ session('error') ? 'Error!' : 'Success!' }}</strong> {!! session('error') ?? session('success') !!}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<form id="frmPayable" method="post" action="{{ route('superuser.finance.payable.store') }}">
    @csrf

    <!-- Customer Details -->
    <div class="block">
        <div class="block-header block-header-default">
            <h3 class="block-title"># Pembayaran Nota</h3>
        </div>
        <div class="block-content block-content-full">
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <label>Account Customer</label>
                        <input type="text" class="form-control" value="{{ $customer->name }} {{ $customer->text_kota }}" readonly>
                        <input type="hidden" name="customer_id" id="customer_id" value="{{ $customer->id }}">
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label>Tanggal Bayar</label>
                        <input type="date" class="form-control" name="pay_date" required>
                    </div>
                </div>
                <div class="col">
                    <div class="form-group">
                        <label>Keterangan</label>
                        <input type="text" class="form-control" name="note" maxlength="255">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice Details -->
    <div class="block">
        <div class="block-content block-content-full">
            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="datatables">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nota</th>
                            <th>Total Nota</th>
                            <th>Total Terbayar</th>
                            <th>Sisa Bayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customer->do as $index => $row)
                            @if($row->invoicing && ($remaining = $row->invoicing->grand_total_idr - $row->invoicing->payable_detail->sum('total')) > 0)
                                <tr>
                                    <input type="hidden" name="repeater[{{ $index }}][invoice_id]" value="{{ $row->invoicing->id }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $row->invoicing->code }}</td>
                                    <td><input type="text" class="form-control total_nota" value="{{ number_format($remaining, 0, ',', '.') }}" readonly></td>
                                    <td><input type="text" name="repeater[{{ $index }}][payable]" class="form-control formatRupiah total_payment"></td>
                                    <td><input type="text" class="form-control formatRupiah count_sisa" readonly></td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Data tidak ditemukan</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right">TOTAL</td>
                            <td><input type="text" class="form-control total" readonly></td>
                            <td><input type="text" class="form-control sisa_bayar" readonly></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="mt-3">
                <a href="{{ route('superuser.finance.payable.index') }}" class="btn btn-warning">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i> Save
                </button>
            </div>
        </div>
    </div>
</form>

@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
    $(document).ready(() => {
        // Initialize DataTables
        $('#datatables').DataTable({
            paging: false,
            searching: false
        });

        // Format Rupiah (Currency) on keyup
        $('.formatRupiah').on('keyup', function () {
            $(this).val(formatRupiah($(this).val()));
        });

        // Recalculate totals when user enters payment
        $('.total_payment').on('keyup', calculateTotals);   

        function calculateTotals() {
            let total = 0;
            let totalSisa = 0;

            // Iterate through each row to calculate totals
            $('#datatables tbody tr').each(function () {
                let $row = $(this);
                let nota = parseRupiah($row.find('.total_nota').val()) || 0;
                let pay = parseRupiah($row.find('.total_payment').val()) || 0;
                let sisa = nota - pay;

                $row.find('.count_sisa').val(formatRupiah(sisa));

                total += pay;
                totalSisa += sisa;
            });

            $('.total').val(formatRupiah(total));
            $('.sisa_bayar').val(formatRupiah(totalSisa));
        }

        // Handle form submission with confirmation
        $('#frmPayable').on('submit', function (e) {
            e.preventDefault();
            if (confirm("Apakah anda yakin ingin melakukan pembayaran ini?")) {
                submitForm($(this));
            }
        });

        // Submit the form via AJAX
        function submitForm(form) {
            let formData = form.serialize();
            let submitButton = form.find('button[type="submit"]');
            submitButton.prop('disabled', true);

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.Message
                    }).then(() => window.location.href = response.redirectUrl || window.location.href);
                },
                error: function (xhr) {
                    Swal.fire('Error', xhr.responseJSON.Message || 'Something went wrong', 'error');
                },
                complete: function () {
                    submitButton.prop('disabled', false);
                }
            });
        }

        // Helper function to parse Rupiah (remove dots and convert to number)
        function parseRupiah(value) {
            return parseFloat(value.replace(/\./g, '')) || 0;
        }

        // Helper function to format number into Rupiah format (with commas)
        function formatRupiah(value) {
            let number_string = value.toString().replace(/[^,\d]/g, '');
            let split = number_string.split(',');
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/g);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            return split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
        }
    });
</script>
@endpush