@extends('superuser.app')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.sub_brand_reference.index') }}">Searah</a>
  <a class="breadcrumb-item" href="{{ route('superuser.master.sub_brand_reference.show', $searah->id) }}">{{ $searah->id }}</a>
  <span class="breadcrumb-item active">Edit</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Upload Image</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.master.sub_brand_reference.update_image', $searah->id) }}" data-type="POST" enctype="multipart/form-data">
        {{csrf_field()}}
        <input type="hidden" name="_method" value="PUT">
        <div class="form-group row">
            <label class="col-md-3 col-form-label text-right" for="upload_image">Upload Image</label>
            <div class="col-md-7">
                <textarea class="form-control summernote" id="summernote" name="upload_image" ></textarea>
            </div>
        </div>

        <div class="form-group row pt-30">
            <div class="col-md-6">
                <a href="{{ route('superuser.master.sub_brand_reference.index') }}">
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

@include('superuser.asset.plugin.fileinput')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
$(document).ready(function () {
    $('#summernote').summernote({
        height: 200,
    });
});
</script>
@endpush