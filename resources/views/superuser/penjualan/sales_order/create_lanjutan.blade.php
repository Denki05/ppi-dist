<?php
  $sub_total = 0;
  $idr_sub_total = 0;
?>
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
                      <input type="number" id="idr_rate" name="idr_rate" style="font-size: 9pt;" class="form-control text-right" step="any">
                    </div>
                  </div>
                  @endif
              </div>
              <div class="col-sm">
                @if($step == 2)
                  <div class="form-group row">
                    <label style="font-size: 10pt;" class="col-md-4 col-form-label text-right">Disc Cash</label>
                      <div class="col-4">
                        <select name="base_disc" id="base_disc" class="form-control js-select2">
                            <option></option>
                            <option data-discount="2">$2</option>
                            <option data-discount="4">$4</option>
                        </select>
                      </div>
                  </div>
                @endif
              </div>
              <!-- <div class="col-sm">
                <div>
                    <label for="price">Harga</label>
                    <input type="text" name="price" readonly />
                    <br>
                    <label for="price">Discount</label>
                    <input type="text" name="discount" readonly />
                    <br>
                    <h4>Total: <span id="total">0</span></h4>
                </div>
              </div> -->
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
                    <input type="number"  name="idr_rate" class="form-control text-center" step="any">
                  </div>
                </div>
                @endif
              </div>
              <div class="col">
                @if($step == 2)
                <div class="form-group row">
                  <label class="col-md-4 col-form-label text-right">Voucher</label>
                  <div class="col-md-6">
                    <input type="number" name="idr_rate" class="form-control text-center" step="any">
                  </div>
                </div>
                @endif
              </div>
              <div class="col">
                @if($step == 2)
                <div class="form-group row">
                  <label class="col-md-4 col-form-label text-right">Subtotal</label>
                  <div class="col-md-6">
                    <input type="number" name="idr_rate" class="form-control text-center" step="any" readonly>
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
                    <input type="number" name="idr_rate" class="form-control text-center" step="any">
                  </div>
                </div>
                @endif
              </div>
              <div class="col">
                @if($step == 2)
                <div class="form-group row">
                  <label class="col-md-4 col-form-label text-right">Ongkir</label>
                  <div class="col-md-6">
                    <input type="number" name="idr_rate" class="form-control text-center" step="any">
                  </div>
                </div>
                @endif
              </div>
              <div class="col">
                @if($step == 2)
                <div class="form-group row">
                  <label class="col-md-4 col-form-label text-right">Grand Total</label>
                  <div class="col-md-6">
                    <input type="number" name="idr_rate" class="form-control text-center" step="any" readonly>
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
                    <input type="number" name="idr_rate" class="form-control text-center" step="any">
                  </div>
                </div>
                @endif
              </div>
              <div class="col-4">
                @if($step == 2)
                <div class="form-group row">
                  <label class="col-md-4 col-form-label text-right">Resi Ongkir</label>
                  <div class="col-md-6">
                    <input type="number" name="idr_rate" class="form-control text-center" step="any">
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
    <form method="POST" action="">  
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
                        <?php
                          $sub_total += floatval($row->total) ?? 0;
                          $idr_sub_total += ceil((($row->price * $result->idr_rate) * $row->qty) - ($row->total_disc * $result->idr_rate)); 
                        ?>
                        <tr>
                          <td>
                            <input type="checkbox" name="checkProductList" step="any">
                          </td>
                          <td>
                            {{$index + 1}}
                          </td>
                          <td>
                            <span>{{ $row->product['code'] }}</span>
                          </td>
                          <td>
                            <span>{{ $row->product['name'] }}</span>
                          </td>
                          <td>
                            <span class="item-price" name="item-price">{{ $row->product->selling_price }}</span>
                          </td>
                          <td>
                            <span class="item-qty" name="item-qty">{{ $row->qty }}</span>
                          </td>
                          <td>
                            <input type="text" style="width: 50px;  margin-right: auto; margin-left: auto; text-align: center;" class="form-control in_stock" value="{{ $row->qty }}"></input>
                          </td>
                          <td>
                            {{$row->packaging_txt()->scalar ?? ''}}
                          </td>
                          <td>
                            <span class="price_before_disc" name="price_before_disc"></span>
                          </td>
                          <td>
                            <input type="text" name="disc-cash" class="form-control" style="width: 50px;  margin-right: auto; margin-left: auto; text-align: center;" readonly />
                          </td>
                          <td>
                            <span class="price_after_disc"></span>
                          </td>
                          <td>
                            <span class="subtotal_item"></span>
                          </td>
                        </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot>
                  <tr class="row-footer-subtotal">
                    <td colspan="11" class="text-right"><span><b>Subtotal</b></span></td>
                    <td class="text-right">
                      <strong><span class="invoice-subtotal-label"></span></strong>
                    </td>
                  </tr>
                  <tr class="row-footer-subtotal">
                    <td colspan="11" class="text-right"><span><b>Total Akhir</b></span></td>
                    <td class="text-right">
                      <strong><span class="invoice-subtotal-label"></span></strong>
                    </td>
                  </tr>
                </tfoot>
            </table>
          </div>
        </div>
      </div>
    </form>
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

    $('#base_disc').on('change', function(){
      // ambil data dari elemen option yang dipilih
      const disc = $('#base_disc option:selected').data('discount');
      // tampilkan data ke element
      $('[name=disc-cash]').val(disc);
    });

    
  })
</script>
@endpush