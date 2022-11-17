@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.customer.index') }}">Dokumen</a>
  <span class="breadcrumb-item active">Create</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Create </h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.master.dokumen.store') }}" data-type="POST" enctype="multipart/form-data">
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="name_person">Name on Card <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="name_person" name="name_person">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="customer">Store <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="customer" name="customer" data-placeholder="Select Store">
            <option></option>
            @foreach($customers as $customer)
            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row cek_list">
        <label class="col-md-3 col-form-label text-right" for="for_member">Data For Members</label>
        <div class="col-md-7">
          <input type="checkbox" id="for_member" name="for_member" value="1">
        </div>
      </div>
      <div class="form-group row member" style="display:none;">
        <label class="col-md-3 col-form-label text-right" for="other_address">Member </label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="other_address" name="other_address" data-placeholder="Select Member"></select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="customer">Card Type <span class="text-danger">*</span></label>
        <div class="col-md-7">
          <select class="js-select2 form-control" id="document_type" name="document_type">
            <option>Select Card Type</option>
            @foreach($cards as $card)
            <option value="{{ $card }}">{{ $card }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="document_number">Card Number</label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="document_number" name="document_number">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right" for="address">Address <br><span class="text-danger"><i>*Include Area</i></span></label>
        <div class="col-md-7">
          <input type="text" class="form-control" id="address" name="address">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Image NPWP</label>
        <div class="col-md-7">
          <input type="file" id="image_npwp" name="image_npwp" data-max-file-size="4000" accept="image/png, image/jpeg">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-md-3 col-form-label text-right">Image KTP</label>
        <div class="col-md-7">
          <input type="file" id="image_ktp" name="image_ktp" data-max-file-size="4000" accept="image/png, image/jpeg">
        </div>
      </div>
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="javascript:history.back()">
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
@include('superuser.asset.plugin.fileinput')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
  $(document).ready(function () {
    
    $(document).on('click', '.cek_list input:checkbox', function() {
        //Find the next answer element to the question and based on the checked status call either show or hide method
        var answer = $(this).closest('.cek_list').next('.member');

        if(this.checked){
            answer.show(0);
        } else {
            answer.hide(0);
        }
    });

    $('.js-select2').select2()
    
    $('#image_npwp').fileinput({
      theme: 'explorer-fa',
      browseOnZoneClick: true,
      showCancel: false,
      showClose: false,
      showUpload: false,
      browseLabel: '',
      removeLabel: '',
      fileActionSettings: {
        showDrag: false,
        showRemove: false
      },
    });

    $('#image_ktp').fileinput({
      theme: 'explorer-fa',
      browseOnZoneClick: true,
      showCancel: false,
      showClose: false,
      showUpload: false,
      browseLabel: '',
      removeLabel: '',
      fileActionSettings: {
        showDrag: false,
        showRemove: false
      },
    });

    $(function(){

      $('#customer').on('change', function(){
        let customer_id = $('#customer').val();

        

        $.ajax({
          type : 'POST',
          url : '{{route('superuser.master.dokumen.getstore')}}',
          data : {customer_id:customer_id},
          cache : false,

          success: function(msg){
            $('#other_address').html(msg);
          },
          error : function(data){
            console.log('error:',data)
          },
        })
      })
      valueChanged();
    });


  })
</script>
@endpush
