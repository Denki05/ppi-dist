@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Purchasing</span>
  <span class="breadcrumb-item">Receiving</span>
  <span class="breadcrumb-item">{{ $receiving->code }}</span>
  <span class="breadcrumb-item active">Add Detail</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Add Detail</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.gudang.receiving.detail.store', $receiving->id) }}" data-type="POST" enctype="multipart/form-data">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="ppb">Select PO <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="ppb" name="ppb" data-placeholder="Select PO">
            <option></option>
            @foreach($purchase_orders as $purchase_order)
            <option value="{{ $purchase_order->id }}">{{ $purchase_order->code }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="ppb_detail">Select Product <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="ppb_detail" name="ppb_detail" data-placeholder="Select Product">
            <option></option>
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="product_text">Packaging</label>
        <div class="col-md-4">
          <input type="text" class="form-control" id="product_text" name="product_text" readonly>
        </div>
      </div>
      <input type="hidden" class="form-control" id="product" name="product">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="quantity">Qty</label>
        <div class="col-md-4">
          <input type="number" class="form-control" id="quantity" name="quantity" readonly>
        </div>
      </div>
      {{--<div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="delivery_cost">Sea Freight</label>
        <div class="col-md-4">
          <input type="number" class="form-control" id="delivery_cost" name="delivery_cost" min="0" value="0" step="any">
        </div>
      </div>--}}
      {{--<div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="no_batch">NO Batch</label>
        <div class="col-md-4">
          <input type="text" class="form-control" id="no_batch" name="no_batch">
        </div>
      </div>--}}
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="description">Note</label>
        <div class="col-md-4">
          <textarea class="form-control" id="description" name="description"></textarea>
          {{-- <input type="number" class="form-control" id="description" name="description"> --}}
        </div>
      </div>

      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.gudang.receiving.step', $receiving->id) }}">
            <button type="button" class="btn bg-gd-cherry border-0 text-white">
              <i class="fa fa-arrow-left mr-10"></i> Back
            </button>
          </a>
        </div>
        <div class="col-md-6 text-right">
          <button type="submit" class="btn bg-gd-corporate border-0 text-white">
            Submit <i class="fa fa-arrow-right ml-10"></i>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {
    function addLoadSpiner(el) {
      if (el.length > 0) {
        if ($("#img_" + el[0].id).length > 0) {
          $("#img_" + el[0].id).css('display', 'block');
        }               
        else {
          var img = $('<img class="ddloading">');
          img.attr('id', "img_" + el[0].id);
          img.attr('src', 'http://ajaxloadingimages.net/gif/image?imageid=aero-spinner&forecolor=000000&backcolor=ffffff&transparent=true');
          img.css({ 'display': 'inline-block', 'width': '25px', 'height': '25px', 'position': 'absolute', 'left': '50%', 'margin-top': '5px' });
          img.prependTo(el[0].nextElementSibling);
        }
        el.prop("disabled", true);               
      }
    }

    function hideLoadSpinner(el) {
      if (el.length > 0) {
        if ($("#img_" + el[0].id).length > 0) {
          setTimeout(function () {
            $("#img_" + el[0].id).css('display', 'none');
            el.prop("disabled", false);
          }, 500);                  
        }
      }
    }

    $("#ppb").on('select2:select', function() {
      var ppb = $("#ppb").val();

      $.ajax({
        url: '{{ route('superuser.gudang.receiving.detail.get_sku_json') }}',
        data: {id:ppb, type: "GET_SELECT_SKU", _token: "{{csrf_token()}}"},
        type: 'POST',
        cache: false,
        dataType: 'json',
        beforeSend: function () {
          addLoadSpiner($('#ppb_detail'))
        },
        complete: function () {
          hideLoadSpinner($('#ppb_detail'))
        },
        success: function(json) {
          $('#ppb_detail').empty().trigger('change');
          $('#product_text, #quantity').val('');
          console.log(json);
          if (json.code == 200) {
            let ph = new Option('', '', false, false);
            $('#ppb_detail').append(ph).trigger('change');

            for (i = 0; i < Object.keys(json.data).length; i++) {
              let newOption = new Option(json.data[i].product.code+' - '+json.data[i].product.name+' - '+json.data[i].packaging.pack_name ,json.data[i].po_detail_id, false, false);
              $('#ppb_detail').append(newOption).trigger('change');
            }

            let data_val = $('#ppb_detail').data('value')
            if (data_val > 0) {
              $('#ppb_detail').val(data_val);
              $('#ppb_detail').trigger('select2:select');
            }
          }
        }
      });
    });

    $("#ppb_detail").on('select2:select', function() {
      var ppb_detail = $("#ppb_detail").val();

      $.ajax({
        url: '{{ route('superuser.gudang.receiving.detail.get_sku_json') }}',
        data: {id:ppb_detail, type: "GET_TEXT_DETAIL", _token: "{{csrf_token()}}"},
        type: 'POST',
        cache: false,
        dataType: 'json',
        success: function(json) {
          $('#product_text, #quantity').val('');
          console.log(json);
          if (json.code == 200) {
            $('#product_text').val(json.data.packaging.pack_name);
            $('#quantity').val(json.data.quantity);
            $('#product').val(json.data.purchase_order_detail.product_packaging_id);
          }
        }
      });
    });

    $('.js-select2').select2()
  })
</script>
@endpush