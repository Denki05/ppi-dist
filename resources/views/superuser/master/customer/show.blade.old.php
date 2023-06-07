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
                        <a href="{{ route('superuser.master.customer_category.show', $customer->category->id) }}">
                            {{ $customer->category->name }}
                        </a>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">PPN</label>
                    <div class="col-md-7">
                        <div class="form-control-plaintext">
                            @if($customer->has_ppn == 0)
                            <span class="badge badge-pill badge-success">NO</span>
                            @elseif($customer->has_ppn ==1)
                            <span class="badge badge-pill badge-success">YES</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">Plafon Piutang</label>
                    <div class="col-md-7">
                        <div class="form-control-plaintext">
                            {{number_format($customer->plafon_piutang,0,",",".")}}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">KTP</label>
                    <div class="col-md-7">
                        <div class="form-control-plaintext">
                            {{ $customer->ktp ?? '-' }}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">Foto KTP</label>
                    <div class="col-md-7">
                        <div class="form-control-plaintext">
                            <a href="{{ $customer->img_npwp }}" class="img-link img-link-zoom-in img-thumb img-lightbox">
                                <img src="{{ $customer->img_npwp }}" class="img-fluid img-show-small">
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">NPWP</label>
                    <div class="col-md-7">
                        <div class="form-control-plaintext">
                            {{ $customer->npwp ?? '-' }}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">Foto NPWP</label>
                    <div class="col-md-7">
                        <div class="form-control-plaintext">
                            <a href="{{ $customer->img_npwp }}" class="img-link img-link-zoom-in img-thumb img-lightbox">
                                <img src="{{ $customer->img_npwp }}" class="img-fluid img-show-small">
                            </a>
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
                    <label class="col-md-3 col-form-label text-right">Pemilik</label>
                    <div class="col-md-7">
                        <div class="form-control-plaintext">{{ $customer->owner_name }}</div>
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
                    <label class="col-md-3 col-form-label text-right">Kode Pos</label>
                    <div class="col-md-7">
                        <div class="form-control-plaintext">{{ $customer->zipcode }}</div>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">Alamat</label>
                    <div class="col-md-7">
                        <div class="form-control-plaintext">{{ $customer->address }}</div>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">Telpon</label>
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
                    <label class="col-md-3 col-form-label text-right">Image Toko</label>
                    <div class="col-md-7">
                        <a href="{{ $customer->img_store }}" class="img-link img-link-zoom-in img-thumb img-lightbox">
                            <img src="{{ $customer->img_store }}" class="img-fluid img-show-small">
                        </a>
                    </div>
                </div>
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
