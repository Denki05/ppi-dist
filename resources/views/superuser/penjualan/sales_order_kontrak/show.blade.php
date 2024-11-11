@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sales</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.sales_order_kontrak.index') }}">Sales Kontrak</a>
  <span class="breadcrumb-item active">Show</span>
</nav>

<div id="alert-block"></div>

    <div class="row">
        <div class="col-6">
            <div class="block">
                <div class="block-header block-header-default">
                    <h3 class="block-title">#Detail Sales Kontrak</h3>
                    
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target=".bd-example-modal-lg"><i class="fa fa-history" aria-hidden="true"></i> Log Kontrak</button>
                    
                </div>
                <div class="block-content">
                    <div class="form-group row">
                        <div class="col">
                            <label class="col-form-label text-right" for="code">Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)" value="{{ $kontrak->code }}" readonly>
                        </div>
                        <div class="col">
                            <label class="col-form-label text-right" for="durasi_kontrak">Durasi <span class="text-danger">*</span></label>
                            <select class="js-select2 form-control" id="durasi_kontrak" name="durasi_kontrak" data-placeholder="Pilih Durasi Kontrak" disabled>
                                <option></option>
                                @foreach(\App\Entities\Penjualan\SalesOrderKontrak::CONTRACT_RANGE as $durasi => $value)
                                <option value="{{ $value }}" {{ $kontrak->contract_range == $value ? 'selected' : ''}}>{{ $durasi }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        
                            <div class="col">
                                <label class="col-form-label text-right" for="sales_senior">Sales  Senior <span class="text-danger">*</span></label>
                                <select class="js-select2 form-control" id="sales_senior" name="sales_senior" data-placeholder="Pilih Sales Senior" disabled>
                                    <option></option>
                                    @foreach(\App\Entities\Penjualan\SalesOrderKontrak::SALES_SENIOR as $sales => $value)
                                    <option value="{{ $value }}" {{ $kontrak->sales_senior == $value ? 'selected' : '' }}>{{ $sales }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col">
                                <label class="col-form-label text-right" for="sales_junior">Sales <span class="text-danger">*</span></label>
                                <select class="js-select2 form-control" id="sales_junior" name="sales_junior" data-placeholder="Pilih Sales" disabled>
                                    <option></option>
                                    @foreach(\App\Entities\Penjualan\SalesOrderKontrak::SALES as $sales => $value)
                                    <option value="{{ $value }}" {{ $kontrak->sales_junior == $value ? 'selected' : '' }}>{{ $sales }}</option>
                                    @endforeach
                                </select>
                            </div>
                        
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6">
            <div class="block">
                <div class="block-header block-header-default">
                    <h3 class="block-title">#Customer</h3>
                </div>
                <div class="block-content">
                    <div class="form-group row">
                        <div class="col">
                            <label class="col-form-label text-right" for="code">Customer <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" value="{{ $kontrak->member->name }} {{ $kontrak->member->text_kota }}" readonly>
                        </div>
                        <div class="col">
                            <label class="col-form-label text-right" for="code">Address</label>
                            <!-- <input type="text" class="form-control" name="customer_address" id="customer_address" readonly value="{{ $kontrak->member->address }}" readonly> -->
                            <textarea class="form-control" id="exampleFormControlTextarea1" rows="1" readonly>{{ $kontrak->member->address }}</textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col">
                            <label class="col-form-label text-right" for="sales_junior">Note</label>
                            <textarea class="form-control" id="exampleFormControlTextarea1" rows="2" readonly>{{ $kontrak->note }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="block">
        <div class="block-header block-header-default">
            <h3 class="block-title">#Detail Product</h3>
        </div>
        <div class="block-content">
            <table id="datatables" class="table table-striped">
                <thead>
                <tr>
                    <th class="text-center">Product</th>
                    <th class="text-center">Packaging</th>
                    <th class="text-center">Price</th>
                    <th class="text-center">Qty</th>
                    <th class="text-center">Disc</th>
                </tr>
                </thead>
                <tbody>
                    <td style="width: 40%;">
                        <input type="text" class="form-control" style="text-align: center;" value="{{ $kontrak_item->product_pack->code }} - {{ $kontrak_item->product_pack->name }}" readonly>
                    </td>
                    <td style="width: 25%;">
                    <input type="text" class="form-control" style="text-align: center;" value="{{ $kontrak_item->packaging->pack_name }}" readonly>
                    </td>
                    <td style="width: auto;">
                        <input type="number" name="price" id="price" class="form-control" style="text-align: center;" value="{{ $kontrak_item->price }}" readonly>
                    </td>
                    <td style="width: auto;">
                        <input type="number" name="qty" id="qty" class="form-control" style="text-align: center;" value="{{ $kontrak_item->qty }}" readonly>
                    </td>
                    <td style="width: auto;">
                        <input type="number" name="disc_usd" id="disc_usd" class="form-control" style="text-align: center;" value="{{ $kontrak_item->disc_usd }}" readonly>
                    </td>
                </tbody>
            </table>
            <div class="form-group row pt-30">
                <div class="col-md-6">
                    <a href="javascript:history.back()">
                        <button type="button" class="btn bg-gd-cherry border-0 text-white">
                        <i class="fa fa-arrow-left mr-10"></i> Back
                        </button>
                    </a>
                </div>
            </div>
        </div>
  </div>

    <div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">#Retrieval Notes</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table" id="log_kontrak">
                        <thead class="thead-dark">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">So Code</th>
                                <th scope="col">Ref Inv</th>
                                <th scope="col">Sent Qty</th>
                                <th scope="col">Outstanding Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $remainingQty = $log_kontrak->first()->qty_ordered ?? 0; // Mulai dari qty_ordered awal
                                $totalSentQty = 0;
                                $totalOutstandingQty = 0;
                            @endphp

                            @foreach($log_kontrak as $row)
                                @php
                                    $currentSentQty = $row->qty_sent;
                                    $outstandingQty = $remainingQty - $currentSentQty;

                                    // Update nilai remainingQty untuk baris berikutnya
                                    $remainingQty = $outstandingQty > 0 ? $outstandingQty : 0;

                                    // Menambahkan ke total
                                    $totalSentQty += $currentSentQty;
                                    $totalOutstandingQty = $outstandingQty; // Menyimpan sisa terakhir sebagai total outstanding
                                @endphp

                                <tr>
                                    <td scope="row">{{ $loop->iteration }}</td>
                                    <td>{{ $row->so_code }}</td>
                                    <td>{{ $row->invoice_code }}</td>
                                    <td>{{ $currentSentQty }}</td>
                                    <td>{{ $outstandingQty }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" style="text-align: right;">Total :</th>
                                <th style="text-align: center;">{{ $totalSentQty }}</th>
                                <th style="text-align: center;">{{ $totalOutstandingQty }} (Sisa)</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
    $(document).ready(function () {
        $('.js-select2').select2();

        $('#datatables').DataTable( {
            "paging":   false,
            "ordering": true,
            "info":     false,
            "searching" : false,
            "columnDefs": [{
                "targets": 0,
                "orderable": false
            }]
        });

        $('#log_kontrak').DataTable( {
            "paging":   false,
            "ordering": true,
            "info":     false,
            "searching" : false,
            "columnDefs": [{
                "targets": 0,
                "orderable": false
            }]
        });
    })
</script>
@endpush