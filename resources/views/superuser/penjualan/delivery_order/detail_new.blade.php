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
              <input type="text" class="form-control" value="{{ $result->warehouse->name ?? '-' }}" readonly>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="ekspedisi">Ekspedisi</label>
            <div class="col-md-7">
              <!-- <div class="form-control-plaintext">{{$result->ekspedisi->name ?? '-'}}</div> -->
              <input type="text" class="form-control" value="{{$result->vendor->name ?? '-'}}" readonly>
            </div>
          </div>
        </div>
        <div class="col-6">
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="customer">Customer</label>
            <div class="col-md-7">
              <!-- <div class="form-control-plaintext">{{$result->member->name ?? ''}}</div> -->
              <input type="text" class="form-control" value="{{ $result->member->name}} {{ $result->member->text_kota }}" readonly>
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
            <button type="button" class="btn btn-warning">
              <i class="fa fa-arrow-left mr-10"></i> Back
            </button>
          </a>
        </div>
        <div class="col-md-6 text-right">
        @if(in_array($result->type_transaction, ['TEMPO','COD','MARKETPLACE']))
          <a href="{{ route('superuser.penjualan.delivery_order.print_manifest', $result->id) }}" 
            class="btn btn-info btn-sm btn-flat" 
            data-id="{{ $result->id }}" 
            target="_blank">
              <i class="fas fa-clipboard-list"></i> Print Manifest
          </a>
        @endif
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
                <td>{{ $row->product_pack->code }} - {{$row->product_pack->name}}</td>
                <td>{{$row->qty}}</td>
                <td>{{$row->product_pack->packaging->pack_name}}</td>
                <td>
                  <input type="checkbox" 
                    class="confirm-item" 
                    name="confirmed_items[]" 
                    value="{{$row->id}}" />
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
        <div class="form-group row">
          <div class="col-6">
            <button type="button" class="btn btn-danger btn-cancel-step">
                <i class="fa fa-undo"></i> Kembali ke Packing Order
            </button>
          </div>
          <div class="col-6 text-right">
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
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="warehouse">Warehouse</label>
            <div class="col-md-7">
              <!-- <div class="form-control-plaintext">{{$result->origin_warehouse->name ?? '-'}}</div> -->
              <input type="text" class="form-control" value="{{ $result->warehouse->name ?? '-' }}" readonly>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="ekspedisi">Ekspedisi</label>
            <div class="col-md-7">
              <!-- <div class="form-control-plaintext">{{$result->ekspedisi->name ?? '-'}}</div> -->
              <input type="text" class="form-control" value="{{$result->vendor->name ?? '-'}}" readonly>
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
            <button type="button" class="btn btn-warning">
              <i class="fa fa-arrow-left mr-10"></i> Back
            </button>
          </a>
        </div>
        <div class="col-md-6 text-right">
          @if($result->count_cancel == 0)
              <a href="{{ route('superuser.penjualan.delivery_order.print', $result->id) }}"
                class="btn btn-info btn-sm btn-flat"
                target="_blank">
                  <i class="fa fa-file-o"></i> Print DO
              </a>
              @if(isset($result->so) && isset($result->so->showroom_mutation))
                  <a href="{{ route(
                      'superuser.gudang.mutasi_showroom.print_pdf',
                      $result->so->showroom_mutation->id
                  ) }}"
                    class="btn btn-secondary btn-sm btn-flat"
                    target="_blank">
                      <i class="fa fa-file-o"></i> Print SJ Internal
                  </a>
              @endif
          @elseif($result->count_cancel == 1)
              <a href="{{ route('superuser.penjualan.delivery_order.print', $result->id) }}"
                class="btn btn-info btn-sm btn-flat"
                target="_blank">
                  <i class="fa fa-print"></i> Print DO Revisi
              </a>
          @endif
      </div>
      </div>
      <hr >
        <div class="form-group row">
          <div class="col-6">
            <button type="button" class="btn btn-danger btn-cancel-step mr-2">
                <i class="fa fa-undo"></i> Kembali ke Checker
            </button>
          </div>
          <div class="col-6 text-right">
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

            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right" for="name">Customer</label>
              <div class="col-md-4">
                <input class="form-control" value="{{$result->member->name}}" readonly>
              </div>
              <div class="col-md-4">
                <input class="form-control" value="{{$result->member->text_kota}}" readonly>
              </div>
            </div>

            @if($result->status == 5 && $result->image == null)
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right" for="name">Upload Image</label>
              <div class="col-md-4">
                <input type="file" id="image" name="image" data-max-file-size="2000" accept="image/png, image/jpeg">
              </div>
              <div class="col-md-4">
                <input type="file" id="image2" name="image2" data-max-file-size="2000" accept="image/png, image/jpeg">
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
              <label class="col-md-2 col-form-label text-right" for="name">Ongkir (IDR)</label>
              <div class="col-md-4">
                <input type="text" class="form-control" placeholder="Input Note" value="{{ $result->vendor->name ?? '-' }}" name="delivery_cost_note" {{$result->status == 6 ? 'readonly' : ''}} readonly>
              </div>
              <div class="col-md-4">
                <input type="text" class="form-control" value="{{ $result->do_detail_cost[0]->delivery_cost_idr ?? 0 }}" name="delivery_cost_idr" step="any" {{$result->status == 5  || $result->status == 6 ? 'readonly' : ''}}>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right" for="name">Resi (IDR)</label>
              <div class="col-md-4">
                <select class="form-control js-select2" name="other_cost_note" id="other_cost_note">
                  <option value="">Pilih Ekspedisi</option>
                  @foreach($ekspedisi as $row)
                   <option value="{{$row->name}}">{{ $row->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-4">
                <input type="number" class="form-control" value="{{$result->do_detail_cost->first()->other_cost_idr ?? 0}}" name="other_cost_idr" step="any" {{$result->status == 6 ? 'readonly' : ''}}>
              </div>
            </div>
            

            <div class="form-group row">
              <div class="col-6">
                <a href="{{route('superuser.penjualan.delivery_order.index')}}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Back</a>
                <button type="button" class="btn btn-danger btn-cancel-step mr-2">
                    <i class="fa fa-undo"></i> Kembali ke Siap Kirim
                </button>
              </div>
              <div class="col-6 text-right">
                @if($result->status==5)
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

<form method="post" action="{{ route('superuser.penjualan.delivery_order.multi_cancel') }}" id="frmCancelStep">
    @csrf
    <input type="hidden" name="ids[]" value="{{ $result->id }}">
</form>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.fileinput')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript">
  $('.js-select2').select2();

    $('#image').fileinput({
      theme: 'explorer-fa',
      browseOnZoneClick: true,
      showCancel: false,
      showClose: false,
      showUpload: false,
      browseLabel: '',
      removeLabel: '',
      fileActionSettings: {
        showDrag: false,
        showRemove: false
      },
    });

    $('#image2').fileinput({
      theme: 'explorer-fa',
      browseOnZoneClick: true,
      showCancel: false,
      showClose: false,
      showUpload: false,
      browseLabel: '',
      removeLabel: '',
      fileActionSettings: {
        showDrag: false,
        showRemove: false
      },
    });

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

    let total = $(".confirm-item").length;
    let checked = $(".confirm-item:checked").length;

    if (total === 0) {
        Swal.fire('Warning!', 'Tidak ada item untuk dikonfirmasi.', 'warning');
        return;
    }

    if (checked !== total) {
        Swal.fire(
            'Warning!',
            'Seluruh item harus dikonfirmasi terlebih dahulu.',
            'warning'
        );
        return;
    }

    // Bersihkan input lama
    $('#frmUpdateStatusPacked input[name="confirmed_items[]"]').remove();

    // Tambahkan yang dicentang ke form
    $(".confirm-item:checked").each(function() {
        $('#frmUpdateStatusPacked').append(
            '<input type="hidden" name="confirmed_items[]" value="'+$(this).val()+'">'
        );
    });

    if (confirm("Apakah anda yakin ingin mengubah status orderan ini menjadi packed?")) {
        $('#frmUpdateStatusPacked').submit();
    }
  }


  function changeStep(stepNumber) {
    $(".wizard .step").removeClass('active');
    $(".wizard .step-container").removeClass('active');

    $("#step" + stepNumber).addClass('active');
    $("#step" + stepNumber + "Container").addClass('active');
  }

  function previewImages(event) {
    var preview = document.getElementById('imagePreview');
    preview.innerHTML = ''; // Clear previous previews
    
    var files = event.target.files;
    for (var i = 0; i < files.length; i++) {
        var file = files[i];
        var reader = new FileReader();
        
        reader.onload = function(e) {
            var img = document.createElement('img');
            img.src = e.target.result;
            img.style.width = '150px'; // Adjust image size as needed
            preview.appendChild(img);
        }
        
        reader.readAsDataURL(file);
    }
  }

  $(document).on('click', '.btn-cancel-step', function () {

      Swal.fire({
          title: 'Yakin ingin membatalkan step ini?',
          text: "Status akan diturunkan satu level.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Ya, Turunkan',
          cancelButtonText: 'Batal'
      }).then((result) => {
          if (result.isConfirmed) {
              $('#frmCancelStep').submit();
          }
      });

  });
</script>
@endpush