@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <span class="breadcrumb-item">Product</span>
  <span class="breadcrumb-item active">Cetak</span>
</nav>
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

<form id="form" target="_blank" action="{{ route('superuser.master.product.pdf') }}"
  enctype="multipart/form-data" method="POST">
  @csrf
  <div class="form-group row">
    <div class="col-md-9">
      <div class="block">
        <div class="block-content">
          <div class="form-group row">
            <label class="col-md-2 col-form-label text-left" for="jenisReport">Jenis :</label>
            <div class=col-md-4>
              <select class="js-select2 form-control" id="jenisReport" name="jenisReport" data-placeholder="Select Jenis Report" required>
                  <option value="pl" selected>Price List</option>
                  <option value="pd">Product List</option>
              </select>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-md-2 col-form-label text-left" for="category">Category :</label>
            <div class=col-md-4>
              <select class="js-select2 form-control" id="category" name="category" data-placeholder="Select Category" required>
                  @foreach($categories as $category)
                  <option value="{{ $category->id }}">{{ $category->name }}</option>
                  @endforeach
              </select>
            </div>
            <label class="col-md-2 col-form-label text-left" for="type">Type :</label>
            <div class=col-md-4>
              <select class="js-select2 form-control" id="type" name="type" data-placeholder="Select Type" required>
              </select>
            </div>
          </div>

          <div class="form-group row d-none">
            <label class="col-md-2 col-form-label text-left" for="category">Group By :</label>
            <div class=col-md-4>
              <select class="js-select2 form-control" id="groupBy" name="groupBy" data-placeholder="Select Group By" required>
                  <option value="tidak" selected>Tidak Group</option>
                  <option value="searah">Searah</option>
                  <option value="type">Type</option>
              </select>
            </div>
            <label class="col-md-2 col-form-label text-left" for="type">Sort By :</label>
            <div class=col-md-4>
              <select class="js-select2 form-control" id="sortBy" name="sortBy" data-placeholder="Select Sort By" required>
                  <option value="namaProduk"selected>Nama Produk</option>
                  <option value="brand">Brand</option>
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
              <a href="#" id="btn-print" class="btn bg-gd-corporate border-0 text-white pl-50 pr-50">
                Print<i class="fa fa-print ml-10"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.select2')

@section('modal')

@endsection


@push('scripts')
<script type="text/javascript">
  var categories = [<?php
    $categoryIndex = 0;
    foreach ($categories as $category) {
      if ($categoryIndex > 0) echo ',';
      echo '{';
      echo 'id: ' . $category->id . ',';
      echo 'code: "' . $category->code . '",';
      echo 'name: "' . $category->name . '",';
      $typeString = '[';
      $typeIndex = 0;
      foreach ($category->types as $type) {
        if ($typeIndex > 0) $typeString .= ',';
        $typeString .= '{';
        $typeString .= 'id: "' . $type->id . '",';
        $typeString .= 'code: "' . $type->code . '",';
        $typeString .= 'name: "' . $type->name . '",';
        $typeString .= '}';
        $typeIndex++;
      }
      $typeString .= ']';
      echo 'type: ' . $typeString . '';
      echo '}';
      $categoryIndex++;
    }
  ?>];
  console.log('categories', categories);

  var start_date = {{ \Carbon\Carbon::now()->subDays(30)->format('Y-m-d') }};
  var end_date = {{ \Carbon\Carbon::now()->format('Y-m-d') }};

  var print_date = "HPP-Produksi-Report-{{ \Carbon\Carbon::now()->subDays(30)->format('dmy') }}-{{ \Carbon\Carbon::now()->format('dmy') }}";

  function format(d) {
    return d['detail'];
  }

  $(document).ready(function() {
    $('.js-select2').select2();

    $('#btn-filter').on('click', function(e) {
      e.preventDefault();

      var product = $('#product').val();
    })

    $('#btn-print').on('click', function(e) {
      e.preventDefault();

      $('#form').submit();
    })

    $('#category').on('change', function() {
      const category = categories.find(category => category.id == this.value);
      $('#type').empty();
      if (category === null || category === undefined) {
        let typeOptions = '<option value="all">All</option>';
        $('#type').html(typeOptions);
        return;
      }

      let typeOptions = '';
      //if (category.name !== 'LONGDA') {
        typeOptions += '<option value="all">All</option>';
      //}
      for (let i = 0; i < category.type.length; i++) {
        const type = category.type[i];
        typeOptions += '<option value="' + type.id + '">' + type.name + '</option>';
      }
      if (category.name === 'PPI') {
        typeOptions += '<option value="nonFF-ALL">Non FF - All</option>';
      }
      $('#type').html(typeOptions);
    })
    $('#category').trigger('change');
  });

</script>
@endpush
