@extends('superuser.app')

@section('content')

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
        <div class="col-6">
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Kode Bahan</label>
            <div class="col-md-7">
              <div class="form-control-plaintext">{{ $product->material_code ?? '' }}</div>
            </div>
          </div>
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Nama Bahan</label>
            <div class="col-md-7">
              <div class="form-control-plaintext">{{ $product->material_name ?? '' }}</div>
            </div>
          </div>
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Kode Barang</label>
            <div class="col-md-7">
              <div class="form-control-plaintext">{{ $product->code ?? '' }}</div>
            </div>
          </div>
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Nama Barang</label>
            <div class="col-md-7">
              <div class="form-control-plaintext">{{ $product->name ?? '' }}</div>
            </div>
          </div>
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Pabrik</label>
            <div class="col-md-7">
              
              <div class="form-control-plaintext"></div>
            </div>
          </div>
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Merek</label>
            <div class="col-md-7">
              <div class="form-control-plaintext">{{ $product->brand_name ?? '' }}</div>
            </div>
          </div>
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Kategori</label>
            <div class="col-md-7">
              <div class="form-control-plaintext">{{ $product->category->name ?? '' }}</div>
            </div>
          </div>
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Gender</label>
            <div class="col-md-7">
              <div class="form-control-plaintext">{{ $product->gender ?? '' }}</div>
            </div>
          </div>
        </div>
        <div class="col-6">
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Alias</label>
            <div class="col-md-7">
              <div class="form-control-plaintext">{{ $product->alias ?? '-' }}</div>
            </div>
          </div>
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Ratio</label>
            <div class="col-md-7">
              <div class="form-control-plaintext">{{ $product->ratio ?? '-' }}</div>
            </div>
          </div>
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Harga Beli</label>
            <div class="col-md-7">
              <div class="form-control-plaintext">{{ '$' . number_format($product->buying_price, 2) }}</div>
            </div>
          </div>
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Harga Jual</label>
            <div class="col-md-7">
              <div class="form-control-plaintext">{{ '$' . number_format($product->selling_price, 2) }}</div>
            </div>
          </div>
          <div class="row">
            <label class="col-md-3 col-form-label text-right">Note</label>
            <div class="col-md-7">
              <div class="form-control-plaintext">{{ $product->note ?? '-' }}</div>
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
          <table id="packaging_list" class="table" style="width:100%">
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
                $pecahId = explode('-', $row->id);
                $kemasan = DB::table('master_packaging')->where('id', $pecahId[1])->first();
              ?>
                <tr>
                  <td>{{$loop->iteration}}</td>
                  <td><b>{{$kemasan->pack_name}}</b></td>
                  <td>{{$row->price}}</td>
                  <td>{{ number_format($row->stock) }}</td>
                  <td>
                    <button type="button" class="btn btn-sm btn-circle btn-alt-secondary upload_button" data-id="{{$row->id}}" title="Update price" data-bs-toggle="modal" data-bs-target="#myModal">
                      <i class="fa fa-pencil"></i>
                    </button>
                  </td>
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
        <div class="col-6">
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
            <label class="col-md-3 col-form-label text-right">Link Web</label>
            <div class="col-md-7">
              <div class="form-control-plaintext">
                <a href="{{ $product->sub_brand_reference->link }}" class="link-primary" target="_blank">{{ $product->sub_brand_reference->link }}</a>
              </div>
            </div>
          </div>
          
        </div>
        <div class="col-6">
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

<!-- Modal update cost -->
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

  <!-- Alert -->
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="display:none;">
      <strong>Success!</strong> to change the selling price!.
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Update Cost Price</h4>
        <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
      </div>
      <form id="myForm" method="POST" role="form" enctype="multipart/form-data">
      {{csrf_field()}}
        <div class="modal-body">
          <div class="form-group">
            <label for="image_botol">Price :</label>
            <input type="text" class="form-control" name="price">
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" id="searah_id" value="0">
          <button type="submit" class="btn btn-info">Save</button>
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
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

    var table = $('#packaging_list').DataTable({
      info: false,
      ordering: false,
      paging: false,
      searching: false,
    });

    $('a.img-lightbox').magnificPopup({
      type: 'image',
      closeOnContentClick: true,
    });

    $('#myForm').on('submit', function (e) {
      e.preventDefault(); // prevent the form submit
      var id = $('.upload_button').data('id');
      var url = "{{ route('superuser.master.product.update_cost', ":id") }}";
      url = url.replace(':id', id);
      var AlertMsg = $('div[role="alert"]');

      var formData = new FormData(this); 
      // build the ajax call
      $.ajax({
          url: url,
          type: 'POST',
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          data: formData,
          contentType: false,
          processData: false,
          success: function (response) {
            $(AlertMsg).show();
            setTimeout(function () {
                    $('#myModal').modal({ show: true });
                    setTimeout(function () {
                        window.location.reload(1);
                    }, 800);
            }, 800);
          }
      });
    });
  
  })
</script>
@endpush