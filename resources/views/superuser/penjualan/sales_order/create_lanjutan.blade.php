@foreach($result->so_detail as $index => $row)
  @php $row->product->name @endphp
@endforeach

@extends('superuser.app')

@section('content')
<div id="main">
    <div class="main-column" id="main-column-left">
      <div class="content">
      <h5>#Detail SO</h5>
      <hr>
        <div class="row">
          <label class="col-xs-4 col-sm-4 col-md-4 control-label" for="textinput">Code</label>
          <div class="col-xs-8 col-sm-8 col-md-8">
            <p>{{ $result->code }}</p>
          </div>
        </div>
        <div class="row">
          <label class="col-xs-4 col-sm-4 col-md-4 control-label" for="textinput">Create</label>
          <div class="col-xs-8 col-sm-8 col-md-8">
            <p>{{ date('d-m-Y', strtotime($result->created_at)) }}</p>
          </div>
        </div>
        <div class="row">
          <label class="col-xs-4 col-sm-4 col-md-4 control-label" for="textinput">Customer</label>
          <div class="col-xs-8 col-sm-8 col-md-8">
            <p>{{ $result->member->name }}</p>
          </div>
        </div>
        <div class="row">
          <label class="col-xs-4 col-sm-4 col-md-4 control-label" for="textinput">Alamat</label>
          <div class="col-xs-8 col-sm-8 col-md-8">
            <p>{{ $result->member->address }}</p>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-xs-4 col-sm-4 col-md-4 control-label" for="origin_warehouse_id">Gudang<span class="text-danger">*</span></label>
            <div class="col-10">
              <select class="form-control js-select2" name="origin_warehouse_id">
                <option value="">Pilih Gudang</option>
                @foreach($warehouse as $index => $row)
                <option value="{{$row->id}}" @if($result->origin_warehouse_id == $row->id) selected @endif>{{$row->name}}</option>
                @endforeach
              </select>
            </div>
        </div>
      </div>
    </div>
    <div class="main-column" id="main-column-middle">
      <div class="content">
      <h5>#Detail Pesanan</h5>
      <hr>
        <table class="table table-bordered" id="tableDetailPesanan">
            <thead>
              <tr>
                <th scope="col" width="5%">NO</th>
                <th scope="col" width="15%">Product</th>
                <th scope="col" width="5%">Qty</th>
                <th scope="col" width="10%">Kemasan</th>
                <th scope="col" width="5%">In Stock</th>
                <th scope="col" width="5%">Price</th>
                <th scope="col" width="20%">Subtotal</th>
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
                      <td>{{$index + 1}}</td>
                      <td>{{$row->product->code ?? ''}} - {{$row->product->name ?? ''}}</td>
                      <td>{{$row->qty}}</td>
                      <td>{{$row->packaging_txt()->scalar ?? ''}}</td>
                      <td>
                        <input type="number" name="repeater[{{$index}}][do_qty]" class="form-control count" data-index="{{$index}}" value="{{$row->qty}}" step="any" min="0" max="{{$row->qty}}">
                      </td>
                      <td>
                        ${{$row->product->selling_price}}
                      </td>
                      <td>

                      </td>
                    </tr>
                @endforeach
              @endif
          </tbody>
        </table>
      </div>
    </div>
    <div class="main-column" id="main-column-right">
      <div class="content">
      <h5>#Discount</h5>
      <hr>
        <div class="row">
        @if($step == 2)
                    <div class="form-group row">
                      <label class="col-md-2 col-form-label text-right">Kurs<span class="text-danger">*</span></label>
                      <div class="col-md-10">
                        <input type="text" name="idr_rate" class="form-control" step="any">
                      </div>
                    </div>
                    @endif
        </div>
      </div>
    </div>
</div>


<!-- Modal -->
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