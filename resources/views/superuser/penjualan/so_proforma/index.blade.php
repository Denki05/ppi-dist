@extends('superuser.app')

@section('content')
<div class="crm-wrapper">

    <div class="card">
        <div class="card-body">

            {{-- TAB HEADER --}}
            <div class="workflow-tabs" role="tablist" aria-label="Tahapan proforma">

                <button class="menu-tab active workflow-tab" data-target="tab-aktif" role="tab" aria-label="Proforma aktif">
                    Aktif <span class="badge bg-light text-dark">{{ $count_aktif }}</span>
                </button>

                <button class="menu-tab workflow-tab" data-target="tab-terbuat" role="tab" aria-label="Proforma terbuat">
                    Terbuat <span class="badge bg-light text-dark">{{ $count_terbuat }}</span>
                </button>

                <button class="menu-tab workflow-tab" data-target="tab-siap" role="tab" aria-label="Proforma siap ACC">
                    Siap <span class="badge bg-light text-dark">{{ $count_siap }}</span>
                </button>

                <button class="menu-tab workflow-tab" data-target="tab-tutup" role="tab" aria-label="Proforma tutup">
                    Tutup <span class="badge bg-light text-dark">{{ $count_tutup }}</span>
                </button>

            </div>
            <small class="text-muted d-block mb-2">Alur: <strong>Aktif → Terbuat → Siap → Tutup</strong>. Baris dari prospek ada tombol Revisi (kuning) / Batal (merah).</small>

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
    margin-bottom:6px;
    flex-wrap:wrap;
}

.menu-tab{
    border:1px solid #dce1e7;
    background:#fff;
    padding:8px 16px;
    border-radius:20px;
    font-weight:500;
    min-height:38px;
}

.menu-tab.active{
    background:#4c6ef5;
    color:white;
}

.menu-tab .badge{
    margin-left:6px;
}
.btn-circle:disabled { opacity:.65; cursor:not-allowed; }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Kunci tombol selama AJAX agar tidak dobel-klik (psikologi: feedback + error prevention)
function pfLock(btn) {
    if (!btn || btn.disabled) return false;
    btn.disabled = true;
    btn.dataset.orig = btn.innerHTML;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
    return true;
}
function pfUnlock(btn) {
    if (!btn) return;
    btn.disabled = false;
    if (btn.dataset.orig) btn.innerHTML = btn.dataset.orig;
}
    // Revisi tab: kembalikan pengajuan ke AO (tanpa pengajuan/mutasi ulang, tanpa redirect)
    function submitKembalikanAoTab(pgId) {
        Swal.fire({
            title: 'Kembalikan ke AO?',
            html: '<div style="text-align:left;">Ubah data di AO, proforma diperbarui otomatis. Catatan revisi (wajib diisi):'
                + '<textarea id="swCatatanAo" class="form-control" rows="3" placeholder="Wajib diisi..."></textarea></div>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Kembalikan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#4e73df',
            preConfirm: function() {
                var v = document.getElementById('swCatatanAo').value.trim();
                if (!v) { Swal.showValidationMessage('Catatan tidak boleh kosong.'); return false; }
                return v;
            }
        }).then(function(r) {
            if (!r.isConfirmed) return;
            Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            $.ajax({
                url: '{{ route("superuser.penjualan.pengajuan_proforma.kembalikanAo", ":id") }}'.replace(':id', pgId),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    catatan: r.value
                },
                success: function(res) {
                    Swal.fire('Berhasil!', res.message || 'Dikembalikan ke AO.', 'success')
                        .then(() => location.reload());
                },
                error: function(xhr) {
                    Swal.fire('Gagal!', (xhr.responseJSON && xhr.responseJSON.message) || 'Terjadi kesalahan', 'error');
                }
            });
        });
    }

    $('.datatable').DataTable({
        pageLength:25
    })

    $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    })

    // Ingat tab aktif agar tetap di halaman/tab yang sama setelah reload
    function activateTab(target) {
        $('.workflow-content').addClass('d-none');
        $('#'+target).removeClass('d-none');
        $('.workflow-tab').removeClass('active');
        $('.workflow-tab[data-target="'+target+'"]').addClass('active');
        try { sessionStorage.setItem('pfTab', target); } catch (e) {}
    }
    $(document).on('click','.workflow-tab',function(){
        activateTab($(this).data('target'));
    });
    (function() {
        var t = null;
        try { t = sessionStorage.getItem('pfTab'); } catch (e) {}
        if (t && document.getElementById(t)) activateTab(t);
    })();

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
                if (!pfLock(button[0])) return;

                $.ajax({
                    url: '/superuser/penjualan/so_proforma/statusSiap/' + id,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    complete: function() { pfUnlock(button[0]); },
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
                if (!pfLock(button[0])) return;

                $.ajax({
                    url: '/superuser/penjualan/so_proforma/acc/' + id,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    complete: function() { pfUnlock(button[0]); },
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
                if (!pfLock(button[0])) return;

                $.ajax({
                    url: '/superuser/penjualan/so_proforma/MultiCancel/' + id,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    complete: function() { pfUnlock(button[0]); },
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

    // Shortcut Batalkan (Prospek) langsung dari tab terbuat.
    // Rute resmi yang sama dengan menu pengajuan (log + flag + kembali + notif AO).
    $(document).on('click', '.btn-batalkan-prospek', function() {
        let pgId = $(this).data('pgid');
        let code = $(this).data('code') || '';

        Swal.fire({
            title: 'Batalkan proforma ' + code + '?',
            html: '<div style="text-align:left;">' +
                  '<label>Alasan pembatalan *</label>' +
                  '<textarea id="btlAlasan" class="form-control" rows="2" placeholder="Wajib diisi"></textarea>' +
                  '<div class="form-check mt-2"><input type="checkbox" class="form-check-input" id="btlRollback" checked>' +
                  '<label class="form-check-label" for="btlRollback">Kembalikan customer ke prospek</label></div>' +
                  '</div>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Batalkan',
            cancelButtonText: 'Batal',
            preConfirm: function() {
                var a = document.getElementById('btlAlasan').value.trim();
                if (!a) {
                    Swal.showValidationMessage('Alasan wajib diisi');
                    return false;
                }
                return {
                    alasan: a,
                    rollback_customer: document.getElementById('btlRollback').checked ? 1 : 0
                };
            }
        }).then((result) => {
            if (!result.isConfirmed) return;
            Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            $.ajax({
                url: '/superuser/penjualan/pengajuan-proforma/' + pgId + '/cancel',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    alasan: result.value.alasan,
                    rollback_customer: result.value.rollback_customer,
                    proforma_code: code
                },
                success: function(res) {
                    if (res.success !== false) {
                        Swal.fire('Berhasil!', res.message || 'Proforma dibatalkan.', 'success')
                            .then(() => location.reload());
                    } else {
                        Swal.fire('Gagal!', res.message || 'Terjadi kesalahan', 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
                }
            });
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
                if (!pfLock(button[0])) return;

                $.ajax({
                    url: '/superuser/penjualan/so_proforma/rollbackProforma/' + id,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    complete: function() { pfUnlock(button[0]); },
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
        let delBtn = $(this);

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data Proforma akan dihapus!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
        }).then((result) => {

            if (result.isConfirmed) {
                if (!pfLock(delBtn[0])) return;

                $.ajax({
                    url: '/superuser/penjualan/so_proforma/destroy/' + id,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    complete: function() { pfUnlock(delBtn[0]); },
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