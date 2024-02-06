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
        <div class="col-8">
          <div class="row">
            <div class="col">
              <div class="form-group row">
                <label for="example-text-input" class="col-3 col-form-label">Kode Bahan</label>
                <div class="col-8">
                  <input class="form-control" type="text" value="{{ $product->material_code }}" id="example-text-input" readonly>
                  <br>
                  <a href="#material_optional" class="link-primary"><i class="fa fa-plus-circle" aria-hidden="true"></i> Other Data</a>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="form-group row">
                <label for="example-text-input" class="col-3 col-form-label">Nama Bahan</label>
                <div class="col-8">
                  <input class="form-control" type="text" value="{{ $product->material_name }}" id="example-text-input" readonly>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="form-group row">
                <label for="example-text-input" class="col-3 col-form-label">Pabrik</label>
                <div class="col-8">
                  <input class="form-control" type="text" value="{{ $product->sourceVendor->name }}" id="example-text-input" readonly>
                </div>
              </div>
            </div>
          </div>

          <!-- Optional -->
          <div class="row" id="material_optional" style="display: none;">
            <div class="col">
              <div class="form-group row">
                <label for="example-text-input" class="col-3 col-form-label">Kode Bahan Opsional</label>
                <div class="col-8">
                  <input class="form-control" type="text" value="{{ $product->material_code_optional ?? '' }}" id="example-text-input" readonly>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="form-group row">
                <label for="example-text-input" class="col-3 col-form-label">Nama Bahan Opsional</label>
                <div class="col-8">
                  <input class="form-control" type="text" value="{{ $product->material_name_optional ?? '' }}" id="example-text-input" readonly>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="form-group row">
                <label for="example-text-input" class="col-3 col-form-label">Pabrik Opsional</label>
                <div class="col-8">
                  <input class="form-control" type="text" value="{{ $product->destinationVendor->name ?? '' }}" id="example-text-input" readonly>
                </div>
              </div>
            </div>
          </div>
          
          <hr>
          
          <div class="form-group row">
            <label for="example-text-input" class="col-2 col-form-label">Kode produk</label>
            <div class="col-8">
              <input class="form-control" type="text" value="{{ $product->code }}" id="example-text-input" readonly>
            </div>
          </div>
          <div class="form-group row">
            <label for="example-text-input" class="col-2 col-form-label">Nama produk</label>
            <div class="col-8">
              <input class="form-control" type="text" value="{{ $product->name }}" id="example-text-input" readonly>
            </div>
          </div>
          <div class="form-group row">
            <label for="example-text-input" class="col-2 col-form-label">Merek</label>
            <div class="col-8">
              <input class="form-control" type="text" value="{{ $product->brand_name }}" id="example-text-input" readonly>
            </div>
          </div>
          <div class="form-group row">
            <label for="example-text-input" class="col-2 col-form-label">Kategori</label>
            <div class="col-8">
              <input class="form-control" type="text" value="{{ $product->category->name }}" id="example-text-input" readonly>
            </div>
          </div>
          <div class="form-group row">
            <label for="example-text-input" class="col-2 col-form-label">Gender</label>
            <div class="col-8">
              <input class="form-control" type="text" value="{{ $product->gender }}" id="example-text-input" readonly>
            </div>
          </div>
        </div>

        <div class="col-4">
          <div class="form-group row">
            <label for="example-text-input" class="col-2 col-form-label">Alias</label>
            <div class="col-8">
              <input class="form-control" type="text" value="{{ $product->alias }}" id="example-text-input" readonly>
            </div>
          </div>
          <div class="form-group row">
            <label for="example-text-input" class="col-2 col-form-label">Ratio</label>
            <div class="col-8">
              <input class="form-control" type="text" value="{{ $product->ratio }}" id="example-text-input" readonly> 
            </div>
          </div>
          {{--<div class="form-group row">
            <label for="example-text-input" class="col-2 col-form-label">Harga Beli</label>
            <div class="col-8">
              <input class="form-control" type="text" value="{{ '$' . number_format($product->buying_price, 2) }}" id="example-text-input" readonly>
            </div>
          </div>
          <div class="form-group row">
            <label for="example-text-input" class="col-2 col-form-label">Harga Jual</label>
            <div class="col-8">
              <input class="form-control" type="text" value="{{ '$' . number_format($product->selling_price, 2) }}" id="example-text-input" readonly>
            </div>
          </div>--}}
          <div class="form-group row">
            <label for="example-text-input" class="col-2 col-form-label">Note</label>
            <div class="col-8">
              <input class="form-control" type="text" value="{{ $product->note ?? '-' }}" id="example-text-input" readonly>
            </div>
          </div>
          <div class="form-group row">
            <label for="example-text-input" class="col-2 col-form-label">Status</label>
            <div class="col-8">
              <input class="form-control" type="text" value="{{ $product->status() }}" id="example-text-input" readonly>
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
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @foreach($product->product_pack as $row)
                <tr>
                  <td>{{$loop->iteration}}</td>
                  <td><b>{{$row->packaging->pack_name}}</b></td>
                  <td>{{$row->price}}</td>
                  <td>
                    <a href="javascript:void(0)" type="button" class="btn btn-sm btn-circle btn-alt-secondary openModal" data-id="{{$row->id}}" title="Update price"><i class="fa fa-money"></i></a> 
                    @if($row->condition == 0)
                      <a href="javascript:saveConfirmation('{{ route('superuser.master.product.disable', base64_encode($row->id)) }}')" type="button" class="btn btn-sm btn-circle btn-alt-warning" title="Disable PL / PD"><i class="fa fa-unlock" aria-hidden="true"> </i></a>
                    @else
                      <a href="javascript:saveConfirmation('{{ route('superuser.master.product.enable', base64_encode($row->id)) }}')" type="button" class="btn btn-sm btn-circle btn-alt-warning" title="Enable PL / PD"><i class="fa fa-lock" aria-hidden="true"> </i></a>
                    @endif
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

<!-- Modal Update Price -->
<!-- Modal -->
<div class="modal fade" id="appointmentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="alert alert-success alert-dismissible fade show" role="alert" style="display:none;">
        <strong>Success!</strong> Update price!.
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Update Price</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="myForm" method="POST" role="form" enctype="multipart/form-data" novalidate>
                  @csrf
                    <input type="hidden" class="form-control" id="colly" name="colly" value="1">
                    <div class="mb-3">
                        <label>Price</label>
                        <input type="text" class="form-control" name="price">
                    </div>
                    <input type="hidden" id="productPackID" />
                    <button type="submit" class="btn btn-info">Save</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                </form>
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

    $(document).on('click', '.openModal', function () {
      var id = $(this).data('id');
      $('#productPackID').val(id);
      $('#appointmentModal').modal('show');
    })

    $('#myForm').on('submit', function (e) {
      e.preventDefault(); // prevent the form submit
      var id = $('#productPackID').val();
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

    $('a[href^="#"]').on('click', function(event) {
      var target = $( $(this).attr('href') );
      target.fadeToggle(100);
    });
  })
</script>
@endpush