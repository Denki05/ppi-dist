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
  <div class="block-content block-content-full">
            <div class="row">
              <div class="col-4">
                <div class="row">
                  <div class="col-lg-2">
                    Store
                  </div>
                  <div class="col-lg-10">
                    <input type="text" class="form-control" value="{{$customer->name}}" readonly>
                  </div>
                </div>
              </div>
              <div class="col-4">
                <div class="row">
                  <div class="col-lg-2">
                    Phone
                  </div>
                  <div class="col-lg-10">
                    <input type="text" class="form-control" value="{{$customer->phone}}" readonly>
                  </div>
                </div>
              </div>
              <div class="col-4">
                <div class="row">
                  <div class="col-lg-4">
                    Total Piutang
                  </div>
                  <?php
                    // $piutang = $customer->invoicing->sum('grand_total_idr');
                  ?>
                  <div class="col-lg-8">
                    <input type="text" class="form-control" value="" readonly>
                  </div>
                </div>
              </div>
            </div>
            <br>
            <div class="row">
              <div class="col-4">
                <div class="row">
                  <div class="col-lg-2">
                    Address
                  </div>
                  <div class="col-lg-10">
                    <input type="text" class="form-control" value="{{$customer->address}}" readonly>
                  </div>
                </div>
              </div>
              <div class="col-4">
                <div class="row">
                  <div class="col-lg-2">
                    Note
                  </div>
                  <div class="col-lg-10">
                    <textarea class="form-control" name="payment_note" rows="1"></textarea>
                  </div>
                </div>
              </div>
              <div class="col-4">
                <div class="row">
                  <div class="col-lg-4">
                    Detail
                  </div>
                  <div class="col-lg-8">
                    <a href="{{ route('superuser.finance.payable.detail', $customer->id) }}" class="btn btn-secondary btn-lg" role="button"><i class="fa fa-list"></i></a>
                  </div>
                </div>
              </div>
            </div>
            
  </div>
</div>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">#INVOICE LIST</h3>
  </div>
  <div class="block-content block-content-full">
    <form id="frmPayable" method="post">
      @csrf
      <input type="hidden" name="customer_id" value="{{$customer->id}}">
      <div class="row">
        <div class="col-12">
          <div class="table-responsive">
            <table class="table table-striped table-bordered">
              <thead>
                  <th class="text-center">#</th>
                  <th class="text-center">Invoice date</th>
                  <th class="text-center">Refrensi INV</th>
                  <th class="text-center">Refrensi SO</th>
                  <th class="text-center">Account Receivable</th>
                  <th class="text-center">Payabel</th>
                  <th class="text-center">Sisa</th>
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
                    <tr class="index{{$index}}" name="repeater" data-index="{{$index}}">
                      <input type="hidden" name="repeater[{{$index}}][invoice_id]" value="{{$row->invoicing->id ?? ''}}">
                      <td width="2%">
                        <input type="text" style="text-align:center" class="form-control-plaintext" value="{{ $loop->iteration }}">
                      </td>
                      <td width="5%">
                        <input type="text" style="text-align:center" class="form-control-plaintext" value="{{ date_format($row->invoicing->created_at, 'd-m-Y') }}">
                      </td>
                      <td width="15%">
                        <input type="text" style="text-align:center" class="form-control-plaintext" value="{{ $row->invoicing->code }}">
                      </td>
                      <td width="15%">
                        <input type="text" style="text-align:center" class="form-control-plaintext" value="{{$row->so->code ?? ''}}">
                      </td>
                      <td width="20%">
                        <input type="text" style="text-align:center" class="form-control-plaintext count" name="repeater[{{$index}}][sisa]" data-index="{{$index}}" step="any" value="{{$sisa}}" readonly>
                      </td>
                      <td width="20%">
                        <input type="text" name="repeater[{{$index}}][payable]" data-index="{{$index}}" class="form-control count">
                      </td>
                      <td width="20%">
                        <input type="text" class="form-control formatRupiah" name="repeater[{{$index}}][payment_sisa]" data-index="{{$index}}" readonly>
                      </td>
                    </tr>
                    <?php $counter++ ?>
                    @endif
                  @endif
                @endforeach
                @if($counter == 0)
                  <tr>
                    <td colspan="7" class="text-center">Data tidak ditemukan</td>
                  </tr>
                @endif
              </tbody>
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
          let index = $(this).attr('data-index');
          count_per_item(index);
        })

        function count_per_item(indx){
          let index = indx;
          let sisa = parseFloat($('tr.index'+index+'').find('input[name="repeater['+index+'][sisa]"]').val()); 
          let payable = parseFloat($('tr.index'+index+'').find('input[name="repeater['+index+'][payable]"]').val()); 


          if(isNaN(payable)){
            payable = 0;
          }
          
          let payment_sisa  = sisa - payable;

          if(isNaN(payment_sisa)){
            payment_sisa = 0;
          }

          $('tr.index'+index+'').find('input[name="repeater['+index+'][payment_sisa]"]').val(payment_sisa);
        }

        $(document).on('submit','#frmPayable',function(e){
          e.preventDefault();
          if(confirm("Apakah anda yakin ingin melakukan pembayaran ini ?")){
            let _form = $('#frmPayable');
            $.ajax({
              url : '{{route('superuser.finance.payable.store', $customer->id)}}',
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
  </script>
@endpush