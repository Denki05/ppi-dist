@extends('superuser.app')

@section('content')
@if(session('error') || session('success'))
<div class="alert alert-{{ session('error') ? 'danger' : 'success' }} alert-dismissible fade show" role="alert">
    @if (session('error'))
    <strong>Error!</strong> {!! session('error') !!}
    @elseif (session('success'))
    <strong>Berhasil!</strong> {!! session('success') !!}
    @endif
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif


<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">#SHOW PRODUCT : {{$product->code}} - {{$product->name}}</h3>
  </div>
  <div class="block-content">
    <input style="display: none;" id="tab1" type="radio" name="tabs" checked>
    <label style="padding: 15px 25px;" for="tab1">DATA</label>
      
    <input style="display: none;" id="tab2" type="radio" name="tabs">
    <label style="padding: 15px 25px;" for="tab2">PACKAGING</label>
      
    <input style="display: none;" id="tab3" type="radio" name="tabs">
    <label style="padding: 15px 25px;" for="tab3">BRAND</label>
      
    <!-- Data -->
    <section id="content1">
      <div class="row mb-30">
        <div class="col-12">
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Material Code</label>
            <div class="col-md-7">
              <div class="form-control-plaintext">{{ $product->material_code ?? '' }}</div>
            </div>
          </div>
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Material Name</label>
            <div class="col-md-7">
              <div class="form-control-plaintext">{{ $product->material_name ?? '' }}</div>
            </div>
          </div>
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Code</label>
            <div class="col-md-7">
              <div class="form-control-plaintext">{{ $product->code ?? '' }}</div>
            </div>
          </div>
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Name</label>
            <div class="col-md-7">
              <div class="form-control-plaintext">{{ $product->name ?? '' }}</div>
            </div>
          </div>
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Ratio</label>
            <div class="col-md-7">
              <div class="form-control-plaintext">{{ $product->ratio ?? '' }}</div>
            </div>
          </div>
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Alias</label>
            <div class="col-md-7">
              <div class="form-control-plaintext">{{ $product->alias ?? '' }}</div>
            </div>
          </div>
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Buying Price</label>
            <div class="col-md-7">
              <div class="form-control-plaintext">{{ number_format($product->buying_price) }}</div>
            </div>
          </div>
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Selling Price</label>
            <div class="col-md-7">
              <div class="form-control-plaintext">{{ number_format($product->selling_price) }}</div>
            </div>
          </div>
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Description</label>
            <div class="col-md-7">
              <div class="form-control-plaintext">{{ $product->description }}</div>
            </div>
          </div>
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Note</label>
            <div class="col-md-7">
              <div class="form-control-plaintext">{{ $product->note }}</div>
            </div>
          </div>
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Status</label>
            <div class="col-md-7">
              <div class="form-control-plaintext">{{ $product->status() }}</div>
            </div>
          </div>
        </div>
      </div>
    </section>
      
    <!-- Paackaging -->
    <section id="content2">
      <div class="row mb-30">
        <div class="col-12">
          <table class="table" id="packaging_list">
            <thead>
              <tr>
                <th>#</th>
                <th>Packaging</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($product->product_child as $row)
              <?php
                $pecahId = explode('.', $row->id);
                $kemasan = DB::table('master_packaging')->where('id', $pecahId[1])->first();
              ?>
                <tr>
                  <td>{{$loop->iteration}}</td>
                  <td><b>{{$kemasan->pack_name}}</b></td>
                  <td>{{$row->price}}</td>
                  <td>{{ number_format($row->stock) }}</td>
                  <td></td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </section>
      
    <!-- Brand -->
    <section id="content3">
    <div class="row mb-30">
        <div class="col-12">
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Brand</label>
            <div class="col-md-7">
              <div class="form-control-plaintext">{{ $product->sub_brand_reference->brand_reference->name }}</div>
            </div>
          </div>
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Searah</label>
            <div class="col-md-7">
              <div class="form-control-plaintext">{{ $product->sub_brand_reference->name }}</div>
            </div>
          </div>
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Merek</label>
            <div class="col-md-7">
              <div class="form-control-plaintext">{{ $product->brand_name }}</div>
            </div>
          </div>
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Image</label>
            <div class="col-md-7">
              <a href="{{ $product->image_url ?? img_holder() }}" class="img-link img-link-zoom-in img-thumb img-lightbox">
                <img src="{{ $product->image_url ?? img_holder() }}" class="img-fluid img-show-small">
              </a>
            </div>
          </div>
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Image HD</label>
            <div class="col-md-7">
              <a href="{{ $product->image_hd_url ?? img_holder() }}" class="img-link img-link-zoom-in img-thumb img-lightbox">
                <img src="{{ $product->image_hd_url ?? img_holder() }}" class="img-fluid img-show-small">
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>

@endsection
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    $('#packaging_list').DataTable({
      info: false,
      ordering: false,
      paging: false,
      searching: false,
    });

    $('a.img-lightbox').magnificPopup({
      type: 'image',
      closeOnContentClick: true,
    });
  })
</script>
@endpush