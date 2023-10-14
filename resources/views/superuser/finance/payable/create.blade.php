@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Finance</span>
  <span class="breadcrumb-item">Payable</span>
  <span class="breadcrumb-item active">Create</span>
</nav>
@if(session('error') || session('success'))
<div class="alert alert-{{ session('error') ? 'danger' : 'success' }} alert-dismissible fade show" role="alert">
    @if (session('error'))
    <strong>Error!</strong> {!! session('error') !!}
    @elseif (session('success'))
    <strong>Berhasil!</strong> {!! session('success') !!}
    @endif
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">#Detail Invoice</h3>
  </div>
  <div class="block-content block-content-full">
    <div class="row">
      <table class="table table-striped" id="datatables">
        <thead>
          <tr>
            <th>#</th>
            <th>Invoice</th>
            <th>Total Nota</th>
            <th>Total Terbayar</th>
            <th>Sisa Bayar</th>
          </tr>
        </thead>
        <tbody>
          @foreach($store as $key)
            @foreach($key->invoicing as $row => $index)
              <?php
                $total_invoicing = $index->grand_total_idr ?? 0;
                $payable = $index->payable_detail->sum('total');
                $sisa = $total_invoicing - $payable;
              ?>
              <tr class="repeater">
                <input type="hidden" name="invoice_id[]" value="{{ $index->id }}">
                <td>{{ $loop->iteration }}</td>
                <td>
                  <input type="text" class="form-control-plaintext text-center" name="invoice_code[]" value="{{ $index->code }}" readonly>
                </td>
                <td>
                  <div class="col-xs-6 col-xs-offset-3">
                    <input type="text" class="form-control text-center" name="total_invoice[]" value="{{number_format($total_invoicing,0,',','.')}}" readonly>
                  </div>
                </td>
                <td>
                  <div class="col-xs-6 col-xs-offset-3">
                    <input type="text" class="form-control text-center" name="payment[]">
                  </div>
                </td>
                <td>
                  <div class="col-xs-6 col-xs-offset-3">
                    <input class="form-control text-center" type="text" name="sisa[]" value="{{number_format($sisa,0,',','.')}}" readonly>
                  </div>
                </td>
              </tr>
            @endforeach
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    $('#datatables').DataTable( {
        "paging":   false,
        "ordering": true,
        "info":     false,
        "searching" : true,
        "columnDefs": [{
          "targets": 0,
          "orderable": false
        }]
    })

    $('#datatables tbody').on( 'keyup', 'input[name="payment[]"]', function (e) {
        let grand_total = $(this).parents('tr').find('input[name="total_invoice[]"]').val();
        let total_bayar = parseFloat($(this).val());

        grand_total = parseFloat(grand_total.split('.').join(''));

        sisa = grand_total - total_bayar;

        $(this).parents('tr').find('input[name="sisa[]"]').val(sisa);
    });
  })
</script>
@endpush