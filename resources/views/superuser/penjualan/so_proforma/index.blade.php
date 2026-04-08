@extends('superuser.app')

@section('content')
<div class="crm-wrapper">

    <div class="card">
        <div class="card-body">

            {{-- TAB HEADER --}}
            <div class="workflow-tabs">

                <button class="menu-tab active workflow-tab" data-target="tab-aktif">
                    Aktif <span class="badge bg-light text-dark">{{ $count_aktif }}</span>
                </button>

                <button class="menu-tab workflow-tab" data-target="tab-terbuat">
                    Terbuat <span class="badge bg-light text-dark">{{ $count_terbuat }}</span>
                </button>

                <button class="menu-tab workflow-tab" data-target="tab-siap">
                    Siap <span class="badge bg-light text-dark">{{ $count_siap }}</span>
                </button>

                <button class="menu-tab workflow-tab" data-target="tab-tutup">
                    Tutup <span class="badge bg-light text-dark">{{ $count_tutup }}</span>
                </button>

            </div>

            <hr>

            {{-- TAB CONTENT --}}
            <div id="tab-aktif" class="workflow-content">
                @include('superuser.penjualan.so_proforma.tab_aktif')
            </div>

            <div id="tab-terbuat" class="workflow-content d-none">
                @include('superuser.penjualan.so_proforma.tab_terbuat')
            </div>

            <div id="tab-siap" class="workflow-content d-none">
                @include('superuser.penjualan.so_proforma.tab_siap')
            </div>

            <div id="tab-tutup" class="workflow-content d-none">
                @include('superuser.penjualan.so_proforma.tab_tutup')
            </div>

        </div>
    </div>

</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')

<style>
.crm-wrapper{
    max-width:1100px;
    margin:auto;
}

.workflow-tabs{
    display:flex;
    gap:10px;
    margin-bottom:10px;
}

.menu-tab{
    border:1px solid #dce1e7;
    background:#fff;
    padding:8px 16px;
    border-radius:20px;
    font-weight:500;
}

.menu-tab.active{
    background:#4c6ef5;
    color:white;
}

.menu-tab .badge{
    margin-left:6px;
}

.workflow-tabs .list-group-item{
  font-weight:500;
  border-radius:6px;
  margin-bottom:5px;
}

.workflow-tabs .list-group-item.active{
  background:#4c6ef5;
  border-color:#4c6ef5;
  color:white;
}

.crm-wrapper{
    max-width:1100px;
    margin:auto;
    height:calc(100vh - 120px);
}

.crm-row{
    display:flex;
    gap:10px;
    height:100%;
}

.frame-a{
    flex:0 0 200px;
}

.frame-b{
    flex:1;
}

.menu-btn{
    width:100%;
    margin-bottom:8px;
    border-radius:10px;
    padding:10px;
    border:1px solid #dce1e7;
    background:#fff;
    font-weight:500;
    text-align:left;
}

.menu-btn.active{
    background:#4c6ef5;
    color:white;
}

.frame-b .card{
    border-radius:16px;
}

.frame-b .card-body{
    overflow-y:auto;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $('.datatable').DataTable({
        pageLength:25
    })

    $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    })

    $(document).on('click','.workflow-tab',function(){
        let target = $(this).data('target');

        $('.workflow-content').addClass('d-none');
        $('#'+target).removeClass('d-none');

        $('.workflow-tab').removeClass('active');
        $(this).addClass('active');
    });

    $(document).on('click', '.btn-status-siap', function() {

        let id = $(this).data('id');
        let button = $(this);

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Sales Order Proforma akan diupdate ke status Siap!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, update!',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    url: '/superuser/penjualan/so_proforma/statusSiap/' + id,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (res.success) {
                            Swal.fire(
                                'Berhasil!',
                                res.message,
                                'success'
                            );
                            // optional: reload datatable atau refresh tab
                            location.reload();
                        } else {
                            Swal.fire(
                                'Gagal!',
                                res.message,
                                'error'
                            );
                        }
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Gagal!',
                            xhr.responseJSON?.message || 'Terjadi kesalahan',
                            'error'
                        );
                    }
                });

            }
        });

    });

    $(document).on('click', '.btn-status-acc', function() {

        let id = $(this).data('id');
        let button = $(this);

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Sales Order Proforma akan diupdate ke status ACC!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, update!',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    url: '/superuser/penjualan/so_proforma/acc/' + id,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (res.success) {
                            Swal.fire(
                                'Berhasil!',
                                res.message,
                                'success'
                            );
                            // optional: reload datatable atau refresh tab
                            location.reload();
                        } else {
                            Swal.fire(
                                'Gagal!',
                                res.message,
                                'error'
                            );
                        }
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Gagal!',
                            xhr.responseJSON?.message || 'Terjadi kesalahan',
                            'error'
                        );
                    }
                });

            }
        });

    });

    $(document).on('click', '.btn-status-cancel', function() {

        let id = $(this).data('id');
        let button = $(this);

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Sales Order Proforma akan diupdate ke status Cancel!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, update!',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    url: '/superuser/penjualan/so_proforma/MultiCancel/' + id,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (res.success) {
                            Swal.fire(
                                'Berhasil!',
                                res.message,
                                'success'
                            );
                            // optional: reload datatable atau refresh tab
                            location.reload();
                        } else {
                            Swal.fire(
                                'Gagal!',
                                res.message,
                                'error'
                            );
                        }
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Gagal!',
                            xhr.responseJSON?.message || 'Terjadi kesalahan',
                            'error'
                        );
                    }
                });

            }
        });

    });

    $(document).on('click', '.btn-status-rollback', function() {

        let id = $(this).data('id');
        let button = $(this);

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Sales Order Proforma akan diupdate ke SO AWAL!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, update!',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    url: '/superuser/penjualan/so_proforma/rollbackProforma/' + id,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (res.success) {
                            Swal.fire(
                                'Berhasil!',
                                res.message,
                                'success'
                            );
                            // optional: reload datatable atau refresh tab
                            location.reload();
                        } else {
                            Swal.fire(
                                'Gagal!',
                                res.message,
                                'error'
                            );
                        }
                    },
                    error: function(xhr) {
                        Swal.fire(
                            'Gagal!',
                            xhr.responseJSON?.message || 'Terjadi kesalahan',
                            'error'
                        );
                    }
                });

            }
        });

    });

    $(document).on('click', '.btn-delete-proforma', function () {

        let id = $(this).data('id');

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data Proforma akan dihapus!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    url: '/superuser/penjualan/so_proforma/destroy/' + id,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {

                        Swal.fire(
                            'Berhasil!',
                            'Proforma berhasil dihapus.',
                            'success'
                        );

                        setTimeout(function(){
                            location.reload();
                        }, 1000);

                    },
                    error: function(xhr) {

                        let msg = 'Terjadi kesalahan';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }

                        Swal.fire('Gagal!', msg, 'error');
                    }
                });

            }

        });

    });
</script>
@endpush