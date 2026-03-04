@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Purchasing</span>
  <span class="breadcrumb-item">Receiving</span>
  <span class="breadcrumb-item">{{ $receiving->code }}</span>
  <span class="breadcrumb-item active">Add Detail</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Add Detail</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.gudang.receiving.detail.store', $receiving->id) }}" data-type="POST" enctype="multipart/form-data">
      <input type="hidden" id="po_id" name="po_id">
      <input type="hidden" id="pack_name" name="pack_name">
      {{-- 1. Select Product --}}
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="product_id">
          Select Product <span class="text-danger">*</span>
        </label>
        <div class="col-md-7">
          <select class="js-select2 form-control"
                  id="product_pack_id"
                  name="product_pack_id"
                  data-placeholder="Select Product">
            <option></option>
            @foreach($products as $p)
              <option value="{{ $p->product_pack_id }}">
                {{ $p->code }} - {{ $p->name }} - {{ $p->pack_name }}
                @if(isset($p->retur_code))
                  [Retur: {{ $p->retur_code }}]
                @endif
                (Available: {{ number_format($p->qty_available, 2) }})
              </option>
            @endforeach
          </select>
        </div>
      </div>

      {{-- Qty TIDAK readonly lagi --}}
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="quantity">Qty</label>
        <div class="col-md-4">
            <input type="number" class="form-control" id="quantity" name="quantity" min="0.01" step="0.01" max="10000">
        </div>
      </div>

      @if (isset($receiving->type) && $receiving->type == 0)
        {{-- No Batch --}}
        <div class="form-group row">
          <label class="col-md-3 col-form-label text-right" for="no_batch">No Batch</label>
          <div class="col-md-4">
            <input type="number" class="form-control" id="no_batch" name="no_batch" min="1">
          </div>
        </div>
      @endif

      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="note">Note</label>
        <div class="col-md-4">
          <input type="text" class="form-control" id="description" name="description">
        </div>
      </div>

      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.gudang.receiving.step', $receiving->id) }}">
            <button type="button" class="btn bg-gd-cherry border-0 text-white">
              <i class="fa fa-arrow-left mr-10"></i> Back
            </button>
          </a>
        </div>
        <div class="col-md-6 text-right">
          <button type="submit" class="btn bg-gd-corporate border-0 text-white">
            Submit <i class="fa fa-arrow-right ml-10"></i>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {
    function addLoadSpiner(el) {
      if (el.length > 0) {
        if ($("#img_" + el[0].id).length > 0) {
          $("#img_" + el[0].id).css('display', 'block');
        }               
        else {
          var img = $('<img class="ddloading">');
          img.attr('id', "img_" + el[0].id);
          img.attr('src', 'http://ajaxloadingimages.net/gif/image?imageid=aero-spinner&forecolor=000000&backcolor=ffffff&transparent=true');
          img.css({ 'display': 'inline-block', 'width': '25px', 'height': '25px', 'position': 'absolute', 'left': '50%', 'margin-top': '5px' });
          img.prependTo(el[0].nextElementSibling);
        }
        el.prop("disabled", true);               
      }
    }

    function hideLoadSpinner(el) {
      if (el.length > 0) {
        if ($("#img_" + el[0].id).length > 0) {
          setTimeout(function () {
            $("#img_" + el[0].id).css('display', 'none');
            el.prop("disabled", false);
          }, 500);                  
        }
      }
    }

    $(function () {
      $('#product_pack_id').on('select2:select', function () {
        const id = $(this).val();
        const receivingId = {{ $receiving->id }};

        $.ajax({
          url: '{{ route('superuser.gudang.receiving.detail.get_sku_json') }}',
          method: 'POST',
          data: {
            id: id,
            receiving_id: receivingId
          },
          success: function (json) {
            if (json.code === 200) {
              const data = json.data;

              // Set quantity dan limit max input
              $('#quantity')
                .val('')
                .attr('max', data.quantity)
                .attr('placeholder', '≤ ' + data.quantity);

              // Jika data packaging tersedia
              if (data.packaging && data.packaging.pack_name) {
                $('#pack_name').val(data.packaging.pack_name);
              } else {
                $('#pack_name').val('');
              }

              // PO id jika tipe inbond
              $('#po_id').val(data.po_id || '');

              // Tambahkan deskripsi retur jika ada
              if (data.retur_code) {
                $('#description').val('Dari Retur: ' + data.retur_code);
              } else {
                $('#description').val('');
              }
            } else {
              alert(json.message || 'Produk tidak ditemukan');
            }
          },
          error: function () {
            alert('Gagal mengambil data produk');
          }
        });
      });

      $('.js-select2').select2();
    });
  })
</script>
@endpush