@extends('superuser.app')

@section('content')

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
  <div class="block">
    <div class="block-content block-content-full">
      <div class="block-header block-header-default">
        <h3 class="block-title">Add Payable</h3>
      </div>
    </div>
    <div class="row mb-30">
      <div class="col-12">
        <div class="form-group row">
          <div class="col-md-9">
            <div class="block">
              <div class="block-content">
                <div class="form-group row">
                  <label class="col-md-2 col-form-label text-left" for="member_name">Member</label>
                  <div class="col-md-4">
                    <select class="form-control js-select2" id="member_name" name="member_name" data-placeholder="Cari Member">
                      <option value="">All</option>
                      @foreach($other_address as $row)
                      <option value="{{$row->id}}">{{$row->name}} - {{$row->text_kota}}</option>
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
        <div class="col-12">
          <table class="table table-striped" id="member_list" style="display:none;" width="100%">
            <thead>
              <tr>
                <th>#</th>
                <th>Nama</th>
                <th>Kota</th>
                <th>Kategori</th>
                <th>Action</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>

@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    let datatableUrl = '{{ route('superuser.master.customer_other_address.json') }}';
    let firstDatatableUrl = datatableUrl +
        '?member_name=all';

      var datatable = $('#member_list').DataTable({
        language: {
              processing: "<span class='fa-stack fa-lg'>\n\
                                    <i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\
                              </span>",
        },
        processing: true,
        serverSide: false,
        searching: false,
        paging: false,
        info: false,
        ajax: {
          "url": datatableUrl,
          "dataType": "json",
          "type": "GET",
          "data":{ _token: "{{csrf_token()}}"}
        },
        columns: [
          {data: 'DT_RowIndex', name: 'id'},
          {data: 'member_name', name: 'master_customer_other_addresses.name'},
          {data: 'member_kota', name: 'master_customer_other_addresses.text_kota'},
          {data: 'category_name', name: 'master_customer_categories.name'},
          {data: 'action'}
        ],
        order: [
          [1, 'desc']
        ],
        pageLength: 5,
        lengthMenu: [
          [5, 15, 20],
          [5, 15, 20]
        ],
      });

      $('#filter').on('click', function(e) {
        e.preventDefault();
        var member_name = $('#member_name').val();
        let newDatatableUrl = datatableUrl + '?member_name=' + member_name;
        datatable.ajax.url(newDatatableUrl).load();
      })

      $("#filter").on("click", function(){
        $("#member_list").toggle();
      });

    $('.js-select2').select2();
  })
</script>
@endpush