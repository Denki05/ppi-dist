@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Finance</span>
  <span class="breadcrumb-item active">Payable</span>
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

@if(session()->has('success'))
<div class="alert alert-success alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Success</h3>
  <p class="mb-0">{{ session()->get('success') }}</p>
</div>
@endif

<div class="block finance-tabs">
  <div class="block-content block-content-full">
    <div class="row">
      <div class="form-group row">
        <div class="col">
          <main class="tab-header">
                <input type="radio" id="tab1" name="tabs" checked hidden>
                <label for="tab1" class="tab-label tab-payment" data-tab="content1">Payment</label>

                <input type="radio" id="tab2" name="tabs" hidden>
                <label for="tab2" class="tab-label tab-list" data-tab="content2">Done</label>
          </main>

          <div class="tab-content active" id="content1">
            <form id="payableForm" method="POST" action="{{ route('superuser.finance.payable.store') }}">
              @csrf
              <div class="row align-items-end mb-3">
                  <div class="col-md-4">
                      <label for="searchCustomer" class="form-label">Cari Customer</label>
                      <div class="input-group">
                          <input type="text" id="searchCustomer" class="form-control" placeholder="Ketik nama customer...">
                          <input type="hidden" name="customer_id" id="customer_id">
                          <button class="btn btn-danger" type="button" id="resetCustomer">
                              <i class="fa fa-times"></i>
                          </button>
                      </div>
                      <div id="customerList" class="list-group" style="position:absolute; z-index:1000; width:100%; display:none;">
                      </div>
                  </div>

                  <div class="col-md-3">
                      <label class="form-label">Tanggal Bayar</label>
                      <input type="date" class="form-control" name="pay_date" required>
                  </div>

                  <div class="col-md-4">
                      <label class="form-label">Keterangan</label>
                      <input type="text" class="form-control" name="note" maxlength="255" placeholder="Catatan (opsional)">
                  </div>
              </div>

              <div id="invoice_section" style="display:none;">
                <table class="table table-striped table-bordered" id="invoice_table">
                    <thead>
                        <tr>
                            <th>Nota</th>
                            <th>Total Nota</th>
                            <th>Pembayaran</th>
                            <th>Sisa Bayar</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-right">TOTAL</td>
                            <td><input type="text" class="form-control total" readonly></td>
                            <td><input type="text" class="form-control sisa_bayar" readonly></td>
                        </tr>
                    </tfoot>
                </table>

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Apakah Anda yakin ingin menyimpan data ini?');">Simpan</button>
                </div>
              </div>
            </form>
          </div>

          <div id="content2" class="tab-content">
            <table class="table table-striped" id="datatables-done">
                <thead>
                    <tr>
                        <th>Created At</th>
                        <th>Customer</th>
                        <th>Payable Code</th>
                        <th>Invoice Code</th>
                        <th>Total Payable</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>

<style>
/* CSS Anda sebelumnya */
.finance-tabs .tab-label::before {
    font-family: "Font Awesome 6 Free","Font Awesome 5 Free";
    font-weight: 900;
    margin-right: 6px;
}
.finance-tabs .tab-payment::before { content: "\f571"; }
.finance-tabs .tab-list::before { content: "\f03a"; }
.finance-tabs .tab-label {
    background-color: #f0f0f0;
    border: 1px solid #ddd;
    border-bottom: none;
    padding: 10px 20px;
    cursor: pointer;
    display: inline-block;
    border-radius: 5px 5px 0 0;
    margin-right: 5px;
    transition: background-color 0.3s ease;
    font-weight: bold;
    color: #555;
}
.finance-tabs .tab-label:hover { background-color: #e0e0e0; }
.finance-tabs .tab-label.active-tab-label {
    background-color: #fff;
    border-color: #ccc;
    border-bottom: 1px solid #fff;
    color: #000;
}
.finance-tabs .tab-content {
    display: none;
    border: 0px solid #ccc;
    padding: 20px;
    border-radius: 0 5px 5px 5px;
    background-color: #fff;
}
.finance-tabs .tab-content.active { display: block; }
</style>

<script>
$(function () {
    let doneTable = null;

    $('.finance-tabs input[name="tabs"]').on('change', function() {
        let tabId = $(this).attr('id');
        let target = $('[for="'+tabId+'"]').data('tab');
        $('.finance-tabs .tab-label').removeClass('active-tab-label');
        $('.finance-tabs .tab-content').removeClass('active');
        $('[for="'+tabId+'"]').addClass('active-tab-label');
        $('#'+target).addClass('active');

        if (target === 'content2') {
            if (!doneTable) {
                doneTable = $('#datatables-done').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('superuser.finance.payable.json_done') }}", // PERBAIKAN: Ubah ke endpoint yang benar
                        type: "GET",
                        data: function (d) {
                            // Anda bisa menambahkan filter di sini jika diperlukan
                        }
                    },
                    columns: [
                        { data: 'tanggal_buat', name: 'finance_payable.pay_date' }, // PERBAIKAN: Gunakan alias 'tanggal_buat'
                        { data: 'customer_display_name', name: 'customer_display_name', orderable: false, searchable: false }, // PERBAIKAN: Gunakan nama yang sesuai atau non-orderable
                        { data: 'code', name: 'finance_payable.code' },
                        { data: 'invoice_code', name: 'finance_invoicing.code' }, // PERBAIKAN: Gunakan alias 'invoice_code'
                        { data: 'total_pay', name: 'finance_payable.total', render: $.fn.dataTable.render.number('.', ',', 0, '') },
                        { data: 'status_label', name: 'status_label', orderable: false, searchable: false }, // PERBAIKAN: Gunakan nama yang sesuai atau non-orderable
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                    ],
                    order: [[0, 'desc']]
                });
            } else {
                doneTable.ajax.reload();
            }
        }
    }).filter(':checked').trigger('change');


    // ... (sisa script JavaScript Anda yang lain)

    // Search customer (tidak ada perubahan)
    $('#searchCustomer').on('keyup', function(){
        let query = $(this).val().trim();

        if(query.length === 0){
            $('#customerList').hide();
            $('#invoice_table tbody').html('');
            $('#invoice_section').hide();
            $('#customer_id').val('');
            return;
        }
        if(query.length < 2){
            $('#customerList').hide();
            return;
        }

        $.ajax({
            url: "{{ route('superuser.finance.payable.customerSearch') }}",
            method: "GET",
            data: {query: query},
            success: function(res){
                let list = $('#customerList');
                list.empty();
                if(res.length > 0){
                    res.forEach(c => {
                        list.append('<a href="#" class="list-group-item list-group-item-action customer-item" data-id="'+c.id+'">'+c.display_name+'</a>');
                    });
                } else {
                    list.append('<div class="list-group-item">Tidak ditemukan</div>');
                }
                list.show();
            }
        });
    });

    // Pilih customer (tidak ada perubahan)
    $(document).on('click', '.customer-item', function(e){
        e.preventDefault();
        let customerId = $(this).data('id');
        let customerName = $(this).text();
        $('#searchCustomer').val(customerName);
        $('#customer_id').val(customerId);
        $('#customerList').hide();

        $('#invoice_table tbody').html('');
        $('#invoice_section').hide();

        $.get("{{ route('superuser.finance.payable.unpaidInvoices') }}", {customer_id: customerId}, function(res){
          let rows = '';
          if(res.length === 0){
              $('#invoice_section').hide();
          } else {
              res.forEach((item, index) => {
                  rows += `
                      <tr>
                          <input type="hidden" name="repeater[${index}][invoice_id]" value="${item.id}">
                          <td>${item.code}</td>
                          <td>
                              <input type="text" class="form-control total_nota"
                                    value="${item.remaining}" readonly>
                          </td>
                          <td>
                              <input type="text"
                                    name="repeater[${index}][payable]"
                                    class="form-control formatRupiah total_payment">
                          </td>
                          <td>
                              <input type="text" class="form-control formatRupiah count_sisa" readonly>
                          </td>
                      </tr>
                  `;
              });

              $('#invoice_table tbody').html(rows);
              $('#invoice_section').show();
          }
      });
    });

    // Hitung total & sisa (tidak ada perubahan)
    $(document).on('keyup change', '.total_payment', function () {
        let row = $(this).closest('tr');
        let totalNota = parseInt(row.find('.total_nota').val().replace(/\./g, '').replace(/,/g, '')) || 0;
        let totalPayment = parseInt($(this).val().replace(/\./g, '').replace(/,/g, '')) || 0;
        let sisa = totalNota - totalPayment;
        row.find('.count_sisa').val(sisa.toLocaleString('id-ID'));

        let totalBayar = 0, totalSisa = 0;
        $('.total_payment').each(function () {
            let val = parseInt($(this).val().replace(/\./g, '').replace(/,/g, '')) || 0;
            totalBayar += val;
        });
        $('.count_sisa').each(function () {
            let val = parseInt($(this).val().replace(/\./g, '').replace(/,/g, '')) || 0;
            totalSisa += val;
        });
        $('.total').val(totalBayar.toLocaleString('id-ID'));
        $('.sisa_bayar').val(totalSisa.toLocaleString('id-ID'));
    });

    // Formatting rupiah (tidak ada perubahan)
    $(document).on('keyup', '.formatRupiah', function () {
        let val = $(this).val().replace(/\D/g, '');
        $(this).val((val ? parseInt(val) : 0).toLocaleString('id-ID'));
    });

    // Reset customer (tidak ada perubahan)
    $('#resetCustomer').on('click', function(){
      $('#searchCustomer').val('');
      $('#customer_id').val('');
      $('#customerList').hide();
      $('#invoice_table tbody').html('');
      $('#invoice_section').hide();
    });
});
</script>
@endpush