@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.customer.index') }}">Member</a>
  <span class="breadcrumb-item active">Show</span>
</nav>
<div class="card">
  <h5 class="card-header">#Member Show</h5>
  <div class="card-body">
    <div class="container">
      <div class="row">
        <div class="col-6">
          <div class="form-group">
            <label>Name Member</label>
            <input class="form-control" type="text" value="{{ $other_address->name }}" readonly>                          
          </div>
        </div>
        <div class="col-6">
          <div class="form-group">
            <label>Owner</label>
            <input class="form-control" type="text" value="{{ $other_address->contact_person }}" readonly>                          
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-6">
          <div class="form-group">
            <label>Category</label>
            <input class="form-control" type="text" value="{{ $other_address->customer->category->name }}" readonly>     
          </div>
        </div>
        <div class="col-6">
          <div class="form-group">
            <label>Telepon</label>
            <input class="form-control" type="text" value="{{ $other_address->phone }}" readonly>                          
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-12">
          <div class="form-group">
            <label>Alamat</label>
            <input class="form-control" type="text" value="{{ $other_address->address }}" readonly>     
          </div>
        </div>
      </div>
      
      <hr>

      <div class="row">
        <div class="col-6">
          <div class="form-group">
            <label>KTP</label>
            <input class="form-control" type="text" value="{{ $other_address->ktp  }}" readonly>     
          </div>
        </div>
        <div class="col-6">
          <div class="form-group">
            <label>NPWP</label>
            <input class="form-control" type="text" value="{{ $other_address->npwp }}" readonly>                          
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-6">
          <div class="form-group">
            <label>Foto KTP</label>
            <a href="{{ $other_address->img_ktp }}" class="img-link img-link-zoom-in img-thumb img-lightbox">
              <img src="{{ $other_address->img_ktp }}" class="img-fluid img-show-small">
            </a>
          </div>
        </div>
        <div class="col-6">
          <div class="form-group">
            <label>Foto NPWP</label>
            <a href="{{ $other_address->img_npwp }}" class="img-link img-link-zoom-in img-thumb img-lightbox">
              <img src="{{ $other_address->img_npwp }}" class="img-fluid img-show-small">
            </a>                      
          </div>
        </div>
      </div>

      <hr>

      <div class="row">
        <div class="col">
          <div class="form-group">
            <label>Provinsi</label>
            <input class="form-control" type="text" value="{{ $other_address->text_provinsi  }}" readonly>     
          </div>
        </div>
        <div class="col">
          <div class="form-group">
            <label>Kota</label>
            <input class="form-control" type="text" value="{{ $other_address->text_kota }}" readonly>                          
          </div>
        </div>
        <div class="col">
          <div class="form-group">
            <label>Kecamatan</label>
            <input class="form-control" type="text" value="{{ $other_address->text_kecamatan }}" readonly>                          
          </div>
        </div>
        <div class="col">
          <div class="form-group">
            <label>Kelurahan</label>
            <input class="form-control" type="text" value="{{ $other_address->text_kelurahan }}" readonly>                          
          </div>
        </div>
      </div>

      <hr>

      <div class="row">
        <div class="col-6">
          <div class="form-group">
            <label>Member Default</label>
            @if($other_address->member_default == 0)
            <span class="badge badge-warning">NO</span>
            @elseif($other_address->member_default == 1)
            <span class="badge badge-success">YES</span>
            @endif
          </div>
        </div>
        <div class="col-6">
          <div class="form-group">
            <label>Status</label>
            @if($other_address->status == 0)
            <span class="badge badge-danger">DELETED</span>
            @elseif($other_address->status == 1)
            <span class="badge badge-success">ACTIVE</span>
            @endif
          </div>
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
      @if($other_address->status != $other_address::STATUS['DELETED'])
      <div class="col-md-6 text-right">
        <a href="javascript:deleteConfirmation('{{ route('superuser.master.customer_other_address.destroy', $other_address->id) }}', true)">
          <button type="button" class="btn bg-gd-pulse border-0 text-white">
            Delete <i class="fa fa-trash ml-10"></i>
          </button>
        </a>
        <a href="{{ route('superuser.master.customer_other_address.edit', $other_address->id) }}">
          <button type="button" class="btn bg-gd-leaf border-0 text-white">
            Edit <i class="fa fa-pencil ml-10"></i>
          </button>
        </a>
      </div>
      @endif
    </div>
    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.magnific-popup')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $('#datatable').DataTable({
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
