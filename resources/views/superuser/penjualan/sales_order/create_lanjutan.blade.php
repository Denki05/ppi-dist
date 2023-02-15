@foreach($result->so_detail as $index => $row)
  @php $row->product->name @endphp
@endforeach

@extends('superuser.app')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-4">
      <div class="card mb-2 border-0">
        <div class="card-body">
          <div class="row">
            <div class="col">
              <div class="row">
                <label style="font-size: 10pt;" class="col-xs-4 col-sm-4 col-md-4 control-label" for="textinput">Code</label>
                <div class="col-xs-6 col-sm-6 col-md-6">
                  <p style="font-size: 9pt;">{{ $result->code }}</p>
                </div>
              </div>
              </div>
              <div class="col">
                <div class="row">
                  <label style="font-size: 10pt;" class="col-xs-6 col-sm-6 col-md-6 control-label" for="textinput">Tanggal</label>
                  <div class="col-xs-6 col-sm-6 col-md-6">
                    <p style="font-size: 9pt;">{{ date('d-m-Y',strtotime($result->created_at)) }}</p>
                  </div>
                </div>
              </div>
          </div>
        </div>
      </div>

      <div class="card mb-2 border-0">
        <div class="card-body" >
          <div class="row">
            <div class="col">
              <div class="form-label-group in-border">
                <label style="font-size: 10pt;">Customer</label>
                <p style="font-size: 9pt;">{{ $result->member->name }}</p>
              </div>
            </div>
            <div class="col">
              <div class="form-label-group in-border">
                <label style="font-size: 10pt;">Address</label>
                <p style="font-size: 9pt;">{{ $result->member->address }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card mb-2 border-0">
        <div class="card-body" style="padding: 2px 5px 2px;">
          <div class="row">
            <div class="col">
              <div class="form-label-group in-border">
                <label style="font-size: 10pt;">Plafon Piutang</label>
                <p style="font-size: 9pt;">{{ $result->customer->plafon_piutang }}</p>
              </div>
            </div>
            <div class="col">
              <div class="form-label-group in-border">
                <label style="font-size: 10pt;">Saldo</label>
                <p style="font-size: 9pt;">{{ $result->customer->saldo }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
    <div class="col-md-8">
      <div class="row">
        <div class="card mb-2 border-0">
          <div class="card-body">
            <div class="row">
              <div class="col-sm">
                @if($step == 2)
                  <div class="form-group row">
                    <label style="font-size: 10pt;" class="col-md-4 col-form-label text-right">Gudang<span class="text-danger">*</span></label>
                      <div class="col-8">
                        <select class="form-control js-select2" style="font-size: 9pt;" name="origin_warehouse_id">
                          <option value="">Pilih Gudang</option>
                          @foreach($warehouse as $index => $row)
                          <option style="font-size: 10pt;" value="{{$row->id}}" @if($result->origin_warehouse_id == $row->id) selected @endif>{{$row->name}}</option>
                          @endforeach
                        </select>
                      </div>
                  </div>
                  @endif
              </div>
              <div class="col-sm">
                @if($step == 2)
                  <div class="form-group row">
                    <label class="col-md-4 col-form-label text-right" style="font-size: 10pt;">Kurs<span class="text-danger">*</span></label>
                    <div class="col-5">
                      <input type="number" name="idr_rate" style="font-size: 9pt;" class="form-control text-right" step="any">
                    </div>
                  </div>
                  @endif
              </div>
              <div class="col-sm">
                @if($step == 2)
                <div class="form-group row">
                  <label class="col-6 col-form-label text-right"><input type="checkbox" name="checkbox_disc-cash">Disc Cash</label>
                  <div class="col-6">
                    <select class="form-control js-select2 disc-cash">
                      <option value="">Disc</option>
                      <option value="2">$2</option>
                      <option value="4">$4</option>
                    </select>
                  </div>
                </div>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="card mb-2 border-0">
          <div class="card-body">
            <div class="row">
              <div class="col">
                @if($step == 2)
                <div class="form-group row">
                  <label class="col-md-4 col-form-label text-right">Disc %</label>
                  <div class="col-md-3">
                    <input type="number" name="idr_rate" class="form-control text-center" placeholder="5%" step="any">
                  </div>
                </div>
                @endif
              </div>
              <div class="col">
                @if($step == 2)
                <div class="form-group row">
                  <label class="col-md-4 col-form-label text-right">Voucher</label>
                  <div class="col-md-6">
                    <input type="number" name="idr_rate" class="form-control text-center" placeholder="Rp1.000.000" step="any">
                  </div>
                </div>
                @endif
              </div>
              <div class="col">
                @if($step == 2)
                <div class="form-group row">
                  <label class="col-md-4 col-form-label text-right">Subtotal</label>
                  <div class="col-md-6">
                    <input type="number" name="idr_rate" class="form-control text-center" placeholder="Rp3.214.800" step="any" readonly>
                  </div>
                </div>
                @endif
              </div>
            </div>

            <div class="row">
              <div class="col">
                @if($step == 2)
                <div class="form-group row">
                  <label class="col-md-4 col-form-label text-right">Disc Kemasan</label>
                  <div class="col-md-3">
                    <input type="number" name="idr_rate" class="form-control text-center" placeholder="6%" step="any">
                  </div>
                </div>
                @endif
              </div>
              <div class="col">
                @if($step == 2)
                <div class="form-group row">
                  <label class="col-md-4 col-form-label text-right">Ongkir</label>
                  <div class="col-md-6">
                    <input type="number" name="idr_rate" class="form-control text-center" placeholder="Rp175.000" step="any">
                  </div>
                </div>
                @endif
              </div>
              <div class="col">
                @if($step == 2)
                <div class="form-group row">
                  <label class="col-md-4 col-form-label text-right">Grand Total</label>
                  <div class="col-md-6">
                    <input type="number" name="idr_rate" class="form-control text-center" placeholder="Rp3.389.800" step="any" readonly>
                  </div>
                </div>
                @endif
              </div>
            </div>

            <div class="row">
              <div class="col-4">
                @if($step == 2)
                <div class="form-group row">
                  <label class="col-md-4 col-form-label text-right">Disc IDR</label>
                  <div class="col-md-6">
                    <input type="number" name="idr_rate" class="form-control text-center" placeholder="Rp1.500.000" step="any">
                  </div>
                </div>
                @endif
              </div>
              <div class="col-4">
                @if($step == 2)
                <div class="form-group row">
                  <label class="col-md-4 col-form-label text-right">Resi Ongkir</label>
                  <div class="col-md-6">
                    <input type="number" name="idr_rate" class="form-control text-center" placeholder="Rp175.000" step="any">
                  </div>
                </div>
                @endif
              </div>
              <div class="col-4">
                <button type="button" class="btn btn-danger "><i class="fas fa-calculator pr-2" aria-hidden="true"></i>Calculate</button>
                <button type="button" class="btn btn-info"><i class="fas fa-save pr-2" aria-hidden="true"></i>Save</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <br>

  <!-- Detail Pesanan -->
  <div class="row">
    <div class="col-12">
      <div class="card mb-2 border-0">
        <div class="card-body">
          <table class="table table-bordered" id="tableDetailPesanan">
              <thead>
                <tr>
                  <th scope="col" width="2%">#</th>
                  <th scope="col" width="2%">NO</th>
                  <th scope="col" width="5%">Code</th>
                  <th scope="col" width="10%">Product</th>
                  <th scope="col" width="2%">Acuan<br>(USD)</th>
                  <th scope="col" width="2%">Qty<br>(KG)</th>
                  <th scope="col" width="5%">In Stock</th>
                  <th scope="col" width="5%">Kemasan</th>
                  <th scope="col" width="5%">Harga</th>
                  <th scope="col" width="2%">Disc</th>
                  <th scope="col" width="5%">Netto</th>
                  <th scope="col" width="10%">Jumlah</th>
                </tr>
              </thead>
              <tbody>
                @if(count($result->so_detail) <= 0)
                  <tr>
                    <td colspan="13" align="center">Data tidak ditemukan</td>
                  </tr>
                @endif
                @if(count($result->so_detail) > 0)
                  @foreach($result->so_detail as $index => $row)
                    <input type="hidden" name="repeater[{{$index}}][product_id]" value="{{$row->product_id}}">
                    <input type="hidden" name="repeater[{{$index}}][so_qty]" value="{{$row->qty}}">
                    <input type="hidden" name="repeater[{{$index}}][so_item_id]" value="{{$row->id}}">
                    <input type="hidden" name="repeater[{{$index}}][packaging]" class="form-control" readonly value="{{$row->packaging_txt()->scalar ?? ''}}">
                    <input type="hidden" name="repeater[{{$index}}][price]" class="form-control" readonly value="{{$row->product->selling_price ?? 0}}">
                      <tr class="index{{$index}}" data-index="{{$index}}">
                        <td><input type="checkbox" name="checkProductList" step="any"></td>
                        <td>{{$index + 1}}</td>
                        <td class="product">{{$row->product->code ?? ''}}</td>
                        <td class="product">{{$row->product->name ?? ''}}</td>
                        <td>{{ __('$') }} {{ number_format($row->product->selling_price, 0) }}</td>
                        <td>{{ $row->qty }}</td>
                        <td>
                          <input type="number" style="width: 50px;  margin-right: auto; margin-left: auto; text-align: center;" name="repeater[{{$index}}][do_qty]" class="form-control count" data-index="{{$index}}" value="{{$row->qty}}" step="any" min="0" max="{{$row->qty}}">
                        </td>
                        <td>{{$row->packaging_txt()->scalar ?? ''}}</td>
                        <td class="text-right">
                          <span class="price-item">Rp750.000</span>
                        </td>
                        <td class="text-right">
                          <span class="invoice-disc_cash">{{ __('$') }}</span>
                        </td>
                        <td class="text-right">
                          <span class="invoice-subtotal-item">Rp3.750.000</span>
                        </td>
                        <td class="text-right">
                          <span class="invoice-subtotal-item">Rp3.600.000</span>
                        </td>
                      </tr>
                  @endforeach
                @endif
              </tbody>
              <tfoot>
                <tr class="row-footer-subtotal">
                  <td colspan="11" class="text-right"><span><b>Subtotal</b></span></td>
                  <td class="text-right">
                    <strong><span class="invoice-subtotal-label">$0</span></strong>
                  </td>
                </tr>
                <tr class="row-footer-subtotal">
                  <td colspan="11" class="text-right"><span><b>Total Akhir</b></span></td>
                  <td class="text-right">
                    <strong><span class="invoice-subtotal-label">$0</span></strong>
                  </td>
                </tr>
              </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
  $(document).ready(function () {
    $('#tableDetailPesanan').DataTable({
        scrollY: '430px',
        scrollCollapse: true,
        paging: false,
        bFilter: false,
        "aoColumnDefs": [
             { "bSortable": false, "aTargets": [ 1, 4, 5, 6, 7, 8, 9, 10, 11 ] }
        ] 
    });

    $('.js-select2').select2();
});
</script>
@endpush