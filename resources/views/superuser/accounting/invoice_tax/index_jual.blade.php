@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Accounting</span>
  <span class="breadcrumb-item">Unifra</span>
  <span class="breadcrumb-item active">Jual</span>
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
    <div class="block-content">
        {{--<a href="{{ route('superuser.accounting.invoice_tax.create') }}">
            <button type="button" class="btn btn-outline-success min-width-125">Create</button>
        </a>--}}

        <button type="button" class="btn btn-outline-primary min-width-125" data-toggle="modal" data-target="#unifraJual">Create</button>
        <hr>
  </div>
  <!-- <hr class="my-20"> -->
  <div class="block-content block-content-full">
    <div class="form-group row">
      <label class="col-md-2 col-form-label text-left" for="period">Bulan :</label>
      <div class="col-md-4">
        <div class="input-group">
          <select id="bulan" name="bulan" class="form-control js-select2">
            @foreach ($bulan as $key => $month)
              <option value="{{ $key }}" {{ $key == $selectedBulan ? 'selected' : '' }}>
                {{ $month }}
              </option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="col-md-4">
        <div class="input-group">
          <select id="tahun" name="tahun" class="form-control js-select2" style="width: 30%;">
              @for ($year = now()->year; $year >= 2024; $year--) <!-- Replace 2000 with your desired start year -->
                  <option value="{{ $year }}" {{ $year == $selectedTahun ? 'selected' : '' }}>
                      {{ $year }}
                  </option>
              @endfor
          </select>
        </div>
      </div>
    </div>
      <div class="row mb-30">
        <div class="col-12">
          <table class="table table-striped" id="datatables">
            <thead>
              <tr>
                <th>#</th>
                <th>Tanggal</th>
                <th>Code</th>
                <th>Mitra</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
  </div>
</div>

<!-- Modal create -->
<div class="modal fade" id="unifraJual" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('superuser.accounting.invoice_tax.create') }}" method="GET" id="invoiceForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Search Invoice</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label text-right" for="month_invoice">Bulan <span class="text-danger">*</span></label>
                        <div class="col-md-7">
                            <select class="js-select2 form-control" id="month_invoice" name="month_invoice" data-placeholder="Search" style="width: 100%;">
                                <option>Pilih Bulan</option>
                                @foreach($months as $key)
                                    <option value="{{ $key['id'] }}">{{ $key['monthName'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <label class="col-md-3 col-form-label text-right" for="addInvoice">Invoice <span class="text-danger">*</span></label>
                        <div class="col-md-7">
                            <select class="js-select2 form-control js-select2-unifra_jual" id="addInvoice" name="addInvoice" data-placeholder="Pilih Invoice" style="width: 100%;">
                                <option value="">Select Invoice</option>
                                @foreach ($invoice_tax as $invoice)
                                    <option value="{{ $invoice->id }}">{{ $invoice->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="type_invoice" id="type_invoice" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
<!-- Modal -->
@foreach($invoice_tax as $row)
<div class="modal fade bd-example-modal-xl" id="myModal{{$row->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel{{$row->id}}" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel{{$row->id}}">View Invoice TAX #{{$row->code}}</h5>
            </div>
            <div class="modal-body">
              <div class="row">
                  <div class="col">
                    <div class="block">
                      <div class="block-header block-header-default">
                        <h3 class="block-title">#Detail Nota</h3>
                      </div>
                      <div class="block-content">
                        <div class="form-row">
                          <div class="form-group col-md-6">
                            <label for="invoice_date{{$row->id}}">Tanggal Nota</label>
                            <input type="text" name="invoice_date" id="invoice_date{{$row->id}}" class="form-control" value="{{ date('d-m-Y', strtotime($row->date)) }}" readonly>
                          </div>
                          <div class="form-group col-md-6">
                            <label for="invoice_code{{$row->id}}">Code</label>
                            <input type="text" class="form-control" id="invoice_code{{$row->id}}" value="{{$row->code}}" readonly>
                          </div>
                        </div>

                        <div class="form-row">
                          <div class="form-group col-md-6">
                            <label for="mitra_name{{$row->id}}">Mitra</label>
                            <input type="text" name="mitra_name" id="mitra_name{{$row->id}}" class="form-control" value="{{ $row->mitra->name }}" readonly>
                          </div>
                          <div class="form-group col-md-6">
                            <label for="invoice_type{{$row->id}}">Type Invoice</label>
                            <input type="text" name="invoice_type" id="invoice_type{{$row->id}}" class="form-control" value="{{ $row->type() }}" readonly>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
              </div>

              <div class="row">
                <div class="col">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($row->invoice_tax_detail as $key)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $key->product_tax->code_product ?? '-' }} - <b>{{$key->product_tax->name_product ?? '-'}}</b> / {{$key->product_tax->packaging->pack_name ?? '-'}}</td>
                            <td>{{ number_format($key->price ,2,",",".") }}</td>
                            <td>{{ $key->qty }}</td>
                            <td>{{ number_format($key->sub_total ,2,",",".") }}</td>
                        </tr>
                      @endforeach
                    </tbody>
                    <tfoot>
                      <tr>
                        <td colspan="4" class="text-right">
                          <b>Subtotal: </b>
                        </td>
                        <td class="text-center">
                          {{ number_format($row->sub_total ,2,",",".") }}
                        </td>
                      </tr>
                      <tr>
                        <td colspan="4" class="text-right">
                          <b>PPN: </b>
                        </td>
                        <td class="text-center">
                          {{ number_format($row->ppn_idr ,2,",",".") }}
                        </td>
                      </tr>
                      <tr>
                        <td colspan="4" class="text-right">
                          <b>Grand Total: </b>
                        </td>
                        <td class="text-center">
                          {{ number_format($row->grand_total ,2,",",".") }}
                        </td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-danger btn-sm btn-flat" data-dismiss="modal"><i class="fa fa-close mr-10"></i>Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">

$('.js-select2').select2();

$(document).ready(function() {
  let datatableUrl = '{{ route('superuser.accounting.invoice_tax.json') }}';

  let cashbackTable = $('#invoice_tax').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: datatableUrl,
            dataType: "json",
            type: "GET",
            data: function (d) {
                d.bulan = $('#bulan').val(); // Send selected month
                d.tahun = $('#tahun').val(); // Send selected year
                d._token = "{{csrf_token()}}";
            },
        },
        columns: [
            {
                data: 'tanggal'
            },
            { data: 'code' },
            { data: 'mitra' },
            // { data: 'type' },
            { data: 'status' },
            { data: 'action', orderable: false, searchable: false },
        ],
        order: [[1, 'desc']],
        pageLength: 5,
        lengthMenu: [[5, 15, 20], [5, 15, 20]],
    });

    // Handle month dropdown change
    $('#bulan, #tahun').change(function () {
      cashbackTable.ajax.reload();
    });

  $(".js-select2-unifra_jual").select2({
      
      ajax: {
        url: '{{ route('superuser.accounting.invoice_tax.search_invreal_jual') }}',
        dataType: 'json',
        delay: 250,
        data: function (params) {
          return {
            q: params.term,
            _token: "{{csrf_token()}}",
            month_invoice: $("#month_invoice").val(), // Pass the month_invoice value here
            customer_name: $("#customer_name").val()  // Pass the customer_name value here
          };
        },
        cache: true
      },
  });

});
</script>
@endpush