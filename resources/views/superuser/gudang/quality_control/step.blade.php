@extends('superuser.app')

@section('content')

@if ( $receiving->status() == $receiving::STATUS['ACTIVE'] )
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Gudang</span>
    <span class="breadcrumb-item">Receiving</span>
    <span class="breadcrumb-item">New</span>
    <span class="breadcrumb-item active">Add Detail</span>
  </nav>
@else
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Gudang</span>
    <span class="breadcrumb-item">Receiving</span>
    <span class="breadcrumb-item">{{ $receiving->code }}</span>
    <span class="breadcrumb-item active">Edit Detail</span>
  </nav>
@endif

@if(session()->has('message'))
<div class="alert alert-success alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Success</h3>
  <p class="mb-0">{{ session('message') }}</p>
</div>
@endif

@if(session()->has('error'))
<div class="alert alert-danger alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Error</h3>
  <p class="mb-0">{{ session('error') }}</p>
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

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">New Receiving</h3>
  </div>
  <div class="block-content">
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Code</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $receiving->code }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Warehouse</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $receiving->warehouse->name }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">PBM Date</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $receiving->pbm_date ? date('d/m/Y', strtotime($receiving->pbm_date)) : '' }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Note</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $receiving->note }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Status</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $receiving->status() }}</div>
      </div>
    </div>

    <div class="row pt-30 mb-15">
      <div class="col-md-6">
        <a href="{{ route('superuser.gudang.quality_control.index') }}">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
      </div>
      <div class="col-md-6 text-right">
        @php
            use App\Entities\Gudang\QualityControl as RI;
            $role = $superuser->division;           // singkat
        @endphp

        {{-- 1. Draft (ACTIVE) – tombol Edit/Publish/Delete hanya utk Admin & Developer --}}
        @if($receiving->status == \App\Entities\Gudang\QualityControl::STATUS['ACTIVE']
            && in_array($role, ['Admin','Developer']))
            <a href="{{ route('superuser.gudang.quality_control.edit', $receiving->id) }}">
                <button type="button" class="btn bg-gd-sea border-0 text-white">
                    Edit <i class="fa fa-pencil ml-10"></i>
                </button>
            </a>

            <a href="{{ route('superuser.gudang.quality_control.publish', $receiving->id) }}">
              <button type="button" class="btn bg-gd-leaf border-0 text-white">
                Publish to QC <i class="fa fa-check ml-10"></i>
              </button>
            </a>

            <a href="javascript:deleteConfirmation('{{ route('superuser.gudang.quality_control.destroy', $receiving->id) }}', true)">
                <button type="button" class="btn bg-gd-pulse border-0 text-white">
                    Delete <i class="fa fa-trash ml-10"></i>
                </button>
            </a>

        {{-- 2. Tahap QC – tombol Finish QC hanya utk Warehouse --}}
        @elseif($receiving->status == \App\Entities\Gudang\QualityControl::STATUS['QC'] && in_array($role, ['Warehouse','Developer']))
            <a href="{{ route('superuser.gudang.quality_control.publish', $receiving->id) }}">
              <button type="button" class="btn bg-gd-leaf border-0 text-white">
                Publish to Ready <i class="fa fa-check ml-10"></i>
              </button>
            </a>

        {{-- 3. Tahap ACC --}}
        @elseif($receiving->status == \App\Entities\Gudang\QualityControl::STATUS['READY'] && in_array($role, ['Admin','Developer']))
            <a href="javascript:saveConfirmation2('{{ route('superuser.gudang.quality_control.acc_ri', $receiving->id) }}')">
                <button type="button" class="btn bg-gd-leaf border-0 text-white" title="ACC">
                  ACC <i class="fa fa-check"></i>
                </button>
            </a>
        @endif
      </div>
    </div>
  </div>
</div>
<div class="block">
  @if(in_array($role, ['Admin','Developer', 'Warehouse', 'Management']) && in_array($receiving->status, [
    \App\Entities\Gudang\QualityControl::STATUS['ACTIVE'],
    \App\Entities\Gudang\QualityControl::STATUS['READY'],
    \App\Entities\Gudang\QualityControl::STATUS['ACC']
  ]))
  <div class="block-header block-header-default">
    <h3 class="block-title">Add Detail ({{ $receiving->details->count() }})</h3>
    @if(in_array($role, ['Admin','Developer', 'Management']))
    <a href="{{ route('superuser.gudang.quality_control.detail.create', [$receiving->id]) }}">
      <button type="button" class="btn btn-outline-primary min-width-125 pull-right">Create</button>
    </a>
    @endif
  </div>
  <div class="block-content">
    <table id="datatable" class="table table-striped">
      @if($receiving->type == 1)
        <thead>
          <tr>
            <th class="text-center">#</th>
            <th class="text-center">Product</th>
            <th class="text-center">Qty Retur</th>
            <th class="text-center">Note</th>
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach($receiving->details as $detail)
          <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td class="text-center">{{ $detail->product_pack->code }} - <b>{{ $detail->product_pack->name }}</b> - {{$detail->product_pack->packaging->pack_name}}</td>
            <td class="text-center">{{ $detail->quantity_po }}</td>
            <td class="text-center">{{ $detail->note }}</td>
            <td class="text-center">
              @if(in_array($role, ['Admin','Developer', 'Management']))
                <a href="javascript:deleteConfirmation('{{ route('superuser.gudang.quality_control.detail.destroy', [$receiving->id, $detail->id]) }}')">
                  <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete Detail">
                    <i class="fa fa-trash"></i>
                  </button>
                </a>
              @endif
            </td>
          </tr>
          @endforeach
      @else
        <thead>
          <tr>
            <th class="text-center">#</th>
            <th class="text-center">Product</th>
            <th class="text-center">Quantity RI</th>
            <th class="text-center">Quantity QC</th>
            <th class="text-center">Kurang Kirim</th>
            <th class="text-center">NO BATCH</th>
            <th class="text-center">Note</th>
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach($receiving->details as $detail)
          <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td class="text-center">{{ $detail->product_pack->code }} - <b>{{ $detail->product_pack->name }}</b> - {{$detail->product_pack->packaging->pack_name}}</td>
            <td class="text-center">{{ $detail->quantity_po }}</td>
            <td class="text-center">{{ $detail->quantity_ri ?? '-' }}</td>
            <td class="text-center">{{ $detail->selisih ?? '-' }}</td>
            <td class="text-center">{{ $detail->no_batch ?? '-'}}</td>
            <td class="text-center">{{ $detail->note }}</td>
            <td class="text-center">
              @if(in_array($role, ['Admin','Developer', 'Management']))
                <a href="javascript:deleteConfirmation('{{ route('superuser.gudang.quality_control.detail.destroy', [$receiving->id, $detail->id]) }}')">
                  <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete Detail">
                    <i class="fa fa-trash"></i>
                  </button>
                </a>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      @endif
    </table>
  </div>

  @elseif($receiving->status == \App\Entities\Gudang\QualityControl::STATUS['QC'])
  <div class="block-content">
    <table id="datatable_qc" class="table table-striped">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">Product</th>
          <th class="text-center">Quantity</th>
          <th class="text-center">Status Qc</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        @if(in_array($role, ['Warehouse','Developer']))
        <div class="mb-3">
        <button type="button"
                  class="btn btn-sm btn-outline-primary btn-add-qc-global">
            <i class="fa fa-plus"></i> Tambah QC
          </button>
        </div>
        @endif
        @foreach($receiving->details as $detail)
          @foreach($detail->qcLogs as $qc)
            <tr>
              <td class="text-center">{{ $loop->iteration }}</td>
              <td class="text-center">{{ $detail->product_pack->code }} - <b>{{ $detail->product_pack->name }}</b> - {{$detail->product_pack->packaging->pack_name}}</td>
              <td class="text-center">{{ $qc->qty_qc }}</td>
              <td class="text-center">{{ $qc->status_qc() }}</td>
              <td class="text-center">
                @if($qc->is_sellable && $qc->is_approved == 0 && in_array($role, ['Admin','Developer']))
                    <a href="javascript:saveConfirmation2('{{ route('superuser.gudang.quality_control.detail.approveQc', $qc->id) }}')">
                      <button type="button" class="btn btn-sm btn-circle btn-alt-warning" title="Approve QC Saleable">
                        <i class="fa fa-check"></i>
                      </button>
                    </a>
                @endif

                @if(in_array($role, ['Warehouse','Developer']))
                  <a href="javascript:void(0);" onclick="deleteQc('{{ route('superuser.gudang.quality_control.detail.destroyQc', $qc->id) }}')">
                    <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus Log QC">
                      <i class="fa fa-trash"></i>
                    </button>
                  </a>
                @endif
              </td>
            </tr>
          @endforeach
        @endforeach
      </tbody>
    </table>
  </div>
 @endif

<!-- Modal QC Partial -->
<div class="modal fade" id="modalQcPartial" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <form id="formQcPartial" method="POST" class="needs-validation" novalidate>
      @csrf
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">
            <i class="fa fa-check-circle mr-2"></i>Tambah QC Partial
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body pb-0">
          {{-- ▼ PILIH PRODUK --}}
          <div class="form-group">
            <label class="font-weight-bold" for="detail_id">Produk</label>
            <select name="detail_id" id="detail_id" class="form-control select2" required>
              <option value="">-- Pilih Produk --</option>
              {{-- Opsi akan di-*render* dari JavaScript --}}
            </select>
          </div>

          <script>
            const productOptions = {!! json_encode(
              $receiving->details->map(function($d) {
                return [
                  'id' => $d->id,
                  'code' => $d->product_pack->code,
                  'name' => $d->product_pack->name,
                  'pack' => optional($d->product_pack->packaging)->pack_name,
                  'po' => (float) $d->quantity_po,
                  'qc' => (float) $d->qcLogs->sum('qty_qc'),
                  'sisa' => (float) $d->quantity_po - (float) $d->qcLogs->sum('qty_qc'),
                ];
              })
            ) !!};
          </script>

          {{-- ▼ JUMLAH QC --}}
          <div class="form-group">
            <label class="font-weight-bold" for="qty_qc">Jumlah QC (kg)</label>
            <input type="number" min="0.1" step="0.1" name="qty_qc" id="qty_qc"
                   class="form-control" placeholder="Contoh: 10.5" required>
          </div>

          {{-- ▼ STATUS QC --}}
          <div class="form-group">
            <label class="font-weight-bold" for="status_qc">Status QC</label>
            <select name="status_qc" id="status_qc" class="form-control select2" required>
              <option value="">-- Pilih Status --</option>
              <option value="OK">OK</option>
              <option value="NOT OK">NOT OK</option>
            </select>
          </div>

          @if($receiving->type == 0)
          {{-- ▼ SALEABLE --}}
          <div class="form-group mb-3">
            <div class="form-check">
              <input type="checkbox" id="is_sellable_checkbox" class="form-check-input">
              <label class="form-check-label font-weight-bold" for="is_sellable_checkbox">
                Langsung bisa dijual <small class="text-muted">(saleable)</small>
              </label>
            </div>
            <input type="hidden" name="is_sellable" id="is_sellable" value="0">
          </div>
          @endif
        </div>

        <div class="modal-footer border-top-0 pt-0">
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save mr-1"></i> Simpan
          </button>
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
            <i class="fa fa-times mr-1"></i> Tutup
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.magnific-popup')

@section('modal')
  @include('superuser.component.modal-manage-receiving-detail', [
    'import_template_url' => route('superuser.gudang.quality_control.import_template'),
    'import_url' => route('superuser.gudang.quality_control.import', $receiving->id),
    // 'export_url' => route('superuser.gudang.quality_control.export')
  ])
@endsection

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function deleteQc(url) {
    if (confirm("Apakah Anda yakin ingin menghapus data QC ini?")) {
        $.ajax({
            url: url,
            type: 'GET', // Gunakan POST/DELETE jika lebih aman
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    alert(response.message);
                    location.reload(); // atau datatable.ajax.reload() jika pakai datatables
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON.message || 'Terjadi kesalahan saat menghapus data QC.');
            }
        });
    }
}
</script>

<script type="text/javascript">
$(document).ready(function () {
  $('#datatable').DataTable({});
  $('#datatable_qc').DataTable({});
  $('.select2').select2({ width: '100%' });

  // Tombol tambah QC
  $('.btn-add-qc-global').on('click', function () {
    filterSelectOptions();
    $('#modalQcPartial').modal('show');
  });

  // Checkbox is_sellable
  $('#is_sellable_checkbox').on('change', function () {
    $('#is_sellable').val(this.checked ? 1 : 0);
  });

  // Filter opsi select detail_id (hanya yang belum selesai QC)
  function filterSelectOptions() {
    const $select = $('#detail_id');
    $select.empty();
    $select.append(`<option value="">-- Pilih Produk --</option>`);

    let count = 0;
    productOptions.forEach(p => {
      if (p.qc < p.po) {
        const displayText = `${p.code} - ${p.name} / ${p.pack} (Remaind : ${p.sisa.toFixed(2)} kg)`;
        $select.append(`<option value="${p.id}">${displayText}</option>`);
        count++;
      }
    });

    $select.val(null).trigger('change');

    if (count === 0) {
      Swal.fire('Info', 'Semua produk sudah selesai QC.', 'info');
      $('#modalQcPartial').modal('hide');
    }
  }

  // Submit form QC
  $('#formQcPartial').on('submit', function (e) {
    e.preventDefault();

    const detailId = $('#detail_id').val();
    if (!detailId) {
      return Swal.fire('Oops', 'Produk harus dipilih', 'warning');
    }

    let url = '{{ route("superuser.gudang.quality_control.detail.qty_qc", ":detail") }}';
    url = url.replace(':detail', detailId);

    $.post(url, $(this).serialize())
      .done(() => {
        Swal.fire('Berhasil', 'Data QC tersimpan', 'success')
          .then(() => location.reload());
      })
      .fail(xhr => {
        if (xhr.responseJSON && xhr.responseJSON.notification) {
          const notif = xhr.responseJSON.notification;
          const content = Array.isArray(notif.content)
            ? notif.content.join('<br>')
            : notif.content;

          Swal.fire({
            icon: notif.type.includes('danger') ? 'error' : 'warning',
            title: notif.header || 'Gagal',
            html: content,
          });
        } else {
          Swal.fire('Gagal', 'Terjadi kesalahan pada server', 'error');
        }
      });
  });

  $(function() {
    $('#modalQcPartial').on('hidden.bs.modal', function () {
      // Reset form fields
      $('#formQcPartial')[0].reset();
      // Reset select2 fields
      $('#detail_id').val('').trigger('change');
      $('#status_qc').val('').trigger('change');
      // Uncheck checkbox and reset hidden input
      $('#is_sellable_checkbox').prop('checked', false);
      $('#is_sellable').val(0);
    });
  });
});
</script>
@endpush

