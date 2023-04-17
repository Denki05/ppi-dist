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

<nav class="breadcrumb bg-white push">
  <a href="{{route('superuser.master.customer.create')}}" class="btn btn-primary btn-lg active" role="button" target="_blank" aria-pressed="true" style="margin-left: 10px !important;">Create</a>
  <a href="{{route('superuser.master.customer_contact.create')}}" class="btn btn-primary btn-lg active" role="button" target="_blank" aria-pressed="true" style="margin-left: 10px !important;">Add Contact</a>
  <button type="button" class="btn btn-outline-info ml-10" data-toggle="modal" data-target="#modal-manage">Manage</button>
</nav>

<div class="block">
  <div class="block-content block-content-full">
  <div class="form-group row">
  <form>
            <div class="row">
              <div class="col-lg-3">
                <div class="form-group row">
                  {{--<label class="col-md-3 col-form-label text-right">Customer</label>
                  <div class="col-md-9">
                    <select class="form-control js-select2" name="customer_name">
                      <option value="">==All Customer==</option>
                      @foreach($customers as $index => $row)
                      <option value="{{$row->name}}">{{$row->name}}</option>
                      @endforeach
                    </select>
                  </div>--}}
                </div>   
              </div>
              <div class="col-lg-3">
                  <div class="form-group row">
                    {{--<label class="col-md-3 col-form-label text-right">Area</label>
                    <div class="col-md-9">
                      <select class="form-control js-select2" name="province">
                        <option value="">==All Provinsi==</option>
                        @foreach($provinsi as $index => $row)
                        <option value="{{$row->prov_id}}">{{$row->prov_name}}</option>
                        @endforeach
                      </select>
                    </div>--}}
                  </div>
              </div>
              <div class="col-lg-6">
                <div class="form-group row">
                  <div class="col-md-3">
                    <!-- <label class="col-md-3 col-form-label text-right">Search</label> -->
                  </div>
                  <div class="col-md-9">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Search" name="search">
                        <div class="input-group-append">
                          <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                        </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>
      </div>
    <table id="customer-table" class="table ">
      <thead class="thead-dark">
        <tr>
          <th></th>
          <th>Code</th>
          <th>Store</th>
          <th>Address</th>
          <th>Region</th>
          <th>Category</th>
          <th>Status</th>
          <th scope="col">Action</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($customers as $row)
          <tr class="clickable js-tabularinfo-toggle" data-toggle="collapse" id="row2" data-target=".a{{ $row->id }}">
              <td>
                <div class="col-sm-6">
                  <div class="row mb-2">
                    <a href="#" class="link">
                      <button type="button" name='edit' id='{{ $row->id }}'>#</button>
                    </a>
                  </div>
                </div>
              </td>
              <td>{{ $row->code }}</td>
              <td>{{ $row->name }}</td>
              <td>{{ $row->address }} - <br><b>{{ $row->text_kota }}</b></br></td>
              <td><b>{{ $row->text_provinsi }}</b></td>
              <td>{{ $row->category->name ?? '-' }}</td>
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
                <a href="javascript:deleteConfirmation('{{ route('superuser.master.customer.destroy', $row->id) }}', true)">
                    <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete">
                        <i class="mdi mdi-delete"></i>
                    </button>
                </a>
                <a href="{{ route('superuser.master.customer.other_address.create', [$row->id]) }}">
                    <button type="button" class="btn btn-sm btn-circle btn-alt-secondary" title="Add Member">
                        <i class="mdi mdi-account-multiple-plus"></i>
                    </button>
                </a>

                <a href="{{ route('superuser.master.dokumen.create') }}">
                    <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Add Document">
                        <i class="mdi mdi-file-document"></i>
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
              
          </tr>

          <tr class="tabularinfo__subblock collapse a{{ $row->id }}">
                  <td colspan="8">
                    <table class="table-active table table-bordered">
                            <tr>
                                <th width="10%">Member</th>
                                <th width="10%">Location</th>
                                <th width="5%">Action</th>
                                <!-- <th width="5%">Other Action</tg> -->
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
                                                <a href="{{ route('superuser.master.customer_other_address.edit', $index->id) }}">
                                                    <button type="button" class="btn btn-sm btn-circle btn-alt-secondary" title="Edit">
                                                        <i class="mdi mdi-lead-pencil"></i>
                                                    </button>
                                                </a>
                                                <a href="javascript:deleteConfirmation('{{ route('superuser.master.customer.other_address.destroy', [$row->id, $index->id]) }}')">
                                                    <button type="button" class="btn btn-sm btn-circle btn-alt-danger" title="Delete">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </a>
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
@include('superuser.asset.plugin.select2')

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
      $('.link').click(function() {
        event.preventDefault();
      });
      
      $('.js-tabularinfo').bootstrapTable({
        escape: false,
        showHeader: false
      });
      
});
</script>

<script>
  $(document).ready(function () {
    $('.js-select2').select2({
    })

    $('#customer-table').DataTable();
  });
</script>
@endpush