@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sales</span>
  <span class="breadcrumb-item">Sale Order PPN</span>
  <span class="breadcrumb-item active">Create</span>
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

    <div class="row">
      <div class="col-md-4">
        <div class="card mb-2 border-0">
          <div class="card-body">
            <div class="row">
              <div class="col">
                <div class="form-label-group in-border">
                  <label>Customer</label>
                  <select class="form-control js-select2 select-customer" name="customer_other_address_id">
                    <option value="">Pilih Customer</option>
                    @foreach ($member as $key => $row)
                    <option value="{{$row->id}}">{{ $row->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col">
                <div class="form-label-group in-border">
                  <label>Address</label>
                  <textarea class="form-control" name="customer-address" value="" rows="1" readonly></textarea>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col">
                <div class="form-label-group in-border">
                  <label>Sales Senior</label>
                  <select class="form-control js-select2 select-customer" name="sales_senior_id">
                    <option value="">Pilih Sales Senior</option>
                    @foreach ($sales as $key => $row)
                    <option value="{{$row->id}}">{{ $row->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col">
                <div class="form-label-group in-border">
                  <label>Sales</label>
                  <select class="form-control js-select2 select-customer" name="sales_id">
                    <option value="">Pilih Sales</option>
                    @foreach ($sales as $key => $row)
                    <option value="{{$row->id}}">{{ $row->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col">
                <div class="form-label-group in-border">
                  <label>Transaksi</label>
                  <select class="form-control js-select2 select-customer" name="type_transaction">
                    <option value="">Pilih type transaksi</option>
                    <option value="1">Cash</option>
                    <option value="2">Tempo</option>
                  </select>
                </div>
              </div>
              <div class="col">
                <div class="form-label-group in-border">
                  <label>Note</label>
                  <textarea class="form-control" name="note" rows="1"></textarea>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="card mb-2 border-0">
          <div class="card-body" >
                    <div class="form-group row">
                      <label class="col-md-3 col-form-label text-right">Ekspedisi</label>
                      <div class="col-md-6">
                        <select class="form-control js-select2" name="ekspedisi">
                          <option value="">Pilih Ekspedisi</option>
                          @foreach($ekspedisi as $index)
                          <option value="{{ $index->id }}">{{ $index->name }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-2">
                        <input type="checkbox" class="form-check-input" value="1" id="shipping_cost_buyer" name="shipping_cost_buyer">
                        <label>Bayar ditempat</label>
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
                  
                    <div class="form-group row">
                      <label style="font-size: 10pt;" class="col-md-4 col-form-label text-right">Gudang<span class="text-danger">*</span></label>
                        <div class="col-8">
                          <select class="form-control js-select2" style="font-size: 9pt;" name="origin_warehouse_id">
                            <option value="">Pilih Gudang</option>
                            @foreach($warehouse as $index => $row)
                            <option style="font-size: 10pt;" value="{{$row->id}}">{{$row->name}}</option>
                            @endforeach
                          </select>
                        </div>
                    </div>
                    
                </div>
                <div class="col-sm">
                  
                    <div class="form-group row">
                      <label class="col-md-4 col-form-label text-right" style="font-size: 10pt;">Kurs<span class="text-danger">*</span></label>
                      <div class="col-5">
                      <input type="text" name="idr_rate" id="idr_rate"  class="form-control" value="">
                      </div>
                    </div>
                    
                </div>
                <div class="col-sm">
                  
                    <div class="form-group row">
                      <label style="font-size: 10pt;" class="col-md-4 col-form-label text-right">Disc Cash</label>
                        <div class="col-5">
                          <select class="form-control js-select2 base_disc" id="base_id">
                              <option value="0">0</option>
                              <option value="2">$2</option>
                              <option value="4">$4</option>
                          </select>
                        </div>
                    </div>
                  
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
                  <div class="form-group row">
                    <label class="col-md-4 col-form-label text-right">Disc %</label>
                    <div class="col-md-3">
                      <input type="text" name="disc_agen_percent" id="disc_agen_percent" class="form-control text-center disc_agen_percent" value="0" step="any">
                    </div>
                    <div class="col-md-5">
                      <input type="text" name="disc_amount2_idr" id="disc_amount2_idr" class="form-control disc_amount2_idr text-center" readonly>
                    </div>
                  </div>
                </div>
                <div class="col">
                  
                  
                  <div class="form-group row">
                      <label class="col-md-4 col-form-label text-right">Disc IDR</label>
                      <div class="col-md-6">
                        <input type="text" name="disc_idr" id="disc_idr" class="form-control disc_idr " step="any">
                      </div>
                    </div>
                  
                </div>
                <div class="col">
                  
                  <div class="form-group row">
                    <label class="col-md-4 col-form-label text-right">Subtotal</label>
                    <div class="col-md-6">
                      <input type="text" id="subtotal_2" name="subtotal_2" class="form-control text-center subtotal_2" step="any" readonly>
                    </div>
                  </div>
                  
                </div>
              </div>

              <div class="row">
                <div class="col">
                  
                    <div class="form-group row">
                      <label class="col-md-4 col-form-label text-right">Disc Kemasan %</label>
                      <div class="col-md-3">
                        <input type="text" name="disc_tambahan" id="disc_tambahan" class="form-control disc_tambahan text-center" value="0" step="any">
                      </div>
                      <div class="col-md-5">
                        <input type="text" name="disc_kemasan_idr" id="disc_kemasan_idr" class="form-control disc_kemasan_idr text-center" readonly>
                      </div>
                    </div>
                    
                </div>
                <div class="col">

                  <div class="form-group row">
                    <label class="col-md-4 col-form-label text-right">Voucher</label>
                    <div class="col-md-6">
                      <input type="text" name="voucher_idr" id="voucher_idr" class="form-control count voucher_idr ">
                    </div>
                  </div>
                    
                </div>
                <div class="col">
                  
                  <div class="form-group row">
                    <label class="col-md-4 col-form-label text-right">Grand Total</label>
                    <div class="col-md-6">
                      <input type="text" name="grand_total_final"  id="grand_total_final" class="form-control text-center grand_total_final" step="any" readonly>
                    </div>
                  </div>
                  
                </div>
              </div>

              <div class="row">
                <div class="col">
                  <div class="form-group row">
                    <label class="col-md-4 col-form-label text-right">PPN</label>
                    <div class="col-md-3">
                      <input type="text" name="disc_agen_percent" id="tax_amount_percent" class="form-control text-center tax_amount_percent" value="0" step="any">
                    </div>
                    <div class="col-md-5">
                      <input type="text" name="disc_amount2_idr" id="tax_amount_idr" class="form-control tax_amount_idr text-center" readonly>
                    </div>
                  </div>
                </div>

                <div class="col-4">
                  <div class="form-group row">
                    <label class="col-md-4 col-form-label text-right">Ongkir</label>
                    <div class="col-md-6">
                      <input type="text" name="delivery_cost_idr" id="delivery_cost_idr" class="form-control delivery_cost_idr ">
                    </div>
                  </div>
                </div>
                
                <div class="col-4">
                  <button type="button" class="btn btn-danger button_cal" id="button_cal"><i class="fas fa-calculator pr-2" aria-hidden="true"></i>Calculate</button>
                  <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

@endsection

@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(function(){
    $('.js-select2').select2();

    $(document).on('change','.select-customer',function(){
      let val = $(this).val();
      if(val != ""){
        customer_address(val);
      }else{
        $('textarea[name="customer-address"]').val("");
      }
    })

    function customer_address(id){
        ajaxcsrfscript();
        $.ajax({
        url : '{{route('superuser.penjualan.sales_order_ppn.ajax_customer_detail')}}',
        method : "POST",
        data : {id:id},
        dataType : "JSON",
        success : function(resp){
            if(resp.IsError == true){
            showToast('danger',resp.Message);
            }
            else{
            $('textarea[name="customer-address"]').val(resp.Data.address);
            }
        },
        error : function(){
            alert('Cek Koneksi Internet');
        },
        })
    }
  });
</script>
@endpush
