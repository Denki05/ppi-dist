@foreach($result->do_detail as $index => $row)
  @php $row->product->name @endphp
@endforeach

@extends('superuser.app')

@section('content')
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

<form class="ajax" data-action="{{ route('superuser.penjualan.delivery_order.do_update') }}" data-type="POST" enctype="multipart/form-data">
  @csrf
  <input type="hidden" name="id" value="{{$result->id}}">
  <input type="hidden" name="cost_id" value="{{$result->do_cost->id ?? 0}}">

  <div class="block">
        <div class="block-header block-header-default">
            <h2 class="block-title">#UPDATE <span class="badge badge-warning">{{ $result->do_code }}</span></h2>
        </div>
        <div class="block-content">
            <div class="row">
                <div class="col-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-md-4 col-form-label text-right">DO Code</label>
                                        <div class="col-md-6">
                                            <div class="form-control-plaintext">{{ $result->do_code }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-md-4 col-form-label text-right">SO Code</label>
                                        <div class="col-md-6">
                                            <div class="form-control-plaintext">{{ $result->so->code }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-md-4 col-form-label text-right">Transaksi Type</label>
                                        <div class="col-md-6">
                                            <div class="form-control-plaintext"><span class="badge badge-info">{{$result->do_type_transaction()->scalar ?? ''}}</span></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                    <label class="col-md-4 col-form-label text-right">Tanggal Buat</label>
                                        <div class="col-md-6">
                                            <div class="form-control-plaintext">{{ date_format($result->created_at, 'Y-m-d ') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-md-4 col-form-label text-right">Customer</label>
                                        <div class="col-md-6">
                                            <div class="form-control-plaintext">{{ $result->member->name }}
                                                <input type="hidden" name="customer_other_address_id" value="{{ $result->member->id }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-4 col-form-label text-right">Phone</label>
                                        <div class="col-md-6">
                                            <div class="form-control-plaintext">{{ $result->member->phone }}</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-md-4 col-form-label text-right">Alamat</label>
                                        <div class="col-md-6">
                                            <div class="form-control-plaintext">{{ $result->member->address }}</div>
                                        </div>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br>
    </div>

    <hr>

    <div class="block">
        <div class="block-header block-header-default">
            <h2 class="block-title">#DISCOUNT</h2>
        </div>
        <div class="block-content">
            <div class="row">
            <div class="card mb-2 border-0">
                <div class="card-body">
                <div class="row">
                    <div class="col">
                    <div class="form-group row">
                                <label style="font-size: 10pt;" class="col-md-4 col-form-label text-right">Gudang<span class="text-danger">*</span></label>
                                <div class="col-8">
                                <select class="form-control js-select2" style="font-size: 9pt;" name="warehouse_id">
                                    <option value="">Pilih Gudang</option>
                                    @foreach($warehouse as $index => $row)
                                    <option value="{{ $row->id }}" @if($result->warehouse_id == $row->id) selected @endif>{{ $row->name }}</option>
                                    @endforeach
                                </select>
                                </div>
                            </div>
                    <div class="form-group row">
                        <label class="col-md-4 col-form-label text-right">Disc %</label>
                        <div class="col-md-3">
                        <input type="text" name="disc_agen_percent" id="disc_agen_percent" class="form-control text-center disc_agen_percent" value="{{ $result->do_cost->discount_1 }}">
                        </div>
                        <div class="col-md-5">
                        <input type="text" name="disc_amount2_idr" id="disc_amount2_idr" class="form-control disc_amount2_idr text-center" readonly>
                        </div>
                    </div>
                    </div>
                    <div class="col">
                    <div class="form-group row">
                                <label class="col-md-4 col-form-label text-right" style="font-size: 10pt;">Kurs<span class="text-danger">*</span></label>
                                <div class="col-4">
                                    <input type="text" name="idr_rate" id="idr_rate"  class="form-control" value="{{ $result->idr_rate }}">
                                </div>
                            </div>
                    <div class="form-group row">
                        <label class="col-md-4 col-form-label text-right">Voucher</label>
                        <div class="col-md-6">
                        <input type="text" name="voucher_idr" id="voucher_idr" class="form-control count voucher_idr" value="{{ $result->do_cost->voucher_idr }}">
                        </div>
                    </div>
                    </div>
                    <div class="col">
                    <div class="form-group row">
                                <label style="font-size: 10pt;" class="col-md-4 col-form-label text-right">Disc Cash</label>
                                <div class="col-3">
                                <select class="form-control js-select2 base_disc" id="base_id">
                                    <option value="0">0</option>
                                    <option value="2">$2</option>
                                    <option value="4">$4</option>
                                </select>
                                </div>
                            </div>
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
                        <label class="col-md-4 col-form-label text-right">Disc Kemasan</label>
                        <div class="col-md-3">
                            <input type="text" name="disc_tambahan" id="disc_tambahan" class="form-control disc_tambahan text-center" value="{{ $result->do_cost->discount_2 }}" step="any">
                        </div>
                        <div class="col-md-5">
                            <input type="text" name="disc_kemasan_idr" id="disc_kemasan_idr" class="form-control disc_kemasan_idr text-center" readonly>
                        </div>
                        </div>
                    </div>
                    <div class="col">
                    <div class="form-group row">
                        <label class="col-md-4 col-form-label text-right">Ongkir</label>
                        <div class="col-md-6">
                        <input type="text" name="delivery_cost_idr" id="delivery_cost_idr" class="form-control delivery_cost_idr" value="{{ $result->do_cost->delivery_cost_idr }}">
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
                    <div class="col-4">
                    <div class="form-group row">
                        <label class="col-md-4 col-form-label text-right">Disc IDR</label>
                        <div class="col-md-6">
                        <input type="text" name="disc_idr" id="disc_idr" class="form-control disc_idr " step="any" value="{{ $result->do_cost->discount_idr }}">
                        </div>
                    </div>
                    </div>
                    <div class="col-4">
                    <div class="form-group row">
                        <label class="col-md-4 col-form-label text-right">Resi Ongkir</label>
                        <div class="col-md-6">
                        <input type="number" name="resi_ongkir" id="resi_ongkir" value="0" class="form-control text-center " step="any" readonly value="{{ $result->do_cost->other_cost_idr }}">
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

    <div class="block">
        <div class="block-header block-header-default">
            <h2 class="block-title">#DO ITEM</h2>
        </div>
        <div class="block-content">
            <div class="row">
                <div class="col-12">
                <div class="card mb-2 border-0">
                    <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                        <th>#</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Packaging</th>
                        <th>Disc (USD)</th>
                        <th>Total</th>
                        <th>Action</th>
                        </thead>
                        <tbody>
                        @if(count($result->do_detail) <= 0)
                            <tr>
                            <td colspan="13" align="center">Data tidak ditemukan</td>
                            </tr>
                        @endif
                        @if(count($result->do_detail) > 0)
                            @foreach($result->do_detail as $index => $detail)
                            <input type="hidden" name="repeater[{{$index}}][product_id]" value="{{$detail->product_id}}">
                            <input type="hidden" name="repeater[{{$index}}][so_item_id]" value="{{$detail->id}}">
                            <input type="hidden" name="repeater[{{$index}}][id]" value="{{$detail->id}}">
                            <tr class="index{{$index}}" data-index="{{$index}}">
                                <td width="5%">{{ $loop->iteration }}</td>
                                <td width="20%">{{ $detail->product->code }} - <b>{{ $detail->product->name }}</td>
                                <td width="5%">
                                    <input type="number" name="repeater[{{$index}}][do_qty]" class="form-control count" data-index="{{$index}}" value="{{$detail->qty}}" step="any" min="0" max="{{$detail->qty}}">
                                </td>
                                
                                <td width="5%">
                                    <input type="text" name="repeater[{{$index}}][price]" class="form-control" readonly value="{{$detail->product->selling_price ?? 0}}">
                                </td>
                                <td width="15%">
                                    <input type="text" name="repeater[{{$index}}][packaging]" class="form-control" readonly value="{{$detail->packaging_txt()->scalar ?? ''}}">
                                </td>
                                <td width="5%">
                                    <input type="text" name="repeater[{{$index}}][usd_disc]" class="form-control count count-disc" data-index="{{$index}}" step="any">
                                </td>
                                
                                <td width="20%">
                                    <input type="text" name="repeater[{{$index}}][total]" class="form-control" readonly>
                                </td>
                                <td width="5%">
                                    <button class="btn btn-danger btn-sm btn-flat btn-delete" data-id="{{$detail->id}}"><i class="fa fa-trash"></i></button>
                                </td>
                            </tr>
                            @endforeach
                        @endif
                        </tbody>
                        <tfoot>
                            <tr class="row-footer-subtotal">
                            <td colspan="6" class="text-right">
                                <b>Total Item</b>
                                <br><span class="text-danger">*Subtotal After Disc(USD)</span>
                            </td>
                            <td class="text-right">
                                <input type="text" name="sub_total_item" id="sub_total_item" class="form-control" readonly>
                            </td>
                            </tr>
                        </tfoot>
                    </table>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
</form>

<form id="frmDestroyItem" action="{{route('superuser.penjualan.packing_order.destroy_item')}}" method="POST">
  @csrf
  <input type="hidden" name="id">
</form>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
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

    $(function(){
      let global_total = 0 ;
      $('button[type="submit"]').removeAttr('disabled');

      $('.js-select2').select2();

      $(document).on('click','.btn-delete',function(){
        let id = $(this).data('id');
        $('#frmDestroyItem').find('input[name="id"]').val(id);
        if(confirm("Apakah anda yakin ingin menghapus item ini ?")){
            $('#frmDestroyItem').submit();
        }
      })

      $(document).on('change', '.base_disc'  ,function () {
        let val = $(this).val();
        // alert(val);
        $('.count-disc').val(val);
      });

      $(document).on('keyup','.count',function(){
        let index = $(this).attr('data-index');
        count_per_item(index);

      })

      function count_per_item(indx){
        let index = indx;
        let price = parseFloat($('tr.index'+index+'').find('input[name="repeater['+index+'][price]"]').val()); 
        let do_qty = parseFloat($('tr.index'+index+'').find('input[name="repeater['+index+'][do_qty]"]').val()); 
        let so_qty = parseFloat($('tr.index'+index+'').find('input[name="repeater['+index+'][so_qty]"]').val()); 
        let val_usd_disc = parseFloat($('tr.index'+index+'').find('input[name="repeater['+index+'][usd_disc]"]').val());
        let val_percent_disc = parseFloat($('tr.index'+index+'').find('input[name="repeater['+index+'][percent_disc]"]').val());
        let kurs = $('#idr_rate').val();

        // kurs = parseFloat(kurs.split('.').join(''));

        if(isNaN(val_usd_disc)){
          val_usd_disc = 0;
        }
        if(isNaN(val_percent_disc)){
          val_percent_disc = 0;
        }

        let total_disc = (val_usd_disc + ((price - val_usd_disc) * (val_percent_disc/100))) * do_qty;
        
        let sub_total  = parseFloat((do_qty * price) - total_disc) * kurs;

        if(isNaN(total_disc)){
          total_disc = 0;
        }

        if(isNaN(sub_total)){
          sub_total = 0;
        }

        $('tr.index'+index+'').find('input[name="repeater['+index+'][total_disc]"]').val(total_disc);
        $('tr.index'+index+'').find('input[name="repeater['+index+'][total]"]').val(sub_total);
        
        sub_total1();
      }

      // total item list
      function sub_total1(){
        let total = 0;
        $('tbody tr').each(function(index,e){
          let sub_total = $('tr.index'+index+'').find('input[name="repeater['+index+'][total]"]').val();
          sub_total = parseFloat(sub_total.split('.').join(''));
          // alert(kurs);

          sub_total = (isNaN(sub_total)) ? 0 : sub_total;
          Math.ceil(total += sub_total);
          
        }) ;

        $('input[name="sub_total_item"]').val(total);
      }

      // input disc % (agen)
      $(document).on('input', "#disc_agen_percent", function(e){
          if($(this).val() != ''){
              let sub_total_item = $('input[name="sub_total_item"]').val();

              sub_total_item = parseFloat(sub_total_item.split('.').join(''));
              let amount = parseFloat(sub_total_item) * parseFloat($(this).val()) / 100;
              $('input[name="disc_amount2_idr"]').val(amount);
          }else{
              $('input[name="disc_amount2_idr').val(0);
          }
          sub_total2();
      });


      // input disc kemasan
      $(document).on('input', "#disc_tambahan", function(e){
          if($(this).val() != ''){
              let sub_total_item = $('input[name="sub_total_item"]').val();
              let disc_percent = $('input[name="disc_amount2_idr"]').val();

              // sub_total_item = parseFloat(sub_total_item.split('.').join(''));
              // disc_percent = parseFloat(disc_percent.split('.').join(''));

              let subAfterDiscPercent = Math.ceil(sub_total_item - disc_percent);

              var amount = parseFloat(subAfterDiscPercent) * parseFloat($(this).val()) / 100;
              $('#disc_kemasan_idr').val(amount);
          }else{
              $('#disc_kemasan_idr').val(0);
          }
          sub_total2();
      });

      // $('#disc_amount2_idr').on('input', function(){
      //   sub_total2();
      // })

      // $('#disc_kemasan_idr').on('input', function(){
      //   sub_total2();
      // })

      // sub total disc agen & kemasan
      function sub_total2(){
        let sub_total_item = $('input[name="sub_total_item"]').val();
        let disc_agen = $('input[name="disc_amount2_idr"]').val();
        let disc_kemasan = $('input[name="disc_kemasan_idr"]').val();

        sub_total_item = (isNaN(sub_total_item)) ? 0 : sub_total_item;
        disc_agen = (isNaN(disc_agen)) ? 0 : disc_agen;
        disc_kemasan = (isNaN(disc_kemasan)) ? 0 : disc_kemasan;
        
        let subtotal_2 = Math.ceil((sub_total_item - disc_agen) - disc_kemasan);

        $('input[name="subtotal_2"]').val(subtotal_2);
      }

      // calculated button after input voucher - disc idr
      $(document).on('click', '#button_cal', function(e) {
        e.preventDefault();

        let subtotal = $('input[name="subtotal_2"]').val();
        let disc_idr = $('input[name="disc_idr"]').val();
        let voucher_idr = $('input[name="voucher_idr"]').val();
        let ongkir = $('input[name="delivery_cost_idr"]').val();
        let resi = $('input[name="resi_ongkir"]').val();
        // alert(resi_ongkir);

        subtotal = parseFloat(subtotal.split('.').join(''));
        disc_idr = parseFloat(disc_idr.split('.').join(''));
        voucher_idr = parseFloat(voucher_idr.split('.').join(''));
        ongkir = parseFloat(ongkir.split('.').join(''));
        resi = parseFloat(resi.split('.').join(''));

        disc_idr = (isNaN(disc_idr)) ? 0 : disc_idr;
        voucher_idr = (isNaN(voucher_idr)) ? 0 : voucher_idr;
        ongkir = (isNaN(ongkir)) ? 0 : ongkir;
        resi = (isNaN(resi)) ? 0 : resi;

        subFinal = Math.ceil(((subtotal - disc_idr) - voucher_idr) + ongkir);
        $('input[name="grand_total_final"]').val(subFinal);
      });
    });
  })
</script>
@endpush