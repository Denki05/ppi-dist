@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">UV</span>
    <span class="breadcrumb-item active">Araya</span>
</nav>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
    <h3 class="alert-heading font-size-h4 font-w400">Error</h3>
    @foreach ($errors->all() as $error)
        <p class="mb-0">{{ $error }}</p>
    @endforeach
</div>
@endif

@if (session('status') && session('message'))
<div class="alert alert-{{ session('status') }} alert-dismissible fade show" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
    <h4 class="alert-heading">{{ ucfirst(session('status')) }}</h4>
    <p>{{ session('message') }}</p>
</div>
@endif

<div id="alert-block"></div>

<div id="loading-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; text-align: center;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 5px;">
        <i class="fa fa-spinner fa-spin" style="font-size: 24px;"></i> Processing...
    </div>
</div>

<div class="block">
    <div class="block-content block-content-full">
        <div class="row mb-3">
            <div class="col-lg-3 col-md-6">
                <label>Bulan</label>
                <select class="form-control js-select2" name="month" id="month">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" 
                            {{ $month == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 col-md-6">
                <label>Tahun</label>
                <select class="form-control js-select2" name="year" id="year">
                    @for ($i = date('Y'); $i >= date('Y') - 10; $i--)
                        <option value="{{ $i }}" {{ $year == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-lg-3 col-md-6 d-flex align-items-end">
                <button type="button" id="btn-search" class="btn btn-primary mr-2">Cari</button>

                {{--<a href="#" id="btn-uv" class="btn bg-gd-sea border-0 text-white mr-2">
                    Generate <i class="fa fa-sync ml-10"></i>
                </a>

                <a href="#" id="btn-remove" class="btn btn-danger">
                    Remove <i class="fa fa-trash ml-10"></i>
                </a>--}}
            </div>
        </div>
    </div>
</div>

<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="new-tab" data-toggle="tab" href="#content1" role="tab">New</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="done-tab" data-toggle="tab" href="#content2" role="tab">Done</a>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="content1" role="tabpanel">
        <table class="table table-hover table-striped" id="araya_new">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Kode</th>
                    <th>Customer</th>
                    <th>Transaksi</th>>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($list as $item)
                    @if($item->status_uv_araya == 0)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->invoice_code }}</td>
                        <td>{{ $item->customer_name }} {{ $item->customer_city }}</td>
                        <td>{{ $item->invoice_type }}</td>
                        <td>
                            <a href="{{ route('superuser.accounting.finance_simulation.create_araya', $item->id) }}" class="btn btn-primary btn-sm">Proses</a>
                        </td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="tab-pane fade" id="content2" role="tabpanel">
        <table class="table table-hover table-striped" id="araya_done">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Code</th>
                    <th>Customer</th>
                    <th>Transaksi</th>
                    <th>Cashback</th>
                    <th>Print</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($list as $key)
                    @if($key->status_uv_araya == 1)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $key->cashback_code }}</td>
                        <td>{{ $key->customer_name }} {{ $key->customer_city }}</td>
                        <td>{{ $key->invoice_type }}</td>
                        <td>
                            Rp {{ number_format($key->total_idr - $key->total_cashback_idr, 2, ',', '.') }}
                        </td>
                        <td>
                            <a class="btn btn-primary btn-sm" href="{{ route('superuser.accounting.finance_simulation.print_jual', $key->invoice_uv_id) }}" role="button" target="_blank"><i class="fa fa-print"></i></a>
                            <a class="btn btn-success btn-sm" href="{{ route('superuser.accounting.finance_simulation.print_beli', $key->invoice_uv_id) }}" role="button" target="_blank"><i class="fa fa-print"></i></a>
                        </td>
                        <td>
                            <a class="btn btn-info btn-sm" href="{{ route('superuser.accounting.finance_simulation.show_araya', $key->invoice_uv_id) }}" role="button"><i class="fa fa-eye"></i></a>
                            <button type="button" class="btn btn-danger btn-sm btn-delete" data-id="{{ $key->invoice_uv_id }}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.datatables-button')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('.js-select2').select2();

        $('#araya_new').DataTable( {
            paging:   true,
            orderin: true,
            info:     false,
            searching : true,
            order: [
                [1, 'asc'],
            ],
            pageLength: 5,
            lengthMenu: [
                [10, 30, 100, -1],
                [10, 30, 100, 'All']
            ], 
        });

        $('#araya_done').DataTable( {
            paging:   true,
            orderin: true,
            info:     false,
            searching : true,
            order: [
                [1, 'asc'],
            ],
            pageLength: 5,
            lengthMenu: [
                [10, 30, 100, -1],
                [10, 30, 100, 'All']
            ], 
        });

        $('#btn-search').click(function () {
            var month = $('#month').val();
            var year = $('#year').val();
            window.location.href = "{{ route('superuser.accounting.finance_simulation.index_araya') }}?month=" + month + "&year=" + year;
        });

        $('.btn-delete').click(function () {
            let id = $(this).data('id'); // Ambil ID dari tombol delete
            Swal.fire({
                title: "Apakah Anda yakin?",
                text: "Data yang dihapus tidak bisa dikembalikan!",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.value) { // Untuk versi lama gunakan `result.value`
                    $.ajax({
                        url: "{{ route('superuser.accounting.finance_simulation.destroy_araya', '') }}" + id, // Sesuaikan dengan route
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}" // Kirim CSRF Token untuk keamanan
                        },
                        success: function(response) {
                            Swal.fire({
                                title: "Berhasil!",
                                text: "Data telah dihapus.",
                                type: "success"
                            }).then(() => {
                                location.reload(); // Reload halaman setelah sukses
                            });
                        },
                        error: function(xhr) {
                            Swal.fire("Gagal!", "Terjadi kesalahan, coba lagi.", "error");
                        }
                    });
                }
            });
        });

        $('#btn-uv').on('click', function(e) {
            e.preventDefault();

            // Tampilkan loading overlay
            $('#loading-overlay').fadeIn();

            $.ajax({
                url: "{{ route('superuser.accounting.finance_simulation.generate_last_year') }}",
                contentType: false,
                cache: false,
                processData: false,
                type: "GET",
                beforeSend: function() {
                    Codebase.layout('header_loader_on'); // Jika Codebase digunakan
                },
                complete: function() {
                    // Sembunyikan loading overlay setelah proses selesai
                    $('#loading-overlay').fadeOut();
                    Codebase.layout('header_loader_off');
                }
            }).done(function(response) {
                if (objHasProp(response, 'data.notification')) {
                    responded(response.data.notification);
                }

                if (objHasProp(response, 'data.redirect_to')) {
                    redirect(response.data.redirect_to, 3000);
                }

                $('#btn-filter').trigger('click');
            }).fail(function(request, status, error) {
                $('#loading-overlay').fadeOut(); // Pastikan loading dihilangkan juga jika request gagal

                if (objHasProp(request, 'responseJSON.data.notification')) {
                    responded(request.responseJSON.data.notification);
                }

                if (objHasProp(request, 'responseJSON.data.redirect_to')) {
                    redirect(request.responseJSON.data.redirect_to, 3000);
                }

                if (objHasProp(request, 'status') && request.status == 429) {
                    Codebase.helpers('notify', {
                        align: 'right',
                        from: 'top',
                        type: 'warning',
                        icon: 'fa fa-ban mr-5',
                        message: "Too many attempts, try again later"
                    });
                }
            });
        });

        $('#btn-remove').on('click', function(e) {
            e.preventDefault();

            // Tampilkan loading overlay
            $('#loading-overlay').fadeIn();

            $.ajax({
                url: "{{ route('superuser.accounting.finance_simulation.delete_data') }}",
                contentType: false,
                cache: false,
                processData: false,
                type: "GET",
                beforeSend: function() {
                    Codebase.layout('header_loader_on'); // Jika Codebase digunakan
                },
                complete: function() {
                    // Sembunyikan loading overlay setelah proses selesai
                    $('#loading-overlay').fadeOut();
                    Codebase.layout('header_loader_off');
                }
            }).done(function(response) {
                if (objHasProp(response, 'data.notification')) {
                    responded(response.data.notification);
                }

                if (objHasProp(response, 'data.redirect_to')) {
                    redirect(response.data.redirect_to, 3000);
                }

                $('#btn-filter').trigger('click');
            }).fail(function(request, status, error) {
                $('#loading-overlay').fadeOut(); // Pastikan loading dihilangkan juga jika request gagal

                if (objHasProp(request, 'responseJSON.data.notification')) {
                    responded(request.responseJSON.data.notification);
                }

                if (objHasProp(request, 'responseJSON.data.redirect_to')) {
                    redirect(request.responseJSON.data.redirect_to, 3000);
                }

                if (objHasProp(request, 'status') && request.status == 429) {
                    Codebase.helpers('notify', {
                        align: 'right',
                        from: 'top',
                        type: 'warning',
                        icon: 'fa fa-ban mr-5',
                        message: "Too many attempts, try again later"
                    });
                }
            });
        });
    });
</script>
@endpush