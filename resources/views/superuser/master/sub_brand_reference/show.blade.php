@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.sub_brand_reference.index') }}">Searah</a>
  <span class="breadcrumb-item active">{{ $sub_brand_reference->id }}</span>
</nav>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Show Searah</h3>
  </div>
  <div class="block-content">
    <div class="container">
      <div class="row">
          <div class="col-6">
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right">Code: </label>
              <div class="col-md-8">
                <div class="form-control-plaintext">{{ $sub_brand_reference->code }}</div>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right">Name: </label>
              <div class="col-md-8">
                <a href="{{ route('superuser.master.brand_reference.show', $sub_brand_reference->brand_reference->id) }}">
                  <div class="form-control-plaintext">{{ $sub_brand_reference->name }}</div>
                </a>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right">URL Fragrantica: </label>
              <div class="col-md-8">
                <div class="form-control-plaintext">{{ $sub_brand_reference->link }}</div>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right">Description: </label>
              <div class="col-md-8">
                <div class="form-control-plaintext">{{ $sub_brand_reference->description }}</div>
              </div>
            </div>
          </div>
          <div class="col-6">
            <div class="form-group row">
              <label class="col-md-2 col-form-label text-right">Status: </label>
              <div class="col-md-7">
                <div class="form-control-plaintext">{{ $sub_brand_reference->status() }}</div>
              </div>
            </div>
          </div>
      </div>
    </div>
    <div class="row pt-30 mb-15">
      <div class="col-md-6">
        <a href="{{ route('superuser.master.sub_brand_reference.index') }}">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
      </div>
      @if($sub_brand_reference->status != $sub_brand_reference::STATUS['DELETED'])
      <div class="col-md-6 text-right">
        <a href="javascript:deleteConfirmation('{{ route('superuser.master.sub_brand_reference.destroy', $sub_brand_reference->id) }}', true)">
          <button type="button" class="btn bg-gd-pulse border-0 text-white">
            Delete <i class="fa fa-trash ml-10"></i>
          </button>
        </a>
        <a href="{{ route('superuser.master.sub_brand_reference.edit', $sub_brand_reference->id) }}">
          <button type="button" class="btn bg-gd-leaf border-0 text-white">
            Edit <i class="fa fa-pencil ml-10"></i>
          </button>
        </a>
      </div>
      @endif
    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
