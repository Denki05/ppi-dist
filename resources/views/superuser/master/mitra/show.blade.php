@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.mitra.index') }}">Mitra</a>
  <span class="breadcrumb-item active">Show</span>
</nav>
<div id="alert-block"></div>

<div class="container-fluid px-2">

    <h4 class="mb-3">Detail Mitra</h4>

    <div class="card mb-3">
        <div class="card-body">
            <p><strong>Nama Mitra:</strong> {{ $mitra->name ?? '-' }}</p>
            <p><strong>Bulan Aktif:</strong> {{ $bulan }}</p>
            <p><strong>Batas Bawah:</strong> {{ number_format($batas_bawah,0,',','.') }}</p>
            <p><strong>Batas Atas:</strong> {{ number_format($batas_atas,0,',','.') }}</p>
            <p><strong>Saldo:</strong> {{ number_format($saldo,0,',','.') }}</p>
        </div>
    </div>

    {{-- Tombol History --}}
    <div class="mb-3">
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#historyModal">
            <i class="bi bi-clock-history"></i> Lihat History Import
        </button>
    </div>

    {{-- Modal History --}}
    <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="historyModalLabel">History Target Omset ({{ date('Y') }})</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th style="width:5%">#</th>
                                <th>Bulan</th>
                                <th>Batas Bawah</th>
                                <th>Batas Atas</th>
                                <th>Saldo</th>
                                <th>Tanggal Import</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($history as $i => $h)
                                <tr>
                                    <td>{{ $i+1 }}</td>
                                    <td>{{ $bulan_list[$h->bulan] ?? $h->bulan }}</td>
                                    <td>{{ number_format($h->batas_bawah,0,',','.') }}</td>
                                    <td>{{ number_format($h->batas_atas,0,',','.') }}</td>
                                    <td>{{ number_format($h->saldo,0,',','.') }}</td>
                                    <td>{{ $h->created_at->format('d-m-Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Belum ada history</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
$(document).ready(function () {
    $('.js-select2').select2()

    var table = $('#customer_mitra').DataTable({
        "paging": false,
        "info": false,
        "searching": false,
        "order": [[0, 'asc']],
        "responsive": true,
    });

    $('#toggleDetailCustomer').on('click', function() {
        var $this = $(this);
        var icon = $this.find('i');

        if ($this.attr('aria-expanded') === 'true') {
            icon.removeClass('fa-chevron-up').addClass('fa-chevron-down'); // Ganti ikon ke panah bawah
        } else {
            icon.removeClass('fa-chevron-down').addClass('fa-chevron-up'); // Ganti ikon ke panah atas
        }
    });
  })
</script>
@endpush