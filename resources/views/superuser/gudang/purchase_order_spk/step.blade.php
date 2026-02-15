@extends('superuser.app')

@section('content')

@if ( $purchase_order->status() == 'DRAFT' )
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Gudang</span>
    <span class="breadcrumb-item">SPK</span>
    <span class="breadcrumb-item">New</span>
    <span class="breadcrumb-item active">Add Product</span>
  </nav>
@else
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Gudang</span>
    <span class="breadcrumb-item">SPK</span>
    <span class="breadcrumb-item">{{ $purchase_order->code }}</span>
    <span class="breadcrumb-item active">Edit Product</span>
  </nav>
@endif

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
<div id="alert-block"></div>

@if(session()->has('collect_success') || session()->has('collect_error'))
<div class="container">
  <div class="row">
    <div class="col pl-0">
      <div class="alert alert-success alert-dismissable" role="alert" style="max-height: 300px; overflow-y: auto;">
        <h3 class="alert-heading font-size-h4 font-w400">Successful Import</h3>
        @foreach (session()->get('collect_success') as $msg)
        <p class="mb-0">{{ $msg }}</p>
        @endforeach
      </div>
    </div>
    <div class="col pr-0">
      <div class="alert alert-danger alert-dismissable" role="alert" style="max-height: 300px; overflow-y: auto;">
        <h3 class="alert-heading font-size-h4 font-w400">Failed Import</h3>
        @foreach (session()->get('collect_error') as $msg)
        <p class="mb-0">{{ $msg }}</p>
        @endforeach
      </div>
    </div>
  </div>
</div>
@endif

@if(session()->has('message'))
<div class="alert alert-success alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Success</h3>
  <p class="mb-0">{{ session()->get('message') }}</p>
</div>
@endif

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">New SPK</h3>
  </div>
  <div class="block-content">
    <div class="row">
      <label class="col-md-3 col-form-label text-right">SPK Code</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->code }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Warehouse</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->warehouse->name }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">ETD</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ \Carbon\Carbon::parse($purchase_order->etd)->format('d-m-Y')}}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Note</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->note }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Status</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $purchase_order->status() }}</div>
      </div>
    </div>

    <div class="row pt-30 mb-15">
      <div class="col-md-6">
        <a href="{{ route('superuser.gudang.purchase_order_spk.index') }}">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
      </div>
      @if ($purchase_order->status == $purchase_order::STATUS['DRAFT'])
      <div class="col-md-6 text-right">
        
        <a href="{{ route('superuser.gudang.purchase_order_spk.edit', $purchase_order->id) }}">
          <button type="button" class="btn bg-gd-sea border-0 text-white">
            Edit <i class="fa fa-pencil ml-10"></i>
          </button>
        </a>
        <a href="javascript:saveConfirmation('{{ route('superuser.gudang.purchase_order_spk.publish', $purchase_order->id) }}')">
          <button type="button" class="btn bg-gd-leaf border-0 text-white">
            Publish <i class="fa fa-check ml-10"></i>
          </button>
        </a>
      </div>
      @else
      
      <div class="col-md-6 text-right">
        <a href="{{ route('superuser.gudang.purchase_order_spk.edit', $purchase_order->id) }}">
          <button type="button" class="btn bg-gd-sea border-0 text-white">
            Edit <i class="fa fa-pencil ml-10"></i>
          </button>
        </a>
        <a href="javascript:saveConfirmation('{{ route('superuser.gudang.purchase_order_spk.save_modify', [$purchase_order->id, 'save']) }}')">
          <button type="button" class="btn bg-gd-corporate border-0 text-white">
            Save <i class="fa fa-check ml-10"></i>
          </button>
        </a>
        @role('Developer|SuperAdmin', 'superuser')
          <a href="javascript:saveConfirmation('{{ route('superuser.gudang.purchase_order_spk.unpublish', $purchase_order->id) }}')">
            <button type="button" class="btn bg-gd-cherry border-0 text-white">
              Unpublish   <i class="fa fa-times mr-10"></i>
            </button>
          </a>
          <a href="javascript:saveConfirmation2('{{ route('superuser.gudang.purchase_order_spk.save_modify', [$purchase_order->id, 'save-acc']) }}')">
            <button type="button" class="btn bg-gd-leaf border-0 text-white">
              ACC <i class="fa fa-check ml-10"></i>
            </button>
          </a>
        @endrole
      </div>
      @endif
    </div>
  </div>
</div>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">{{ ( $purchase_order->status() == 'DRAFT' ? 'Add' : 'Edit' ) }} Product </h3>

    @if($purchase_order->status == $purchase_order::STATUS['DRAFT'])
    
    <button type="button" class="btn btn-outline-info mr-10 min-width-125 pull-right" data-toggle="modal" data-target="#modal-manage">Import</button>
    
    <a href="{{ route('superuser.gudang.purchase_order_spk.detail.create', [$purchase_order->id]) }}">
      <button type="button" class="btn btn-outline-primary min-width-125 pull-right">Create</button>
    </a>
    @endif
   
  </div>
  <div class="block-content">
    <table id="datatable" class="table table-striped">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">Kode</th>
          <th class="text-center">Nama Varian</th>
          <th class="text-center">Qty (KG)</th>
          <th class="text-center">Packaging</th>
          <th class="text-center">Notes</th>
          <th class="text-center">Customer</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($purchase_order->purchase_order_detail as $row)
          <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td class="text-center">{{ $row->product_pack->code }}</td>
            <td class="text-center">{{ $row->product_pack->name }}</td>
            <td class="text-center">{{ $row->quantity }}</td>
            <td class="text-center">{{ $row->product_pack->packaging->pack_name }}</td>
            <td class="text-center">{{ $row->note_produksi ?? '-' }}</td>
            <td class="text-center">{{ $row->note_repack ?? '-' }}</td>
            <td class="text-center">
              @if($purchase_order->status == $purchase_order::STATUS['DRAFT'])
              <a href="{{ route('superuser.gudang.purchase_order_spk.detail.edit', [$purchase_order->id, $row->id]) }}">
                <button type="button" class="btn btn-sm btn-circle btn-alt-warning" title="Edit">
                  <i class="fa fa-pencil"></i>
                </button>
              </a>
              <a href="javascript:deleteConfirmation('{{ route('superuser.gudang.purchase_order_spk.detail.destroy', [$purchase_order->id, $row->id]) }}')">
                <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete">
                    <i class="fa fa-times"></i>
                </button>
              </a>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Ref PO -->
<div class="modal fade" id="refPoModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Cari Ref PO</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="text" id="searchRefPo" class="form-control mb-3" placeholder="Ketik kode / nama PO...">

        <table class="table table-bordered" id="refPoTable">
          <thead>
            <tr>
              <th>ID</th>
              <th>Code</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr><td colspan="3" class="text-center">Silakan ketik untuk mencari...</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.select2')

@section('modal')
  @include('superuser.component.modal-manage-purchase-order-detail', [
    'import_template_url' => route('superuser.gudang.purchase_order_spk.import_template'),
    'import_url' => route('superuser.gudang.purchase_order_spk.import', $purchase_order->id),
    // 'export_url' => route('superuser.gudang.purchase_order_spk.export')
  ])
@endsection

@push('scripts')
<script src="{{ asset('public/utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function () {
    $('.js-select2').select2()

    $('#datatable').DataTable();

    // trigger pencarian ketika user ketik (debounce biar tidak terlalu sering request)
    let typingTimer;
    const typingDelay = 400; // ms

    $('#searchRefPo').on('keyup', function () {
        clearTimeout(typingTimer);
        let keyword = $(this).val();

        typingTimer = setTimeout(function () {
            if (keyword.length >= 2) { // baru cari kalau minimal 2 huruf
                loadRefPo(keyword);
            } else {
                $('#refPoTable tbody').html('<tr><td colspan="3" class="text-center">Ketik minimal 2 huruf...</td></tr>');
            }
        }, typingDelay);
    });

    function loadRefPo(keyword) {
        $.ajax({
            url: "{{ route('superuser.gudang.purchase_order_spk.listRefPo') }}",
            type: "GET",
            data: { q: keyword }, // kirim parameter pencarian
            dataType: "json",
            success: function (data) {
                if (data.length === 0) {
                    $('#refPoTable tbody').html('<tr><td colspan="3" class="text-center">Data tidak ditemukan</td></tr>');
                    return;
                }

                let rows = '';
                $.each(data, function (index, item) {
                    rows += `
                        <tr>
                            <td>${item.id}</td>
                            <td>${item.code ?? '-'}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-success selectRefPo" data-id="${item.id}">
                                    Pilih
                                </button>
                            </td>
                        </tr>
                    `;
                });
                $('#refPoTable tbody').html(rows);
            },
            error: function () {
                $('#refPoTable tbody').html('<tr><td colspan="3" class="text-center text-danger">Gagal memuat data</td></tr>');
            }
        });
    }

    // handle klik tombol pilih (sama seperti sebelumnya)
    $(document).on('click', '.selectRefPo', function () {
        let refPoId = $(this).data('id');
        let currentPoId = "{{ $purchase_order->id }}";

        $.ajax({
            url: "{{ route('superuser.gudang.purchase_order_spk.updateRefPo', ':id') }}".replace(':id', currentPoId),
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                ref_po_id: refPoId
            },
            success: function (res) {
                alert(res.message);
                $('#refPoModal').modal('hide');
                location.reload();
            },
            error: function () {
                alert('Gagal update Ref PO');
            }
        });
    });
});
</script>
@endpush
