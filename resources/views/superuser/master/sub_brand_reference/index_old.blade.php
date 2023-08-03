@extends('superuser.app')

@section('content')
{{--<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <span class="breadcrumb-item active">Searah</span>
</nav>--}}
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
  <a href="{{route('superuser.master.brand_reference.index')}}" class="btn btn-primary btn-lg active" role="button" aria-pressed="true" style="margin-left: 10px !important;">Brand Fragrantica</a>
  <a href="{{route('superuser.master.sub_brand_reference.create')}}" class="btn btn-primary btn-lg active" role="button" aria-pressed="true" style="margin-left: 10px !important;">Add Searah</a>
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
        <div class="col-md-3">
          <div class="block">
            <div class="block-content">
              <div class="form-group row">
                <div class="col-md-12 text-center">
                  <a href="#" id="filter" name="filter" class="btn bg-gd-corporate border-0 text-white pl-50 pr-50">
                    Filter <i class="fa fa-search ml-10"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>
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
          <tbody>
          </tbody>
        </table>
        </div>
        <br>
</div>

<!-- Modal -->
<!-- Modal -->
<div id="updateModal" class="modal fade" role="dialog">
           <div class="modal-dialog">

               <!-- Modal content-->
              <div class="modal-content">
                  <div class="modal-header">
                      <h4 class="modal-title">Update</h4>
                      <button type="button" class="close" data-dismiss="modal">&times;</button> 
                  </div>
                  <div class="modal-body">
                      <div class="form-group">
                          <label for="image_botol">Searah Name</label>
                          <input class="form-control name" id="name" name="name" type="text" readonly>
                      </div>
                      <div class="form-group">
                          <label for="image_botol">Image Upload</label>
                          <textarea class="form-control upload_image" id="summernote" name="upload_image" ></textarea> 
                      </div>

                  </div>
                  <div class="modal-footer">
                      <input type="hidden" id="txt_empid" value="0">
                      <button type="button" class="btn btn-success btn-sm" id="btn_save">Save</button>
                      <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
                  </div>
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
var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content'); 
$(document).ready(function() {
  $('.js-select2').select2()

  let datatableUrl = '{{ route('superuser.master.sub_brand_reference.json') }}';
  let firstDatatableUrl = datatableUrl +
        '?filter_searah=all';

  var datatable = $('#searah_list').DataTable({
    language: {
          processing: "<span class='fa-stack fa-lg'>\n\
                                <i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\
                           </span>",
    },
    processing: true,
    serverSide: false,
    searching: false,
    paging: true,
    bInfo: false,
    scrollX: false,
    scrollY: false,
    ajax: {
      "url": firstDatatableUrl,
      "dataType": "json",
      "type": "GET",
      "data":{ _token: "{{csrf_token()}}"}
    },
    columns: [
      {data: 'DT_RowIndex', name: 'searah_id'},
      {
        data: 'created_date',
        render: {
          _: 'display',
          sort: 'timestamp'
        }, name: 'master_sub_brand_references.created_at'
      },
      {data: 'brand_name', name: 'master_brand_references.name'},
      {data: 'searah_name', name: 'master_sub_brand_references.name'},
      {
        data: 'searah_link',
        name: 'master_sub_brand_references.link',
        render : function(data, type, row, meta) {
          // return'<a class="d-inline-block fw-normal w-100 h-100 pe-auto" href="' + row.searah_link + '">' + "Get" + '</a>';
          return '<a class="btn btn-primary" href="' + row.searah_link + '" role="button">' + "Get" + '</a>'
        },
      },
      {data: 'action', orderable: false, searcable: false}
    ],
    order: [
      [1, 'desc']
    ],
    pageLength: 25,
    lengthMenu: [
      [25, 50, 75],
      [25, 50, 75]
    ],
  });

  $('#filter').on('click', function(e) {
        e.preventDefault();
        var filter_searah = $('#filter_searah').val();
        let newDatatableUrl = datatableUrl + '?filter_searah=' + filter_searah;
        datatable.ajax.url(newDatatableUrl).load();
  });

  // Update record
  $('#searah_list').on('click','.upload_button',function(){
            var id = $(this).data('id');

            $('#txt_empid').val(id);

            // AJAX request
            $.ajax({
                url: '{{route('superuser.master.sub_brand_reference.getSearahData')}}',
                type: 'POST',
                data: {_token: CSRF_TOKEN,id: id},
                dataType: 'JSON',
                success: function(response){

                    if(response.success == 1){

                        $('#name').val(response.name);

                         datatable.ajax.reload();
                    }else{
                         alert("Invalid ID.");
                    }
                }
            });

       });

       // Btn Update 
       $('#btn_save').click(function(){
            var id = $('#txt_empid').val();
            var upload_image = $('#upload_image').val().trim();
            var name = $('#name').val().trim();
            var url = '{{ route('superuser.master.sub_brand_reference.update_image', ':id') }}';
            url = url.replace(':id', id);

            if(name !=''){

                 // AJAX request
                 $.ajax({
                     url: url,
                     type: 'POST',
                     enctype: 'multipart/form-data',
                     data: {_token: CSRF_TOKEN,id: id, name: name},
                     dataType: 'json',
                     success: function(response){
                         if(response.success == 1){
                              alert(response.msg);

                              // Empty and reset the values
                              $('#upload_image').val('');
                              $('#name').val('');
                              $('#txt_empid').val(0);

                              // Reload DataTable
                              datatable.ajax.reload();

                              // Close modal
                              $('#updateModal').modal('toggle');
                         }else{
                              alert(response.msg);
                         }
                     }
                 });

            }else{
                 alert('Please fill all fields.');
            }
       });

  $('#summernote').summernote({
        toolbar: [
            ['image', ['resizeFull', 'resizeHalf', 'resizeQuarter', 'resizeNone']],
            ['float', ['floatLeft', 'floatRight', 'floatNone']],
            ['remove', ['removeMedia']],
        ]
    });
});
</script>
@endpush
