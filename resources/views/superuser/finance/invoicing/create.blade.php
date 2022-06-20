<?php
  $sub_total = 0;
  $idr_sub_total = 0;
?>
@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Finance</span>
  <span class="breadcrumb-item">Invoicing</span>
  <span class="breadcrumb-item active">Create</span>
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
    <form id="frmUpdateDataPemesan" action="#" method="post">
      <input type="hidden" name="id" value="{{$result->do_cost->id ?? 0}}">
      <div class="row">
        <div class="col-12">
          <h5>#Data Pesanan</h5>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Warehouse</label>
            <div class="col-md-8">
              <input type="text" class="form-control" value="{{$result->warehouse->name ?? ''}}" readonly>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Customer</label>
            <div class="col-md-8">
              <input type="text" class="form-control" value="{{$result->customer->name ?? ''}}" readonly>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Address</label>
            <div class="col-md-8">
              <textarea class="form-control" readonly name="address" rows="1">{{$result->customer->address ?? ''}}</textarea>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Delivery</label>
            <div class="col-md-8">
              <input type="text" class="form-control" value="{{$result->customer_other_address->label ?? ''}}" readonly>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Delivery Address</label>
            <div class="col-md-8">
              <textarea class="form-control" readonly name="delivery_address" rows="1">{{$result->customer_other_address->address ?? ''}}</textarea>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">IDR Rate</label>
            <div class="col-md-8">
              <input type="text" class="form-control formatRupiah" value="{{number_format($result->idr_rate,0,',','.')}}" name="idr_rate">
            </div>
          </div>

          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Transaction</label>
            <div class="col-md-8">
              <select class="form-control js-select2" disabled>
                <?php
                  $selected1 = "";
                  $selected2 = "";
                  $selected3 = "";

                  if($result->type_transaction == 1){
                    $selected1 = "selected";
                  }
                  else if($result->type_transaction == 2){
                    $selected2 = "selected";
                  }
                  else{
                    $selected3 = "selected";
                  }
                ?>
                <option value="">==Select type transaction==</option>
                <option value="1" <?= $selected1 ?>>Cash</option>
                <option value="2" <?= $selected2 ?>>Tempo</option>
                <option value="3" <?= $selected3 ?>>Marketplace</option>
              </select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Ekspedisi</label>
            <div class="col-md-8">
              <input type="text" class="form-control" value="{{$result->ekspedisi->name ?? ''}}" readonly>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right">Note</label>
            <div class="col-md-8">
              <textarea class="form-control summernote" name="note" >{!!$result->note!!}</textarea>
            </div>
          </div>
          
        </div>
      </div>
      <div class="row mb-30">
        <div class="col-12">
          <button type="submit" class="btn btn-primary"><i class="fa fa-save"> Save </i></button>
        </div>
      </div>
    </form>
  </div>
</div>
<div class="block">
  <div class="block-content">
    <div class="row">
      <div class="col-12">
        <h5>#Item</h5>
        <div class="table table-responsive">
          <table class="table table-striped table-bordered">
            <thead>
              <th>Product</th>
              <th>Qty</th>
              <th>Price</th>
              <th>Discount</th>
              <th>Sub Total</th>
              <th>Note</th>
            </thead>
            @if(count($result->do_detail) == 0)
              <tr><td colspan="7" align="center">Data tidak ditemukan</td></tr>
            @endif
            @foreach($result->do_detail as $index => $row)
              <?php
                $sub_total += floatval($row->total) ?? 0; 
                $idr_sub_total += ceil((($row->price * $result->idr_rate) * $row->qty ) - ($row->total_disc * $result->idr_rate));
              ?>
              <tr>
                <td>{{$row->product->name ?? ''}}</td>
                <td>{{$row->qty ?? ''}}</td>
                <td>{{$row->price ?? ''}}</td>
                <td>{{$row->total_disc ?? ''}}</td>
                <td>{{$row->total ?? ''}}</td>
                <td>{{$row->note ?? ''}}</td>
              </tr>
            @endforeach
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="block">
  <div class="block-content">
    <div class="row">
      <div class="col-12">
        <h5>#Cost</h5>
        <form id="frmSimpanCost">
        @csrf
        <div class="row">
          <input type="hidden" name="id" value="{{$result->do_cost->id ?? 0}}">
          <div class="col-lg-3"></div>
          <div class="col-lg-9 float-right">
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right">IDR Total</label>
              <div class="col-md-8">
                <input type="text" name="idr_sub_total" class="form-control" readonly value="{{number_format($idr_sub_total,0,',','.')}}">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right">Discount 1 (%)</label>
              <div class="col-md-8">
                <input type="text" name="discount_1" class="form-control count" value="{{$result->do_cost->discount_1 ?? 0}}">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right">Discount 2 (%)</label>
              <div class="col-md-8">
                <input type="text" name="discount_2" class="form-control count" value="{{$result->do_cost->discount_2 ?? 0}}">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right">Discount IDR</label>
              <div class="col-md-8">
                <input type="text" name="discount_idr" class="form-control count formatRupiah" value="{{number_format($result->do_cost->discount_idr ?? 0,0,',','.')}}">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right">Total Discount (IDR)</label>
              <div class="col-md-8">
                <input type="text" name="discount_total" class="form-control" readonly value="{{number_format($result->do_cost->total_discount_idr ?? 0,0,',','.')}}">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right">PPN</label>
              <div class="col-md-8">
                <input type="text" name="ppn" class="form-control" readonly value="{{number_format($result->do_cost->ppn ?? 0,0,',','.')}}">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right"> Voucher (IDR)</label>
              <div class="col-md-8">
                <input type="text" name="voucher_idr" class="form-control count formatRupiah" value="{{number_format($result->do_cost->voucher_idr ?? 0,0,',','.')}}">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right"> Cashback (IDR)</label>
              <div class="col-md-8">
                <input type="text" name="cashback_idr" class="form-control count formatRupiah" value="{{number_format($result->do_cost->cashback_idr ?? 0,0,',','.')}}">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right"> Purchase Total (IDR)</label>
              <div class="col-md-8">
                <input type="text" name="purchase_total_idr" class="form-control formatRupiah" readonly value="{{number_format($result->do_cost->purchase_total_idr ?? 0,0,',','.')}}">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right"> Delivery Cost (IDR)</label>
              <div class="col-md-4">
                <input type="text" name="delivery_cost_note" class="form-control" value="{{$result->do_cost->delivery_cost_note ?? ''}}" placeholder="Note">
              </div>
              <div class="col-md-4">
                <input type="text" name="delivery_cost_idr" class="form-control count formatRupiah" value="{{number_format($result->do_cost->delivery_cost_idr ?? 0,0,',','.')}}">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right"> Other Cost (IDR)</label>
              <div class="col-md-4">
                <input type="text" name="other_cost_note" class="form-control" value="{{$result->do_cost->other_cost_note ?? ''}}" placeholder="Note">
              </div>
              <div class="col-md-4">
                <input type="text" name="other_cost_idr" class="form-control count formatRupiah" value="{{number_format($result->do_cost->other_cost_idr ?? 0,0,',','.')}}">
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right"> Grand Total (IDR)</label>
              <div class="col-md-8">
                <input type="text" name="grand_total_idr" class="form-control" readonly value="{{number_format($result->do_cost->grand_total_idr ?? 0,0,',','.')}}">
              </div>
            </div>
            
            <?php
              $account_receivable = 0;
            ?>
            @foreach($result->customer->do as $index => $row)
              @if($row->invoicing)
                <?php
                  $total_invoicing = $row->invoicing->grand_total_idr ?? 0;
                  $payable = $row->invoicing->payable_detail->sum('total');
                  $account_receivable += $total_invoicing - $payable;
                ?>
              @endif
            @endforeach
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right">Customer Account Receivable</label>
              <div class="col-md-8">
                <input type="text" class="form-control" value="{{number_format($account_receivable,0,',','.')}}" readonly>
              </div>
            </div>
          </div>
          
        </div>
        <div class="row mb-30">
          <div class="col-12">
            <button class="btn btn-primary btn-md" type="submit" disabled="disabled"><i class="fa fa-save"></i> Save</button>
          </div>
        </div>
        </form>
      </div>
    </div>
  </div>
</div>


<div class="block">
  <div class="block-content">
    <form class="mb-30" id="frmCreateInvoicing" method="post" action="{{route('superuser.finance.invoicing.store_invoicing')}}" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="do_id" value="{{$result->id}}">
      <div class="form-group">
        <label>Upload Image</label>
        <input type="file" name="image" class="form-control" accept="image/*">
      </div>
      <a href="{{route('superuser.finance.invoicing.index')}}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Back</a>
      <a href="#" class="btn btn-success btn-create-invoicing" data-doid="{{$result->id}}"><i class="fa fa-save"></i> Create Invoicing</a>
    </form>
    
  </div>
</div>

@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script>
  let grand_total_idr = parseInt("{{$result->do_cost->grand_total_idr ?? 0}}");

  // total();

  $(function(){
    $('button[type="submit"]').attr('disabled',false);

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

    $(document).on('keyup','.count',function(){
      total();
    })
    $(document).on('keyup','.formatRupiah',function(){
      let val = $(this).val();
      $(this).val(formatRupiah(val));
    })
    $(document).on('submit','#frmUpdateDataPemesan',function(e){
      e.preventDefault();
      if(confirm("Apakah anda yakin ingin mengubah data pemesan ?")){
        let _form = $('#frmUpdateDataPemesan');
        $.ajax({
          url : '{{route('superuser.finance.invoicing.update_pemesan')}}',
          method : "POST",
          data : getFormData(_form),
          dataType : "JSON",
          beforeSend : function(){
            $('button[type="submit"]').html('Loading...');
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
            $('button[type="submit"]').html('<i class="fa fa-save"> Save</i>');
          }
        })
      }
    })
    $(document).on('submit','#frmSimpanCost',function(e){
      e.preventDefault();
      total();
      if(confirm("Apakah anda yakin ingin mengubah rincian biaya order ?")){
        let _form = $('#frmSimpanCost');
        total();
        $.ajax({
          url : '{{route('superuser.finance.invoicing.update_cost')}}',
          method : "POST",
          data : getFormData(_form),
          dataType : "JSON",
          beforeSend : function(){
            $('button[type="submit"]').html('Loading...');
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
            $('button[type="submit"]').html('<i class="fa fa-save"> Save</i>');
          }
        })
      }
    })
    $(document).on('click','.btn-create-invoicing',function(){
      if(confirm("Apakah anda yakin ingin menambahkan orderan ini kedalam invoice ?")){
        $('#frmCreateInvoicing').submit();
      }
    })
  })
  function total(){
    let idr_sub_total = parseFloat(<?= $idr_sub_total ?>);
    let discount_1 = parseFloat($('input[name="discount_1"]').val());
    let discount_2 = parseFloat($('input[name="discount_2"]').val());
    let discount_idr = $('input[name="discount_idr"]').val();
    let voucher_idr = $('input[name="voucher_idr"]').val();
    let cashback_idr = $('input[name="cashback_idr"]').val();
    let delivery_cost_idr = $('input[name="delivery_cost_idr"]').val();
    let other_cost_idr = $('input[name="other_cost_idr"]').val();
    let ppn = $('input[name="ppn"]').val();
    let sub_total_discount = 0;
    let sub_ppn = 0;
    let sub_purchase_total = 0;
    let grand_total_idr = 0;

    discount_idr = parseFloat(discount_idr.split('.').join(''));
    voucher_idr = parseFloat(voucher_idr.split('.').join(''));
    cashback_idr = parseFloat(cashback_idr.split('.').join(''));
    delivery_cost_idr = parseFloat(delivery_cost_idr.split('.').join(''));
    other_cost_idr = parseFloat(other_cost_idr.split('.').join(''));
    ppn = parseFloat(ppn.split('.').join(''));

    idr_sub_total = (isNaN(idr_sub_total)) ? 0 : idr_sub_total;
    discount_1 = (isNaN(discount_1)) ? 0 : discount_1 / 100;
    discount_2 = (isNaN(discount_2)) ? 0 : discount_2 / 100;
    discount_idr = (isNaN(discount_idr)) ? 0 : discount_idr;
    voucher_idr = (isNaN(voucher_idr)) ? 0 : voucher_idr;
    cashback_idr = (isNaN(cashback_idr)) ? 0 : cashback_idr;
    delivery_cost_idr = (isNaN(delivery_cost_idr)) ? 0 : delivery_cost_idr;
    other_cost_idr = (isNaN(other_cost_idr)) ? 0 : other_cost_idr;

    sub_total_discount = Math.ceil((idr_sub_total * discount_1) + ((idr_sub_total - (idr_sub_total * discount_1)) * discount_2) + discount_idr);

    sub_ppn = ppn;

    sub_purchase_total= Math.ceil(idr_sub_total - sub_total_discount - voucher_idr - cashback_idr + sub_ppn);
    grand_total_idr = Math.ceil(sub_purchase_total + delivery_cost_idr + other_cost_idr);

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
    

    $('input[name="discount_total"]').val(formatRupiah(sub_total_discount)); 
    $('input[name="ppn"]').val(formatRupiah(sub_ppn));
    $('input[name="purchase_total_idr"]').val(formatRupiah(sub_purchase_total));
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
</script>
@endpush