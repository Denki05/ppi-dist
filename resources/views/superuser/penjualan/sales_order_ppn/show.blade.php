@php
$subtotal = 0;
@endphp

@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.sales_order_ppn.index') }}">SO Khusus(PPN)</a>
  <span class="breadcrumb-item active">Show</span>
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

<div id="alert-block"></div>

@if(session()->has('message'))
<div class="alert alert-success alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Success</h3>
  <p class="mb-0">{{ session()->get('message') }}</p>
</div>
@endif


  <div class="row">
    <div class="col-6">
      <div class="block">
        <div class="block-header block-header-default">
          <h3 class="block-title">#Detail Nota</h3>
        </div>
        <div class="block-content">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="so_date">Tanggal Nota</label>
              <input type="date" name="so_date" class="form-control" value="{{ $so_khusus->so_date }}" readonly>
            </div>
            <div class="form-group col-md-6">
              <label for="type_transaction">Type Transaksi</label>
              <input type="text" name="type_transaction" class="form-control" value="{{ $so_khusus->type_transaction }}" readonly>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="so_date">Sales Senior</label>
              <input type="text" name="type_transaction" class="form-control" value="{{ $so_khusus->so_sales_senior() }}" readonly>
            </div>
            <div class="form-group col-md-6">
              <label for="type_transaction">Sales</label>
              <input type="text" name="type_transaction" class="form-control" value="{{ $so_khusus->so_sales() }}" readonly>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="warehouse_id">Gudang <span class="text-danger">*</span></label>
              <input type="text" name="type_transaction" class="form-control" value="{{ $so_khusus->origin_warehouse->name ?? '' }}" readonly>
            </div>
            <div class="form-group col-md-6">
              <label for="type_transaction">Eksepdisi <span class="text-danger">*</span></label>
              <input type="text" name="type_transaction" class="form-control" value="{{ $so_khusus->vendor->name ?? '' }}" readonly>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="type_transaction">Nota Type <span class="text-danger">*</span></label>
              <input type="text" name="nota_type" class="form-control" value="{{ $so_khusus->type_so ?? '' }}" readonly>
            </div>
          </div>
          <br>
          <br>
        </div>
      </div>
    </div>

    <div class="col-6">
      <div class="row">
        <div class="col">
          <div class="block">
            <div class="block-header block-header-default">
              <h3 class="block-title">#Customer Info</h3>
            </div>
            <div class="block-content">
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="type_transaction">Customer</label>
                  <input type="text" class="form-control" name="customer_name" id="customer_name" value="{{ $so_khusus->member->name }}" readonly>
                </div>
                <div class="form-group col-md-6">
                  <label for="note">Alamat Kirim</label>
                  <!-- <textarea class="form-control" rows="1" readonly></textarea> -->
                  <input type="text" class="form-control" name="customer_address" id="customer_address" value="{{ $so_khusus->member->address }}" readonly>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="customer_city">Kota</label>
                  <input type="text" name="customer_city" id="customer_city" class="form-control" value="{{ $so_khusus->member->text_kota }}" readonly>
                </div>
                <div class="form-group col-md-6">
                  <label for="customer_area">Provinsi</label>
                  <input type="text" name="customer_area" id="customer_area" class="form-control" value="{{ $so_khusus->member->text_provinsi }}" readonly>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col">
          <div class="block">
            <div class="block-content">
              <div class="form-row">
                <div class="form-group col-md-4">
                  <label for="customer_area">No. Dokumen <span class="text-danger">*</span></label>
                  <input type="text" name="no_document" id="no_document"  class="form-control" value="{{ $so_khusus->catatan }}" readonly>
                </div>
                <div class="form-group col-md-4">
                  <label for="note">Rekening <span class="text-danger">*</span></label>
                  <input type="text" name="type_transaction" class="form-control" value="{{ $so_khusus->rekening ?? '' }}" readonly>
                </div>
                <div class="form-group col-md-4">
                  <label for="customer_area">Kurs <span class="text-danger">*</span></label>
                  <input type="text" name="idr_rate" id="idr_rate"  class="form-control" value="{{ $so_khusus->idr_rate }}" readonly>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
      <aside class="col-lg-9">
        <div class="block">
          <div class="block-header block-header-default">
            <h3 class="block-title">#Product</h3>
          </div>
          <div class="block-content">
            <table id="datatables" class="table table-striped">
              <thead>
                <tr>
                  <th class="text-center">#</th>
                  <th class="text-center">Produk</th>
                  <th class="text-center">Kemasan</th>
                  <th class="text-center">Harga</th>
                  <th class="text-center">Qty</th>
                  <th class="text-center">Disc</th>
                  <th class="text-center">Subtotal</th>
                </tr>
              </thead>
              <tbody>
                @foreach($so_khusus->do as $row)
                  @foreach($row->do_items as $key => $value)
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $value->product_pack->code }} - <b>{{ $value->product_pack->name }}</b></td>
                    <td>{{ $value->product_pack->packaging->pack_name }}</td>
                    <td>{{ $value->price }}</td>
                    <td>{{ $value->qty }}</td>
                    <td>{{ $value->usd_disc ?? '-' }}</td>
                    <td>{{ number_format($row->idr_rate * $value->total) }}</td>
                  </tr>
                  @endforeach
                @endforeach
              </tbody>
              <tfoot>
                <?php
                  foreach($so_khusus->do as $row){
                    foreach($row->do_items as $key => $value){
                      $subtotal_item = $row->idr_rate * $value->total;

                      $subtotal += $subtotal_item;
                      // DD($subtotal);
                    }
                  }
                ?>
                <tr class="row-footer-subtotal">
                  <td colspan="6" class="text-right">
                    <b>Subtotal</b>
                  </td>
                  <td class="text-right">
                    <input type="text" name="sub_total_item" id="sub_total_item" class="form-control" style="text-align:center; font-weight: bold;" value="{{ number_format($subtotal) }}" readonly step="any">
                  </td>
                </tr>
              </tfoot>
            </table>
            <br>
          </div>
        </div>
      </aside>

      <aside class="col-lg-3">
        <div class="card border-0">
          <div class="card-body">
            <?php 
              foreach($so_khusus->do as $row){
                foreach($row->do_detail_cost as $key => $value){
                  $discount_percent = $value->discount_1;
                  $discount_percent_idr = $value->discount_1_idr;
                  $discount_kemasan_percent = $value->discount_2;
                  $discount_kemasan_idr = $value->discount_2_idr;
                  $discount_idr = $value->discount_idr;
                  $voucher_idr = $value->voucher_idr;
                  $ppn_percent = $value->ppn;
                  $ppn_idr = $value->ppn_idr;
                  $ongkir = $value->delivery_cost_idr;
                  $grand_total = $value->grand_total_idr;
                }
              }
            ?>
            <div class="form-group row">
              <label class="col-sm-4 col-form-label">Disc %</label>
              <div class="col-sm-3">
                <input type="text" class="form-control" id="disc_agen_percent" name="disc_agen_percent" value="{{ $discount_percent }}" readonly>
              </div>
              <div class="col-sm-5">
                <input type="text" readonly class="form-control" id="disc_agen_idr" name="disc_agen_idr" value="{{ $discount_percent_idr }}">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-4 col-form-label">Disc Kemasan</label>
              <div class="col-sm-3">
                <input type="text" class="form-control" id="disc_kemasan_percent" name="disc_kemasan_percent" value="{{ $discount_kemasan_percent }}" readonly>
              </div>
              <div class="col-sm-5">
                <input type="text" readonly class="form-control" id="disc_kemasan_idr" name="disc_kemasan_idr" value="{{ $discount_kemasan_idr }}">
              </div>
            </div> 
            <div class="form-group row">
              <label class="col-sm-4 col-form-label">Disc IDR</label>
              <div class="col-sm-8">
                <input type="text" class="form-control" id="disc_tambahan_idr" name="disc_tambahan_idr" value="{{ $discount_idr }}" readonly>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-4 col-form-label">Voucher</label>
              <div class="col-sm-8">
                <input type="text" class="form-control" id="voucher_idr" name="voucher_idr" value="{{ $voucher_idr }}" readonly>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-4 col-form-label">Pajak</label>
              <div class="col-sm-3">
                <input type="text" class="form-control" id="ppn_percent" name="ppn_percent" value="{{ $ppn_percent }}" readonly>
              </div>
              <div class="col-sm-5">
                <input type="text" class="form-control" id="ppn_idr" name="ppn_idr" value="{{ number_format($ppn_idr) }}" readonly>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-4 col-form-label">Ongkir</label>
              <div class="col-sm-8">
                <input type="text" class="form-control" id="delivery_cost_idr" name="delivery_cost_idr" value="{{ number_format($ongkir) }}" readonly>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-4 col-form-label">Grand Total</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" id="grand_total_idr" name="grand_total_idr" value="{{ number_format($grand_total) }}" readonly>
                  <input type="hidden" class="form-control" name="subtotal_2" id="subtotal_2">
                </div>
            </div>
          </div>
        </div>
    </aside>
  </div>

  <div class="row pt-30 mb-15">
    <div class="col-md-6">
      <a href="{{ route('superuser.penjualan.sales_order_ppn.index') }}">
        <button type="button" class="btn bg-gd-cherry border-0 text-white">
          <i class="fa fa-arrow-left mr-10"></i> Back
        </button>
      </a>
    </div>
  </div>

@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function () {
    $('.js-select2').select2();

    $('#datatables').DataTable({
      
    })
  })
</script>
@endpush