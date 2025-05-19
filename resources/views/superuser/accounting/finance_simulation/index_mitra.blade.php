@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">UV</span>
    <span class="breadcrumb-item active">Mitra UV</span>
</nav>

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

@if (session('status') && session('message'))
<div class="alert alert-{{ session('status') }} alert-dismissable" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
    <h4 class="alert-heading">{{ ucfirst(session('status')) }}</h4>
    <p>{{ session('message') }}</p>
</div>
@endif

<div id="alert-block"></div>

<div class="block">
    <div class="block-content">
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
                <button type="button" id="btn-search" class="btn btn-primary btn-block">Cari</button>
              </div>
          </div>
    </div>
    <div class="block-content block-content-full">
      <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="new-tab" data-toggle="tab" href="#content1" role="tab">Mitra</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="done-tab" data-toggle="tab" href="#content2" role="tab">Non Mitra</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="done-tab" data-toggle="tab" href="#content3" role="tab">Done</a>
        </li>
      </ul>

      <div class="tab-content">
        <div class="tab-pane fade show active" id="content1" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="mitra_new">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Kode</th>
                            <th>Customer</th>
                            <th>Mitra</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mitra as $key => $row)
                        <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $row->uv_code }}</td>
                        <td>{{ $row->customer_name }} {{ $row->customer_kota }}</td>
                        <td>{{ $row->mitra_nama }}</td>
                        <td>
                            <a href="{{ route('superuser.accounting.finance_simulation.create_mitra', [$row->do_id, $row->id_mitra]) }}" class="btn btn-primary btn-sm">Proses</a>
                        </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="content2" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="non_mitra">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Kode</th>
                        <th>Customer</th>
                        <th>Transaksi</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach($nonMitra as $key => $row)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $row->uv_code }}</td>
                            <td>{{ $row->customer_name }} {{ $row->customer_kota }}</td>
                            <td>{{ $row->transaksi }}</td>
                            <td>
                                @if($row->uv_unifra == 0)
                                    <a href="{{ route('superuser.accounting.finance_simulation.create_non_mitra', [$row->do_id]) }}" class="btn btn-primary btn-sm">Proses</a>
                                @else
                                    <a class="btn btn-info btn-sm" href="{{ route('superuser.accounting.finance_simulation.print_jual_mitra', $row->do_uv) }}" role="button" title="Invoice Jual Mitra" target="_blank"><i class="fa fa-print"></i></a>
                                    <a class="btn btn-success btn-sm" href="{{ route('superuser.accounting.finance_simulation.print_beli_mitra', $row->do_uv) }}" role="button" title="Invoice Beli Mitra" target="_blank"><i class="fa fa-print"></i></a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="content3" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-hover table-striped" id="mitra_done">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Code</th>
                            <th>Customer</th>
                            <th>Mitra</th>
                            <th>Print</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($done as $key => $row)
                        <tr>
                            <td>{{ $key +1 }}</td>
                            <td>{{ $row->uv_code }}</td>
                            <td>{{ $row->customer_name }} {{ $row->customer_kota }}</td>
                            <td>{{ $row->mitra_nama }}</td>
                            <td>
                                <a class="btn btn-info btn-sm" href="{{ route('superuser.accounting.finance_simulation.print_jual_mitra', $row->do_id) }}" role="button" title="Invoice Jual Mitra" target="_blank"><i class="fa fa-print"></i></a>
                                <a class="btn btn-success btn-sm" href="{{ route('superuser.accounting.finance_simulation.print_beli_mitra', $row->do_id) }}" role="button" title="Invoice Beli Mitra" target="_blank"><i class="fa fa-print"></i></a>
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

        $('#mitra_new').DataTable( {
            responsive: true,
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

        $('#non_mitra').DataTable( {
            responsive: true,
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

        $('#mitra_done').DataTable( {
            responsive: true,
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
            window.location.href = "{{ route('superuser.accounting.finance_simulation.index_mitra') }}?month=" + month + "&year=" + year;
        });
    });
</script>
@endpush