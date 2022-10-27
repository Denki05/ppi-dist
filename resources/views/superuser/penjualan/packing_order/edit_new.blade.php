<?php
  $sub_total = 0;
  $idr_sub_total = 0;
?>
@extends('superuser.app')
@push('styles')
  <link rel="stylesheet" href="{{ asset('superuser_assets/css/page/packaging-order.css') }}">
  <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
@endpush

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.packing_order.index') }}">Packing Order</a>
  <span class="breadcrumb-item active">Edit Packing Order ({{$result->do_type_transaction()->scalar ?? ''}})</span>
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
  <div class="block-content wizard">
    <form id="frmUpdateNew" action="#">
    @csrf

      <input type="hidden" name="id" value="{{$result->id}}">
      <input type="hidden" name="cost_id" value="{{$result->do_cost->id ?? 0}}">
  
      <div class="row steps mb-3">
        <div class="col-4 text-center step {{$result->status == 1 ? 'active' : ''}}" id="step1">Konfirmasi SO</div>
        <div class="col-4 text-center step" id="step2">Detail Kiriman</div>
        <div class="col-4 text-center step {{$result->status == 2 ? 'active' : ''}}" id="step3">Finalisasi dan Cetak</div>
      </div>

      <div class="row step-container {{$result->status == 1 ? 'active' : ''}}" id="step1Container">
        <div class="col-12">

          <div class="form-group row">
            <label class="col-6 col-md-2 col-form-label font-weight-bold">SO Referensi</label>
            <div class="col-6 col-md-4 col-form-label">
              {{$result->do_detail[0]->so_item->so->code}}
            </div>
            
            <label class="col-6 col-md-2 col-form-label font-weight-bold">Packing Order</label>
            <div class="col-6 col-md-4 col-form-label">
              {{$result->code}}
            </div>
          </div>

          <div class="form-group row">
            <label class="col-6 col-md-2 col-form-label font-weight-bold">Gudang Asal</label>
            <div class="col-6 col-md-4 col-form-label">
              {{$result->warehouse->name}}
            </div>

            <label class="col-6 col-md-2 col-form-label font-weight-bold">Store / Member</label>
            <div class="col-6 col-md-4 col-form-label">
              {{$result->customer->name}}
            </div>
          </div>

          <div class="form-group row">
            <label class="col-6 col-md-2 col-form-label font-weight-bold">Note</label>
            <div class="col-6 col-md-4 col-form-label">
              {{$result->so ? $result->so->note : '-'}}
            </div>

            <label class="col-6 col-md-2 col-form-label font-weight-bold">Kategori Barang</label>
            <div class="col-6 col-md-4 col-form-label">
              <strong>{{$result->do_detail[0]->so_item->product->category->name}}</strong>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label font-weight-bold">Detail Pesanan</label>
          </div>

          <div class="form-group row">
            <table class="col-12 table table-striped table-bordered table-hover">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Nama Barang</th>
                  <th>Jumlah Permintaan</th>
                  <th>Packaging</th>
                  <th>Harga Acuan</th>
                  <th>Diskon USD <input type="text" class="base_disc form-control formatRupiah" {{ $result->status == 1 ? '' : 'readonly' }} onkeyup="discountOnChange()" /></th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                @if(count($result->do_detail) == 0)
                  <tr><td colspan="3" align="center">Data tidak ditemukan</td></tr>
                @endif
                @foreach($result->do_detail as $index => $row)
                  <?php
                    $sub_total += floatval($row->total) ?? 0;
                    $idr_sub_total += ceil((($row->price * $result->idr_rate) * $row->qty) - ($row->total_disc * $result->idr_rate)); 
                  ?>
                  <tr>
                    <td>{{$index+1}}</td>
                    <td>{{$row->product->name}}</td>
                    <td class="do-detail-qty" data-id="{{$row->id}}">{{$row->qty}}</td>
                    <td>{{$row->packaging_txt()->scalar ?? ''}}</td>
                    <td>$<span class="do-detail-price" data-id="{{$row->id}}">{{$row->price}}</span></td>
                    <td>
                      <input type="hidden" name="do_details[{{$index}}][id]" value="{{$row->id}}" />
                      <input type="text" name="do_details[{{$index}}][usd_disc]" value="{{$row->usd_disc}}" {{ $result->status == 1 ? '' : 'readonly' }} class="form-control formatRupiah do-detail-disc-usd" data-id="{{$row->id}}" onchange="discountOnChange({{$row->id}})" />
                    </td>
                    <td>$<span class="do-detail-total" data-id="{{$row->id}}">{{$row->total}}</span></td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <div class="form-group row">
            <div class="col-6">
              <a href="{{route('superuser.penjualan.packing_order.index')}}" class="btn btn-warning btn-md text-white"><i class="fa fa-arrow-left"></i> Exit</a>
            </div>
            <div class="col-12 text-right">
              <button type="button" class="btn btn-primary" onclick="changeStep(2)">Next</button>
            </div>
          </div>

        </div>
      </div>

      <div class="row step-container" id="step2Container">
        <div class="col-12">

          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label font-weight-bold">Asal Gudang</label>
            <div class="col-6 col-md-6 col-form-label">
              {{$result->warehouse->name}}
            </div>
          </div>

          <div class="form-group row">
          <label class="col-6 col-md-3 col-form-label font-weight-bold">Other Address</label>
            <div class="col-6 col-md-6 col-form-label">
              <input type="checkbox" name="other_address" value="1" name="other_address"  />
            </div>
          </div>
          

          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label font-weight-bold">Delivery</label>
            <div class="col-6 col-md-6 col-form-label">
              <select class="form-control js-select2 select-other-address" {{ $result->status == 1 ? '' : 'disabled' }} name="customer_other_address_id">
                <option value=""></option>
                @foreach($customer as $index => $row)
                <option value="{{$row->id}}">{{$row->name}}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label font-weight-bold">Delivery Address</label>
            <div class="col-md-8">
              <textarea class="form-control" readonly name="delivery_address" rows="1">{{$result->customer->address ?? '$result->other_address->address'}}</textarea>
            </div>
          </div>

          <!-- <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label font-weight-bold">Detail NPWP</label>
            <div class="col-6 col-md-6 col-form-label">
            <textarea class="form-control" readonly rows="1">{{$result->customer->npwp ?? ''}}</textarea>
            </div>
          </div> -->

          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label font-weight-bold">Cara Bayar</label>
            <div class="col-6 col-md-6 col-form-label">
              {{$result->do_type_transaction()->scalar ?? ''}}
            </div>
          </div>

          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label font-weight-bold">Nilai Kurs</label>
            <div class="col-6 col-md-6 col-form-label">
              <input type="text" name="idr_rate" {{ $result->status == 1 ? '' : 'readonly' }} class="form-control formatRupiah" value="{{number_format($result->idr_rate,0,',','.')}}" onchange="updateIdrSubTotal()">
            </div>
          </div>

          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label font-weight-bold">Kurir</label>
            <div class="col-6 col-md-6 col-form-label">
              <select class="form-control js-select2" {{ $result->status == 1 ? '' : 'disabled' }} name="ekspedisi_id">
                <option value="">==Select ekspedisi==</option>
                @foreach($ekspedisi as $index => $row)
                <option value="{{$row->id}}" @if($result->ekspedisi_id == $row->id) selected @endif>{{$row->name}}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label font-weight-bold">Perkiraan Ongkir</label>
            <div class="col-6 col-md-6 col-form-label">
              <input type="hidden" name="delivery_cost_note" class="form-control" value="{{$result->do_cost->delivery_cost_note}}">
              <input type="text" name="delivery_cost_idr" class="form-control count formatRupiah" {{ $result->status == 1 ? '' : 'readonly' }} value="{{number_format($result->do_cost->delivery_cost_idr ?? 0,0,',','.')}}">
            </div>
          </div>

          <!-- <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label font-weight-bold">Validasi KTP</label>
            <div class="col-6 col-md-6 col-form-label">
              <input type="hidden" name="delivery_cost_note" class="form-control" value="{{$result->do_cost->delivery_cost_note}}">
              <input type="text" name="delivery_cost_idr" class="form-control count formatRupiah" {{ $result->status == 1 ? '' : 'readonly' }} value="{{number_format($result->do_cost->delivery_cost_idr ?? 0,0,',','.')}}">
              <button type="button" class="btn btn-outline-info ml-10" data-toggle="modal" data-target="#modal-manage">Manage</button>
            </div>
          </div> -->

          <div class="form-group row">
            <div class="col-6">
              <button type="button" class="btn btn-warning" onclick="changeStep(1)">Back</button>
            </div>
            <div class="col-6 text-right">
              <button type="button" class="btn btn-primary" onclick="changeStep(3)">Next</button>
            </div>
          </div>

        </div>
      </div>

      <div class="row step-container {{$result->status == 2 ? 'active' : ''}}" id="step3Container">
        <div class="col-12">
          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label font-weight-bold">Total Amount</label>
            <div class="col-6 col-md-6 col-form-label">
              <input type="text" name="idr_total_before_discount" class="form-control" readonly value="{{number_format($idr_sub_total,0,',','.')}}">
            </div>
          </div>

          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label font-weight-bold">Diskon Item</label>
            <div class="col-6 col-md-6 col-form-label total_discount_cash_idr">
              {{number_format($idr_sub_total,0,',','.')}}
            </div>
          </div>

          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label font-weight-bold">Total Setelah Diskon</label>
            <div class="col-6 col-md-6 col-form-label">
              <input type="text" name="idr_sub_total" class="form-control" readonly value="{{number_format($idr_sub_total,0,',','.')}}">
            </div>
          </div>

          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label font-weight-bold">Diskon %</label>
            <div class="col-6 col-md-6 col-form-label">
            <input type="text" name="discount_1" class="form-control count" {{ $result->status == 1 ? '' : 'readonly' }} value="{{$result->do_cost->discount_1 ?? 0}}">
            </div>
          </div>

          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label font-weight-bold">Diskon Kemasan %</label>
            <div class="col-6 col-md-6 col-form-label">
              <input type="text" name="discount_2" class="form-control count" {{ $result->status == 1 ? '' : 'readonly' }} value="{{$result->do_cost->discount_2 ?? 0}}">
            </div>
          </div>

          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label font-weight-bold">Diskon (IDR)</label>
            <div class="col-6 col-md-6 col-form-label">
              <input type="text" name="discount_total" class="form-control" readonly value="{{number_format($result->do_cost->total_discount_idr ?? 0,0,',','.')}}">
            </div>
          </div>

          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label font-weight-bold">Total</label>
            <div class="col-6 col-md-6 col-form-label purchase_total_idr">
              {{number_format($idr_sub_total,0,',','.')}}
            </div>
          </div>

          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label"><input type="checkbox" name="checkbox_ppn" {{ $result->status == 1 ? '' : 'disabled' }} class="checkbox_ppn count" @if($result->do_cost->ppn ?? 0 > 0) checked @endif> PPN 10%</label>
            <div class="col-6 col-md-6 col-form-label">
              <input type="text" name="ppn" class="form-control" readonly value="{{number_format($result->do_cost->ppn ?? 0,0,',','.')}}">
            </div>
          </div>

          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label font-weight-bold">Voucher</label>
            <div class="col-6 col-md-6 col-form-label">
              <input type="text" name="voucher_idr" class="form-control count formatRupiah" {{ $result->status == 1 ? '' : 'readonly' }} value="{{number_format($result->do_cost->voucher_idr ?? 0,0,',','.')}}">
            </div>
          </div>

          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label font-weight-bold">Cashback</label>
            <div class="col-6 col-md-6 col-form-label">
              <input type="text" name="cashback_idr" class="form-control count formatRupiah" {{ $result->status == 1 ? '' : 'readonly' }} value="{{number_format($result->do_cost->cashback_idr ?? 0,0,',','.')}}">
            </div>
          </div>

          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label font-weight-bold">Perkiraan Ongkir</label>
            <div class="col-6 col-md-6 col-form-label">
              <input type="text" name="perkiraan_ongkir_view" class="form-control" readonly value="{{number_format($result->do_cost->delivery_cost_idr ?? 0,0,',','.')}}">
            </div>
          </div>

          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label font-weight-bold">Purchase Total</label>
            <div class="col-6 col-md-6 col-form-label">
              <input type="text" name="grand_total_idr" class="form-control" readonly value="{{number_format($result->do_cost->grand_total_idr ?? 0,0,',','.')}}">
            </div>
          </div>

          <div class="form-group row">
            <div class="col-6">
              <button type="button" class="btn btn-warning" onclick="changeStep(2)">Back</button>
            </div>
            <div class="col-6 text-right">
              @if($result->status > 1 && $result->invoicing != null)
              <a href="{{route('superuser.finance.invoicing.print_proforma',$result->invoicing->id)}}" class="btn btn-info mx-1" data-id="{{$result->invoicing->id}}" target="_blank"><i class="fa fa-print"></i> Print Proforma</a>
              <a href="{{route('superuser.finance.invoicing.print',$result->invoicing->id)}}" class="btn btn-primary mx-1" data-id="{{$result->invoicing->id}}" target="_blank"><i class="fa fa-print"></i> Print Invoice</a>
              <a href="{{route('superuser.finance.invoicing.print_portait',$result->invoicing->id)}}" class="btn btn-primary mx-1" data-id="{{$result->invoicing->id}}" target="_blank"><i class="fa fa-print"></i> Print Invoice A4</a>
              @endif
              @if($result->status === 1)
              <button type="submit" class="btn btn-primary">Finish & Cetak</button>
              @endif
            </div>
          </div>

        </div>
      </div>

    </form>
  </div>
</div>



@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.select2')

@include('superuser.component.modal-manage-validasi')

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
    const discUsd = $("input.base_disc").val();
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