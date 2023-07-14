@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.contact.index') }}">Contact</a>
  <span class="breadcrumb-item active">Create</span>
</nav>
<div id="alert-block"></div>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Create Contact</h3>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.master.contact.store') }}" data-type="POST" enctype="multipart/form-data">
      <div class="containe">
        <div class="row">
          <div class="col">
            <div class="form-group row">
              <label class="col-sm-2 col-form-label" for="name">Name <span class="text-danger">*</span></label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="name" name="name">
              </div>
            </div>
          </div>
          <div class="col">
            <div class="form-group row">
              <label class="col-sm-2 col-form-label" for="dob">DOB </label>
              <div class="col-sm-10">
                <input type="text" class="js-flatpickr form-control bg-white" placeholder="d-m-Y" data-date-format="d-m-Y" id="dob" name="dob">
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col">
            <div class="form-group row">
              <label for="sync" class="col-sm-2 col-form-label">Sync <span class="text-danger">*</span></label>
              <div class="col-sm-10">
                <div class="form-row">
                  <div class="col">
                    <select class="form-control js-select2 manage_sync" id="manage_sync" name="manage_sync">
                      <option value="">Get Account</option>
                      <option value="member">Member</option>
                      <option value="vendor">Vendor</option>
                    </select>
                  </div>
                  <div class="col">
                    <select class="form-control js-select2" name="manage_id" id="manage_id">
                      <option>Pilih Account</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col">
            <div class="form-group row">
              <label class="col-sm-2 col-form-label" for="position">Position <span class="text-danger">*</span></label>
              <div class="col-sm-10">
                <select class="js-select2 form-control" id="position" name="position" data-placeholder="Select or Add New">
                  <option value="">Pilih Jabatan</option>
                  @foreach($position as $row)
                  <option value="{{$row->name}}">{{$row->name}}</option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col">
            <div class="form-group row">
              <label class="col-sm-2 col-form-label" for="phone">Phone <span class="text-danger">*</span></label>
              <div class="col-sm-10">
                <input type="text" class="form-control" id="phone" name="phone">
              </div>
            </div>
          </div>
          <div class="col">
            <div class="form-group row">
              <label class="col-sm-2 col-form-label" for="email">Email </label>
              <div class="col-sm-10">
                <input type="email" class="form-control" id="email" name="email">
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col">
            <div class="form-group row">
              <label for="ktp" class="col-sm-2 col-form-label">KTP <span class="text-danger">*</span></label>
              <div class="col-sm-10">
                <div class="form-row">
                  <div class="col">
                    <input type="text" class="form-control" id="ktp" name="ktp">
                  </div>
                  <div class="col">
                    <input class="form-control" type="file" id="image_ktp" name="image_ktp">
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col">
            <div class="form-group row">
              <label for="ktp" class="col-sm-2 col-form-label">NPWP <span class="text-danger">*</span></label>
              <div class="col-sm-10">
                <div class="form-row">
                  <div class="col">
                    <input type="text" class="form-control" id="npwp" name="npwp">
                  </div>
                  <div class="col">
                    <input class="form-control" type="file" id="image_npwp" name="image_npwp">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
      </div>
      <div class="form-group row pt-30">
        <div class="col-md-6">
          <a href="{{ route('superuser.master.contact.index') }}">
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

@include('superuser.asset.plugin.flatpickr')
@include('superuser.asset.plugin.select2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
    Codebase.helpers('flatpickr')
    $('.js-select2').select2({
      tags: true
    });

    $(document).on('change', '#manage_sync'  ,function () {
        let sync = $(this).val();
        
        if(sync == "member"){
          $.ajax({
           type:"get",
           url:"{{ route('superuser.master.contact.get_member') }}",
           success:function(res)
           {     
                $("#manage_id").empty();
                $("#manage_id").append('<option>Pilih Member</option>');
                if(res)
                {
                    $.each(res,function(key,value){
                        $('#manage_id').append($("<option/>", {
                           value: key,
                           text: value
                        }));
                    });
                }
           }

          });
        }
    });

    $(document).on('change', '#manage_sync'  ,function () {
        let sync = $(this).val();
        
        if(sync == "vendor"){
          $.ajax({
           type:"get",
           url:"{{ route('superuser.master.contact.get_vendor') }}",
           success:function(res)
           {     
                $("#manage_id").empty();
                $("#manage_id").append('<option>Pilih Vendor</option>');
                if(res)
                {
                    $.each(res,function(key,value){
                        $('#manage_id').append($("<option/>", {
                           value: key,
                           text: value
                        }));
                    });
                }
           }

          });
        }
    });
  })
</script>
@endpush
