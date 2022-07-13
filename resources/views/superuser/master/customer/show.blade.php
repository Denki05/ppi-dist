@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.customer.index') }}">Store</a>
  <span class="breadcrumb-item active">{{ $customer->name }}</span>
</nav>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Show Store</h3>
    <div class="col-md-6 text-right">
        <a href="javascript:deleteConfirmation('{{ route('superuser.master.customer.destroy', $customer->id) }}', true)">
          <button type="button" class="btn bg-gd-pulse border-0 text-white">
            Delete <i class="fa fa-trash ml-10"></i>
          </button>
        </a>
        <a href="{{ route('superuser.master.customer.edit', $customer->id) }}">
          <button type="button" class="btn bg-gd-leaf border-0 text-white">
            Edit <i class="fa fa-pencil ml-10"></i>
          </button>
        </a>
    </div>
  </div>

  <div class="container">
              <div class="row">
                <div class="col-xs-12 ">
                  <nav>
                    <div class="nav nav-tabs nav-fill" id="nav-tab" role="tablist">
                      <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true">Profile</a>
                      <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="nav-profile" aria-selected="false">Address</a>
                      <a class="nav-item nav-link" id="nav-contact-tab" data-toggle="tab" href="#nav-contact" role="tab" aria-controls="nav-contact" aria-selected="false">Data</a>
                      <a class="nav-item nav-link" id="nav-about-tab" data-toggle="tab" href="#nav-about" role="tab" aria-controls="nav-about" aria-selected="false">Document</a>
                    </div>
                  </nav>
                  <div class="tab-content py-3 px-3 px-sm-0" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
                        <div class="row">
                          <label class="col-md-3 col-form-label text-right">Code</label>
                          <div class="col-md-7">
                            <div class="form-control-plaintext">{{ $customer->code }}</div>
                          </div>
                        </div>
                        <div class="row">
                          <label class="col-md-3 col-form-label text-right">Name</label>
                          <div class="col-md-7">
                            <div class="form-control-plaintext">{{ $customer->name }}</div>
                          </div>
                        </div>
                        <div class="row">
                          <label class="col-md-3 col-form-label text-right">Plafon Piutang</label>
                          <div class="col-md-7">
                            <div class="form-control-plaintext">{{ rupiah($customer->plafon_piutang) }}</div>
                          </div>
                        </div>
                        <div class="row">
                          <label class="col-md-3 col-form-label text-right">Saldo</label>
                          <div class="col-md-7">
                            <div class="form-control-plaintext">@mod</div>
                          </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
                        <div class="row">
                          <label class="col-md-3 col-form-label text-right">Address</label>
                          <div class="col-md-7">
                            <div class="form-control-plaintext">{{ $customer->address }}</div>
                          </div>
                        </div>
                        <div class="row">
                          <label class="col-md-3 col-form-label text-right">Phone</label>
                          <div class="col-md-7">
                            <div class="form-control-plaintext">{{ $customer->phone }}</div>
                          </div>
                        </div>
                        <div class="row">
                          <label class="col-md-3 col-form-label text-right">Email</label>
                          <div class="col-md-7">
                            <div class="form-control-plaintext">{{ $customer->email }}</div>
                          </div>
                        </div>
                        <div class="row">
                          <label class="col-md-3 col-form-label text-right">Provinsi</label>
                          <div class="col-md-7">
                            <div class="form-control-plaintext">{{ $customer->text_provinsi }}</div>
                          </div>
                        </div>
                        <div class="row">
                          <label class="col-md-3 col-form-label text-right">Kota</label>
                          <div class="col-md-7">
                            <div class="form-control-plaintext">{{ $customer->text_kota }}</div>
                          </div>
                        </div>
                        <div class="row">
                          <label class="col-md-3 col-form-label text-right">Kecamatan</label>
                          <div class="col-md-7">
                            <div class="form-control-plaintext">{{ $customer->text_kecamatan }}</div>
                          </div>
                        </div>
                        <div class="row">
                          <label class="col-md-3 col-form-label text-right">Kelurahan</label>
                          <div class="col-md-7">
                            <div class="form-control-plaintext">{{ $customer->text_kelurahan }}</div>
                          </div>
                        </div>
                        <div class="row">
                          <label class="col-md-3 col-form-label text-right">Zipcode</label>
                          <div class="col-md-7">
                            <div class="form-control-plaintext">{{ $customer->zipcode }}</div>
                          </div>
                        </div>
                        <div class="row">
                          <label class="col-md-3 col-form-label text-right">GPS Coordinate</label>
                          <div class="col-md-3">
                            <div class="form-control-plaintext">Latitude: {{ $customer->gps_latitude ?? '-' }}</div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-control-plaintext">Longitude:  {{ $customer->gps_longitude ?? '-' }}</div>
                          </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab">
                      <div class="row">
                        <label class="col-md-3 col-form-label text-right">Category</label>
                        <div class="col-md-7">
                          <div class="form-control-plaintext">
                            <a href="{{ route('superuser.master.customer_category.show', $customer->category->id) }}">
                              {{ $customer->category->name }}
                            </a>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <label class="col-md-3 col-form-label text-right">Type</label>
                        <div class="col-md-7">
                          <div class="form-control-plaintext">
                            @foreach($customer->types as $type)
                            <a href="{{ route('superuser.master.customer_type.show', $type->id) }}" class="badge badge-info">
                              {{ $type->name }}
                            </a>
                            @endforeach
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="tab-pane fade" id="nav-about" role="tabpanel" aria-labelledby="nav-about-tab">
                      <div class="row">
                        <label class="col-md-3 col-form-label text-right">NPWP</label>
                        <div class="col-md-7">
                          <div class="form-control-plaintext">{{ $customer->npwp ?? '-' }}</div>
                        </div>
                      </div>
                      <div class="row">
                        <label class="col-md-3 col-form-label text-right">KTP</label>
                        <div class="col-md-7">
                          <div class="form-control-plaintext">{{ $customer->ktp ?? '-' }}</div>
                        </div>
                      </div>
                      <div class="row">
                        <label class="col-md-3 col-form-label text-right">Image NPWP</label>
                        <div class="col-md-7">
                          <a href="{{ $customer->img_npwp }}" class="img-link img-link-zoom-in img-thumb img-lightbox">
                            <img src="{{ $customer->img_npwp }}" class="img-fluid img-show-small">
                          </a>
                        </div>
                      </div>
                      <div class="row">
                        <label class="col-md-3 col-form-label text-right">Image KTP</label>
                        <div class="col-md-7">
                          <a href="{{ $customer->img_ktp }}" class="img-link img-link-zoom-in img-thumb img-lightbox">
                            <img src="{{ $customer->img_ktp }}" class="img-fluid img-show-small">
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                
                </div>
              </div>
        </div>
      </div>
</div>
  <!-- <div class="block-content">
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Code</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->code }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Name</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->name }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Category</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">
          <a href="{{ route('superuser.master.customer_category.show', $customer->category->id) }}">
            {{ $customer->category->name }}
          </a>
        </div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Type</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">
          @foreach($customer->types as $type)
          <a href="{{ route('superuser.master.customer_type.show', $type->id) }}" class="badge badge-info">
            {{ $type->name }}
          </a>
          @endforeach
        </div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Email</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->email }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Phone</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->phone }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">NPWP</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->npwp }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">KTP</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->ktp }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Address</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->address }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Owner Name</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->owner_name }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Website</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->website ?? '-' }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Plafon Piutang</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ rupiah($customer->plafon_piutang) }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">GPS Coordinate</label>
      <div class="col-md-3">
        <div class="form-control-plaintext">Latitude: {{ $customer->gps_latitude }}</div>
      </div>
      <div class="col-md-3">
        <div class="form-control-plaintext">Longitude:  {{ $customer->gps_longitude }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Provinsi</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->text_provinsi }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Kota</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->text_kota }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Kecamatan</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->text_kecamatan }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Kelurahan</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->text_kelurahan }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Zipcode</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->zipcode }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Image NPWP</label>
      <div class="col-md-7">
        <a href="{{ $customer->img_npwp }}" class="img-link img-link-zoom-in img-thumb img-lightbox">
          <img src="{{ $customer->img_npwp }}" class="img-fluid img-show-small">
        </a>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Image KTP</label>
      <div class="col-md-7">
        <a href="{{ $customer->img_ktp }}" class="img-link img-link-zoom-in img-thumb img-lightbox">
          <img src="{{ $customer->img_ktp }}" class="img-fluid img-show-small">
        </a>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Status</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $customer->status() }}</div>
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
      @if($customer->status != $customer::STATUS['DELETED'])
      <div class="col-md-6 text-right">
        <a href="javascript:deleteConfirmation('{{ route('superuser.master.customer.destroy', $customer->id) }}', true)">
          <button type="button" class="btn bg-gd-pulse border-0 text-white">
            Delete <i class="fa fa-trash ml-10"></i>
          </button>
        </a>
        <a href="{{ route('superuser.master.customer.edit', $customer->id) }}">
          <button type="button" class="btn bg-gd-leaf border-0 text-white">
            Edit <i class="fa fa-pencil ml-10"></i>
          </button>
        </a>
      </div>
      @endif
    </div>
  </div> -->
</div>



@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.magnific-popup')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    $('#datatable-other_address').DataTable({
      columnDefs: [
        { orderable: false, targets: [2, 3] }
      ]
    })

    $('#datatable-contact').DataTable({
      columnDefs: [
        { orderable: false, targets: [3] }
      ]
    })

    $('a.img-lightbox').magnificPopup({
      type: 'image',
      closeOnContentClick: true,
    });

    Codebase.helpers('table-tools')
  })
</script>
@endpush
