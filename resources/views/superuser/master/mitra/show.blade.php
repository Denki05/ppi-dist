@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.mitra.index') }}">Mitra</a>
  <span class="breadcrumb-item active">Show</span>
</nav>
<div id="alert-block"></div>

<div class="row">
    <div class="col-6">
        <div class="block">
            <div class="block-header block-header-default">
                <h3 class="block-title">Detail {{ $mitra->name }}</h3>
            </div>
            <div class="block-content">
                <div class="form-group row">
                    <label class="col-lg-3 col-form-label text-right">Kode</label>
                    <div class="col-lg-8">
                        <input type="text" class="form-control" value="{{ $mitra->code }}" readonly>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-lg-3 col-form-label text-right">Nama</label>
                    <div class="col-lg-8">
                        <input type="text" class="form-control" value="{{ $mitra->name }}" readonly>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-lg-3 col-form-label text-right">Bulan</label>
                    <div class="col-lg-8">
                        <input type="text" class="form-control" value="{{ $bulan }}" readonly>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-lg-3 col-form-label text-right">Batas Bawah</label>
                    <div class="col-lg-8">
                        <input type="text" class="form-control" value="{{ number_format($batas_bawah) }}" readonly>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-lg-3 col-form-label text-right">Batas Atas</label>
                    <div class="col-lg-8">
                        <input type="text" class="form-control" value="{{ number_format($batas_atas) }}" readonly>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-lg-3 col-form-label text-right">Saldo</label>
                    <div class="col-lg-8">
                        <input type="text" class="form-control" value="{{ number_format($saldo) }}" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6">
        <div class="block">
            <div class="block-header block-header-default">
                <h3 class="block-title">List Customer</h3>
                <a class="btn btn-secondary btn-sm" data-toggle="collapse" href="#detailCustomer" role="button" aria-expanded="false" aria-controls="detailCustomer" id="toggleDetailCustomer">
                    <i class="fas fa-chevron-down"></i> <!-- Ikon awal -->
                </a>
            </div>
            <div class="collapse" id="detailCustomer">
                <div class="block-content">
                    <table class="table table-striped table-hover" id="customer_mitra">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Kota</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($mitra->mitra_detail as $detail)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $detail->customers->name }}</td>
                                <td>{{ $detail->customers->text_kota }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.master.mitra.index') }}">
            <button type="button" class="btn bg-gd-cherry border-0 text-white">
              <i class="fa fa-arrow-left mr-10"></i> Back
            </button>
          </a>
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