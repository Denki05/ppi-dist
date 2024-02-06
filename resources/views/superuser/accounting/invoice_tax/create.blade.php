@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Sale</span>
  <a class="breadcrumb-item" href="{{ route('superuser.accounting.invoice_tax.index') }}">Invoice TAX</a>
  <span class="breadcrumb-item active">Create</span>
</nav>
<div id="alert-block"></div>

<form class="ajax" data-action="{{ route('superuser.accounting.invoice_tax.store') }}" data-type="POST" enctype="multipart/form-data">
  <div class="block">
    <div class="block-header block-header-default">
      <h3 class="block-title">Create Invoice TAX</h3>
    </div>
    <div class="block-content">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">Code <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)" value="{{ App\Repositories\CodeRepo::generateINVTAX() }}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="delivery_order">Invoice REAL <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control js-select2-do" id="delivery_order" name="delivery_order" data-placeholder="Select Invoice">
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="idr_rate">Kurs <span class="text-danger">*</span></label>
        <div class="col-md-4">
          <input type="text" class="form-control" name="idr_rate" id="idr_rate" readonly>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="mitra_id">Mitra <span class="text-danger">*</span></label>
        <div class="col-md-4">
          <select class="js-select2 form-control" id="mitra_id" name="mitra_id" data-placeholder="Select Mitra">
            <option></option>
            @foreach($mitra as $row)
            <option value="{{ $row->id }}">{{ $row->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="type">Type <span class="text-danger">*</span></label>
        <div class="col-md-4">
          <select class="js-select2 form-control" id="type" name="type" data-placeholder="Select Type">
            <option></option>
            <option value="1">INVOICE TAX JUAL</option>
            <option value="2">INVOICE TAX BELI</option>
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="invoice_tax_date">Tanggal</label>
        <div class="col-md-4">
          <input type="date" class="form-control" id="invoice_tax_date" name="invoice_tax_date">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="invoice_tax_date">Note</label>
        <div class="col-md-4">
          <input type="text" class="form-control" id="note" name="note">
        </div>
      </div>
      <div class="form-group row pt-30">
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
  <div class="block">
    <div class="block-header">
      <h3 class="block-title">Add Product TAX</h3>
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
            <th class="text-center">Counter</th>
            <th class="text-center">Select SKU</th>
            <th class="text-center">Quantity</th>
            <th class="text-center">Price(USD)</th>
            <th class="text-center">Subtotal</th>
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
          <tr class="row-footer-subtotal">
            <td colspan="4" class="text-right">
              <b>Subtotal</b>
            </td>
            <td class="text-right">
              <input type="text" name="sub_total_item" id="sub_total_item" class="form-control " readonly step="any">
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
<script type="text/javascript">
  $(document).ready(function () {
    $('.js-select2').select2()

    $(".js-select2-do").select2({
      ajax: {
        url: '{{ route('superuser.accounting.invoice_tax.search_invreal') }}',
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

    var product_data = new Object();

    var table = $('#datatable').DataTable({
        paging: false,
        bInfo : false,
        searching: false,
        columns: [
          {name: 'counter', "visible": false},
          {name: 'sku', orderable: false, width: "25%"},
          {name: 'quantity', orderable: false, searcable: false, width: "5%"},
          {name: 'price_satuan', orderable: false, searcable: false,  width: "10%"},
          {name: 'subtotal', orderable: false, searcable: false,  width: "20%"},
          {name: 'action', orderable: false, searcable: false, width: "5%"}
        ],
        'order' : [[0,'desc']]
    })

    var counter = 1;

    $('a.row-add').on( 'click', function (e) {
      e.preventDefault();
      if($('#delivery_order').val()) {
        if($('#type').val() == 1) {
          $('#submit-table').prop('disabled', false);
          
          makeselect = '<select class="js-select2 form-control js-ajax" id="sku['+counter+']" name="sku[]" data-placeholder="Select SKU" style="width:100%" required><option></option>';
          
          $.map( product_data, function( val, i ) {
            makeselect += '<option value="'+ val['id'] +'" data-name="'+ val['name'] +'" data-code="'+ val['code'] +'" data-pricejualsatuan="'+ val['selling_price_usd_unit'] +'" data-quantity="'+ val['qty'] +'" data-kurs="'+ val['kurs'] +'">'+ val['code'] + ' - '+ val['name'] +'</option>';
          });

          makeselect += '</select>';

          table.row.add([
                      counter,
                      makeselect,
                      '<input type="number" class="form-control" name="quantity[]" min="1" required>',
                      '<input type="number" class="form-control" name="price_satuan[]">',
                      '<input type="number" class="form-control" name="subtotal[]" readonly>',
                      '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
                    ]).draw( false );
                    
                    initailizeSelect2();
          counter++;
        } else if ($('#type').val() == 2){
            $('#submit-table').prop('disabled', false);
            
            makeselect = '<select class="js-select2 form-control js-ajax" id="sku['+counter+']" name="sku[]" data-placeholder="Select SKU" style="width:100%" required><option></option>';
            
            $.map( product_data, function( val, i ) {
              makeselect += '<option value="'+ val['id'] +'" data-name="'+ val['name'] +'" data-code="'+ val['code'] +'" data-pricebelisatuan="'+ val['buying_price_usd_unit'] +'" data-quantity="'+ val['qty'] +'" data-kurs="'+ val['kurs'] +'">'+ val['code'] + ' - '+ val['name'] +'</option>';
            });

            makeselect += '</select>';

            table.row.add([
                        counter,
                        makeselect,
                        '<input type="number" class="form-control" name="quantity[]" step="any" required>',
                        '<input type="number" class="form-control" name="price_satuan[]" step="any">',
                        '<input type="number" class="form-control" name="subtotal[]" readonly step="any">',
                        '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
                      ]).draw( false );
                      
                      initailizeSelect2();
            counter++;

        }
      }
      
    });

    function initailizeSelect2(){
      $(".js-ajax").select2();

      $('.js-ajax').on('select2:select', function (e) {
        var name = $(this).find(':selected').data('code');
        $(this).parents('tr').find('.name').text(name);

        var kurs = $(this).find(':selected').data('kurs');
        $("#idr_rate").val(kurs);

        

        var quantity = $(this).find(':selected').data('quantity');
        $(this).parents('tr').find('input[name="quantity[]"]').val(quantity);

        if($('#type').val() == 1){

          var harga_jual_tax_drum = $(this).find(':selected').data('pricejualdrum');
          $(this).parents('tr').find('input[name="price_drum[]"]').val(harga_jual_tax_drum);

          var harga_jual_tax_satuan = $(this).find(':selected').data('pricejualsatuan');
          $(this).parents('tr').find('input[name="price_satuan[]"]').val(harga_jual_tax_satuan);

        } else if ($('#type').val() == 2){

          var harga_beli_tax_drum = $(this).find(':selected').data('pricebelidrum');
          $(this).parents('tr').find('input[name="price_drum[]"]').val(harga_beli_tax_drum);

          var harga_beli_tax_satuan = $(this).find(':selected').data('pricebelisatuan');
          $(this).parents('tr').find('input[name="price_satuan[]"]').val(harga_beli_tax_satuan);
        }
      });
    };


    $('#datatable tbody').on( 'click', '.row-delete', function (e) {
      e.preventDefault();
      table.row( $(this).parents('tr') ).remove().draw();

      if(typeof $('input[name="id[]"]').val() == 'undefined') {
        $('#submit-table').prop('disabled', true);
      }
    });

    $('#delivery_order').on('select2:select', function (e) {
      table.clear().draw();

      $.ajax({
        url: '{{ route('superuser.accounting.invoice_tax.get_product') }}',
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

    $('#type').on('select2:select', function (e) {
      table.clear().draw();
    });

    $('#datatable tbody').on( 'keyup', 'input[name="quantity[]"]', function (e) {
      var price_satuan = $(this).parents('tr').find('input[name="price_satuan[]"]').val();
      var kurs = $('#idr_rate').val();

      var total = ($(this).val() * price_satuan) * kurs;

      $(this).parents('tr').find('input[name="subtotal[]"]').val(total);
      $(this).parents('tr').find('input[name="subtotal[]"]').change();

    });

    $('#datatable tbody').on( 'keyup', 'input[name="price_satuan[]"]', function (e) {
      var qty = $(this).parents('tr').find('input[name="quantity[]"]').val();
      var kurs = $('#idr_rate').val();

      var total = (qty * $(this).val()) * kurs;

      $(this).parents('tr').find('input[name="subtotal[]"]').val(total);
      $(this).parents('tr').find('input[name="subtotal[]"]').change();

    });

    $('#datatable tbody').on( 'change', 'input[name="subtotal[]"]', function (e) {
      var subtotal = 0;
      $('input[name="subtotal[]"]').each(function(){
        subtotal += Number($(this).val());
      });
      $('#sub_total_item').val(subtotal);
    });
  })
</script>
@endpush
