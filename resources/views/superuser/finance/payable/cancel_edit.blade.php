@extends('superuser.app')
@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Finance</span>
  <span class="breadcrumb-item">Payable</span>
  <span class="breadcrumb-item active">Revisi</span>
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

<form id="frmPayable" method="post">
@csrf
    <input type="hidden" name="payable_header" value="{{$result->id}}">

  <div class="block">
    <div class="block-header block-header-default">
      <h3 class="block-title">#Revisi Nota {{ $result->code }}</h3>
    </div>
    <div class="block-content block-content-full">
      <div class="row">
        <div class="col">
          <div class="form-group">
            <label>Account customer</label>
            <input type="text" class="form-control" name="customer_name" value="{{ $result->customer->name }} {{ $result->customer->text_kota }}" readonly>
            <input type="hidden" value="{{$result->customer->id}}" name="customer_id">
          </div>
      </div>
      <div class="col">
          <div class="form-group">
            <label>Tanggal Bayar</label>
            <input type="date" class="form-control" id="pay_date" name="pay_date" required value="{{ date_format(date_create($result->pay_date), 'Y-m-d') }}" readonly>
          </div>
      </div>
      <div class="col">
          <div class="form-group">
            <label>Keterangan</label>
            <input type="text" class="form-control" name="note" value="{{ $result->note ?? '' }}">
          </div>
      </div>
    </div>
  </div>

  <div class="block">
    <div class="block-content block-content-full">
        <div class="row">
          <div class="col-12">
            <div class="table-responsive">
              <table class="table table-striped table-bordered" id="datatables">
                <thead>
                  <th>Nota</th>
                  <th>Total Nota</th>
                  <th>Total Terbayar</th>
                  <th>Sisa Bayar</th>
                </thead>
                <tbody>
                    @foreach($result->payable_detail as $index => $row)
                        <tr class="repeater">
                          <input type="hidden" name="repeater[{{$index}}][id_invoice]" value="{{$row->invoice->id}}">
                          <input type="hidden" name="repeater[{{$index}}][payable_detail_id]" value="{{$row->id}}">
                          <td>{{$row->invoice->code ?? ''}}</td>
                          <td><input type="text" name="repeater[{{$index}}][total_nota]" class="form-control total_nota" value="{{number_format($row->prev_account_receivable ,0,',','.')}}" readonly></td>
                          <td>
                            <input type="text" name="repeater[{{$index}}][payable]" value="{{number_format($row->total ,0,',','.')}}" class="form-control formatRupiah count total_payment">
                          </td>
                          <td>
                            <input type="text" name="repeater[{{$index}}][sisa]" class="form-control formatRupiah count_sisa" readonly>
                          </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="2" class="text-center">
                      <label class="col-md-8 col-form-label text-right">TOTAL</label>
                    </td>
                    <td class="text-center">
                      <input type="text" class="form-control total" readonly>
                    </td>
                    <td class="text-center">
                      <input type="text" class="form-control sisa_bayar" readonly>
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <a href="{{route('superuser.finance.payable.index')}}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Back</a>
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"> Save </i></button>
          </div>
        </div>
        </div>
  </div>

  @endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')

@push('scripts')
  <script type="text/javascript">
    $(function(){
      $('#datatables').DataTable( {
        "paging":   false,
        "ordering": true,
        "info":     false,
        "searching" : false,
        "columnDefs": [{
          "targets": 0,
          "orderable": false
        }]
      });

      $('.js-select2').select2();

      $(document).on('keyup','.count',function(){
        total();
      })

      $(document).on('keyup', '.total_payment', function(){
        total_sisa();
      })

      $(document).on('keyup','.formatRupiah',function(){
        let val = $(this).val();
        $(this).val(formatRupiah(val));
      })
      
      $(document).on('submit','#frmPayable',function(e){
        e.preventDefault();
        if(confirm("Apakah anda yakin ingin melakukan pembayaran ini ?")){
          let _form = $('#frmPayable');
          $.ajax({
            url : '{{route('superuser.finance.payable.update_cancel', $result->id)}}',
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
                    window.location.href = '{{route('superuser.finance.payable.index')}}';
                })
              }
            },
            error : function(){
              alert('Cek Koneksi Internet');
            },
            complete : function(){
              $('button[type="submit"]').html('<i class="fa fa-save"> Save</i>');
            }
          })
        }
      })
    })
    function total(){
      let total = 0 ;
      $('#frmPayable tr.repeater').each(function(i,e){
        let val = $('#frmPayable tr.repeater').eq(i).find('.count').val();
        if(val != "" && val != undefined){
          val = parseFloat(val.split('.').join(''));
        }
        else{
          val = 0;
        }
        
        if(isNaN(val)){
          val = 0;
        }
        total += val;
      })
      $('.total').val(formatRupiah(total));
    }
    function total_sisa(){
      let total_sisa = 0 ;
      $('#frmPayable tr.repeater').each(function(i,e){
        let pay = $('#frmPayable tr.repeater').eq(i).find('.total_payment').val();
        let nota = $('#frmPayable tr.repeater').eq(i).find('.total_nota').val();
        
        pay = parseFloat(pay.split('.').join(''));
        nota = parseFloat(nota.split('.').join(''));
        sisa = nota - pay;
        
        total_sisa += sisa;

        $('#frmPayable tr.repeater').eq(i).find('.count_sisa').val(formatRupiah(sisa));
      })

      $('.sisa_bayar').val(formatRupiah(total_sisa));
    }
    function formatRupiah(angka, prefix){
      angka = angka.toString();
      var number_string = angka.replace(/[^,\d]/g, '').toString(),
      split       = number_string.split(','),
      sisa        = split[0].length % 3,
      rupiah        = split[0].substr(0, sisa),
      ribuan        = split[0].substr(sisa).match(/\d{3}/gi);
     
      // tambahkan titik jika yang di input sudah menjadi angka ribuan
      if(ribuan){
        separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
      }
     
      rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
      return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
    }
  </script>
@endpush