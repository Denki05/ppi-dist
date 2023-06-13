@extends('superuser.app')

@section('content')


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

<div id="alert-block"></div>
<form class="ajax" data-action="{{ route('superuser.penjualan.sales_order_ppn.store') }}" data-type="POST" enctype="multipart/form-data">
  @csrf
  <input type="hidden" name="ajukankelanjutan" value="0">
  <div class="row">
    <div class="col-8">
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="sales_senior_id">Sales Senior <span class="text-danger">*</span></label>
                <div class="col-md-7">
                  <input type="text" class="form-control" value="{{$sales_order->sales_senior->name}}" readonly>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="sales_id">Sales <span class="text-danger">*</span></label>
                <div class="col-md-7">
                    <input type="text" class="form-control" value="{{$sales_order->sales->name}}" readonly>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="origin_warehouse_id">Gudang <span class="text-danger">*</span></label>
                <div class="col-md-7">
                    <input type="text" class="form-control" value="{{$sales_order->origin_warehouse->name}}" readonly>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="ekspedisi_id">Ekspedisi <span class="text-danger">*</span></label>
                <div class="col-md-7">
                    <input type="text" class="form-control" value="{{$sales_order->vendor->name}}" readonly>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="type_transaction">Transaksi <span class="text-danger">*</span></label>
                <div class="col-md-7">
                    <input type="text" class="form-control" value="{{$sales_order->so_type_transaction()->scalar}}" readonly>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="idr_rate">Kurs <span class="text-danger">*</span></label>
                <div class="col-md-7">
                  <input type="text" class="form-control idr_rate" name="idr_rate" id="idr_rate" value="{{ number_format($sales_order->idr_rate,0,',','.') }}" readonly>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-4">
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="form-group row">
              <label class="col-md-4 col-form-label text-right" for="customer_other_address_id">Customer <span class="text-danger">*</span></label>
              <div class="col-md-7">
                <select class="form-control js-select2 select-customer" name="customer_other_address_id" id="customer_other_address_id" data-placeholder="Select Customer" disabled>
                <option value=""></option>
                @foreach($member as $key => $row)
                <option value="{{$row->id}}" {{ ($row->id == $sales_order->customer_other_address_id ) ? 'selected' : '' }}>{{$row->name}}</option>
                @endforeach
                </select>
                <input type="hidden" class="form-control" name="customer_id" id="customer_id">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <br>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="customer_recipient">Penerima</label>
                <div class="col-md-7">
                  <input style="font-size: 9pt;" class="form-control customer_recipient" type="text" name="customer_recipient" value="{{$sales_order->member->contact_person}}" readonly>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="text_provinsi">Provinsi</label>
                <div class="col-md-7">
                  <input class="form-control text_provinsi" type="text" name="text_provinsi" value="{{$sales_order->member->text_provinsi}}" readonly>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="text_kota">Kota</label>
                <div class="col-md-7">
                  <input class="form-control text_kota" type="text" name="text_kota" value="{{$sales_order->member->text_kota}}" readonly>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="zipcode">Kode Pos</label>
                <div class="col-md-7">
                  <input class="form-control zipcode" type="text" name="zipcode" value="{{$sales_order->member->zipcode}}" readonly>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="customer_address">Alamat Kirim</label>
                <div class="col-md-7">
                  <input class="form-control customer_address" type="text" name="customer_address" value="{{$sales_order->member->address}}" readonly>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="form-group row">
                <label class="col-md-4 col-form-label text-right" for="note">No. Dokumen <span class="text-danger">*</span></label>
                <div class="col-md-7">
                  <input class="form-control note" type="text" name="note" value="{{$sales_order->note ?? ''}}" readonly>
                </div>
              </div>
            </div>
          </div>
          <div class="form-group row pt-30">
            <div class="col-md-6">
              <a href="{{ route('superuser.penjualan.sales_order_ppn.index') }}">
                <button type="button" class="btn bg-gd-cherry border-0 text-white">
                  <i class="fa fa-arrow-left mr-10"></i> Back
                </button>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <hr>

  <div class="block">
    <div class="block-header block-header-default">
      <h3 class="block-title">List product</h3>
    </div>
    <div class="block-content">
      <table id="datatable" class="table table-striped table-vcenter">
        <thead>
          <tr>
            <th class="text-center">#</th>
            <th class="text-center">Product</th>
            <th class="text-center">Category</th>
            <th class="text-center">Qty</th>
            <th class="text-center">Packaging</th>
            <th class="text-center">Disch (Cash)</th>
            <th class="text-center">Price</th>
            <th class="text-center">Total</th>
          </tr>
        </thead>
        <tbody>
          @foreach($table as $value)
            <tr>
              <td>{{$loop->iteration}}</td>
              <td>{{$value->productCode}} - {{$value->productName}}</td>
              <td>{{$value->categoryName}}</td>
              <td>{{ number_format($value->doQty) }}</td>
              <td>{{$value->packagingName}}</td>
              <td>{{ number_format($value->doUsdDisc) }}</td>
              <td>{{ number_format($value->productPrice) }}</td>
              <td>
                {{ number_format((($value->productPrice - $value->doUsdDisc) * $value->doQty ) * $value->soKurs) }}
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="block-header block-header-default">
      <div class="container">
        <div class="form-group row justify-content-end">
          <label class="col-md-3 col-form-label text-right" for="subtotal">SubTotal</label>
          <div class="col-md-2">
            <input type="text" class="form-control subtotal" id="subtotal" name="subtotal" value="{{ number_format($table[0]->doPurchaseTotal) }}" readonly>
          </div>
        </div>
        <div class="form-group row justify-content-end">
          <label class="col-md-3 col-form-label text-right" for="disc_percent">Disc%</label>
          <div class="col-md-1">
            <input type="text" class="form-control disc_percent" id="disc_percent" name="disc_percent" value="{{ number_format($table[0]->discPercent) }}" readonly>
          </div>
          <div class="col-md-2">
            <input type="text" class="form-control" id="disc_percent_idr" name="disc_percent_idr" value="{{ number_format($table[0]->discPercentIdr) }}" readonly>
          </div>
        </div>
        <div class="form-group row justify-content-end">
          <label class="col-md-3 col-form-label text-right" for="disc_pack">Disc Kemasan</label>
          <div class="col-md-1">
            <input type="text" class="form-control" id="disc_pack" name="disc_pack" value="{{ number_format($table[0]->discPack) }}" readonly>
          </div>
          <div class="col-md-2">
            <input type="text" class="form-control" id="disc_pack_idr" name="disc_pack_idr" value="{{ number_format($table[0]->discPackIdr) }}" readonly>
          </div>
        </div>
        <div class="form-group row justify-content-end">
          <label class="col-md-3 col-form-label text-right" for="discount_idr">Disc IDR</label>
          <div class="col-md-2">
            <input type="text" class="form-control" id="discount_idr" name="discount_idr" value="{{ number_format($table[0]->discIdr) }}" readonly>
          </div>
        </div>
        <div class="form-group row justify-content-end">
          <label class="col-md-3 col-form-label text-right" for="tax_ammount">Pajak</label>
          <div class="col-md-1">
            <input class="form-check-input" type="checkbox" id="tax_ammount" name="tax_ammount" checked>
          </div>
          <div class="col-md-2">
            <input type="text" class="form-control" id="tax_ammount_idr" name="tax_ammount_idr" value="{{ number_format($table[0]->taxAmmount) }}" readonly>
          </div>
        </div>
        <div class="form-group row justify-content-end">
          <label class="col-md-3 col-form-label text-right" for="voucher_idr">Voucher</label>
          <div class="col-md-2">
            <input type="text" class="form-control" id="voucher_idr" name="voucher_idr" value="{{ number_format($table[0]->voucherIdr) }}" readonly>
          </div>
        </div>
        <div class="form-group row justify-content-end">
          <label class="col-md-3 col-form-label text-right" for="delivery_cost">Ongkir</label>
          <div class="col-md-2">
            <input type="text" class="form-control" id="delivery_cost" name="delivery_cost" value="{{ number_format($table[0]->ongkirIdr) }}" readonly>
          </div>
        </div>
        <div class="form-group row justify-content-end">
          <label class="col-md-3 col-form-label text-right" for="grand_total_idr">Grand Total</label>
          <div class="col-md-2">
            <input type="text" class="form-control" id="grand_total_idr" name="grand_total_idr" value="{{ number_format($table[0]->grandTotalIdr) }}" readonly>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

@endsection
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $('.js-select2').select2()

    function delay(fn, ms) {
      let timer = 0
      return function(...args) {
        clearTimeout(timer)
        timer = setTimeout(fn.bind(this, ...args), ms || 0)
      }
    }
  });
</script>
@endpush
