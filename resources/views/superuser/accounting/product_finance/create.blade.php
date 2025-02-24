@extends('superuser.app')

@section('content')

@if($errors->any())
<div class="alert alert-danger alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Error</h3>
  @foreach ($errors->all() as $error)
  <p class="mb-0">{{ $error }}</p>
  @endforeach
</div>
@endif

<div id="alert-block"></div>

@if(session()->has('message'))
<div class="alert alert-success alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Success</h3>
  <p class="mb-0">{{ session()->get('message') }}</p>
</div>
@endif

<div class="block">  
  <div class="block-header block-header-default">
    <h2 class="block-title">#Create Product TAX</h2>
  </div>
  <div class="block-content">
    <form class="ajax" data-action="{{ route('superuser.accounting.product_finance.store') }}" data-type="POST" enctype="multipart/form-data">
    @csrf

        <div class="row mb-30">
          <div class="col-8">
            <div class="form-group row">
              <label for="example-text-input" class="col-2 col-form-label">Brand</label>
              <div class="col-8">
                <select class="form-control js-select2" id="brand" name="brand">
                    <option value="">Pilih Brand</option>
                    @foreach($brand AS $item)
                    <option value="{{ $item->brand_name }}">{{ $item->brand_name }}</option>
                    @endforeach
                </select>
              </div>
            </div>

            <div class="form-group row">
              <label class="col-2 col-form-label">Nama Produk</label>
              <div class="col-8">
                <select class="form-control js-select2" id="product" name="product">
                  <option value="">Pilih Produk</option>
                </select>

                <input type="hidden" id="packaging_code" name="packaging_code" readonly>
              </div>
            </div>
          </div>

          <div class="col-4">
            <div class="form-group row">
              <label for="example-text-input" class="col-6 col-form-label">Mitra</label>
              <div class="col-6">
                <select class="form-control js-select2" name="mitra_id">
                    <option value="">Pilih Mitra</option>
                    @foreach($mitra AS $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
              </div>
            </div>
            <div class="form-group row">
              <label for="example-text-input" class="col-6 col-form-label">Harga Beli Satuan(IDR)</label>
              <div class="col-6">
                <input class="form-control" type="text" name="harga_beli_satuan"> 
              </div>
            </div>
            <div class="form-group row">
              <label for="example-text-input" class="col-6 col-form-label">Harga Jual Satuan(IDR)</label>
              <div class="col-6">
                <input class="form-control" type="text" name="harga_jual_satuan">
              </div>
            </div>
          </div>
          <div class="row pt-30 mb-15">
              <div class="col-md-6">
              <a href="{{ route('superuser.accounting.product_finance.index') }}">
                    <button type="button" class="btn bg-gd-cherry border-0 text-white">
                        <i class="fa fa-arrow-left mr-10"></i> Back
                    </button>
                  </a>
              </div>
              
              <div class="col-md-6 text-right">
                <button type="submit" class="btn bg-gd-corporate border-0 text-white">
                    Save <i class="fa fa-check ml-10"></i>
                </button>
              </div>
          </div>
        </div>
      </form>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script type="text/javascript">
  $(document).ready(function () {
    $('.js-select2').select2();

    // Saat brand dipilih
    $('#brand').change(function () {
        let brandName = $(this).val();
        $('#product').empty().append('<option value="">Pilih Produk</option>'); // Reset produk
        $('#packaging_code').val(''); // Reset kode kemasan

        if (brandName) {
            $.ajax({
                url: "{{ route('superuser.accounting.product_finance.get_product') }}",
                type: "GET",
                data: { brand_name: brandName },
                dataType: "json",
                success: function (response) {
                    if (response.length > 0) {
                        response.forEach(function (product) {
                            $('#product').append(
                                `<option value="${product.id}" data-packaging="${product.packaging_id}">
                                    ${product.code} - ${product.name} /  ${product.packaging_name}
                                </option>`
                            );
                        });
                    }
                }
            });
        }
    });

    // Saat produk dipilih, isi kode kemasan
    $('#product').change(function () {
        let packagingCode = $(this).find(':selected').data('packaging');
        $('#packaging_code').val(packagingCode || '');
    });

    $('form.ajax').submit(function (e) {
        e.preventDefault();
        let form = $(this);
        let url = form.data('action');
        let formData = new FormData(this);

        $.ajax({
            url: url,
            type: form.data('type'),
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                form.find('button[type=submit]').prop('disabled', true);
            },
            success: function (response) {
                if (response.status === 200) {
                    // Tampilkan Notifikasi Sukses
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.notification.content,
                        showConfirmButton: false,
                        timer: 2000
                    });

                    // Redirect setelah sukses
                    setTimeout(function () {
                        window.location.href = response.redirect_to;
                    }, 2000);
                }
            },
            error: function (xhr) {
                let response = xhr.responseJSON;
                let errors = response.errors;
                
                if (xhr.status === 400 && errors) {
                    let errorMessages = Object.values(errors).map(msg => msg.join('<br>')).join('<br>');

                    // Tampilkan Notifikasi Error
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        html: errorMessages
                    });
                } else {
                    // Error lainnya (500, dll)
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan, silakan coba lagi!'
                    });
                }
            },
            complete: function () {
                form.find('button[type=submit]').prop('disabled', false);
            }
        });
    });
});
</script>
@endpush