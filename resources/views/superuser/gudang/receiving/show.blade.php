@extends('superuser.app')

@section('content')

<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Purchasing</span>
  <span class="breadcrumb-item">Receiving</span>
  <span class="breadcrumb-item">{{ $receiving->code }}</span>
</nav>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Receiving</h3>
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
        <div class="form-control-plaintext">{{ $receiving->description ?? '-' }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Status</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $receiving->status() }}</div>
      </div>
    </div>

    <div class="form-group row pt-30">
      <div class="col-md-6">
        <a href="{{ route('superuser.gudang.receiving.index') }}">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
      </div>

      <div class="col-md-6 text-right">
        @php
            use App\Entities\Gudang\Receiving as RI;
            $role = $superuser->division;           // singkat
        @endphp

        {{-- 1. Draft (ACTIVE) – tombol Edit/Publish/Delete hanya utk Admin & Developer --}}
        @if($receiving->status == \App\Entities\Gudang\Receiving::STATUS['ACTIVE']
            && in_array($role, ['Admin','Developer']))
            <a href="{{ route('superuser.gudang.receiving.edit', $receiving->id) }}">
                <button type="button" class="btn bg-gd-sea border-0 text-white">
                    Edit <i class="fa fa-pencil ml-10"></i>
                </button>
            </a>

            <a href="{{ route('superuser.gudang.receiving.publish', $receiving->id) }}">
              <button type="button" class="btn bg-gd-leaf border-0 text-white">
                Publish to QC <i class="fa fa-check ml-10"></i>
              </button>
            </a>

            <a href="javascript:deleteConfirmation('{{ route('superuser.gudang.receiving.destroy', $receiving->id) }}', true)">
                <button type="button" class="btn bg-gd-pulse border-0 text-white">
                    Delete <i class="fa fa-trash ml-10"></i>
                </button>
            </a>

        {{-- 2. Tahap QC – tombol Finish QC hanya utk Warehouse --}}
        @elseif($receiving->status == \App\Entities\Gudang\Receiving::STATUS['QC'] && in_array($role, ['Warehouse','Developer']))
            <a href="{{ route('superuser.gudang.receiving.publish', $receiving->id) }}">
              <button type="button" class="btn bg-gd-leaf border-0 text-white">
                Publish to Ready <i class="fa fa-check ml-10"></i>
              </button>
            </a>

        {{-- 3. Tahap ACC --}}
        @elseif($receiving->status == \App\Entities\Gudang\Receiving::STATUS['READY'] && in_array($role, ['Admin','Developer']))
            <a href="javascript:saveConfirmation2('{{ route('superuser.gudang.receiving.acc', $receiving->id) }}')">
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
  <div class="block-header block-header-default">
    <h3 class="block-title">Detail ({{ $receiving->details->count() }})</h3>
  </div>
  <div class="block-content">
    <table id="datatable" class="table table-striped">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">Product</th>
          <th class="text-center">Quantity SJ</th>
          <th class="text-center">Quantity QC</th>
          <th class="text-center">Selisih</th>
          <th class="text-center">NO BATCH</th>
          <th class="text-center">Note</th>
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
          <td class="text-center">{{ $detail->note ?? '-' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.magnific-popup')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $('#datatable').DataTable({
      "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>> <"row"<"col-sm-12 col-md-12"p>> <"row"<"col-sm-12"rt>> <"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>'
    })

    $('a.img-lightbox').magnificPopup({
    type: 'image',
    closeOnContentClick: true,
  });
  })
</script>
@endpush