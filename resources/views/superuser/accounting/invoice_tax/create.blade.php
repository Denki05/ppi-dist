@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">FAT</span>
  <a class="breadcrumb-item" href="{{ route('superuser.accounting.invoice_tax.index') }}">Invoice Unifra</a>
  <span class="breadcrumb-item active">Create</span>
</nav>

<div id="alert-block"></div>

<div class="row">
  <div class="col-6">
    <div class="block">
      <div class="block-header block-header-default">
        <h3 class="block-title">#Detail Invoice</h3>
      </div>
      <div class="block-content">
        <div class="form-row">
        <div class="form-group col-md-6">
          <label for="so_date">Code</label>
          <input type="text" class="form-control" name="code" id="code">
          </div>
          <div class="form-group col-md-6">
            <label for="type_transaction">Invoice REAL</label>
            <input type="text" class="form-control" value="{{ $invoice->do_code }}" readonly>
            <input type="hidden" value="{{ $invoice->id }}" id="delivery_order" name="delivery_order">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="mitra_id">Mitra</label>
            <select class="js-select2 form-control" id="mitra_id" name="mitra_id" data-placeholder="Select Mitra">
              <option></option>
              @foreach($mitra as $row)
              <option value="{{ $row->id }}">{{ $row->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group col-md-6">
            <label for="type">Type</label>
            <input type="hidden" name="type" id="type" value="{{ $type }}">
            <input type="text" class="form-control" name="type_name" id="type_name" 
              value="{{ $type == 0 ? 'INVOICE UNIFRA JUAL' : 'INVOICE UNIFRA BELI' }}" readonly>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="invoice_tax_date">Tanggal</label>
            <input type="date" class="form-control" id="invoice_tax_date" name="invoice_tax_date">
          </div>
          <div class="form-group col-md-6">
            <label for="note">Note</label>
            <input type="text" class="form-control" id="note" name="note">
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-6">
    <div class="block">
      <div class="block-header block-header-default">
        <h3 class="block-title">#Detail Customer</h3>
      </div>
      <div class="block-content">
        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="so_date">Customer</label>
            <input type="text" class="form-control" value="{{ $invoice->member->name }} {{ $invoice->member->text_kota }}" readonly>
          </div>
          <div class="form-group col-md-6">
            <label for="type_transaction">Alamat</label>
            <input type="text" class="form-control" value="{{ $invoice->member->address }}" readonly>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="so_date">Kota</label>
            <input type="text" class="form-control" value="{{ $invoice->member->text_kota }}" readonly>
          </div>
          <div class="form-group col-md-6">
            <label for="type_transaction">Provinsi</label>
            <input type="text" class="form-control" value="{{ $invoice->member->text_provinsi }}" readonly>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


  <div class="block">
    <div class="block-content">
      <table id="datatable" class="table table-striped">
          <thead>
            <tr>
              <th class="text-center">#</th>
              <th class="text-center">Product</th>
              <th class="text-center">Quantity</th>
              <th class="text-center">Price</th>
              <th class="text-center">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            @foreach($products as $product)
              <tr>
                <input type="hidden" value="{{ $product['id'] }}" id="product_id" name="product_id">
                <td>{{ $loop->iteration }}</td>
                <td>{{ $product['code'] }} - {{ $product['name'] }} / {{ $product['kemasan'] }}</td>
                <td><input type="text" class="form-control text-center" value="{{ $product['qty'] }}" id="qty_item" readonly></td>
                <td>
                    <input type="text" class="form-control text-center" 
                          value="{{ $type == 0 ? $product['selling_price_usd_unit'] : $product['buying_price_usd_unit'] }}" 
                          id="price_selling" readonly>
                </td>
                <td><input type="text" class="form-control text-center" name="subtotal_item" id="subtotal_item" readonly></td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr class="row-footer-subtotal">
              <td colspan="4" class="text-right">
                <b>Subtotal</b>
              </td>
              <td class="text-center" style="width: 20%;">
                <input type="text" name="sub_total_item" id="sub_total_item" class="form-control text-right" readonly step="any">
              </td>
            </tr>
            <tr class="row-footer-subtotal">
              <td colspan="4" class="text-right">
                <b>PPN</b>
              </td>
              <td class="text-center">
                <div class="row">
                  <div class="col">
                    <input type="text" name="ppn_percent" id="ppn_percent" class="form-control text-right" step="any">
                  </div>

                  <div class="col">
                    <input type="text" name="ppn_idr" id="ppn_idr" class="form-control text-right" readonly step="any">
                  </div>
                </div>
              </td>
            </tr>
            <tr class="row-footer-subtotal">
              <td colspan="4" class="text-right">
                <b>Grand Total Invoice</b>
              </td>
              <td class="text-center">
                <input type="text" name="grand_total" id="grand_total" class="form-control text-right" readonly step="any">
              </td>
            </tr>
          </tfoot>
        </table>
        <div class="form-group row pt-30">
          <div class="col-md-6">
            <a href="javascript:history.back()">
              <button type="button" class="btn bg-gd-cherry border-0 text-white">
                <i class="fa fa-arrow-left mr-10"></i> Back
              </button>
            </a>
          </div>
          <div class="col-md-6 text-right">
            <!-- <button type="button" class="btn btn-warning" id="calculate-btn"><i class="fa-solid fa-calculator"></i> Calculated</button> -->
            <button type="submit" class="btn bg-gd-corporate border-0 text-white" id="submit-table">
              Submit <i class="fa fa-arrow-right ml-10"></i>
            </button>
          </div>
        </div>
    </div>
  </div>

@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function () {
    var table = $('#datatable').DataTable({
        // DataTable options here
    });

    $('.js-select2').select2();
    
    $(".js-example-tags").select2({
      tags: true
    });

    function calculateTotals() {
        let totalSubtotal = 0;
        let ppnPercent = parseFloat($('#ppn_percent').val()) || 0;
        let ppnAmount = 0;
        let grandTotal = 0;

        // Iterate through each row in the table body
        $('#datatable tbody tr').each(function() {
            // Get quantity and price for the current row
            let qty = parseFloat($(this).find('#qty_item').val()) || 0;
            let price = parseFloat($(this).find('#price_selling').val()) || 0;

            // Calculate the subtotal for the current row
            let subtotal = qty * price;
            totalSubtotal += subtotal;

            // Update the subtotal input for the current row
            $(this).find('#subtotal_item').val(subtotal.toFixed(2));
        });

        // Update subtotal in the footer
        $('#sub_total_item').val(totalSubtotal.toFixed(2));

        // Calculate PPN amount
        ppnAmount = (totalSubtotal * ppnPercent) / 100;
        $('#ppn_idr').val(ppnAmount.toFixed(2));

        // Calculate grand total
        grandTotal = totalSubtotal + ppnAmount;
        $('#grand_total').val(grandTotal.toFixed(2));
    }

    // Calculate totals when the PPN percentage changes
    $('#ppn_percent').on('input', function() {
        calculateTotals();
    });

    // Initial calculation when the page loads
    calculateTotals();

    // Function to store product data
    function storeProductData() {
        let products = [];
        // Gather product data from the table
        $('#datatable tbody tr').each(function() {
            let qty = parseFloat($(this).find('#qty_item').val()) || 0;
            let price = parseFloat($(this).find('#price_selling').val()) || 0;
            let subtotal = parseFloat($(this).find('#subtotal_item').val()) || 0;
            let id = $(this).find('#product_id').val(); // Assuming ID is in the first cell

            if (price > 0) { // Ensure price is greater than 0 before adding to the products array
                products.push({
                    id: id,
                    qty: qty,
                    price: price,
                    total: subtotal
                });
            }
        });

        // Check if products array is empty
        if (products.length === 0) {
            alert('Harga belum di setting, Silahkan input data terlebih dahulu!!');
            return;
        }

        // Gather other form data
        let code = $('#code').val();
        let delivery = $('#delivery_order').val();
        let type = $('#type').val();
        let mitra = $('#mitra_id').val();
        let date = $('#invoice_tax_date').val();
        let note = $('#note').val();
        let subtotal = $('#sub_total_item').val();
        let ppn_idr = $('#ppn_idr').val();
        let ppn_percent = $('#ppn_percent').val();
        let grandTotal = $('#grand_total').val();

        // Send data using AJAX
        $.ajax({
            url: '{{ route('superuser.accounting.invoice_tax.store') }}',
            type: 'POST',
            data: {
                products: products,
                code: code,
                delivery: delivery,
                type: type,
                mitra: mitra,
                date: date,
                note: note,
                subtotal: subtotal,
                ppn_idr: ppn_idr,
                ppn_percent: ppn_percent,
                grand_total: grandTotal,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.success) {
                    alert('Invoice data has been successfully saved!');
                    window.location.href = response.redirect_url;
                } else {
                    alert('Failed to save invoice data.');
                }
            },
            error: function(xhr, status, error) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    var errorMessages = '';

                    $.each(errors, function(key, value) {
                        errorMessages += value[0] + '\n';
                    });

                    alert('Validation failed:\n' + errorMessages);
                } else {
                    alert('Error occurred while saving invoice data: ' + error);
                }
            }
        });
    }

    // Trigger store product data when the "Submit" button is clicked
    $('#submit-table').on('click', function() {
        calculateTotals(); // Ensure calculations are done before saving
        storeProductData(); // Proceed with saving
    });
});
</script>
@endpush