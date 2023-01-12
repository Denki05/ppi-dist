@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.product_category.index') }}">Product Category</a>
  <span class="breadcrumb-item active">Create</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Create Product Category</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.master.product_category.store') }}" data-type="POST" enctype="multipart/form-data">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="brand_ppi">Brand <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="brand_ppi" name="brand_ppi" data-placeholder="Select Brand">
            <option value="">==Select Brand==</option>
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
          <input type="text" class="form-control" id="name" name="name">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="type">Type</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="type" name="type">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="packaging">Packaging <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="packaging" name="packaging" data-placeholder="Select Packaging">
            <option value="">==Select Packaging==</option>
            <option value="100gr">100 gr</option>
            <option value="500gr">500 gr</option>
            <option value="5000gr">5000 gr / 5 kg</option>
            <option value="2500gr">2.5 kg</option>
            <option value="5000gr">5000 gr / 5 kg</option>
            <option value="25kg">25 kg</option>
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
@endsection

@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {
    $('.js-select2').select2()
  });

  $(function(){
    $('#brand_ppi').on('change', function(){
        let brand_name = (objHasProp($('#brand_ppi').select2('data')[0], 'text')) ? $('#brand_ppi').select2('data')[0].text : '';
        $('input[name=brand_name]').val(brand_name);
    })
  });
  
</script>
@endpush