@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Finance</span>
  <a class="breadcrumb-item" href="">Cash/Bank Payment (Inv)</a>
  <span class="breadcrumb-item active">Create</span>
</nav>
<div id="alert-block"></div>

<form class="ajax" data-action="{{ route('superuser.finance.payment_invoice.store') }}" data-type="POST" enctype="multipart/form-data">
  <div class="block">
    <div class="block-header block-header-default">
      <h3 class="block-title">Create Cash/Bank Payment (Inv)</h3>
    </div>
    <div class="block-content">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="code">Code <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="code" name="code" onkeyup="nospaces(this)">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="select_date">Select Date <span class="text-danger">*</span></label>
        <div class="col-md-4">
          <input type="date" class="form-control" id="select_date" name="select_date">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="coa">Select Account <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="coa" name="coa" data-placeholder="Select COA">
            @foreach($coa as $item)
              <option></option>
              <option value="{{ $item->id }}">{{ $item->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="supplier">Select Supplier <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="supplier" name="supplier" data-placeholder="Select Supplier">
            @foreach($suppliers as $item)
              <option></option>
              <option value="{{ $item->id }}">{{ $item->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="transaction_type">Transaction Type <span class="text-danger">*</span></label>
        <div class="col-md-7 text-right">
          <div class="col-md-3 form-check form-check-inline">
            <input class="form-check-input" type="radio" name="transaction_type" id="transaction_type1" value="1">
            <label class="form-check-label" for="transaction_type1">Tunai</label>
          </div>
          <div class="col-md-3 form-check form-check-inline">
            <input class="form-check-input" type="radio" name="transaction_type" id="transaction_type0" value="0">
            <label class="form-check-label" for="transaction_type0">Non Tunai (Hutang Dagang)</label>
          </div>
          <div class="col-md-3 form-check form-check-inline">
            <input class="form-check-input" type="radio" name="transaction_type" id="transaction_type0" value="2">
            <label class="form-check-label" for="transaction_type2">Non Tunai (Hutang Ekspedisi)</label>
          </div>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="address">Address</label>
        <div class="col-md-7">
          <textarea class="form-control" id="address" name="address" readonly></textarea>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="note">Note</span></label>
        <div class="col-md-7">
          <textarea class="form-control" id="note" name="note"></textarea>
        </div>
      </div>
    </div>
    <hr>
    <div class="block-content">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="select_pbm">Select PBM No.</label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="select_pbm" name="select_pbm" data-placeholder="Select PBM No.">
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="select_total">Total</label>
        <div class="col-md-7">
          <input type="number" class="form-control" id="select_total" name="select_total" readonly>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-md-10 text-right">
          <a href="#" id="add">
            <button type="button" class="btn bg-gd-sea border-0 text-white">
              <i class="fa fa-plus mr-10"></i> ADD
            </button>
          </a>
        </div>
      </div>
    </div>
    <hr>
    <div class="block-content">
      <table id="datatable" class="table table-striped table-vcenter table-responsive">
        <thead>
          <tr>
            <th class="text-center">Counter</th>
            <th class="text-center">PBM No.</th>
            <th class="text-center">Total</th>
            <th class="text-center">Paid</th>
            <th class="text-center"></th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
      <div class="form-group row justify-content-end">
        <label class="col-md-3 col-form-label text-right" for="subtotal_total">Grand Total</label>
        <div class="col-md-3">
          <input type="text" class="form-control" id="subtotal_total" name="subtotal_total" readonly>
        </div>
        <div class="col-md-3">
          <input type="text" class="form-control" id="subtotal_paid" name="subtotal_paid" readonly>
        </div>
        <div class="col-md-1">
        </div>
      </div>
      <hr>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="total_payment">Total Payment</label>
        <div class="col-md-6">
          <input type="text" class="form-control" id="total_payment" name="total_payment" readonly>
        </div>
        {{-- <div class="col-md-1">
        </div> --}}
      </div>
    </div>
    
    <div class="block-content">
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.finance.payment_invoice.index') }}">
            <button type="button" class="btn bg-gd-cherry border-0 text-white">
              <i class="fa fa-arrow-left mr-10"></i> Back
            </button>
          </a>
        </div>
        <div class="col-md-6 text-right">
          <button type="submit" class="btn bg-gd-corporate border-0 text-white" id="submit-table">
            Submit <i class="fa fa-arrow-right ml-10"></i>
          </button>
        </div>
      </div>
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
          setTimeout(function () {4
            $("#img_" + el[0].id).css('display', 'none');
            el.prop("disabled", false);
          }, 500);                  
        }
      }
    }

    $('#supplier').on('select2:select', function (e) {
      table.clear().draw();
      $.ajax({
        url: '{{ route('superuser.finance.payment_invoice.get_pbm') }}',
        data: {id:$(this).val() , _token: "{{csrf_token()}}"},
        type: 'POST',
        cache: false,
        dataType: 'json',
        beforeSend: function () {
          addLoadSpiner($('#select_pbm'))
        },
        complete: function () {
          hideLoadSpinner($('#select_pbm'))
        },
        success: function(json) {
          $('#select_pbm').empty().trigger('change');
          $('#address').val('');
          
          if (json.code == 200) {
            let ph = new Option('', '', false, false);
            $('#select_pbm').append(ph).trigger('change');

            for (i = 0; i < Object.keys(json.data).length; i++) {
              let newOption = '<option value="'+ json.data[i].pbm_id +'" data-total="'+ json.data[i].total +'">'+ json.data[i].code +'</option>';
              $('#select_pbm').append(newOption).trigger('change');
            }

            $('#address').val(json.address);
          }
        }
      });
    });

    $('#select_pbm').on('select2:select', function (e) {
      var data = e.params.data;
      var total = $(this).find(':selected').data('total');
      $('#select_total').val(total);
    });

    var table = $('#datatable').DataTable({
        paging: false,
        bInfo : false,
        searching: false,
        columns: [
          {name: 'counter', "visible": false},
          {name: 'ppb', orderable: false},
          {name: 'total', orderable: false, searcable: false, width: "25%"},
          {name: 'paid', orderable: false, searcable: false, width: "25%"},
          {name: 'action', orderable: false, searcable: false, width: "9%"}
        ],
        'order' : [[0,'desc']]
    })
  
    var counter = 1;
  
    $('#add').on( 'click', function (e) {
      e.preventDefault();

      var select_pbm = $('#select_pbm').select2('data');

      var name_credit = $('#name_credit').val() ?? '';
      var total = $('#select_total').val() ?? '';

      var duplicate = 0;
      $('input[name="pbm_id[]"]').each( function  () {
        if($(this).val() == select_pbm[0]['id']) {
          duplicate = 1;
        } 
      });

      if(duplicate == 1) {
        alert('PBM is already in the table.')
      } else {
        if(select_pbm[0]['id']) {
          table.row.add([
                      counter,
                      '<input type="hidden" name="pbm_id[]" value="'+select_pbm[0]['id']+'"><span>'+select_pbm[0]['text']+'</span>',
                      '<input type="number" class="form-control" name="total[]" value="'+total+'" required readonly>',
                      '<input type="text" class="form-control paid" name="paid[]" min="1" max="'+total+'">',
                      '<a href="#" class="row-delete"><button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete"><i class="fa fa-trash"></i></button></a>'
                    ]).draw( false );
          counter++;

          $('#select_total').val('')
          $('#select_pbm').val(null).trigger("change")
          grandTotal()
        }
      }
      
    });

    $('#datatable tbody').on( 'click', '.row-delete', function (e) {
      e.preventDefault();
      table.row( $(this).parents('tr') ).remove().draw();

      grandTotal()
    });

    $('#datatable tbody').on( 'keyup', 'input[name="paid[]"]', function (e) {
      grandTotal();
    });

    $('#datatable tbody').on( 'keypress keyup', 'input[name="paid[]"]', function(event) {
        $(this).val($(this).val().replace(/[^0-9\.]/g, ''));
        if ((event.which != 46 || $(this).val().indexOf('.') != -1) && (event.which < 48 || event.which > 57)) {
            event.preventDefault();
        }
        
        if(parseFloat($(this).val()) > parseFloat($(this).attr('max'))){
                alert('Payments cannot be more than debts');
                
                $(this).val($(this).attr('max'));
                grandTotal();
        }

    });

    function grandTotal() {
      var subtotal_total = 0;
      $('input[name="total[]"]').each(function(){
        subtotal_total += Number($(this).val());
      });
      $('#subtotal_total').val(subtotal_total);

      var subtotal_paid = 0;
      $('input[name="paid[]"]').each(function(){
        subtotal_paid += Number($(this).val());
      });
      $('#subtotal_paid').val(subtotal_paid);
      $('#total_payment').val('Rp. '+subtotal_paid.toLocaleString('id-ID', {maximumFractionDigits:2}));
      
    }

  })
</script>
@endpush