@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.customer.index') }}">Member</a>
  <span class="breadcrumb-item active">{{ $other_address->name }}</span>
</nav>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Show Member</h3>
  </div>

  <div class="container">
              <div class="row">
                <div class="col-xs-12 ">
                  <nav>
                    <div class="nav nav-tabs nav-fill" id="nav-tab" role="tablist">
                      <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true">Profile</a>
                      <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false">Address</a>
                      <a class="nav-item nav-link" id="nav-contact-tab" data-toggle="tab" href="#nav-contact" role="tab" aria-controls="nav-contact" aria-selected="false">Location</a>
                      <a class="nav-item nav-link" id="nav-about-tab" data-toggle="tab" href="#nav-about" role="tab" aria-controls="nav-about" aria-selected="false">Document</a>
                    </div>
                  </nav>
                  <div class="tab-content py-3 px-3 px-sm-0" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                      A
                    </div>
                    <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                      B
                    </div>
                    <div class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">
                      C
                    </div>
                    <div class="tab-pane fade" id="nav-about" role="tabpanel" aria-labelledby="nav-about-tab">
                      D
                    </div>
                  </div>
                
                </div>
              </div>
        </div>
      </div>
</div>



  <!-- <div class="block-content">
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Name</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $other_address->name }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Store</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">
            {{ $other_address->customer->name }}
        </div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Contact Person</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $other_address->contact_person }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">NPWP</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $other_address->npwp }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">KTP</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $other_address->ktp }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Phone</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $other_address->phone }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Address</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $other_address->address }}</div>
      </div>
    </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">GPS Coordinate</label>
      <div class="col-md-3">
        <div class="form-control-plaintext">Latitude: {{ $other_address->gps_latitude ?? '-'}}</div>
      </div>
      <div class="col-md-3">
        <div class="form-control-plaintext">Longitude:  {{ $other_address->gps_longitude ?? '-'}}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Provinsi</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $other_address->text_provinsi ?? '-' }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Kota</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $other_address->text_kota ?? '-' }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Kecamatan</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $other_address->text_kecamatan ?? '-' }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Kelurahan</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $other_address->text_kelurahan ?? '-' }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Zipcode</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $other_address->zipcode ?? '-' }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Image NPWP</label>
      <div class="col-md-7">
        <a href="{{ $other_address->img_npwp }}" class="img-link img-link-zoom-in img-thumb img-lightbox">
          <img src="{{ $other_address->img_npwp }}" class="img-fluid img-show-small">
        </a>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Image KTP</label>
      <div class="col-md-7">
        <a href="{{ $other_address->img_ktp }}" class="img-link img-link-zoom-in img-thumb img-lightbox">
          <img src="{{ $other_address->img_ktp }}" class="img-fluid img-show-small">
        </a>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Status</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $other_address->status() }}</div>
      </div>
    </div>
    <div class="row pt-30 mb-15">
      <div class="col-md-6">
        <a href="{{ route('superuser.master.customer.index') }}">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
      </div>
    </div>
  </div> -->
</div>



@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.magnific-popup')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.nav-menu')

@push('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    $('a.img-lightbox').magnificPopup({
      type: 'image',
      closeOnContentClick: true,
    });

    Codebase.helpers('table-tools')
  })

 
</script>
@endpush
