@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Finance</span>
  <span class="breadcrumb-item">Payable</span>
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
  <div class="block-header block-header-default">
    <h3 class="block-title">#Payable Create</h3>
  </div>
  <div class="block-content block-content-full">
    <div class="row">
      <div class="col">
        <div class="form-group">
          <label for="payable_date">Payable Date</label>
          <input type="date" class="form-control" id="payable_date" name="payable_date">
        </div>
      </div>
      <div class="col">
        <div class="form-group">
          <label for="note">Note</label>
          <input type="text" class="form-control" id="note" name="note">
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <div class="form-group">
          <label for="customer_other_address_id">Customer</label>
          <select class="form-control js-select2 select-customer" name="customer_other_address_id" id="other_address" data-index="0">
            <option value="">Pilih Customer</option>
            @foreach($other_address as $key)
            <option value="{{$key->id}}">{{$key->name}} - {{$key->text_kota}}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="col">
        <div class="form-group">
          <label for="invoice_id">Invoice</label>
          <select class="form-control js-select2 select-invoice" name="invoice_id" id="invoice" data-index="0">
            <option value="">Pilih Invoice</option>
          </select>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">#Detail Invoice</h3>
  </div>
  <div class="block-content block-content-full">
    <div class="container">
      <div class="row" align="center">
        <div class="col">
          <div class="card bg-light mb-3" style="max-width: 18rem;">
            <div class="card-header"></div>
            <div class="card-body">
              <h5 class="card-title">Light card title</h5>
              <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card bg-light mb-3" style="max-width: 18rem;">
            <div class="card-header">Header</div>
            <div class="card-body">
              <h5 class="card-title">Light card title</h5>
              <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="card bg-light mb-3" style="max-width: 18rem;">
            <div class="card-header">Header</div>
            <div class="card-body">
              <h5 class="card-title">Light card title</h5>
              <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

<!-- Modal -->


@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')

@push('scripts')
  <script type="text/javascript">
    $(document).ready(function() {
      $('.js-select2').select2();

      var param = [];
      param["customer_other_address_id"] = "";

      loadInvoice({});

      $(document).on('change','.select-customer',function(){
        if ($(this).val() === '') return;

        param["customer_other_address_id"] = $(this).val();
        loadInvoice({
          customer_other_address_id:param["customer_other_address_id"],
          index: $(this).data("index")
        })
      });

      function loadInvoice(param){
      $.ajax({
        url : '{{route('superuser.finance.payable.get_invoice')}}',
        method : "GET",
        data : param,
        dataType : "JSON",
        success : function(resp){
          let option = "";
          option = '<option value="">Select Invoice</option>';
          $.each(resp.Data,function(i,e){
            option += '<option value="'+e.id+'">'+e.invoiceCode+'</option>';
          })
          $('.select-invoice[data-index=' + param.index + ']').html(option);
        },
        error : function(){
          alert("Cek Koneksi Internet");
        }
      })
    }
    })
  </script>
@endpush