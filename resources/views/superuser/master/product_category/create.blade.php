@extends('superuser.app')

@section('content')
{{--<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.product_category.index') }}">Product Category</a>
  <span class="breadcrumb-item active">Create</span>
</nav>--}}
<div id="alert-block"></div>
<div class="row">
  <div class="col-md-6">
    <div class="block">
      <div class="block-header block-header-default">
        <h3 class="block-title">Create Product Category</h3>
      </div>
      <div class="block-content block-content-full">
        <form class="ajax" data-action="{{ route('superuser.master.product_category.store') }}" data-type="POST" enctype="multipart/form-data">
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="brand_ppi">Brand <span class="text-danger">*</span></label>
            <div class="col-md-7">
              <select class="js-select2 form-control" id="brand_ppi" name="brand_ppi" placeholder="Select Brand">
                <option value="">Select Brand</option>
                @foreach($brand_lokal as $i)
                <option value="{{$i->id}}">{{$i->brand_name}}</option>
                @endforeach
              </select>
              <input type="hidden" name="brand_name">
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="name">Category <span class="text-danger">*</span></label>
            <div class="col-md-7">
              <select class="js-select2 form-control" id="name" name="name" data-placeholder="Select or Add New">
                <option value="">==Select Category==</option>
                @foreach($category_name as $cat_name)
                <option value="{{$cat_name->name}}">{{$cat_name->name}}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="type">Type <span class="text-danger">*</span></label>
            <div class="col-md-7">
              <input id="type" name="type" class="form-control"></input>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="packaging">Packaging <span class="text-danger">*</span></label>
            <div class="col-md-7">
              <select class="js-select2 form-control" id="packaging" name="packaging" data-placeholder="Select Packaging">
                <option value="">Select Packaging</option>
                @foreach($packaging as $pack)
                <option value="{{$pack->id}}">{{$pack->pack_value}}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-group row pt-30">
            <div class="col-md-6">
              <a href="{{ route('superuser.master.product_category.index') }}">
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
  </div>
  <div class="col-md-6">
    <div class="block">
      <div class="block-header block-header-default">
        <h3 class="block-title">List Category </h3>
      </div>
      <div class="block-content block-content-full">
        <table id="category-table-check" class="table table-bordered">
          <thead>
            <tr>
              <th>Category</th>
              <th>Type</th>
              <th>Pack</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {
    $('.js-select2').select2({
      tags: true
    })

    $(function(){

      $('#brand_ppi').on('change', function(){
        let brand_lokal_id = $('#brand_ppi').val();

        $.ajax({
          type : 'POST',
          url : '{{route('superuser.master.product_category.getproductcategory')}}',
          data : {brand_lokal_id:brand_lokal_id},
          cache : false,

          success: function(msg){
            $('#name').html(msg);
          },
          error : function(data){
            console.log('error:',data)
          },
        })
      })
    })
  });

  $(function(){
    $('#brand_ppi').on('change', function(){
        let brand_name = (objHasProp($('#brand_ppi').select2('data')[0], 'text')) ? $('#brand_ppi').select2('data')[0].text : '';
        $('input[name=brand_name]').val(brand_name);
    })
  });

</script>

<script>
  $(document).ready(function () {
    let datatableUrl = '{{ route('superuser.master.product_category.json') }}';
    let firstDatatableUrl = datatableUrl +
          '?brand_ppi=all';

    var datatable = $('#category-table-check').DataTable({
      processing: true,
      serverSide: true,
      "bPaginate": false,
      "bFilter": false,
      "bInfo": false,
      ajax: {
        "url": datatableUrl,
        "dataType": "json",
        "type": "GET",
        "data":{ _token: "{{csrf_token()}}"}
      },
      columns: [
        {data: 'name'},
        {data: 'type'},
        {data: 'packaging'},
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
    $('#brand_ppi').on('change', function(e) {
        e.preventDefault();
        var brand_filter = $('#brand_ppi').val();
        let newDatatableUrl = datatableUrl + '?brand_ppi=' + brand_filter;
        datatable.ajax.url(newDatatableUrl).load();
    })
  })
</script>
@endpush