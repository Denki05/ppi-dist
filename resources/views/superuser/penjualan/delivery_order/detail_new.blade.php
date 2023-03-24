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
@if($result->status == 3)
<div class="card">
  <div class="card-header">
    <h4 style="font-weight: bold;">#DO PROSES</h4>
  </div>
  <div class="card-body">
    <div class="block-content">
      <div class="row">
        <div class="col-6">
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="code">DO Code</label>
            <div class="col-md-7">
              <!-- <div class="form-control-plaintext">{{ $result->do_code }}</div> -->
              <input class="form-control" type="text" value="{{ $result->do_code }}" readonly>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="warehouse">Warehouse</label>
            <div class="col-md-7">
              <!-- <div class="form-control-plaintext">{{$result->origin_warehouse->name ?? '-'}}</div> -->
              <input type="text" class="form-control" value="{{ $result->origin_warehouse->name ?? '-' }}" readonly>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="ekspedisi">Ekspedisi</label>
            <div class="col-md-7">
              <!-- <div class="form-control-plaintext">{{$result->ekspedisi->name ?? '-'}}</div> -->
              <input type="text" class="form-control" value="{{$result->ekspedisi->name ?? '-'}}" readonly>
            </div>
          </div>
        </div>
        <div class="col-6">
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="customer">Customer</label>
            <div class="col-md-7">
              <!-- <div class="form-control-plaintext">{{$result->member->name ?? ''}}</div> -->
              <input type="text" class="form-control" value="{{ $result->member->name}} | {{ $result->member->address }}" readonly>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="refrensi_so">Referensi SO</label>
            <div class="col-md-7">
              <!-- <div class="form-control-plaintext">{{$result->member->address ?? ''}}</div> -->
              <input type="text" class="form-control" value="{{$result->so->code ?? '-'}}" readonly>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="status">Status</label>
            <div class="col-md-7">
              <div class="form-control-plaintext">
                <span class="badge badge-{{ $result->do_status()->class }}"><b>{{ $result->do_status()->msg }}</b></span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row pt-30 mb-15">
        <div class="col-md-6">
          <a href="{{ route('superuser.penjualan.delivery_order.index') }}">
            <button type="button" class="btn bg-gd-cherry border-0 text-white">
              <i class="fa fa-arrow-left mr-10"></i> Back
            </button>
          </a>
        </div>
        <div class="col-md-6 text-right">
          <a href="{{route('superuser.penjualan.delivery_order.print_manifest', $result->id)}}" class="btn btn-info btn-sm btn-flat" data-id="{{$result->id}}" target="_blank"><i class="fas fa-clipboard-list"></i> Print Manifest</a>
        </div>
      </div>
      <hr >
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
                <td>{{ $row->product->code }} - {{$row->product->name}}</td>
                <td>{{$row->qty}}</td>
                <td>{{$row->packaging_txt()->scalar ?? ''}}</td>
                <td><input type="checkbox" class="confirm-item" value="{{$row->id}}" /></td>
              </tr>
            @endforeach
          </tbody>
        </table>
        <div class="form-group row">
          <div class="col-6"></div>
          <div class="col-12 text-right">
            <button type="button" class="btn btn-primary" onclick="konfirmasiBarang()">Save</button>
          </div>
        </div>
    </div>
  </div>
</div>
@endif

@if($result->status == 4)
<div class="card">
  <div class="card-header">
    <h4 style="font-weight: bold;">#DO SIAP KIRIM</h4>
  </div>
  <div class="card-body">
    <div class="block-content">
      <div class="row">
        <div class="col-6">
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="code">DO Code</label>
            <div class="col-md-7">
              <!-- <div class="form-control-plaintext">{{ $result->do_code }}</div> -->
              <input class="form-control" type="text" value="{{ $result->do_code }}" readonly>
            </div>
          </div>
        </div>
        <div class="col-6">
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="customer">Customer</label>
            <div class="col-md-7">
              <!-- <div class="form-control-plaintext">{{$result->member->name ?? ''}}</div> -->
              <input type="text" class="form-control" value="{{ $result->member->name}} | {{ $result->member->address }}" readonly>
            </div>
          </div>
        </div>
      </div>
      <div class="row pt-30 mb-15">
        <div class="col-md-6">
          <a href="{{ route('superuser.penjualan.delivery_order.index') }}">
            <button type="button" class="btn bg-gd-cherry border-0 text-white">
              <i class="fa fa-arrow-left mr-10"></i> Back
            </button>
          </a>
        </div>
        <div class="col-md-6 text-right">
          <a href="{{route('superuser.penjualan.delivery_order.print', $result->id)}}" class="btn btn-info btn-sm btn-flat" data-id="{{$result->id}}" target="_blank"><i class="fa fa-print"></i> Print DO</a>
          <a href="{{ route('superuser.finance.proforma.print_proforma', [$result->id]) }}" class="btn btn-info btn-sm btn-flat" target="_blank"><i class="fa fa-print"></i> Print Proforma</a>
          @if($result->invoicing != null)
          <a href="{{route('superuser.finance.invoicing.print_paid',$result->invoicing->id)}}" class="btn btn-primary btn-sm btn-flat" data-id="{{$result->invoicing->id}}" target="_blank"><i class="fa fa-print"></i> Print Invoice</a>
          @endif
        </div>
      </div>
      <hr >
        <div class="form-group row">
          <div class="col-6"></div>
          <div class="col-12 text-right">
            <button type="button" class="btn btn-primary btn-delivery"><i class="fas fa-shipping-fast"></i> DELIVERING / BERANGKAT</button>
          </div>
        </div>
    </div>
  </div>
</div>
@endif

@if($result->status == 5)
<div class="card">
  <div class="card-header">
    <h4 style="font-weight: bold;">#DO UPDATE RESI : {{ $result->do_code }}</h4>
  </div>
  <div class="card-body">
    <div class="block-content">
      <div class="row">
        <div class="col-12">
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
                <input type="text" class="form-control" placeholder="Input Note" value="{{strlen($result->do_cost->delivery_cost_note) > 0 ? $result->do_cost->delivery_cost_note : ($result->ekspedisi ? $result->ekspedisi->name : '')}}" name="delivery_cost_note" {{$result->status == 6 ? 'readonly' : ''}}>
              </div>
              <div class="col-md-4">
                <input type="number" class="form-control" value="{{$result->do_cost->delivery_cost_idr ?? 0}}" name="delivery_cost_idr" step="any" {{$result->status == 6 ? 'readonly' : ''}}>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right" for="name">Other Cost(IDR)</label>
              <div class="col-md-4">
                <input type="text" class="form-control" value="{{$result->do_cost->other_cost_note ?? ''}}" name="other_cost_note" placeholder="Input Note" {{$result->status == 6 ? 'readonly' : ''}}>
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
</div>
@endif

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
