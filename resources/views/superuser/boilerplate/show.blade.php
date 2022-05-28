@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <a class="breadcrumb-item" href="{{ route('superuser.boilerplate.index') }}">Boilerplate</a>
  <span class="breadcrumb-item active">{{ $boilerplate->id }}</span>
</nav>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Show Boilerplate</h3>
  </div>
  <div class="block-content">
    <div class="row">
      <div class="col-md-12">
        <div class="form-material">
          <div class="form-control-plaintext">{{ $boilerplate->text }}</div>
          <label>Text</label>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-12">
        <div class="form-material">
          <div class="form-control-plaintext">{!! nl2br($boilerplate->textarea) !!}</div>
          <label>Textarea</label>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6">
        <div class="form-material">
          <div class="form-control-plaintext">
            <span class="badge badge-info">{{ $boilerplate->select }}</span>
          </div>
          <label>Select2</label>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-material">
          <div class="form-control-plaintext">
            @foreach (explode(',', $boilerplate->select_multiple) as $item)
            <span class="badge badge-info">{{ $item }}</span>
            @endforeach
          </div>
          <label>Select2 Multiple</label>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6">
        <div class="form-material">
          <a href="{{ $boilerplate->image_url }}" class="img-link img-link-zoom-in img-thumb img-lightbox">
            <img src="{{ $boilerplate->image_url }}" class="img-fluid img-show-small">
          </a>
          <label>Image</label>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-material">
          @foreach ($boilerplate->images as $img)
          <a href="{{ $img->image_url }}" class="img-link img-link-zoom-in img-thumb img-lightbox">
            <img src="{{ $img->image_url }}" class="img-fluid img-show-small">
          </a>
          @endforeach
          <label>Image Multiple</label>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="form-material">
          <div class="form-control-plaintext">{{ $boilerplate->createdBySuperuser() }}</div>
          <label>Created By</label>
        </div>
      </div>
    </div>
    <div class="row pt-30">
      <div class="col-md-6">
        <a href="{{ route('superuser.boilerplate.index') }}">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
      </div>
      <div class="col-md-6 text-right">
        <a href="javascript:deleteConfirmation('{{ route('superuser.boilerplate.destroy', $boilerplate->id) }}', true)">
          <button type="button" class="btn bg-gd-pulse border-0 text-white">
            Delete <i class="fa fa-trash ml-10"></i>
          </button>
        </a>
        <a href="{{ route('superuser.boilerplate.edit', $boilerplate->id) }}">
          <button type="button" class="btn bg-gd-leaf border-0 text-white">
            Edit <i class="fa fa-pencil ml-10"></i>
          </button>
        </a>
      </div>
    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.magnific-popup')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script type="text/javascript">
$(document).ready(function () {
  $('a.img-lightbox').magnificPopup({
    type: 'image',
    closeOnContentClick: true,
  });
});
</script>
@endpush
