@extends('superuser.app')
@push('styles')
  <link rel="stylesheet" href="{{ asset('superuser_assets/css/page/delivery-order.css') }}">
  <link href="{{ asset('css/styles.css') }}" rel="stylesheet">
@endpush

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Penjualan</span>
  <span class="breadcrumb-item active">Detail Delivery Order</span>
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
  <div class="block-content wizard">

    <input type="hidden" name="id" value="{{$result->id}}">
    <input type="hidden" name="cost_id" value="{{$result->do_cost->id ?? 0}}">

    <div class="row steps mb-3">
      <div class="col-4 text-center step {{$result->status == 3 ? 'active' : ''}}" id="step1">Konfirmasi Barang</div>
      <div class="col-4 text-center step {{$result->status == 4 ? 'active' : ''}}" id="step2">Berangkat</div>
      <div class="col-4 text-center step {{$result->status == 5 || $result->status == 6 ? 'active' : ''}}" id="step3">Update Resi</div>
    </div>

    <form id="frmUpdateNew" action="#">
    @csrf
      <div class="row step-container {{$result->status == 3 ? 'active' : ''}}" id="step1Container">
        <div class="col-12">

          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label">Store / Member</label>
            <div class="col-6 col-md-9">
              {{$result->customer->name ?? ''}} / {{$result->member->name ?? ''}}
            </div>
          </div>

          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label">Kategori Barang</label>
            <div class="col-6 col-md-9">
              <strong>{{$result->do_detail[0]->so_item->product->category->name}}</strong>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label">Detail Pesanan</label>
          </div>

          <div class="form-group row">
            <table class="col-12 table table-striped table-bordered table-hover">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Nama Barang</th>
                  <th>Jumlah Permintaan</th>
                  <th>Packaging</th>
                  <th>Status Barang <input type="checkbox" class="check-all-confirm-item" onclick="$('.confirm-item').prop('checked', $(this).prop('checked'))" /></th>
                </tr>
              </thead>
              <tbody>
                @if(count($result->do_detail) == 0)
                  <tr><td colspan="3" align="center">Data tidak ditemukan</td></tr>
                @endif
                @foreach($result->do_detail as $index => $row)
                  <tr>
                    <td>{{$index+1}}</td>
                    <td>{{$row->product->name}}</td>
                    <td>{{$row->qty}}</td>
                    <td>{{$row->packaging_txt()->scalar ?? ''}}</td>
                    <td><input type="checkbox" class="confirm-item" value="{{$row->id}}" /></td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <div class="form-group row">
            <div class="col-6"></div>
            <div class="col-12 text-right">
              <button type="button" class="btn btn-primary" onclick="konfirmasiBarang()">Next</button>
            </div>
          </div>

        </div>
      </div>

      <div class="row step-container {{$result->status == 4 ? 'active' : ''}}" id="step2Container">
        <div class="col-12">

          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label">Packaging Order</label>
            <div class="col-6 col-md-6">
              {{$result->code}}
            </div>
          </div>

          <div class="form-group row">
            <label class="col-6 col-md-3 col-form-label">Store / Member</label>
            <div class="col-6 col-md-9">
              {{$result->customer->name ?? ''}} / {{$result->member->name}}
            </div>
          </div>

          <div class="form-group row">
            <div class="col-12"><a href="{{route('superuser.penjualan.delivery_order.print',$result->id)}}" class="btn btn-info btn-sm btn-flat" target="_blank"><i class="fa fa-print"></i> Cetak DO</a></div>
          </div>


          @if($result->invoicing != null)
          <div class="form-group row">
            <div class="col-12"><a href="{{route('superuser.finance.invoicing.print_proforma',$result->invoicing->id)}}" class="btn btn-info btn-sm btn-flat" data-id="{{$result->invoicing->id}}" target="_blank"><i class="fa fa-print"></i> Print Proforma</a></div>
          </div>

          <div class="form-group row">
            <div class="col-12"><a href="{{route('superuser.finance.invoicing.print_paid',$result->invoicing->id)}}" class="btn btn-primary btn-sm btn-flat" data-id="{{$result->invoicing->id}}" target="_blank"><i class="fa fa-print"></i> Print Invoice</a></div>
          </div>
          @endif

          <div class="form-group row">
            <div class="col-6">
            </div>
            <div class="col-6 text-right">
              <button type="button" class="btn btn-primary btn-delivery"><i class="fa fa-save"></i> DELIVERING / BERANGKAT</button>
            </div>
          </div>

        </div>
      </div>
    </form>

    <div class="row step-container {{$result->status == 5 || $result->status == 6 ? 'active' : ''}}" id="step3Container">
      <div class="col-12">

        <div class="form-group row">
          <label class="col-6 col-md-3 col-form-label">Packaging Order</label>
          <div class="col-6 col-md-6">
            {{$result->code}}
          </div>
        </div>

        
        <form id="frmSent" action="{{route('superuser.penjualan.delivery_order.sent')}}" method="post" enctype="multipart/form-data">
        @csrf
          <input type="hidden" name="do_id" value="{{$result->id}}">

          @if($result->status == 5 && $result->image == null)
          <div class="form-group row">
            <div class="col-lg-12">
              <div class="row">
                <label class="col-md-2 col-form-label text-right">Upload Image</label>
                <div class="col-md-8">
                  <input type="file" name="image" class="form-control" accept="image/*">
                </div>
              </div>
            </div>
          </div>
          @endif

          @if(!empty($result->image))
          <div class="form-group row">
            <div class="col-12">
              <a href="<?= asset($result->image) ?>" class=" mb-5" target="_blank"><img src="<?= asset($result->image) ?>" style="max-width: 300px; max-height: 300px" /></a><br>
            </div>
          </div>
          @endif

          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right" for="name">Delivery Cost(IDR)</label>
            <div class="col-md-4">
              <input type="text" class="form-control" value="{{strlen($result->do_cost->delivery_cost_note) > 0 ? $result->do_cost->delivery_cost_note : ($result->ekspedisi ? $result->ekspedisi->name : '')}}" name="delivery_cost_note" {{$result->status == 6 ? 'readonly' : ''}}>
            </div>
            <div class="col-md-4">
              <input type="number" class="form-control" value="{{$result->do_cost->delivery_cost_idr ?? 0}}" name="delivery_cost_idr" step="any" {{$result->status == 6 ? 'readonly' : ''}}>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-right" for="name">Other Cost(IDR)</label>
            <div class="col-md-4">
              <input type="text" class="form-control" value="{{$result->do_cost->other_cost_note ?? ''}}" name="other_cost_note" {{$result->status == 6 ? 'readonly' : ''}}>
            </div>
            <div class="col-md-4">
              <input type="number" class="form-control" value="{{$result->do_cost->other_cost_idr ?? 0}}" name="other_cost_idr" step="any" {{$result->status == 6 ? 'readonly' : ''}}>
            </div>
          </div>

          <div class="form-group row">
            <div class="col-6">
              <a href="{{route('superuser.penjualan.delivery_order.index')}}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Back</a>
            </div>
            <div class="col-6 text-right">
              @if($result->status===5)
              <button type="button" class="btn btn-primary btn-delivered"><i class="fa fa-save"></i> Selesaikan</button>
              @endif
            </div>
          </div>
        </form>

      </div>
    </div>

  </div>
</div>


<form method="post" action="{{route('superuser.penjualan.delivery_order.packed')}}" id="frmUpdateStatusPacked">
    @csrf
    <input type="hidden" name="id" value="{{$result->id}}">
</form>
<form method="post" action="{{route('superuser.penjualan.delivery_order.sending')}}" id="frmUpdateStatus">
    @csrf
    <input type="hidden" name="id" value="{{$result->id}}">
</form>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script type="text/javascript">
  let idx = 0;
  $(function(){
    $(document).on('click','.btn-delivery',function(){
      if(confirm("Apakah anda yakin ingin mengubah status orderan ini menjadi delivery? ")){
        $('#frmUpdateStatus').submit();
      }
    })

    $(document).on('click','.btn-delivered',function(){
      if(confirm("Apakah anda yakin ingin mengubah status orderan ini menjadi delivered? ")){
        $('#frmSent').submit();
      }
    })
    
  })

  function konfirmasiBarang() {
    if ($(".confirm-item").length === $(".confirm-item:checked").length) {
      //changeStep(2);
      if(confirm("Apakah anda yakin ingin mengubah status orderan ini menjadi packed?")){
        $('#frmUpdateStatusPacked').submit();
      }
    } else {
      Swal.fire(
                'Warning!',
                "Seluruh item harus sudah dikonfirmasi packingnya sebelum diberangkatkan (pilih centang untuk masing masing item yang sudah dikonfirmasi)",
                'warning'
              );
    }
  }

  function changeStep(stepNumber) {
    $(".wizard .step").removeClass('active');
    $(".wizard .step-container").removeClass('active');

    $("#step" + stepNumber).addClass('active');
    $("#step" + stepNumber + "Container").addClass('active');
    
    $('.js-select2').select2();
  }
</script>
@endpush
