@foreach($result->so_detail as $index => $row)
  @php $row->product->name @endphp
@endforeach

@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.sales_order.index_' . strtolower($step_txt)) }}">Sales Order {{ $step_txt }}</a>
  <span class="breadcrumb-item active">Validasi SO <?php echo $step_txt ?></span>
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

<section class="row">
  <div class="col-lg-8">
    <div id="accordion">
      <div class="card">
        <div class="card-header" id="headingOne">
          <h5 class="mb-0">
            <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
              #Data Pesanan
            </button>
          </h5>
        </div>
        <div class="card-content">
          <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
            <div class="card-body padding-bottom-zero padding-top-zero">
              <div class="row">
                  <div class="col-lg-6">
                      @if($step == 1 || $step == 2)
                      <div class="form-group row">
                        <label>Customer</label>
                        <div class="col-md-8">
                          <select class="form-control js-select2 select-customer" name="customer_other_address_id" @if($step == 2) disabled @endif>
                            <option value="">Pilih Customer</option>
                            @foreach($member as $index => $row)
                              <option value="{{$row->id}}" @if($result->customer_other_address_id == $row->id && $result->so_for == 1) selected @endif>{{$row->name}}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                      @endif
                  </div>
                  <div class="col-lg-6">
                      @if($step == 1 || $step == 2)
                      <div class="form-group row">
                        <label>Address</label>
                        <div class="col-md-8">
                          @if($result->so_for == 1)
                          <input type="text" name="address" class="form-control" value="{{$result->member->address ?? ''}}" readonly>
                          @else
                          <input type="text" name="address" class="form-control" value="{{$result->warehouse->address ?? ''}}" readonly>
                          @endif
                        </div>
                      </div>
                      @endif
                  </div>
                  <div class="col-lg-6">
                    @if($step == 1 || $step == 2)
                      <div class="form-group row">
                        <label>Team Leader<span class="text-danger">*</span></label>
                        <div class="col-md-8">
                          <select class="form-control js-select2" name="sales_senior_id" @if($step == 2) disabled @endif>
                            <option value="">==Select sales senior==</option>
                            @foreach($sales as $index => $row)
                              <option value="{{$row->id}}" @if($result->sales_senior_id == $row->id) selected @endif>{{$row->name}}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                      @endif
                  </div>
                  <div class="col-lg-6">
                    @if($step == 1 || $step == 2)
                      <div class="form-group row">
                        <label>Salesman <span class="text-danger">*</span></label>
                        <div class="col-md-8">
                          <select class="form-control js-select2" name="sales_id" @if($step == 2) disabled @endif>
                            <option value="">==Select sales==</option>
                            @foreach($sales as $index => $row)
                              <option value="{{$row->id}}" @if($result->sales_id == $row->id) selected @endif>{{$row->name}}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                      @endif
                  </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
        <div class="card">
        <div class="card-header" id="headingTwo">
          <h5 class="mb-0">
            <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
              #Cek Hutang Customer
            </button>
          </h5>
        </div>
            <div class="card-content">
                <div class="card-body padding-bottom-zero padding-top-zero">
                  <div class="row">
                    <div class="col-8">
                      <select id="selectCustomerOutstandingCategory" class="form-control js-select2 select-category" data-index="0">
                        <option value="">All</option>
                        @foreach($product_category as $index => $row)
                          <option value="{{$row->id}}">{{$row->brand_name}} - {{ $row->name }} - {{ $row->type }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-12">
                      <table class="customer-outstanding mt-3 mb-3">
                        <?php
                          $total_outstanding = 0;
                          $total_outstanding_past_due_date = 0;
                          if(isset($customer_history) && sizeof($customer_history) > 0) {
                            foreach($customer_history as $index => $row) {
                              $total_outstanding += $row->grand_total_idr;
                            }
                          }
                        ?>
                        <tr>
                          <td class="text-left">Total Outstanding</td>
                          <td>:</td>
                          <td class="text-left">{{ number_format($total_outstanding,0,',','.') }}</td>
                        </tr>
                        <tr>
                          <td class="text-left">Total Outstanding Lewat Jatuh Tempo</td>
                          <td>:</td>
                          <td class="text-left">{{ number_format($total_outstanding,0,',','.') }}</td>
                        </tr>
                      </table>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-12">
                      @if(isset($customer_history) && sizeof($customer_history) > 0)
                      <table id="tableCustomerOutstanding" class="table table-hover">
                        <thead>
                          <tr>
                            <th class="text-center">No</th>
                            <th class="text-center">Invoice No</th>
                            <th class="text-center">Tanggal</th>
                            <th class="text-center">Product</th>
                            <th class="text-right">Total Invoice</th>
                            <th class="text-right">Total Payment</th>
                            <th class="text-right">Outstanding</th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach($customer_history as $index => $row)
                          <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->code }}</td>
                            <td>{{ date('Y-m-d h:i:s',strtotime($row->created_at)) }}</td>
                            <td>
                              <?php
                                if (sizeof($row->do->do_detail) > 0) {
                                  foreach($row->do->do_detail as $indexdodetail => $row_do_detail) {
                                    echo $row_do_detail->product->name . ' - ' . $packaging_dictionary[$row_do_detail->packaging] . '<br />';
                                  }
                                }
                              ?>
                            </td>
                            <td class="text-right">{{ number_format($row->grand_total_idr,0,',','.') }}</td>
                            <td class="text-right">
                              <?php
                                $total_paid = 0;
                                if (sizeof($row->payable_detail) > 0) {
                                  foreach($row->payable_detail as $indexpayable => $row_payable) {
                                    $total_paid += $row_payable->total;
                                  }
                                }
                              ?>{{ number_format($total_paid,0,',','.') }}</td>
                            <td class="text-right">{{ number_format(($row->grand_total_idr - $total_paid),0,',','.') }}</td>
                          </tr>
                          @endforeach
                        </tbody>
                      </table>
                      @endif
                      @if(isset($customer_history) && sizeof($customer_history) == 0)
                      <div class="text-center">Tidak ada data outstanding</div>
                      @endif
                    </div>
                  </div>
                </div>
            </div>
        </div>
    </div>
</section>

<br>

<!--  -->


<br>

<!-- Detail Product -->
<section class="row">
  <div class="col-lg-12">
        <div class="card">
          <div class="card-header" id="detailPesanan">
            <h5 class="mb-0">
              <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#detailPesanan" aria-expanded="false" aria-controls="detailPesanan">
                #Detail Pesanan
              </button>
            </h5>
          </div>
            <div class="card-content">
                <div class="card-body padding-bottom-zero padding-top-zero">
                    <div class="row">
                      <form id="frmEditSOMaster" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" value="{{$result->id}}">
                        <input type="hidden" name="step" value="{{$step}}">
                        <div class="row">
                          <div class="col-lg-4">
                            @if($step == 2)
                            <div class="form-group row">
                              <label>Gudang Asal<span class="text-danger">*</span></label>
                              <div class="col-md-8">
                                <select class="form-control js-select2" name="origin_warehouse_id">
                                  <option value="">Pilih Gudang Asal</option>
                                  @foreach($warehouse as $index => $row)
                                    <option value="{{$row->id}}" @if($result->origin_warehouse_id == $row->id) selected @endif>{{$row->name}}</option>
                                  @endforeach
                                </select>
                              </div>
                            </div>
                            @endif
                          </div>
                          <div class="col-lg-4">
                            @if($step == 2)
                            <div class="form-group row">
                              <label>Kurs<span class="text-danger">*</span></label>
                              <div class="col-md-8">
                                <input type="text" name="idr_rate" class="form-control" data-index="0" step="any">
                              </div>
                            </div>
                            @endif
                          </div>
                          <div class="col-lg-4">
                            @if($step == 2)
                            <div class="form-group row">
                              <label>Note</label>
                              <div class="col-md-8">
                                <input class="form-control" readonly name="note" value="{{$result->note}}">
                              </div>
                            </div>
                            @endif
                          </div>
                        </div>
                        <hr>
                        <div class="row">
                          <div class="col-12 product-list">
                            <table id="tableDetailPesanan" class="table striped table-bordered">
                              <thead>
                                <th>No</th>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th>Jumlah Permintaan (Kg)</th>
                                <th>Kemasan</th>
                                <th>In Stock / KG (dikerjakan)</th>
                                <th>Diskon</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                                <th>Keterangan</th>
                                <th></th>
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
                                    <td>{{$index + 1}}</td>
                                    <td>{{$row->product->code ?? ''}} - {{$row->product->name ?? ''}}</td>
                                    <td>{{$row->product->category->name ?? ''}} - {{$row->product->category->type ?? ''}}</td>
                                    <td>{{$row->qty}}</td>
                                    <td>{{$row->packaging_txt()->scalar ?? ''}}</td>
                                    <td>
                                      <input type="number" name="repeater[{{$index}}][do_qty]" class="form-control count" data-index="{{$index}}" value="{{$row->qty}}" step="any" min="0" max="{{$row->qty}}">
                                    </td>
                                    <td style="width: 80px;">
                                      <input type="number" name="" class="form-control">
                                    </td>
                                    <td>${{ number_format($row->product->selling_price, 2, ',', '.') }}</td>
                                    <td>
                                      <span class="invoice-item-row-total-label">$</span>
                                    </td>
                                    <td class="text-right">
                                      <input type="text" name="repeater[{{$index}}][note]" class="form-control">
                                    </td>
                                    <td></td>
                                  </tr>
                                  <tr class="row-footer-subtotal">
                                      <td colspan="8" class="text-right"><span><b>Subtotal</b></span></td>
                                      <input type="hidden" class="form-control" name="">
                                  </tr>
                                  <tr>
                                        <td colspan="8" class="text-right td-disc"><span><b>Disc Agen</b></span></td>
                                        <td colspan="2">
                                            <div class="row">
                                                <div class="col-lg-4 padding-right-zero">
                                                  <div class="d-flex align-items-center small">
                                                      <i class="fa fa-percent fa-fw text-muted position-absolute pl-3"></i>
                                                      <input type="number" class="form-control py-4" placeholder="" style="padding-left: 38px;"/>
                                                  </div>
                                                </div>
                                                <div class="col-lg-8">
                                                  <div class="d-flex align-items-center small">
                                                      <i class="fa fa-dollar fa-fw text-muted position-absolute pl-3"></i>
                                                      <input type="number" class="form-control py-4" placeholder="" style="padding-left: 38px;"/>
                                                  </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                  <tr>
                                      <td colspan="8" class="text-right"><span><b>Disc Qty + Kemasan</b></span></td>
                                      <td colspan="2">
                                        <input type="number" class="form-control disc-kemasan">
                                      </td>
                                  </tr>
                                  <tr>
                                      <td colspan="8" class="text-right"><span><b>Voucher / Cashback</b></span></td>
                                      <td colspan="2">
                                        <input type="number" class="form-control disc-kemasan">
                                      </td>
                                  </tr>
                                  <tr>
                                        <td colspan="8" class="text-right td-tax"><span><b>Pajak</b></span></td>
                                        <td colspan="2">
                                            <div class="row" >
                                                <div class="col-lg-4 padding-right-zero">
                                                  <div class="d-flex align-items-center small">
                                                      <i class="fa fa-percent fa-fw text-muted position-absolute pl-3"></i>
                                                      <input type="text" class="form-control py-4" placeholder="" style="padding-left: 38px;"/>
                                                  </div>
                                                </div>
                                                <div class="col-lg-8">
                                                  <div class="d-flex align-items-center small">
                                                      <i class="fa fa-dollar fa-fw text-muted position-absolute pl-3"></i>
                                                      <input type="text" class="form-control py-4" placeholder="" style="padding-left: 38px;"/>
                                                  </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                  <tr>
                                      <td colspan="8" class="text-right"><span><b>Ongkir</b></span></td>
                                      <td colspan="2">
                                        <input type="number" class="form-control disc-kemasan">
                                      </td>
                                  </tr>
                                      <td colspan="8" class="text-right"><span><b>Total Akhir</b></span></td>
                                      <td class="text-right" colspan="2">
                                        <strong><span class="invoice-grand-total">$0</span></strong>
                                      </td>
                                      <td> <input type="hidden" name=""></td>
                                  @endforeach
                                @endif
                              </tbody>
                            </table>
                          </div>
                        </div>

                        <div class="row mb-30">
                          <div class="col-12 text-center">
                            <a href="{{ URL::previous() }}" class="btn btn-warning  btn-md text-white"><i class="fa fa-arrow-left"></i> Back</a>
                            <button type="button" class="btn btn-info" onclick="activaTab('validasiCustomer')">Kembali Validasi Customer</button>
                            <button class="btn btn-primary btn-md" type="submit" disabled="disabled"><i class="fa fa-save"></i> Save</button>
                          </div>
                        </div>
                      </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<form method="post" action="{{route('superuser.penjualan.sales_order.destroy_item')}}" id="frmDestroyItem">
    @csrf
    <input type="hidden" name="id">
</form>

@include('superuser.penjualan.sales_order.customer_performance_modal')
@include('superuser.penjualan.sales_order.tidak_lanjut_modal')

@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script>
  var customerOutstandingData = [<?php
    if(isset($customer_history) && sizeof($customer_history) > 0) {
      $index = 0;
      foreach($customer_history as $index => $row) {
        echo $row;

        if ($index < sizeof($customer_history)) {
          echo ',';
        }

        $index++;
      }
    }
  ?>];

  var soDetails = [<?php
    if(isset($result->so_detail) && sizeof($result->so_detail) > 0) {
      $index = 0;
      foreach($result->so_detail as $index => $row) {
        echo $row;

        if ($index < sizeof($result->so_detail)) {
          echo ',';
        }

        $index++;
      }
    }
  ?>];

  var packagingDictionary = [];
  <?php
    if(isset($packaging_dictionary) && sizeof($packaging_dictionary) > 0) {
      foreach($packaging_dictionary as $index => $row) {
        echo 'packagingDictionary[' . $index . '] = "' . $row . '";';

      }
    }
  ?>

  console.log('asd', soDetails);

  $(function(){
    let global_total = 0 ;
    $('button[type="submit"]').removeAttr('disabled');
    $('.js-select2').select2();

    let param = [];
    param["category_id"] = "";
    param["type_id"] = "";

    loadProduct({});

    $(document).on('change','.select-category',function(){
      param["category_id"] = $(this).val();
      loadProduct({
        category_id:param["category_id"],
        type_id : param["type_id"]
      })
    })

    $(document).on('click','.select-checkbox',function(){
      if($(this).is(':checked')){

        $('select[name="destination_warehouse_id"]').attr('disabled',false);

        $('select[name="customer_id"]').val(null).trigger('change')
        $('select[name="customer_id"]').attr('disabled',true);
        $('textarea[name="address"]').val("");
      }
      else{
       $('select[name="customer_id"]').attr('disabled',false);

       $('select[name="destination_warehouse_id"]').val(null).trigger('change')
       $('select[name="destination_warehouse_id"]').attr('disabled',true);
       $('textarea[name="address"]').val("");
      }
    })

    $(document).on('change','.select-customer',function(){
      let val = $(this).val();
      if(val != ""){
        customer_address(val);
      }else{
        $('textarea[name="address"]').val("");
      }
    })

    $(document).on('change','.select-warehouse',function(){
      let val = $(this).val();
      if(val != ""){
        warehouse_address(val);
      }else{
        $('textarea[name="address"]').val("");
      }
    })

    $(document).on('click','#buttonAddProduct',function(){
      const categoryId = $('.select-category[data-index=0]').val();
      const categoryText = $('.select-category[data-index=0] option:selected').text();
      const productId = $('.select-product[data-index=0]').val();
      const productText = $('.select-product[data-index=0] option:selected').text();
      const qty = $('.input-qty[data-index=0]').val();
      const packagingId = $('.select-packaging[data-index=0]').val();
      const packagingText = $('.select-packaging[data-index=0] option:selected').text();
      if (categoryId === null || categoryId === '' || productId === null || productId === '' || qty === null || qty === '' || packagingId == null || packagingId === '') {
        Swal.fire(
          'Error!',
          'Please input all the data',
          'error'
        );
        return;
      }

      let html = "<div class='row mt-10 product-row category-" + categoryId + "'>";
      html += "  <div class='col-3'>";
      html += "    <input type='hidden' class='form-control' value='" + categoryId + "'>";
      html += categoryText;
      html += "  </div>";
      html += "  <div class='col-3'>";
      html += "    <input type='hidden' name='product_id[]' class='form-control' value='" + productId + "'>";
      html += productText;
      html += "  </div>";
      html += "  <div class='col-1 text-right'>";
      html += "    <input type='hidden' name='qty[]' class='form-control' value='" + qty + "'>";
      html += qty;
      html += "  </div>";
      html += "  <div class='col-3'>";
      html += "    <input type='hidden' name='packaging[]' class='form-control' value='" + packagingId + "'>";
      html += packagingText;
      html += "  </div>";
      html += "  <div class='col-1'>";
      html += "    <button type='button' id='buttonDeleteProduct' class='btn btn-danger'><em class='fa fa-minus'></em></button>";
      html += "  </div>";
      html += "</div>";
      
      if ($('.product-row.category-' + categoryId).length > 0) {
        $('body').find('.product-row.category-' + categoryId + ':last').after(html);
      } else {
        $('body').find('.product-list').append(html);
      }

      //let option = '<option value="">==Select product==</option>';
      //$('.select-product[data-index=0]').html(option);

      //$('.select-category[data-index=0]').val('').change();
      $('.select-product[data-index=0]').val('').change();
      $('.input-qty[data-index=0]').val('');
      $('.select-packaging[data-index=0]').val('').change();

      $('.select-category[data-index=0]').select2('focus');

      productCount++;
    })

    $(document).on('click','#buttonDeleteProduct',function(){
      $(this).parents(".product-row").remove();
    })

    $(document).on('click','.btn-delete',function(){
      if(confirm("Apakah anda yakin ingin menghapus item ini ? ")){
        let id = $(this).data('id');
        $('#frmDestroyItem').find('input[name="id"]').val(id);
        $('#frmDestroyItem').submit();
      }
    })

    $(document).on('click','.btn-cek-customer',function(){
      $('#modalCustomerInvoice').modal('show');
    })

    $(document).on('click','.btn-tidak-lanjut',function(){
      $('#modalTidakLanjut').modal('show');
    })

    $(document).on('click','.btn-close-tidak-lanjut-modal',function(){
      $('#modalTidakLanjut').modal('hide');
    })

    $(document).on('submit','#frmEditSOMaster',function(e){
      e.preventDefault();
      if(confirm("Apakah anda yakin ingin mengubah sales order ini ? ?")){
        let _form = $('#frmEditSOMaster');
        $.ajax({
          url : '{{route('superuser.penjualan.sales_order.tutup_so')}}',
          method : "POST",
          data : $('#frmEditSOMaster').serializeArray(),
          dataType : "JSON",
          beforeSend : function(){
            $('#frmEditSOMaster').find('button[type="submit"]').html('Loading...');
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
                document.location.href = '{{ route('superuser.penjualan.sales_order.index_' . strtolower($step_txt)) }}';
              })
              
            }
          },
          complete : function(){
            $('#frmEditSOMaster').find('button[type="submit"]').html('<i class="fa fa-save"> Save</i>');
          }
        })
      }
    })

    $(document).on('submit','#frmTidakLanjutSO',function(e){
      e.preventDefault();
      if(confirm("Apakah anda yakin tidak melanjutkan sales order ini?")){
        $.ajax({
          url : '{{route('superuser.penjualan.sales_order.tidak_lanjut_so')}}',
          method : "POST",
          data : $('#frmTidakLanjutSO').serializeArray(),
          dataType : "JSON",
          beforeSend : function(){
            $('#frmTidakLanjutSO').find('button[type="submit"]').html('Loading...');
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
                window.history.back();
              })
              
            }
          },
          complete : function(){
            $('#frmTidakLanjutSO').find('button[type="submit"]').html('<i class="fa fa-save"> Save</i>');
          }
        })
      }
    })

    $(document).on('submit','#frmSaveItem',function(e){
      e.preventDefault();
      if(confirm("Apakah anda yakin ingin menyimpan item ini ?")){
        let _form = $('#frmSaveItem');
        $.ajax({
          url : '{{route('superuser.penjualan.sales_order.store_item')}}',
          method : "POST",
          data : getFormData(_form),
          dataType : "JSON",
          beforeSend : function(){
            $('#frmSaveItem').find('button[type="submit"]').html('Loading...');
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
          complete : function(){
            $('#frmSaveItem').find('button[type="submit"]').html('<i class="fa fa-save"> Save Item</i>');
          }
        })
      }
    })
  })

  function loadProduct(param){
    $.ajax({
      url : '{{route('superuser.penjualan.sales_order.get_product')}}',
      method : "GET",
      data : param,
      dataType : "JSON",
      success : function(resp){
        let option = "";
        option = '<option value="">==Select product==</option>';
        $.each(resp.Data,function(i,e){
          option += '<option value="'+e.id+'">'+e.code+' - '+e.name+'</option>';
        })
        $('.select-product').html(option);
      },
      error : function(){
        alert("Cek Koneksi Internet");
      }
    })
  }

  function customer_address(id){
    ajaxcsrfscript();
    $.ajax({
      url : '{{route('superuser.penjualan.sales_order.ajax_customer_detail')}}',
      method : "POST",
      data : {id:id},
      dataType : "JSON",
      success : function(resp){
        if(resp.IsError == true){
          showToast('danger',resp.Message);
        }
        else{
          $('textarea[name="address"]').val(resp.Data.address);
        }
      },
      error : function(){
        alert('Cek Koneksi Internet');
      },
    })
  }
  function warehouse_address(id){
    ajaxcsrfscript();
    $.ajax({
      url : '{{route('superuser.penjualan.sales_order.ajax_warehouse_detail')}}',
      method : "POST",
      data : {id:id},
      dataType : "JSON",
      success : function(resp){
        if(resp.IsError == true){
          showToast('danger',resp.Message);
        }
        else{
          $('textarea[name="address"]').val(resp.Data.address);
        }
      },
      error : function(){
        alert('Cek Koneksi Internet');
      },
    })
  }
  let firstShowTabPembuatanPO = true;
  function activaTab(tab){
    $('.nav-tabs a[href="#' + tab + '"]').tab('show');
    if (firstShowTabPembuatanPO) {
      $('.js-select2').select2();
      firstShowTabPembuatanPO = false;
    }
  };

  $(document).on('click','.select-all',function(){
    if($(this).is(':checked')){
      isSelected();
    }
    else{
      removeSelected();
    }
  })

  $(document).on('change', '#selectCustomerOutstandingCategory', function() {
    loadCustomerOutstandingTable();
  })

  function loadCustomerOutstandingTable() {
    let htmlString = '';
    const categorySelected = $('#selectCustomerOutstandingCategory').val();
    let totalPaid = 0;
    let totalOutstanding = 0;

    for (let i = 0; i < customerOutstandingData.length; i++) {
      const outstandingData = customerOutstandingData[i];

      const doDetails = outstandingData.do.do_detail;
      let productString = '';
      if (doDetails && doDetails.length > 0) {
        let isCategoryMatch = false;
        for (let j = 0; j < doDetails.length; j++) {
          const doDetail = doDetails[j];
          if (doDetail.product.category_id == categorySelected || categorySelected == '') {
            isCategoryMatch = true;

            productString += doDetail.product.name + ' - ' + packagingDictionary[doDetail.packaging] + '<br />';
          }
        }
        if (!isCategoryMatch) {
          continue;
        }
      }

      if (outstandingData.payable_detail && outstandingData.payable_detail.length > 0) {
        for (let j = 0; j < outstandingData.payable_detail.length; j++) {
          const payable_detail = outstandingData.payable_detail[j];
          totalPaid += payableDetail.total;
        }
      }

      htmlString += '<tr>';
      htmlString += ' <td>' + (i + 1) + '</td>';
      htmlString += ' <td>' + outstandingData.code + '</td>';
      htmlString += ' <td>' + outstandingData.created_at + '</td>';
      htmlString += ' <td>' + productString + '</td>';
      htmlString += ' <td class="text-right">' + numberWithCommas(outstandingData.grand_total_idr) + '</td>';
      htmlString += ' <td class="text-right">' + numberWithCommas(totalPaid) + '</td>';
      htmlString += ' <td class="text-right">' + numberWithCommas(outstandingData.grand_total_idr - totalPaid) + '</td>';
      htmlString += '</tr>';
    }

    if (htmlString == '') {
      htmlString = '<tr><td colspan="7">Data tidak ditemukan</td></tr>';
    }

    $('#tableCustomerOutstanding tbody').html(htmlString);
  }

$(document).on('change', '#selectCategoryDetailPesanan', function() {
  loadDetailPesanan();
})

function loadDetailPesanan() {
  let htmlString = '';
  const categorySelected = $('#selectCategoryDetailPesanan').val();
  if (categorySelected == '') {
    htmlString = '<tr><td colspan="7">Data tidak ditemukan</td></tr>';
    $('#tableDetailPesanan tbody').html(htmlString);
    return;
  }

  for (let i = 0; i < soDetails.length; i++) {
    const soDetail = soDetails[i];

    htmlString += '<tr>';
    htmlString += ' <input type="text" name="repeater[' + i + '][product_id]" value="' + soDetail.product_id + '" />';
    htmlString += ' <input type="text" name="repeater[' + i + '][so_qty]" value="' + soDetail.qty + '" />';
    htmlString += ' <input type="text" name="repeater[' + i + '][so_item_id]" value="' + soDetail.id + '" />';
    htmlString += ' <input type="text" name="repeater[' + i + '][packaging]" value="' + soDetail.packaging + '" />';
    htmlString += ' <td>' + soDetail.product.name +  '</td>';
    htmlString += ' <td>' + soDetail.qty +  '</td>';
    htmlString += ' <td>' + packagingDictionary[soDetail.packaging] +  '</td>';
    htmlString += ' <td><input type="text" name="repeater[' + i + '][do_qty]" class="form-control" /></td>';
    htmlString += ' <td><input type="text" name="repeater[' + i + '][note]" class="form-control" /></td>';
    htmlString += '</tr>';
  }

  if (htmlString == '') {
    htmlString = '<tr><td colspan="7">Data tidak ditemukan</td></tr>';
  }

  $('#tableDetailPesanan tbody').html(htmlString);
}

  $(document).on('submit','#frmInsert',function(e){
    e.preventDefault();
    if(confirm("Apakah anda yakin ingin menambahkan item ini ke packing order ?")){
      length = $('#frmInsert tbody').find('input:checkbox:checked').length;
      if(length == 0){
        alert("No Item SO Checked")  
      }
      else{
        let _form = $('#frmInsert');
        $.ajax({
          url : '{{route('superuser.penjualan.packing_order.store_so')}}',
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
                //redirect disini
              })
              
            }
          },
          error : function(){
              alert("Cek Koneksi Internet");
          },
          complete : function(){
            $('button[type="submit"]').html('<i class="fa fa-save"> Insert Into Packing</i>');
          }
        })
      }
      
    }
  })

  function isSelected(){
    $('tbody').find('input[type="checkbox"]').attr('checked','checked');
    $('tbody').find('input[type="checkbox"]').prop('checked', true);
  }
  function removeSelected(){
    $('tbody').find('input[type="checkbox"]').removeAttr('checked');
    $('tbody').find('input[type="checkbox"]').prop('checked', false);
  }

  function numberWithCommas(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }
</script>
@endpush