@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Finance</span>
  <a class="breadcrumb-item" href="{{ route('superuser.finance.payable.index') }}">Payable</a>
  <span class="breadcrumb-item active">Create Payable</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-content">
    <form id="frmCreate" action="#" data-type="POST" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="customer_id" value="{{$customer->id}}">
      <div class="row">
        <div class="col-4">
          <div class="card">
            <div class="card-body">
                <div class="col-10">
                    <div class="form-group row">
                      <label class="col-md-4 col-form-label text-right">Store</label>
                      <div class="col-md-8">
                        <input type="text" class="form-control-plaintext" value="{{ $customer->name }}">
                    </div>
                  </div>
                </div>
                <div class="col-10">
                    <div class="form-group row">
                      <label class="col-md-4 col-form-label text-right">Address</label>
                      <div class="col-md-8">
                        <input type="text" class="form-control-plaintext" value="{{ $customer->address }}">
                    </div>
                  </div>
                </div>
            </div>
          </div>
        </div>

        <div class="col-8">
          <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                    <div class="form-group row">
                      <label class="col-md-4 col-form-label text-right">Type<span class="text-danger">*</span></label>
                      <div class="col-md-6">
                        <select class="form-control js-select2 type_payment" name="type_payment">
                          <option value="">Type payment</option>
                          <option value="1">Lunas per nota</option>
                          <option value="2">Lunas beberapa nota</option>
                          <option value="3">Cicilan</option>
                        </select>
                      </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group row">
                      <label class="col-md-4 col-form-label text-right">Note</label>
                      <div class="col-8">
                        <textarea class="form-control" name="note" rows="1"></textarea>
                      </div>
                    </div>
                </div>
                <div class="col-md-6">
                      <div class="form-group row">
                        <label class="col-md-4 col-form-label text-right">Jumlah payment<span class="text-danger">*</span></label>
                        <div class="col-md-6">
                          <input type="text" class="form-control" name="payment_cash" id="payment_cash">
                        </div>
                      </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <hr />
        <div class="block">
          <div class="block-header block-header-default">
            <h3 class="block-title">#INVOICE LIST</h3>
          </div>
          <div class="block-content">
            <table id="datatable" class="table table-striped table-vcenter">
              <thead>
                <tr>
                  <th class="text-center">#</th>
                  <th class="text-center">Refrensi INV</th>
                  <th class="text-center">Refrensi SO</th>
                  <th class="text-center">Account Receivable</th>
                  <th class="text-center">Payabel</th>
                  <th class="text-center">Sisa</th>
                </tr>
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
                    <tr class="index{{$index}}" data-index="{{$index}}">
                      <input type="hidden" name="repeater[{{$index}}][invoice_id]" value="{{$row->invoicing->id ?? ''}}">
                      <td>{{ $loop->iteration }}</td>
                      <td>{{ $row->invoicing->code }}</td>
                      <td>{{$row->so->code ?? ''}}</td>
                      <td>
                        <input type="text" class="form-control count" name="repeater[{{$index}}][sisa]" data-index="{{$index}}" step="any" value="{{$sisa}}" readonly>
                      </td>
                      <td>
                        <input type="text" name="repeater[{{$index}}][payable]" data-index="{{$index}}" class="form-control count">
                      </td>
                      <td>
                        <input type="text" class="form-control" name="repeater[{{$index}}][payment_sisa]" data-index="{{$index}}" readonly>
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
      <hr />
      
      <div class="row pt-30 mb-15">
        <div class="col-md-6">
          <a href="{{route('superuser.finance.payable.index')}}">
            <button type="button" class="btn bg-gd-cherry border-0 text-white">
              <i class="fa fa-arrow-left mr-10"></i> Back
            </button>
          </a>
        </div>
        <div class="col-md-6 text-right">
          <button class="btn btn-primary btn-md btn-simpan" type="button"><i class="fa fa-save"></i> Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>


@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')


@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
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
  })
</script>
@endpush