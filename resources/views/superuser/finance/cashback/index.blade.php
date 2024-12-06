@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Finance</span>
  <span class="breadcrumb-item active">Cashback</span>
</nav>
@if($errors->any())
<div class="alert alert-danger alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Error</h3>
  @foreach ($errors->all() as $error)
  <p class="mb-0">{{ $error }}</p>
  @endforeach
</div>
@endif
<div class="block">
  <div class="block-content">
      <button type="button" class="btn btn-outline-primary min-width-125" data-toggle="modal" data-target="#addFinanceCashback">Create</button>
  </div>
  <hr class="my-20">
  <div class="block-content block-content-full">
    <div class="form-group row">
      <label class="col-md-2 col-form-label text-left" for="period">Bulan :</label>
      <div class="col-md-4">
        <div class="input-group">
          <select id="bulan" name="bulan" class="form-control js-select2">
            @foreach ($bulan as $key => $month)
              <option value="{{ $key }}" {{ $key == $selectedBulan ? 'selected' : '' }}>
                {{ $month }}
              </option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="col-md-4">
        <div class="input-group">
          <select id="tahun" name="tahun" class="form-control js-select2" style="width: 30%;">
              @for ($year = now()->year; $year >= 2024; $year--) <!-- Replace 2000 with your desired start year -->
                  <option value="{{ $year }}" {{ $year == $selectedTahun ? 'selected' : '' }}>
                      {{ $year }}
                  </option>
              @endfor
          </select>
        </div>
      </div>
    </div>
    <table id="datatable" class="table table-striped">
      <thead>
        <tr>
          <th class="text-center">Created at</th>
          <th class="text-center">Code</th>
          <th class="text-center">Ref Inv</th>
          <th class="text-center">Customer</th>
          <th class="text-center">Cashback</th>
          <th class="text-center">Status</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<div class="modal fade" id="addFinanceCashback" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Search Invoice</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form>
          @csrf
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="month_invoice">Bulan <span class="text-danger">*</span></label>
            <div class="col-md-7">
              <select class="js-select2 form-control" id="month_invoice" name="month_invoice" data-placeholder="Search" style="width: 100%;">
                <option>Pilih Bulan</option>
                @foreach($months AS $key)
                  <option value="{{ $key['id'] }}">{{ $key['monthName'] }}</option>
                @endforeach
              </select>
            </div>

            <label class="col-md-3 col-form-label text-right" for="customer_name">Customer <span class="text-danger">*</span></label>
            <div class="col-md-7">
              <select class="js-select2 form-control" id="customer_name" name="customer_name" data-placeholder="Search" style="width: 100%;">
                <option>Pilih Customer</option>
                @foreach($customer AS $item)
                  <option value="{{ $item->id }}">{{ $item->name }} {{ $item->kota }}</option>
                @endforeach
              </select>
            </div>

            <label class="col-md-3 col-form-label text-right" for="delivery_order">Invoice <span class="text-danger">*</span></label>
            <div class="col-md-7">
              <select class="js-select2 form-control js-select2-kontrak" id="addInvoice" name="addInvoice" data-placeholder="Pilih Invoice" style="width: 100%;">
              </select>
            </div>
          </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <a href="#" id="addCashback" class="btn btn-primary btn-lg active" role="button" aria-pressed="true">Add</a>
      </div>
      </form>
    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
    $('.js-select2').select2();

    let datatableUrl = '{{ route('superuser.finance.cashback.json') }}';

    let cashbackTable = $('#datatable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: datatableUrl,
            dataType: "json",
            type: "GET",
            data: function (d) {
                d.bulan = $('#bulan').val(); // Send selected month
                d.tahun = $('#tahun').val(); // Send selected year
                d._token = "{{csrf_token()}}";
            },
        },
        columns: [
            {
                data: 'tanggal_invoice'
            },
            { data: 'code' },
            { data: 'code_invoice' },
            { data: 'customer_name' },
            { 
              data: 'selisih_cashback',
              render: $.fn.dataTable.render.number('.', ',', 2, 'Rp. '),
              searchable: false
            },
            { data: 'status' },
            { data: 'action' },
        ],
        order: [[1, 'desc']],
        pageLength: 5,
        lengthMenu: [[5, 15, 20], [5, 15, 20]],
    });

    // Handle month dropdown change
    $('#bulan, #tahun').change(function () {
      cashbackTable.ajax.reload();
    });

    $(".js-select2-kontrak").select2({
      
      ajax: {
        url: '{{ route('superuser.finance.cashback.get_invoice') }}',
        dataType: 'json',
        delay: 250,
        data: function (params) {
          return {
            q: params.term,
            _token: "{{csrf_token()}}",
            month_invoice: $("#month_invoice").val(), // Pass the month_invoice value here
            customer_name: $("#customer_name").val()  // Pass the customer_name value here
          };
        },
        cache: true
      },
    });

    $('#addCashback').on('click', function() {
        var id = $('#addInvoice').val();
        var url = '{{ route('superuser.finance.cashback.create',  [":id"]) }}';
        url = url.replace(':id', id); 
        
        $.ajax({
            url: url,
            type: 'GET',
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success:function(data)
            {
              window.location.href = url;
            }
        });
      });

      $(document).on('click', '.delete-button', function() {
        var url = $(this).data('url');
        if (confirm('Are you sure you want to delete this item?')) {
            $.ajax({
                url: url,
                type: 'POST', // Use POST for method spoofing
                data: {
                    _method: 'DELETE', // Spoofing the DELETE method
                    _token: '{{ csrf_token() }}' // Add CSRF token
                },
                success: function(response) {
                    // Optionally display a success message
                    alert(response.message);

                    // Refresh the DataTable
                    $('#datatable').DataTable().ajax.reload();
                },
                error: function(xhr) {
                    // Display an error message
                    alert('An error occurred while trying to delete this item.');
                }
            });
        }
      });
});
</script>
@endpush