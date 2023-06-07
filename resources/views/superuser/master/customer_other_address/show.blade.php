@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.customer.index') }}">Member</a>
  <span class="breadcrumb-item active">Show</span>
</nav>
<div class="row">
    <div class="col">
        <div class="block">
            <div class="block-header block-header-default">
                <h3 class="block-title">#Detail</h3>
            </div>
            <div class="block-content">
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">Category</label>
                    <div class="col-md-7">
                        <div class="form-control-plaintext">
                        <a href="{{ route('superuser.master.customer_category.show', $other_address->customer->category->id) }}">
                            {{ $other_address->customer->category->name }}
                        </a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">Member Default</label>
                    <div class="col-md-7">
                        <div class="form-control-plaintext">
                            @if($other_address->member_default == 0)
                            <span class="badge badge-pill badge-success">NO</span>
                            @elseif($other_address->member_default ==1)
                            <span class="badge badge-pill badge-success">YES</span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">KTP</label>
                    <div class="col-md-7">
                        <div class="form-control-plaintext">
                            {{ $other_address->ktp ?? '-' }}
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">NPWP</label>
                    <div class="col-md-7">
                        <div class="form-control-plaintext">
                            {{ $other_address->npwp ?? '-' }}
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col">
                      <label class="col-md-5 col-form-label text-right">Image Npwp</label>
                      <div class="col-md-7">
                          <div class="form-control-plaintext">
                              <a href="{{ $other_address->img_npwp }}" class="img-link img-link-zoom-in img-thumb img-lightbox">
                                  <img src="{{ $other_address->img_npwp }}" class="img-fluid img-show-small">
                              </a>
                          </div>
                      </div>
                    </div>
                    <div class="col">
                      <label class="col-md-5 col-form-label text-right">Image KTP</label>
                      <div class="col-md-7">
                          <div class="form-control-plaintext">
                              <a href="{{ $other_address->img_npwp }}" class="img-link img-link-zoom-in img-thumb img-lightbox">
                                  <img src="{{ $other_address->img_npwp }}" class="img-fluid img-show-small">
                              </a>
                          </div>
                      </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
      <div class="block">
          <div class="block-header block-header-default">
            <h3 class="block-title">#Profile Customer</h3>
          </div>
              <div class="block-content">
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">Name</label>
                    <div class="col-md-7">
                        <div class="form-control-plaintext">{{ $other_address->name }}</div>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">Pemilik</label>
                    <div class="col-md-7">
                        <div class="form-control-plaintext">{{ $other_address->contact_person }}</div>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">Telpon</label>
                    <div class="col-md-7">
                        <div class="form-control-plaintext">{{ $other_address->phone }}</div>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">Email</label>
                    <div class="col-md-7">
                        <div class="form-control-plaintext">{{ $other_address->email }}</div>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">Provinsi</label>
                    <div class="col-md-7">
                        <div class="form-control-plaintext">{{ $other_address->text_provinsi }}</div>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">Kota</label>
                    <div class="col-md-7">
                        <div class="form-control-plaintext">{{ $other_address->text_kota }}</div>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">Kecamatan</label>
                    <div class="col-md-7">
                        <div class="form-control-plaintext">{{ $other_address->text_kecamatan }}</div>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">Kelurahan</label>
                    <div class="col-md-7">
                        <div class="form-control-plaintext">{{ $other_address->text_kelurahan }}</div>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">Kode Pos</label>
                    <div class="col-md-7">
                        <div class="form-control-plaintext">{{ $other_address->zipcode }}</div>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">Alamat</label>
                    <div class="col-md-7">
                        <div class="form-control-plaintext">{{ $other_address->address }}</div>
                    </div>
                </div>
                <br>
            </div>
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
