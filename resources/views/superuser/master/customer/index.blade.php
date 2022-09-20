@extends('superuser.app')

@section('content')
<!-- <nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <span class="breadcrumb-item active">Store</span>
</nav> -->
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
<div class="block">
  <div class="block-content">
    <a href="{{ route('superuser.master.customer.create') }}">
      <button type="button" class="btn btn-outline-primary min-width-125">Create</button>
    </a>

    <button type="button" class="btn btn-outline-info ml-10" data-toggle="modal" data-target="#modal-manage">Manage</button>

    <!-- <button type="button" class="btn btn-outline-danger ml-10" onclick="deleteMultiple()">Delete Checked</button> -->

    <!-- <a class="ml-10" href="{{ route('superuser.master.product.cetak') }}">
      <button type="button" class="btn btn-outline-warning min-width-125">Print</button>
    </a> -->
  </div>
  <div class="block-content block-content-full">
    <!-- <form id="form" target="_blank" action="#"
      enctype="multipart/form-data" method="POST">
      @csrf
      <input type="hidden" name="download_type" id="download_type" value="">
      <div class="form-group row">
        <div class="col-md-9">
          <div class="block">
            <div class="block-content">
              <div class="form-group row">
                <label class="col-md-2 col-form-label text-left" for="period">Period :</label>
                <div class="col-md-4">
                  <div class="input-group">
                    <div class="input-group-prepend"><span class="input-group-text"><i class="fa fa-calendar"
                          aria-hidden="true"></i></span></div><input type="text" class="form-control pull-right"
                      id="datesearch" name="datesearch" placeholder="Select period"
                      value="">
                  </div>
                </div>
                <label class="col-md-2 col-form-label text-left" for="marketplace">Marketplace :</label>
                <div class="col-md-4">
                  <select class="js-select2 form-control" id="marketplace" name="marketplace" data-placeholder="Select Marketplace">
                    <option value="all">All</option>
                  
                  </select>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-md-2 col-form-label text-left" for="status">Status :</label>
                <div class="col-md-4">
                  <select class="js-select2 form-control" id="status" name="status" data-placeholder="Select Status">
                    <option value="all">All</option>
                    <option value="paid">Paid</option>
                    <option value="debt">Unpaid</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="block">
            <div class="block-content">
              <div class="form-group row">
                <div class="col-md-12 text-center">
                  <a href="#" id="btn-filter" class="btn bg-gd-corporate border-0 text-white pl-50 pr-50">
                    Filter <i class="fa fa-search ml-10"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form> -->
    <table id="customer-table" class="table table-striped">
      <thead class="thead-dark">
        <tr>
          <th></th>
          <th>Store</th>
          <th>Address</th>
          <th>Status</th>
          <th scope="col">Action</th>
          <th>Add Member</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($customers as $row)
          <tr class="clickable js-tabularinfo-toggle" data-toggle="collapse" id="row2" data-target=".a{{ $row->id }}">
              <td>
                <div class="col-sm-6">
                  <div class="row mb-2">
                    <a href="#" class="link">
                      <button type="button" name='edit' id='{{ $row->id }}'
                      class="edit btn btn-xs btn-outline-secondary btn-sm my-0">
                        <i class="mdi mdi-plus-box"></i></button>
                    </a>
                  </div>
                </div>
              </td>
              <td>{{ $row->name }}</td>
              <td>{{ $row->address }} - <br><b>{{ $row->text_kota }}</b></br></td>
              <td>
                @if($row->status == $row::STATUS['ACTIVE'])
                  <span class="badge badge-success">ACTIVE</span>
                @elseif($row->status == $row::STATUS['DELETED'])
                  <span class="badge badge-danger">IN ACTIVE</span>
                @endif
              </td>
              <td>
              @if($row->status == $row::STATUS['ACTIVE'])
                <a href="{{ route('superuser.master.customer.show', $row->id) }}">
                    <button type="button" class="btn btn-sm btn-circle btn-alt-secondary" title="View">
                        <i class="mdi mdi-eye"></i>
                    </button>
                </a>
                <a href="{{ route('superuser.master.customer.edit', $row->id) }}">
                    <button type="button" class="btn btn-sm btn-circle btn-alt-secondary" title="Edit">
                        <i class="mdi mdi-lead-pencil"></i>
                    </button>
                </a>
                <a href="javascript:deleteConfirmation('{$row->id}')">
                    <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete">
                        <i class="mdi mdi-delete"></i>
                    </button>
                </a>
                <a href="{{ route('superuser.master.customer.export_customer') }}">
                    <button type="button" class="btn btn-sm btn-circle btn-alt-secondary" title="Export">
                        <i class="mdi mdi-file-pdf"></i>
                    </button>
                </a>
              @elseif($row->status == $row::STATUS['DELETED'])
                <a href="{{ route('superuser.master.customer.show', $row->id) }}">
                      <button type="button" class="btn btn-sm btn-circle btn-alt-secondary" title="View">
                          <i class="mdi mdi-eye"></i>
                      </button>
                </a>
              @endif
              </td>
              <td>
              @if($row->status == $row::STATUS['ACTIVE'])
                <a class="btn" href="{{ route('superuser.master.customer.other_address.create', [$row->id]) }}" target="_blank" role="button" title="Add Member"><i class="mdi mdi-file-multiple"></i></a>
              @elseif($row->status == $row::STATUS['DELETED'])
              @endif
              </td>
          </tr>

          <tr class="tabularinfo__subblock collapse a{{ $row->id }}">
                  <td colspan="8">
                    <table class="table-active table table-bordered">
                            <tr>
                                <th width="10%">Member</th>
                                <th width="10%">Location</th>
                                <th width="5%">Action</th>
                                <th width="2%">Add Dokumen</th>
                            </tr>

                            <tbody>
                                @foreach ($other_address as $index)
                                    @if ($row->id == $index->customer_id)
                                        <tr>
                                            <td width="20%"><b>{{ $index->name }}</b> - <br><i>{{ $index->address }}</i></br></td>
                                            <td width="5%">
                                              <iframe  style="height:100px; width: 200px;" src="https://maps.google.com/maps?q={{ $index->gps_latitude }},{{ $index->gps_longitude }}&hl=es;z=14&amp;output=embed" ?? ></iframe>
                                            </td>
                                            @if ($index->status != $index::STATUS['DELETED'] AND $row->status != $row::STATUS['DELETED'])
                                            <td>
                                                <a href="{{ route('superuser.master.customer_other_address.show', $index->id) }}">
                                                    <button type="button" class="btn btn-sm btn-circle btn-alt-secondary" title="View">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                </a>
                                                <a href="javascript:deleteConfirmation('{{ route('superuser.master.customer.other_address.destroy', [$row->id, $index->id]) }}')">
                                                    <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </a>
                                            </td>
                                            <td>
                                              <a class="btn" href="{{ route('superuser.master.dokumen.create', [$index->id]) }}" target="_blank" role="button" title="Add Dokumen"><i class="mdi mdi-file-multiple"></i></a>
                                            </td>
                                            @endif
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                    </table>
                </td>
            </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="d-flex justify-content-center">
    {!! $customers->links() !!}
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@section('modal')

@include('superuser.component.modal-manage', [
  'import_template_url' => route('superuser.master.customer.import_template'),
  'import_url' => route('superuser.master.customer.import'),
  'export_url' => route('superuser.master.customer.export')
])

@endsection

@push('scripts')
<script type="text/javascript">
  $(function () {
	    $('#customer-table').DataTable({
		    "searching": true
      });

      $('.link').click(function() {
        event.preventDefault();
      });
      
      $('.js-tabularinfo').bootstrapTable({
        escape: false,
        showHeader: false
      });
});
</script>
@endpush