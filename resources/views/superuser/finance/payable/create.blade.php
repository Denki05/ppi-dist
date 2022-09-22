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
  <hr class="my-20">
  <div class="block-content block-content-full">
    <div class="row">
      <div class="col-lg-2">
        Store Code
      </div>
      <div class="col-lg-10">
        : {{$customer->code}}
      </div>
    </div>
    <div class="row">
      <div class="col-lg-2">
        Store/Member
      </div>
      <div class="col-lg-10">
        : {{$customer->name}}
      </div>
    </div>
    <div class="row">
      <div class="col-lg-2">
        Address
      </div>
      <div class="col-lg-10">
        : {{$customer->address}}
      </div>
    </div>
    <div class="row">
      <div class="col-lg-2">
        Phone
      </div>
      <div class="col-lg-10">
        : {{$customer->phone}}
      </div>
    </div>
  </div>
</div>
<div class="block">
  <hr class="my-20">
  <div class="block-content block-content-full">
    <form id="frmPayable" method="post">
      @csrf
      <input type="hidden" name="customer_id" value="{{$customer->id}}">
      <div class="row">
        <div class="col-12">
          <div class="table-responsive">
            <table class="table table-striped table-bordered">
              <thead>
                <th>Invoice</th>
                <th>Account Receivable</th>
                <th>Payabe</th>
              </thead>
              <tbody>
                <?php
                  $counter = 0;
                ?>
                @foreach($customer->do as $index => $row)
                  @if($row->invoicing)
                    <?php
                      $total_invoicing = $row->invoicing->grand_total_idr ?? 0;
                      $payable = $row->invoicing->payable_detail->sum('total');
                      $sisa = $total_invoicing - $payable;
                    ?>
                    @if($sisa > 0)
                    <tr class="repeater">
                      <input type="hidden" name="repeater[{{$index}}][invoice_id]" value="{{$row->invoicing->id ?? ''}}">
                      <td>{{$row->invoicing->code ?? ''}}</td>
                      <td>{{number_format($sisa,0,',','.')}}</td>
                      <td>
                        <input type="text" name="repeater[{{$index}}][payable]" class="form-control formatRupiah count">
                      </td>
                    </tr>
                    <?php $counter++ ?>
                    @endif
                  @endif
                @endforeach
                @if($counter == 0)
                  <tr>
                    <td colspan="3" class="text-center">Data tidak ditemukan</td>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-12">
          <div class="form-group row">
            <label class="col-md-8 col-form-label text-right">Total</label>
            <div class="col-md-4">
              <input type="text" class="form-control total" readonly>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-12">
          <a href="{{route('superuser.finance.payable.index')}}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Back</a>
          <button type="submit" class="btn btn-primary"><i class="fa fa-save"> Save </i></button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

<!-- Modal -->


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

      $(document).on('keyup','.formatRupiah',function(){
        let val = $(this).val();
        $(this).val(formatRupiah(val));
      })

      $(document).on('submit','#frmPayable',function(e){
        e.preventDefault();
        if(confirm("Apakah anda yakin ingin melakukan pembayaran ini ?")){
          let _form = $('#frmPayable');
          $.ajax({
            url : '{{route('superuser.finance.payable.store')}}',
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
