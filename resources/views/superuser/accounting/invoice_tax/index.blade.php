@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Accounting</span>
  <span class="breadcrumb-item active">Invoice Tax</span>
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
        <a href="{{ route('superuser.accounting.invoice_tax.create') }}">
            <button type="button" class="btn btn-outline-primary min-width-125">Create</button>
        </a>
        <hr>
  </div>
  <!-- <hr class="my-20"> -->
  <div class="block-content block-content-full">
      <div class="row mb-30">
        <div class="col-12">
          <table class="table table-striped" id="datatables">
            <thead>
              <tr>
                <th>#</th>
                <th>Tanggal</th>
                <th>Invoice Tax</th>
                <th>Mitra</th>
                <th>Type</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($invoice_tax as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $row->invoice_tax_date ?? '' }}</td>
                    <td>{{ $row->no_invoice_tax ?? '' }}</td>
                    <td>{{ $row->mitra->name ?? '' }}</td>
                    <td>{{ $row->type() ?? '' }}</td>
                    <td>
                      <button type="button" class="btn btn-primary btn-sm btn-flat" data-toggle="modal" data-target="#myModal{{$row->id}}"><i class="fa fa-eye"></i> View</button>
                      <a  class="btn btn-success" href="#" role="button"><i class="fa fa-print"></i> Print</a>
                    </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      
      
  </div>
</div>

@endsection

<!-- Modal -->
@foreach($invoice_tax as $row)
<div class="modal fade bd-example-modal-xl" id="myModal{{$row->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">View Invoice TAX #{{$row->no_invoice_tax}}</h5>
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
                            <label for="invoice_date">Tanggal Nota</label>
                            <input type="text" name="invoice_date" class="form-control" value="{{ date('d-m-Y',strtotime($row->invoice_tax_date)) }}" readonly>
                          </div>
                          <div class="form-group col-md-6">
                            <label for="invoice_code">Code</label>
                            <input type="text" class="form-control" id="invoice_code" value="{{$row->no_invoice_tax}}" readonly>
                          </div>
                        </div>

                        <div class="form-row">
                          <div class="form-group col-md-6">
                            <label for="type_transaction">Mitra</label>
                            <input type="text" name="mitra_id" class="form-control" value="{{ $row->mitra->name }}"  readonly>
                          </div>
                          <div class="form-group col-md-6">
                            <label for="type_transaction">Type Invoice</label>
                            <input type="text" name="mitra_id" class="form-control" value="{{ $row->type() }}"  readonly>
                          </div>
                        </div>

                        <div class="form-row">
                          <div class="form-group col-md-6">
                            <label for="type_transaction">Kurs</label>
                            <input type="text" name="mitra_id" class="form-control" value="{{ number_format($row->kurs, 2) }}"  readonly>
                          </div>
                          <div class="form-group col-md-6">
                            <label for="type_transaction">Total Hitung Baru</label>
                            <input type="text" name="mitra_id" class="form-control" value="{{ number_format($row->tot_hit_baru, 2) }}"  readonly>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col">
                    <div class="block">
                      <div class="block-header block-header-default">
                        <h3 class="block-title">#Customer</h3>
                      </div>
                      <div class="block-content">
                        <div class="form-row">
                          <div class="form-group col-md-6">
                            <label for="type_transaction">Customer</label>
                            <input type="text" name="customer_name" id="customer_name" class="form-control" value="{{ $row->member->name }} {{$row->member->text_kota}}"  readonly>
                          </div>
                          <div class="form-group col-md-6">
                            <label for="note">Alamat Kirim</label>
                            <textarea class="form-control" rows="1" readonly>{{$row->member->address}}</textarea>
                          </div>
                        </div>

                        <div class="form-row">
                          <div class="form-group col-md-6">
                            <label for="customer_city">Kota</label>
                            <input type="text" name="customer_city" class="form-control" value="{{$row->member->text_kota}}" readonly>
                          </div>
                          <div class="form-group col-md-6">
                            <label for="customer_area">Provinsi</label>
                            <input type="text" name="customer_area" class="form-control" value="{{$row->member->text_provinsi}}"  readonly>
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
                        <th>Qty</th>
                        <th>Subtotal</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($row->invoice_tax_detail as $row => $key)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $key->product_tax->code_product ?? '-' }} - <b>{{$key->product_tax->name_product ?? '-'}}</b></td>
                            <td>{{ $key->qty }}</td>
                            <td>{{ number_format($key->subtotal ,2,",",".") }}</td>
                        </tr>
                      @endforeach
                    </tbody>
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
$(document).ready(function() {
  $('#datatables').DataTable({
    "searching": false,
    "paging": false,
    "info": false
  });
});
</script>
@endpush


