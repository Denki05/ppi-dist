@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <a class="breadcrumb-item" href="{{ route('superuser.penjualan.sale_return.index') }}">Retur</a>
  <span class="breadcrumb-item active">Create</span>
</nav>
<div id="alert-block"></div>

<form class="ajax" data-action="{{ route('superuser.penjualan.sale_return.store') }}" data-type="POST" enctype="multipart/form-data">
  <div class="row">
    <div class="col-md-12">
      <div class="block">
        <div class="block-header block-header-default">
          <h3 class="block-title">Create Return</h3>
        </div>
        <div class="block-content">
          <div class="row mb-3">
            <label class="col-md-3 col-form-label text-right" for="code">Code <span class="text-danger">*</span></label>
            <div class="col-md-7">
              <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)" value="{{ App\Repositories\CodeRepo::generateReturCode() }}" readonly>
            </div>
          </div>
          <div class="row mb-3">
            <label class="col-md-3 col-form-label text-right" for="type">Type <span class="text-danger">*</span></label>
            <div class="col-md-7">
              <select class="js-select2 form-control" id="type" name="type" data-placeholder="Select Type" require>
                <option></option>
                @foreach(\App\Entities\Penjualan\SaleReturn::TYPE as $key => $value)
                <option value="{{ $value }}">{{ $key }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <label class="col-md-3 col-form-label text-right" for="delivery_order">Invoice <span class="text-danger">*</span></label>
            <div class="col-md-7">
              <select class="js-select2 form-control js-select2-do" id="delivery_order" name="delivery_order" data-placeholder="Search Invoice" require>
              </select>
            </div>
          </div>
          <div class="row pt-30">
            <div class="col-md-6">
              <a href="javascript:history.back()">
                <button type="button" class="btn bg-gd-cherry border-0 text-white">
                  <i class="fa fa-arrow-left mr-10"></i> Back
                </button>
              </a>
            </div>
            <div class="col-md-6 text-right">
              <button type="submit" class="btn bg-gd-corporate border-0 text-white" id="submit-table" disabled>
                Submit <i class="fa fa-arrow-right ml-10"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="block">
    <div class="block-header">
      <h3 class="block-title">Add Product</h3>
      <a href="#" class="row-add">
        <button type="button" class="btn bg-gd-sea border-0 text-white">
          <i class="fa fa-plus mr-10"></i> Row
        </button>
      </a>
    </div>
    <div class="block-content">
      <table id="datatable" class="table table-striped">
        <thead>
          <tr>
            <th style="width: 2%;">#</th>
            <th style="width: 15%;">Product</th>
            <th style="width: 10%;">Kemasan</th>
            <th class="text-right" style="width: 5%;">Acuan</th>
            <th class="text-right" style="width: 5%;">Qty</th>
            <th class="text-right" style="width: 5%;">Disc (USD)</th>
            <th class="text-center" style="width: 10%;">Jumlah</th>
            <th class="text-center" style="width: 10%;">Action</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
          <tr class="row-footer-subtotal">
            <td colspan="6" class="text-end">
              <b>Subtotal :</b>
            </td>
            <td class="text-end">
              <input type="text" name="subtotal_item" id="subtotal_item" class="form-control text-end" readonly step="any">
            </td>
          </tr>
          <tr class="row-footer-subtotal">
            <td colspan="5" class="text-end">
              <b>Disc (%)</b>
            </td>
            <td class="text-center" style="width: 100px;">
              <input type="text" name="disc_percent" id="disc_percent" class="form-control text-center" readonly step="any">
            </td>
            <td class="text-end">
              <input type="text" name="disc_amount_1" id="disc_amount_1" class="form-control text-end" readonly step="any">
             </td>
          </tr>
          <tr class="row-footer-subtotal">
            <td colspan="5" class="text-end">
              <b>Disc Kemasan</b>
            </td>
            <td class="text-center">
              <input type="text" name="disc_percent_2" id="disc_percent_2" class="form-control text-center" readonly step="any">
            </td>
            <td class="text-end">
              <input type="text" name="disc_amount_2" id="disc_amount_2" class="form-control text-end" readonly step="any">
            </td>
          </tr>
          <tr class="row-footer-subtotal">
            <td colspan="6" class="text-end">
              <b>Disc IDR</b>
            </td>
            <td class="text-end">
                <input type="text" name="disc_idr" id="disc_idr" class="form-control text-end" readonly step="any">
            </td>
          </tr>
          <tr class="row-footer-subtotal">
            <td colspan="6" class="text-end">
              <b>Grand Total</b>
            </td>
            <td class="text-end">
              <input type="text" name="grand_total" id="grand_total" class="form-control text-end" readonly step="any">
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</form>
@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {
    $('.js-select2').select2()

    $(".js-select2-do").select2({
      ajax: {
        url: '{{ route('superuser.penjualan.sale_return.search_do') }}',
        dataType: 'json',
        delay: 250,
        data: function (params) {
          return {
            q: params.term,
            _token: "{{csrf_token()}}"
          };
        },
        cache: true
      },
    });

    var product_data = [];

    var table = $('#datatable').DataTable({
        paging: false,
        bInfo : false,
        searching: false,
        columns: [
          {name: 'counter', "visible": false},
          {name: 'product', orderable: false, width: "15%"},
          {name: 'kemasan', orderable: false, width: "5%"},
          {name: 'acuan', orderable: false, width: "5%"},
          {name: 'qty', orderable: false, width: "5%"},
          {name: 'disc', orderable: false, width: "5%"},
          {name: 'jumlah', orderable: false, width: "10%"},
          {name: 'action', orderable: false, width: "5%"}
        ],
        'order' : [[0,'desc']]
    })
  
    var counter = 1;
  
    $('a.row-add').on( 'click', function (e) {
      e.preventDefault();
      if($('#delivery_order').val()) {
        $('#submit-table').prop('disabled', false);
        
        makeselect = '<select class="js-select2 form-control js-ajax" id="sku['+counter+']" name="sku[]" data-placeholder="Select Product" style="width:100%" required><option></option>';

        $.map(product_data, function(val, i) {
          makeselect += '<option value="'+ val['id'] +'" data-name="'+ val['name'] +'" data-sku="'+ val['sku'] +'" data-quantity="'+ val['quantity'] + '" data-kemasan="'+ val['kemasan'] +'" data-acuan="'+ val['acuan'] +'" data-disc_usd="'+ val['disc_usd'] +'" data-disc_1="'+ val['discount_percent'] +'" data-disc_2="'+ val['discount_kemasan'] +'" data-disc_idr="'+ val['discount_idr'] +'" data-idr_rate="'+ val['idr_rate'] +'">'+ val['sku'] +' - '+ val['name'] +'</option>';
        });

        makeselect += '</select>';

        table.row.add([
          counter,
          makeselect, // Select product
          '<span class="packaging"></span>',
          '<input type="number" class="form-control text-right" name="acuan[]" min="0.01" step="0.01" readonly><input type="hidden" name="kurs[]">', // Acuan info
          '<input type="number" class="form-control text-right" name="quantity[]" id="quantity['+counter+']" min="0.01" step="0.01" required>',
          '<input type="number" class="form-control text-right" name="disc_usd[]" id="disc_usd['+counter+']" min="0.01" step="0.01" readonly>',
          '<input type="number" class="form-control text-right" name="jumlah[]" id="jumlah['+counter+']" min="0.01" step="0.01" readonly>',
          '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>',
        ]).draw(false);
                  
                  initailizeSelect2();
        counter++;
      }
      
    });

    function initailizeSelect2(){
      $(".js-ajax").select2();

      $('.js-ajax').on('select2:select', function (e) {
        var sku = $(this).find(':selected').data('sku');
        var quantity = $(this).find(':selected').data('quantity');
        var kemasan = $(this).find(':selected').data('kemasan');
        var acuan = $(this).find(':selected').data('acuan');
        var disc_usd = $(this).find(':selected').data('disc_usd');
        var disc_1 = $(this).find(':selected').data('disc_1');
        var disc_2 = $(this).find(':selected').data('disc_2');
        var disc_idr = $(this).find(':selected').data('disc_idr');
        var idr_rate = $(this).find(':selected').data('idr_rate');

        $(this).parents('tr').find('.packaging').text(kemasan);
        $(this).parents('tr').find('input[name="quantity[]"]').prop('max', quantity);
        $(this).parents('tr').find('input[name="quantity[]"]').prop('placeholder', quantity);
        $(this).parents('tr').find('input[name="acuan[]"]').val(acuan);
        $(this).parents('tr').find('input[name="disc_usd[]"]').val(disc_usd);
        $(this).parents('tr').find('input[name="kurs[]"]').val(idr_rate);
        $('input[name="disc_percent"]').val(disc_1);
        $('input[name="disc_percent_2"]').val(disc_2);
        $('input[name="disc_idr"]').val(disc_idr);
      });

    };

    // calculate jumlah
    $('#datatable tbody').on('keyup', 'input[name="quantity[]"]', function () {
      var qty = $(this).val();
      var acuan = $(this).parents('tr').find('input[name="acuan[]"]').val();
      var disc_usd = $(this).parents('tr').find('input[name="disc_usd[]"]').val();
      var kurs = $(this).parents('tr').find('input[name="kurs[]"]').val();
      var jumlah = 0;
      if(qty && acuan) {
        jumlah = ((acuan - disc_usd) * qty) * kurs;
      }
      $(this).parents('tr').find('input[name="jumlah[]"]').val(jumlah.toFixed(2));
      calculateTotal();
    });

    function calculateTotal() {
      var subtotal = 0;
      $('input[name="jumlah[]"]').each(function() {
      var val = parseFloat($(this).val());
      if (!isNaN(val)) {
        subtotal += val;
      }
      });
      $('#subtotal_item').val(subtotal.toFixed(2));

      // Get discount values
      var disc_percent = parseFloat($('#disc_percent').val()) || 0;
      var disc_percent_2 = parseFloat($('#disc_percent_2').val()) || 0;
      var disc_idr = parseFloat($('#disc_idr').val()) || 0;

      // Calculate disc_amount_1 (percent of subtotal)
      var disc_amount_1 = subtotal * (disc_percent / 100);
      $('#disc_amount_1').val(disc_amount_1.toFixed(2));

      // Calculate disc_amount_2 (percent of subtotal)
      var total_after_disc_1 = subtotal - $('#disc_amount_1').val();
      var disc_amount_2 = total_after_disc_1 * (disc_percent_2 / 100);
      $('#disc_amount_2').val(disc_amount_2.toFixed(2));

      // Calculate grand total
      var grand_total = subtotal - disc_amount_1 - disc_amount_2 - disc_idr;
      $('#grand_total').val(grand_total.toFixed(2));
    }

    
    $('#datatable').on('draw.dt', function () {
      calculateTotal();
    });

    $('#datatable tbody').on('change', 'input[name="quantity[]"]', function () {
      calculateTotal();
    });

    // Recalculate when a row is deleted
    $('#datatable tbody').on('click', '.row-delete', function () {
      setTimeout(function() {
        calculateTotal();
      }, 100); // wait for row to be removed
    });

    $('#datatable tbody').on( 'click', '.row-delete', function (e) {
      e.preventDefault();
      table.row( $(this).parents('tr') ).remove().draw();

      if(typeof $('input[name="id[]"]').val() == 'undefined') {
        $('#submit-table').prop('disabled', true);
      }
    });

    $('#delivery_order').on('select2:select', function (e) {
      table.clear().draw();

      $('input[name="disc_percent"]').val('');
      $('input[name="disc_percent_2"]').val('');
      $('input[name="disc_idr"]').val('');
      $('input[name="disc_amount_1"]').val('');
      $('input[name="disc_amount_2"]').val('');
      $('input[name="subtotal_item"]').val('');
      $('input[name="grand_total"]').val('');

      $.ajax({
        url: '{{ route('superuser.penjualan.sale_return.get_product') }}',
        data: {id:$(this).val() , _token: "{{csrf_token()}}"},
        type: 'POST',
        cache: false,
        dataType: 'json',
        success: function(json) {
          if (json.code == 200) {
            product_data = json.data;
          }
        }
      });

    });

  })
</script>
@endpush
