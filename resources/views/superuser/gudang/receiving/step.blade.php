@extends('superuser.app')

@section('content')

@if ( $receiving->status() == $receiving::STATUS['ACTIVE'] )
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Purchasing</span>
    <span class="breadcrumb-item">Receiving</span>
    <span class="breadcrumb-item">New</span>
    <span class="breadcrumb-item active">Add Detail</span>
  </nav>
@else
  <nav class="breadcrumb bg-white push">
    <span class="breadcrumb-item">Purchasing</span>
    <span class="breadcrumb-item">Receiving</span>
    <span class="breadcrumb-item">{{ $receiving->code }}</span>
    <span class="breadcrumb-item active">Edit Detail</span>
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
    {{--<div class="row">
      <label class="col-md-3 col-form-label text-right">No container</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $receiving->no_container }}</div>
      </div>
    </div>--}}
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Note</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $receiving->description }}</div>
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
      </div>

      <div class="col-md-6 text-right">

        <a href="{{ route('superuser.gudang.receiving.edit', $receiving->id) }}">
          <button type="button" class="btn bg-gd-sea border-0 text-white">
            Edit <i class="fa fa-pencil ml-10"></i>
          </button>
        </a>
        <a href="javascript:deleteConfirmation('{{ route('superuser.gudang.receiving.destroy', $receiving->id) }}', true)">
          <button type="button" class="btn bg-gd-pulse border-0 text-white">
            Delete <i class="fa fa-trash ml-10"></i>
          </button>
        </a>
        <a href="javascript:saveConfirmation2('{{ route('superuser.gudang.receiving.publish', $receiving->id) }}')">
          <button type="button" class="btn bg-gd-leaf border-0 text-white">
            ACC <i class="fa fa-check ml-10"></i>
          </button>
        </a>
      </div>
    </div>
  </div>
</div>

<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Add Detail ({{ $receiving->details->count() }})</h3>

    <button type="button" class="btn btn-outline-info mr-10 min-width-125 pull-right" data-toggle="modal" data-target="#modal-manage">Import</button>

    <a href="{{ route('superuser.gudang.receiving.detail.create', [$receiving->id]) }}">
      <button type="button" class="btn btn-outline-primary min-width-125 pull-right">Create</button>
    </a>
  </div>
  <div class="block-content">
    <table id="datatable" class="table table-striped">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">PO Number</th>
          <th class="text-center">Product</th>
          
          <th class="text-center">PO Quantity</th>
          <th class="text-center">RI Quantity</th>
          <th class="text-center">Sisa Quantity</th>
          <th class="text-center">Colly Quantity</th>
          <th class="text-center">NO BATCH</th>
          <th class="text-center">Note</th>
          <th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach($receiving->details as $detail)
        <tr>
          <td class="text-center">{{ $loop->iteration }}</td>
          <td class="text-center">{{ $detail->purchase_order->code }}</td>
          <td class="text-center">{{ $detail->product_pack->code }} - <b>{{ $detail->product_pack->name }}</b></td>
          
          <td class="text-center">{{ $receiving->price_format($detail->quantity) }}</td>
          <td class="text-center">{{ $receiving->price_format($detail->total_quantity_ri) }}{{ $detail->total_reject_ri($detail->id) ? ' [RE '.$receiving->price_format($detail->total_reject_ri($detail->id)).']' : '' }}</td>
          <td class="text-center">{{ $receiving->price_format($detail->quantity) - $receiving->price_format($detail->total_quantity_ri ?? 0) }}</td>
          <td class="text-center">{{ $receiving->price_format($detail->total_quantity_colly) }}{{ $detail->total_reject_colly($detail->id) ? ' [RE '.$receiving->price_format($detail->total_reject_colly($detail->id)).']' : '' }}</td>
          <td class="text-center">{{ $detail->no_batch ?? '-'}}</td>
          <td class="text-center">{{ $detail->note }}</td>
          <td class="text-center" style="white-space: nowrap;">
            <a href="{{ route('superuser.gudang.receiving.detail.edit', [$receiving->id, $detail->id]) }}">
              <button type="button" class="btn btn-sm btn-circle btn-alt-warning" title="Edit Note">
                <i class="fa fa-pencil"></i>
              </button>
            </a>
            @if(is_null($detail->colly))
              <!-- <button type="button" class="btn btn-sm btn-circle btn-alt-info addColly" data-id="{{$receiving->id}}" data-detail-id="{{ $detail->id }}" data-bs-toggle="modal" data-bs-target="#myModal">
                <i class="fa fa-plus"></i>
              </button> -->
              <a href="javascript:void(0)" type="button" class="btn btn-sm btn-circle btn-alt-info openModal" data-id="{{$receiving->id}}" data-detail-id="{{$detail->id}}"><i class="fa fa-plus"></i></a> 
            @endif
            <a href="javascript:deleteConfirmation('{{ route('superuser.gudang.receiving.detail.destroy', [$receiving->id, $detail->id]) }}')">
              <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete">
                  <i class="fa fa-times"></i>
              </button>
            </a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="appointmentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="alert alert-success alert-dismissible fade show" role="alert" style="display:none;">
        <strong>Success!</strong> add quantity RI!.
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Quantity RI</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="myForm" method="POST" role="form" enctype="multipart/form-data" novalidate>
                  @csrf
                    <input type="hidden" class="form-control" id="colly" name="colly" value="1">
                    <div class="mb-3">
                        <label>Quantity RI</label>
                        <input type="number" class="form-control" id="ri" name="ri" step="any">
                    </div>
                    <input type="hidden" id="receivingID" />
                    <input type="hidden" id="detailID" />
                    <button type="submit" class="btn btn-info">Save</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.magnific-popup')
@include('superuser.asset.plugin.swal2')

@section('modal')
  @include('superuser.component.modal-manage-receiving-detail', [
    'import_template_url' => route('superuser.gudang.receiving.import_template'),
    'import_url' => route('superuser.gudang.receiving.import', $receiving->id),
    // 'export_url' => route('superuser.gudang.receiving.export')
  ])
@endsection

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $('#datatable').DataTable({})
  })

  $(document).on('click', '.openModal', function () {
    var id = $(this).data('id');
    var detail = $(this).data('detail-id');
    $('#receivingID').val(id);
    $('#detailID').val(detail);
    $('#appointmentModal').modal('show');
  })

  $('#myForm').on('submit', function (e) {
      e.preventDefault(); // prevent the form submit
      var id = $('#receivingID').val();
      var detail = $('#detailID').val();
      var url = '{{ route("superuser.gudang.receiving.detail.colly.store", [":id",":detail_id"]) }}';
      url = url.replace(':id', id);
      url = url.replace(':detail_id', detail);
      var AlertMsg = $('div[role="alert"]');

      var formData = new FormData(this); 
      $.ajax({
          url: url,
          type: 'POST',
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          data: formData,
          contentType: false,
          processData: false,
          success: function (response) {
            $(AlertMsg).show();
            setTimeout(function () {
                    $('#myModal').modal({ show: true });
                    setTimeout(function () {
                        window.location.reload(1);
                    }, 800);
            }, 800);
          }
      });
    });
</script>
@endpush
