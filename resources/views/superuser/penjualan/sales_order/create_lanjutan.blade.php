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
                      <input type="number" name="idr_rate" style="font-size: 9pt;" class="form-control text-right" step="any">
                    </div>
                  </div>
                  @endif
              </div>
              <div class="col-sm">
                @if($step == 2)
                  <div class="form-group row">
                    <label style="font-size: 10pt;" class="col-md-4 col-form-label text-right">Disc Cash</label>
                      <div class="col-4">
                      <select class="base_disc form-control formatRupiah js-select2" onchange="discountOnChange()">
                        <option value=""></option>
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
                    <!-- <input type="hidden" name="repeater[{{$index}}][product_id]" value="{{$row->product_id}}">
                    <input type="hidden" name="repeater[{{$index}}][so_qty]" value="{{$row->qty}}">
                    <input type="hidden" name="repeater[{{$index}}][so_item_id]" value="{{$row->id}}">
                    <input type="hidden" name="repeater[{{$index}}][packaging]" class="form-control" readonly value="{{$row->packaging_txt()->scalar ?? ''}}">
                    <input type="hidden" name="repeater[{{$index}}][price]" class="form-control" readonly value="{{$row->product->selling_price ?? 0}}"> -->
                      <tr class="index{{$index}}" data-index="{{$index}}">
                        <td><input type="checkbox" name="checkProductList" step="any"></td>
                        <td>{{$index + 1}}</td>
                        <td class="product">{{$row->product->code ?? ''}}</td>
                        <td class="product">{{$row->product->name ?? ''}}</td>
                        <td>{{ __('$') }} {{ number_format($row->product->selling_price, 0) }}</td>
                        <td>{{ $row->qty }}</td>
                        <td>
                          <input type="text" style="width: 50px;  margin-right: auto; margin-left: auto; text-align: center;" name="repeater[{{$index}}][do_qty]" class="form-control count" data-index="{{$index}}" value="{{$row->qty}}" step="any" min="0" max="{{$row->qty}}">
                        </td>
                        <td>{{$row->packaging_txt()->scalar ?? ''}}</td>
                        <td class="text-right">
                          <span class="price-item">Rp750.000</span>
                        </td>
                        <td class="text-right">
                          <input type="text" name="do_details[{{$index}}][usd_disc]" value="{{$row->usd_disc}}"  class="form-control formatRupiah do-detail-disc-usd" data-id="{{$row->product->id}}" onchange="discountOnChange({{$row->id}})" />
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
<script>
  var ekspedisiList = [<?php
    if(isset($ekspedisi) && sizeof($ekspedisi) > 0) {
      $index = 0;
      foreach($ekspedisi as $index => $row) {
        echo $row;

        if ($index < sizeof($ekspedisi)) {
          echo ',';
        }

        $index++;
      }
    }
  ?>];

  $(function(){
    $('button[type="submit"]').removeAttr('disabled');

    summernote = $('.summernote').length;
    if(summernote > 0){
      $('.summernote').summernote({
          toolbar: [
             ['style', ['style']],
               ['font', ['bold', 'italic', 'underline', 'clear']],
               ['fontname', ['fontname']],
               ['color', ['color']],
               ['para', ['ul', 'ol', 'paragraph']],
          ],
      });
    }

    $('.js-select2').select2();

    customer_other_address('{{$result->customer_id}}','{{$result->customer_other_address_id}}');
   
    $(document).on('change','.count',function(){
        total();
    })
    $(document).on('keyup','.count',function(){
        total();
    })

    $(document).on('click','.checkbox_ppn',function(){
        if($(this).is(":checked")){
          $('input[name="ppn"]').val(10);
          total();
        }
        else{  
          $('input[name="ppn"]').val(0);
        }

    })

    

    $(document).on('change','select[name="ekspedisi_id"]',function(){
      let ekspedisi_id = $('select[name="ekspedisi_id"]').val();
      if (ekspedisi_id == "") {
        $('input[name="delivery_cost_note"]').val("");
      } else {
        const ekspedisi = ekspedisiList.find(eks => eks.id == ekspedisi_id);
        $('input[name="delivery_cost_note"]').val(ekspedisi.name);
      }
    })

    $(document).on('keyup','input[name="delivery_cost_idr"]',function(){
      let delivery_cost_idr = $('input[name="delivery_cost_idr"]').val();
      delivery_cost_idr = parseFloat(delivery_cost_idr.split('.').join(''));
      $('input[name="perkiraan_ongkir_view"]').val(formatRupiah(delivery_cost_idr));
    })

    $(document).on('click','.btn-delete',function(){
      let id = $(this).data('id');
      $('#frmDestroyItem').find('input[name="id"]').val(id);
      if(confirm("Apakah anda yakin ingin menghapus item ini ?")){
        $('#frmDestroyItem').submit();
      }
    })

    $(document).on('change','.select-other-address',function(){
      let val = $(this).val();
      if(val != ""){
        customer_other_detail(val,0);
      }else{
        $('textarea[name="delivery_address"]').val("");
      }
    })

    $(document).on('keyup','.formatRupiah',function(){
      let val = $(this).val();
      $(this).val(formatRupiah(val));
    })

    $(document).on('submit','#frmUpdateNew',function(e){
      e.preventDefault();
      if(confirm("Apakah anda yakin ingin menyimpan data ini?")){
        let _form = $('#frmUpdateNew');
        $.ajax({
          url : '{{route('superuser.penjualan.packing_order.update_new')}}',
          method : "POST",
          data : getFormData(_form),
          dataType : "JSON",
          beforeSend : function(){
            $('#frmUpdateDataPemesan').find('button[type="submit"]').html('Loading...');
          },
          success : function(resp){
            if(resp.IsError == true){
              showToast('danger',resp.Message);
            }
            else{
              Swal.fire(
                'Success!',
                resp.Message,
                'success'
              ).then((result) => {
                location.reload();
              })
            }
          },
          error : function(){
            alert('Cek Koneksi Internet');
          },
          complete : function(){
            $('#frmUpdateNew').find('button[type="submit"]').html('<i class="fa fa-save"> Save</i>');
          }
        })
      }
    })

    $(document).on('submit','#frmUpdateDataPemesan',function(e){
      e.preventDefault();
      if(confirm("Apakah anda yakin ingin mengubah data pemesan ?")){
        let _form = $('#frmUpdateDataPemesan');
        $.ajax({
          url : '{{route('superuser.penjualan.packing_order.update')}}',
          method : "POST",
          data : getFormData(_form),
          dataType : "JSON",
          beforeSend : function(){
            $('#frmUpdateDataPemesan').find('button[type="submit"]').html('Loading...');
          },
          success : function(resp){
            if(resp.IsError == true){
              showToast('danger',resp.Message);
            }
            else{
              Swal.fire(
                'Success!',
                resp.Message,
                'success'
              ).then((result) => {
                  location.reload();
              })
            }
          },
          error : function(){
            alert('Cek Koneksi Internet');
          },
          complete : function(){
            $('#frmUpdateDataPemesan').find('button[type="submit"]').html('<i class="fa fa-save"> Save</i>');
          }
        })
      }
    })

    $(document).on('submit','#frmSimpanCost',function(e){
      e.preventDefault();
      total();
      if(confirm("Apakah anda yakin ingin menyimpan rincian cost packing order ?")){
        let _form = $('#frmSimpanCost');
        total();
        $.ajax({
          url : '{{route('superuser.penjualan.packing_order.update_cost')}}',
          method : "POST",
          data : getFormData(_form),
          dataType : "JSON",
          beforeSend : function(){
            $('#frmSimpanCost').find('button[type="submit"]').html('Loading...');
          },
          success : function(resp){
            if(resp.IsError == true){
              showToast('danger',resp.Message);
            }
            else{
              Swal.fire(
                'Success!',
                resp.Message,
                'success'
              ).then((result) => {
                  location.reload();
              })
            }
          },
          error : function(){
            alert('Cek Koneksi Internet');
          },
          complete : function(){
            $('#frmSimpanCost').find('button[type="submit"]').html('<i class="fa fa-save"> Save</i>');
          }
        })
      }
    })
  })

  function customer_other_address(customer_id,selected=0){
    ajaxcsrfscript();
    $.ajax({
      url : '{{route('superuser.penjualan.packing_order.ajax_customer_other_address')}}',
      method : "POST",
      data : {customer_id:customer_id},
      dataType : "JSON",
      success : function(resp){
        if(resp.IsError == true){
          showToast('danger',resp.Message);
        }
        else{
          let option = '<option value="">{{$result->customer->name}}</option>';
          $.each(resp.Data,function(i,e){
            if(selected != 0){
              option += '<option value="'+e.id+'" selected>'+e.name+'</option>';
            }
            else{
              option += '<option value="'+e.id+'">'+e.name+'</option>';
            }
          })
          $('.select-other-address').html(option);
        }
      },
      error : function(){
        alert('Cek Koneksi Internet');
      },
    })
  }
  function customer_other_detail(id,selected=0){
    ajaxcsrfscript();
    $.ajax({
      url : '{{route('superuser.penjualan.packing_order.ajax_customer_other_address_detail')}}',
      method : "POST",
      data : {id:id},
      dataType : "JSON",
      success : function(resp){
        if(resp.IsError == true){
          showToast('danger',resp.Message);
        }
        else{
          $('textarea[name="delivery_address"]').val(resp.Data.address);
        }
      },
      error : function(){
        alert('Cek Koneksi Internet');
      },
    })
  }
  function total(){
    let idr_sub_total = $('input[name="idr_sub_total"]').val();
    let discount_1 = parseFloat($('input[name="discount_1"]').val());
    let discount_2 = parseFloat($('input[name="discount_2"]').val());
    let discount_idr = $('input[name="discount_idr"]').val();
    let voucher_idr = $('input[name="voucher_idr"]').val();
    let cashback_idr = $('input[name="cashback_idr"]').val();
    let delivery_cost_idr = $('input[name="delivery_cost_idr"]').val();
    let other_cost_idr = $('input[name="other_cost_idr"]').val();
    let sub_total_discount = 0;
    let sub_ppn = 0;
    let sub_purchase_total = 0;
    let grand_total_idr = 0;


    idr_sub_total = parseFloat(idr_sub_total.split('.').join(''));
    //discount_idr = parseFloat(discount_idr.split('.').join(''));
    voucher_idr = parseFloat(voucher_idr.split('.').join(''));
    cashback_idr = parseFloat(cashback_idr.split('.').join(''));
    delivery_cost_idr = parseFloat(delivery_cost_idr.split('.').join(''));
    //other_cost_idr = parseFloat(other_cost_idr.split('.').join(''));

    idr_sub_total = (isNaN(idr_sub_total)) ? 0 : idr_sub_total;
    discount_1 = (isNaN(discount_1)) ? 0 : discount_1 / 100;
    discount_2 = (isNaN(discount_2)) ? 0 : discount_2 / 100;
    //discount_idr = (isNaN(discount_idr)) ? 0 : discount_idr;
    voucher_idr = (isNaN(voucher_idr)) ? 0 : voucher_idr;
    cashback_idr = (isNaN(cashback_idr)) ? 0 : cashback_idr;
    delivery_cost_idr = (isNaN(delivery_cost_idr)) ? 0 : delivery_cost_idr;
    other_cost_idr = (isNaN(other_cost_idr)) ? 0 : other_cost_idr;

    sub_total_discount = Math.ceil((idr_sub_total * discount_1) + ((idr_sub_total - (idr_sub_total * discount_1)) * discount_2));
    //sub_total_discount = Math.ceil((idr_sub_total * discount_1) + ((idr_sub_total - (idr_sub_total * discount_1)) * discount_2) + discount_idr);

    if($('.checkbox_ppn').is(":checked")){
      sub_ppn = Math.ceil((idr_sub_total - sub_total_discount) * 10/100);
    }
    else{
      sub_ppn = 0;
    }

    sub_purchase_total= Math.ceil(idr_sub_total - sub_total_discount - voucher_idr - cashback_idr + sub_ppn);
    grand_total_idr = Math.ceil(sub_purchase_total + delivery_cost_idr);
    //grand_total_idr = Math.ceil(sub_purchase_total + delivery_cost_idr + other_cost_idr);


    if(sub_total_discount < 0){
      sub_total_discount = 0;
    }
    if(sub_ppn < 0){
      sub_ppn = 0;
    }
    if(sub_purchase_total < 0){
      sub_purchase_total = 0;
    }
    if(grand_total_idr < 0){
      grand_total_idr = 0;
    }
    
    //$('.total_discount_cash_idr').html(formatRupiah(idr_sub_total - sub_total_discount));
    $('input[name="discount_total"]').val(formatRupiah(sub_total_discount)); 
    $('input[name="ppn"]').val(formatRupiah(sub_ppn));
    $('.purchase_total_idr').html(formatRupiah(idr_sub_total - sub_total_discount));
    $('input[name="grand_total_idr"]').val(formatRupiah(grand_total_idr));
  }
  /* Fungsi formatRupiah */
  function formatRupiah(angka, prefix){
    angka = angka.toString();
    var number_string = angka.replace(/[^,\d]/g, '').toString(),
    split       = number_string.split(','),
    sisa        = split[0].length % 3,
    rupiah        = split[0].substr(0, sisa),
    ribuan        = split[0].substr(sisa).match(/\d{3}/gi);
   
    // tambahkan titik jika yang di input sudah menjadi angka ribuan
    if(ribuan){
      separator = sisa ? '.' : '';
      rupiah += separator + ribuan.join('.');
    }
   
    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
  }

  function changeStep(stepNumber) {
    $(".wizard .step").removeClass('active');
    $(".wizard .step-container").removeClass('active');

    $("#step" + stepNumber).addClass('active');
    $("#step" + stepNumber + "Container").addClass('active');
    
    $('.js-select2').select2();
    total();
  }

  function discountOnChange() {
    const discUsd = $("select.base_disc").val();
    const allDisc = $("input.do-detail-disc-usd");
    let totalDisc = 0;
    for(let i = 0; i < allDisc.length; i++) {
      $(allDisc[i]).val(discUsd);
      setDoDetailTotal($(allDisc[i]).data('id'));
    }
    updateIdrSubTotal();
  }

  function setDoDetailTotal(id) {
    const qty = $("td.do-detail-qty[data-id='" + id + "']")[0].innerHTML;
    const price = $("span.do-detail-price[data-id='" + id + "']")[0].innerHTML;
    const discUsd = $("input.do-detail-disc-usd[data-id='" + id + "']").val();

    $("span.do-detail-total[data-id='" + id + "']")[0].innerHTML = (price - (discUsd != null ? discUsd : 0)) * qty;
  }

  function updateIdrSubTotal() {
    const idr_sub_total = parseFloat(<?= $idr_sub_total ?>);

    const allQty = $("td.do-detail-qty");
    const allPrice = $("span.do-detail-price");
    let totalBeforeDisc = 0;
    for(let i = 0; i < allPrice.length; i++) {
      totalBeforeDisc += parseFloat(allQty[i].innerHTML) * parseFloat(allPrice[i].innerHTML);
    }
    let idrRate = $('input[name="idr_rate"]').val();
    idrRate = parseFloat(idrRate.split('.').join(''));
    $('input[name="idr_total_before_discount"]').val(formatRupiah(totalBeforeDisc * idrRate)); // ini total sebelom diskon

    const allTotal = $("span.do-detail-total");
    let totalAfterDisc = 0;
    for(let i = 0; i < allTotal.length; i++) {
      totalAfterDisc += parseFloat(allTotal[i].innerHTML);
    }
    $('input[name="idr_sub_total"]').val(formatRupiah(totalAfterDisc * idrRate)); // ini total setelah diskon

    const allDisc = $("input.do-detail-disc-usd");
    let totalDisc = 0;
    for(let i = 0; i < allDisc.length; i++) {
      totalDisc += parseFloat(allQty[i].innerHTML) * parseFloat($(allDisc[i]).val());
    }

    $("div.total_discount_cash_idr")[0].innerHTML = formatRupiah(totalDisc * idrRate);
    total();
  }
  updateIdrSubTotal();
  
</script>
@endpush