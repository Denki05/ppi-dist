@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.customer.index') }}">Store</a>
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
                          {{ $other_address->store->category->name }}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">Member Default</label>
                    <div class="col-md-7">
                        <div class="form-control-plaintext">
                          @if($other_address->member_default == 0)
                            <span class="badge badge-pill badge-info">NO</span>
                          @else
                            <span class="badge badge-pill badge-info">YES</span>
                          @endif
                        </div>
                    </div>
                </div>
                {{--<div class="row">
                    <label class="col-md-3 col-form-label text-right">Plafon Piutang</label>
                    <div class="col-md-7">
                        <div class="form-control-plaintext">
                           
                        </div>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">Tempo Limit</label>
                    <div class="col-md-7">
                        <div class="form-control-plaintext">
                          
                        </div>
                    </div>
                </div>--}}
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">KTP</label>
                    <div class="col-md-7">
                        <div class="form-control-plaintext">
                           {{ $other_address->ktp ?? '-' }}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">Foto KTP</label>
                    <div class="col-md-7">
                        <div class="form-control-plaintext">
                          <a href="{{ $other_address->img_ktp }}" class="img-link img-link-zoom-in img-thumb img-lightbox">
                            <img src="{{ $other_address->img_ktp }}" class="img-fluid img-show-small">
                          </a>
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
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">Foto NPWP</label>
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
    <div class="col">
        <div class="block">
            <div class="block-header block-header-default">
                <h3 class="block-title">#Profile Member</h3>
            </div>
            <div class="block-content">
                
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">Name</label>
                    <div class="col-md-7">
                      <div class="form-control-plaintext">{{ $other_address->name }}</div>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">Contact Person</label>
                    <div class="col-md-7">
                      <div class="form-control-plaintext">{{ $other_address->contact_person }}</div>
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
                <?php
                  $pecah_telp = explode(",", $other_address->phone)
                ?>
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">Telpon 1</label>
                    <div class="col-md-7">
                      <div class="form-control-plaintext">{{ $pecah_telp[0] ?? '-' }}</div>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">Telpon 2</label>
                    <div class="col-md-7">
                      <div class="form-control-plaintext">{{ $pecah_telp[1] ?? '-' }}</div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">Officer</label>
                    <div class="col-md-7">
                      <div class="form-control-plaintext">{{ $other_address->officer ?? '-' }}</div>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3 col-form-label text-right">AR</label>
                    <div class="col-md-7">
                      <div class="form-control-plaintext">{{ $other_address->account_representative ?? '-' }}</div>
                    </div>
                </div>
                <div class="row">
                    @php
                      $hasil_rupiah = "Rp " . number_format($other_address->setting_income_target, 2,',','.');
                    @endphp
                    <label class="col-md-3 col-form-label text-right">Target</label>
                    <div class="col-md-7">
                      <div class="form-control-plaintext">{{ $hasil_rupiah }}</div>
                    </div>
                </div>
                {{--<div class="row">
                    <label class="col-md-3 col-form-label text-right">Email</label>
                    <div class="col-md-7">
                       
                    </div>
                </div>--}}
                {{--<div class="row">
                    <label class="col-md-3 col-form-label text-right">Image Toko</label>
                    <div class="col-md-7">
                        
                    </div>
                </div>--}}
                <br>
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
