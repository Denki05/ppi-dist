@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <span class="breadcrumb-item">Produk</span>
  <span class="breadcrumb-item active">Searah</span>
</nav

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
  <a href="{{route('superuser.master.brand_reference.index')}}" class="btn btn-primary btn-lg active" role="button" style="margin-left: 10px;">Brand Fragrantica</a>
  <a href="{{route('superuser.master.sub_brand_reference.create')}}" class="btn btn-primary btn-lg active" role="button" style="margin-left: 10px;">Add Searah</a>
  <button type="button" class="btn btn-outline-info ml-10" data-toggle="modal" data-target="#modal-manage">Manage</button>
</nav>

<div class="block">
  <div class="block-content">
    <div class="form-group row">
      <div class="col-md-9">
        <div class="block">
          <div class="block-content">
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-left" for="filter_brand">Searah :</label>
              <div class="col-md-4">
                <select class="form-control js-select2" id="filter_searah" name="filter_searah" data-placeholder="Find Searah Name">
                  <option value="all">All</option>
                  @foreach($parfume_searah as $searah)
                  <option value="{{$searah->name}}">{{$searah->name}}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3 text-center">
        <button id="filter" class="btn bg-gd-corporate border-0 text-white">Filter <i class="fa fa-search ml-10"></i></button>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-striped" id="searah_list">
        <thead>
          <tr>
            <th>#</th>
            <th>Created</th>
            <th>Brand Fragrantica</th>
            <th>Searah</th>
            <th>URL</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Upload Image</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <form id="myForm" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="form-group">
            <label for="image_botol">Image Upload</label>
            <textarea class="form-control upload_image" id="summernote" name="upload_image"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-info">Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.select2')

@section('modal')

@include('superuser.component.modal-manage', [
  'import_template_url' => route('superuser.master.sub_brand_reference.import_template'),
  'import_url' => route('superuser.master.sub_brand_reference.import', $brand->id),
  'export_url' => route('superuser.master.sub_brand_reference.export')
])

@endsection

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $('.js-select2').select2();

    // Initialize DataTable
    const datatable = $('#searah_list').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: '{{ route('superuser.master.sub_brand_reference.json') }}',
        type: 'GET',
        data: function(d) {
          d.filter_searah = $('#filter_searah').val();
        }
      },
      columns: [
        { data: 'DT_RowIndex', name: 'id' },
        { data: 'created_date', name: 'created_at' },
        { data: 'brand_name', name: 'brand' },
        { data: 'searah_name', name: 'searah' },
        { data: 'searah_link', name: 'link', render: data => `<a href="${data}" class="btn btn-primary"><i class="fa fa-link"></i></a>` },
        { data: 'action', orderable: false, searchable: false }
      ]
    });

    $('#filter').on('click', () => datatable.ajax.reload());

    $('#summernote').summernote({
      height: 200
    });

    $('#myForm').on('submit', function(e) {
      e.preventDefault();
      const url = '{{ route("superuser.master.sub_brand_reference.update_image", ":id") }}'.replace(':id', 1); // Update ID dynamically
      const formData = new FormData(this);

      $.ajax({
        url,
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: formData,
        contentType: false,
        processData: false,
        success: function() {
          $('#myModal').modal('hide');
          datatable.ajax.reload();
        },
        error: function(xhr) {
          alert('Error: ' + xhr.responseText);
        }
      });
    });
  });
</script>
@endpush