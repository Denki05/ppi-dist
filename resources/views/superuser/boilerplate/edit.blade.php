@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <a class="breadcrumb-item" href="{{ route('superuser.boilerplate.index') }}">Boilerplate</a>
  <a class="breadcrumb-item" href="{{ route('superuser.boilerplate.show', $boilerplate->id) }}">{{ $boilerplate->id }}</a>
  <span class="breadcrumb-item active">Edit</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Edit Boilerplate</h3>
    @if($boilerplate_image_trash->isNotEmpty())
    <div class="block-options">
      <button type="button" class="btn btn-warning border-0" data-toggle="modal" data-target="#modal-trash"><i class="si si-trash mr-10"></i> Trash</button>
    </div>
    @endif
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.boilerplate.update', $boilerplate) }}" data-type="POST" enctype="multipart/form-data">
      <input type="hidden" name="_method" value="PUT">
      <div class="form-group row">
        <div class="col-md-12">
          <div class="form-material">
            <input type="text" class="form-control" id="text" name="text" placeholder="Lorem ipsum dolor sit amet." value="{{ $boilerplate->text }}">
            <label for="text">Text</label>
          </div>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-12">
          <div class="form-material">
            <textarea class="form-control" id="textarea" name="textarea" rows="3" placeholder="Lorem ipsum dolor sit amet.">{{ $boilerplate->textarea }}</textarea>
            <label for="textarea">Textarea</label>
          </div>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-md-6">
          <div class="form-material">
            <select class="js-select2 form-control" id="select2" name="select2" data-placeholder="Select">
              <option></option>
              <option {{ ($boilerplate->select == 'html') ? 'selected' : '' }} value="html">HTML</option>
              <option {{ ($boilerplate->select == 'css') ? 'selected' : '' }} value="css">CSS</option>
              <option {{ ($boilerplate->select == 'javascript') ? 'selected' : '' }} value="javascript">JavaScript</option>
              <option {{ ($boilerplate->select == 'php') ? 'selected' : '' }} value="php">PHP</option>
            </select>
            <label for="select2">Select2</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-material">
            <select class="js-select2 form-control" id="select2-multiple" name="select2-multiple[]" data-placeholder="Select multiple" multiple data-value="{{ $boilerplate->select_multiple }}">
              <option></option>
              <option value="html">HTML</option>
              <option value="css">CSS</option>
              <option value="javascript">JavaScript</option>
              <option value="php">PHP</option>
            </select>
            <label for="select2-multiple">Select2 Multiple</label>
          </div>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-md-6">
          <div class="form-material">
            <input type="file" id="image" name="image" data-max-file-size="2000" accept="image/png, image/jpeg" data-src="{{ $boilerplate->image_url }}">
            <label for="image">Image</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-material">
            <input type="file" id="image-multiple" name="image-multiple[]" data-max-file-size="2000" accept="image/png, image/jpeg" multiple data-src="{{ $boilerplate->images_url }}">
            <label for="image-multiple">Image Multiple</label>
          </div>
        </div>
      </div>
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.boilerplate.show', $boilerplate->id) }}">
            <button type="button" class="btn bg-gd-cherry border-0 text-white">
              <i class="fa fa-arrow-left mr-10"></i> Back
            </button>
          </a>
          <button type="reset" class="btn bg-gd-sun border-0 text-white">
            <i class="fa fa-repeat mr-10"></i> Reset
          </button>
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

@section('modal')
@if($boilerplate_image_trash->isNotEmpty())
<div class="modal fade" id="modal-trash" tabindex="-1" role="dialog" aria-labelledby="modal-trash" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-slideup" role="document">
    <div class="modal-content">
      <div class="block block-themed block-transparent mb-0">
        <div class="block-header bg-primary-dark">
          <h3 class="block-title">Trash</h3>
          <div class="block-options">
            <button type="button" class="btn-block-option" data-dismiss="modal" aria-label="Close">
              <i class="si si-close"></i>
            </button>
          </div>
        </div>
        <div class="block-content">
          <div class="row">
            @foreach($boilerplate_image_trash as $image_trash)
            <div class="col-md-3">
              <img class="img-fluid img-trash" src="{{ $image_trash->image_url }}">
              <div class="mt-10 mb-10 text-center">
                <a href="{{ route('superuser.boilerplate_img.restore', [$boilerplate->id, $image_trash->id]) }}">
                  <button type="button" class="btn btn-lg btn-circle btn-alt-warning" >
                    <i class="fa fa-undo"></i>
                  </button>
                </a>
                <a href="{{ route('superuser.boilerplate_img.destroy', [$boilerplate->id, $image_trash->id]) }}">
                  <button type="button" class="btn btn-lg btn-circle btn-alt-danger">
                    <i class="fa fa-trash"></i>
                  </button>
                </a>
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <a href="{{ route('superuser.boilerplate_img.restore_all', $boilerplate->id) }}">
          <button type="button" class="btn btn-alt-warning">Restore All</button>
        </a>
        <a href="{{ route('superuser.boilerplate_img.destroy_all', $boilerplate->id) }}">
          <button type="button" class="btn btn-alt-danger">Remove All</button>
        </a>
      </div>
    </div>
  </div>
</div>
@endif
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.fileinput')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
$(document).ready(function () {
  $('.js-select2').select2();

  let sm = $('#select2-multiple');
  let sm_val = sm.data('value').split(",");
  sm.select2().val(sm_val).trigger('change');

  $('#image').fileinput({
    theme: 'explorer-fa',
    browseOnZoneClick: true,
    showCancel: false,
    showClose: false,
    showUpload: false,
    browseLabel: '',
    removeLabel: '',
    initialPreview: $('#image').data('src'),
    initialPreviewAsData: true,
    fileActionSettings: {
      showDrag: false,
      showRemove: false
    },
    initialPreviewConfig: [
      {
          caption: '{{ $boilerplate->image }}'
      }
    ]
  });

  let ipc = [];
  let images = JSON.parse('{{ $boilerplate->images }}'.replace(/&quot;/g,'"'));

  images.forEach(img => {
    ipc.push({
      caption: img.image,
      key: img.id,
      url: base_url+ '/superuser/boilerplate_img/delete/' +img.id
    })
  });

  $('#image-multiple').fileinput({
    theme: 'explorer-fa',
    browseOnZoneClick: true,
    showCancel: false,
    showClose: false,
    showUpload: false,
    overwriteInitial: false,
    browseLabel: '',
    removeLabel: '',
    initialPreview: $('#image-multiple').data('src'),
    initialPreviewAsData: true,
    fileActionSettings: {
      showDrag: false,
      showRemove: (ipc.length > 1) || false
    },
    initialPreviewConfig: ipc
  });

  // TODO: change style to swal / other
  // $("#image-multiple").on("filebeforedelete", function(e) {
  //   e.preventDefault();
  //   e.stopImmediatePropagation();
  
  //   var abort = true;
  
  //   Swal.fire({
  //     title: 'Are you sure you want to delete this image?',
  //     type: 'warning',
  //     showCancelButton: true,
  //     allowOutsideClick: false,
  //     allowEscapeKey: false,
  //     allowEnterKey: false,
  //     backdrop: false,
  //   }).then(result => {
  //     if (result.value) {
  //       abort = false;
  //     }
  //   }).then(() => { return abort });
  // });

  // $("#image-multiple").on("filepredelete", function(event) {
  //   var abort = true;
  //   if (confirm("Are you sure you want to delete this image?")) {
  //     abort = false;
  //   }
  //   return abort;
  // });


  $('#image-multiple').on('filedeleted', function(event, key, jqXHR, data) {
    redirect('reload()');
  });


  $('button[type="reset"]').click(function () {
    $('.js-select2').val('').trigger('change');
  });
})
</script>
@endpush
