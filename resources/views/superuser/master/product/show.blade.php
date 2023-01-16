@extends('superuser.app')

@section('content')

<div class="block">
  
  <div class="container py-5">
  <div class="p-5 bg-white rounded shadow mb-5">
    <!-- Rounded tabs -->
    <ul id="myTab" role="tablist" class="nav nav-tabs nav-pills flex-column flex-sm-row text-center bg-light border-0 rounded-nav">
      <li class="nav-item flex-sm-fill">
        <a id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="true" class="nav-link border-0 text-uppercase font-weight-bold active">Data</a>
      </li>
      <li class="nav-item flex-sm-fill">
        <a id="address-tab" data-toggle="tab" href="#address" role="tab" aria-controls="address" aria-selected="false" class="nav-link border-0 text-uppercase font-weight-bold">Warehouse</a>
      </li>
      <li class="nav-item flex-sm-fill">
        <a id="document-tab" data-toggle="tab" href="#document" role="tab" aria-controls="document" aria-selected="false" class="nav-link border-0 text-uppercase font-weight-bold">Brand</a>
      </li>
      <li class="nav-item flex-sm-fill">
        <a id="fragrant-tab" data-toggle="tab" href="#fragrant" role="tab" aria-controls="fragrant" aria-selected="false" class="nav-link border-0 text-uppercase font-weight-bold">Fragrantic</a>
      </li>
    </ul>
    <div id="myTabContent" class="tab-content">
      <div id="profile" role="tabpanel" aria-labelledby="profile-tab" class="tab-pane fade px-4 py-5 show active">
        <div class="media align-items-center py-3 mb-3">
          <img src="{{$product->image_url ?? img_holder() }}" class="d-block ui-w-100 rounded-circle" alt=""> 
          <div class="media-body ml-4">
            <h3 class="font-weight-bold mb-0">Name : {{ $product->name }}</h3>
            <h5 class="font-weight-bold mb-0"><span class="text-muted font-weight-normal">Code : {{ $product->code }} </span></h5>
            <div class="text-muted mb-2">ID: {{ $product->id }}</div>
            <a href="{{ route('superuser.master.product.edit', $product->id) }}" class="btn btn-primary btn-sm" target="_blank">Edit</a>&nbsp;
              <a href="javascript:deleteConfirmation('{{ route('superuser.master.product.destroy', $product->id) }}', true)" class="btn btn-danger btn-sm">Delete</a>&nbsp;
          </div>
        </div>

        <div class="card mb-4">
          <div class="card-body">
            <table class="table user-view-table m-0" id="profile_table">
              <tbody>
                <tr>
                  <td>Material Code</td>
                  <td><b>:</b></td>
                  <td>
                    {{$product->material_code ?? '-'}}
                  </td>
                </tr>
                <tr>
                  <td>Material Name</td>
                  <td><b>:</b></td>
                  <td>
                      {{$product->material_name ?? '-'}}
                  </td>
                </tr>
                <tr>
                  <td>Ratio</td>
                  <td><b>:</b></td>
                  <td>
                      {{$product->ratio ?? '-'}}
                  </td>
                </tr>
                <tr>
                  <td>Alias</td>
                  <td><b>:</b></td>
                  <td>
                      {{$product->alias ?? '-'}}
                  </td>
                </tr>
                <tr>
                  <td>Buying Price</td>
                  <td><b>:</b></td>
                  <td>{{number_format($product->buying_price) ?? '-'}}</span></td>
                </tr>
                <tr>
                  <td>Selling Price</td>
                  <td><b>:</b></td>
                  <td>{{number_format($product->selling_price) ?? '-'}}</span></td>
                </tr>
                <tr>
                  <td>Description</td>
                  <td><b>:</b></td>
                  <td><b>{{$customer->description ?? '-'}}</td>
                </tr>
                <tr>
                  <td>Note</td>
                  <td><b>:</b></td>
                  <td>{{$product->note}}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div id="address" role="tabpanel" aria-labelledby="address-tab" class="tab-pane fade px-4 py-5">
        <div class="card mb-4">
          <div class="card-body">
            <table class="table user-view-table m-0" id="address_table">
              <tbody>
                <tr>
                  <td>Default Quantity</td>
                  <td><b>:</b></td>
                  <td>{{$product->default_quantity}}</td>
                </tr>
                <tr>
                  <td>Default Unit</td>
                  <td><b>:</b></td>
                  <td>
                  <a href="{{ route('superuser.master.unit.show', $product->default_unit_id) }}">
                    {{ $product->default_unit->name }}
                  </a>
                  </td>
                </tr>
                <tr>
                  <td>Default Warehouse</td>
                  <td><b>:</b></td>
                  <td>
                    <a href="{{ route('superuser.master.warehouse.show', $product->default_warehouse_id) }}">
                      {{ $product->default_warehouse->name }}
                    </a>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div id="document" role="tabpanel" aria-labelledby="document-tab" class="tab-pane fade px-4 py-5">
        <div class="card mb-4">
          <div class="card-body">
            <table class="table user-view-table m-0" id="document_table">
              <tbody>
                <tr>
                  <td>Brand Reference</td>
                  <td><b>:</b></td>
                  <td>
                    
                      {{ $product->sub_brand_reference->brand_reference->name }}
                    
                  </td>
                </tr>
                <tr>
                  <td>Sub Brand Reference</td>
                  <td><b>:</b></td>
                  <td>
                    <a href="{{ route('superuser.master.sub_brand_reference.show', $product->sub_brand_reference_id) }}">
                      {{ $product->sub_brand_reference->name }}
                    </a>
                  </td>
                </tr>
                <tr>
                  <td>Category | Type | Packaging</td>
                  <td><b>:</b></td>
                  <td>
                      {{ $product->category->name }} | {{$product->category->type}} | {{$product->category->packaging}}
                  </td>
                </tr>
                <tr>
                  <td>Image</td>
                  <td><b>:</b></td>
                  <td>
                    <a href="{{ $product->image_url ?? img_holder() }}" class="img-link img-link-zoom-in img-thumb img-lightbox">
                      <img src="{{ $product->image_url ?? img_holder() }}" class="img-fluid img-show-small">
                    </a>
                  </td>
                </tr>
                <tr>
                  <td>Image HD</td>
                  <td><b>:</b></td>
                  <td>
                    <a href="{{ $product->image_hd_url ?? img_holder() }}" class="img-link img-link-zoom-in img-thumb img-lightbox">
                      <img src="{{ $product->image_hd_url ?? img_holder() }}" class="img-fluid img-show-small">
                    </a>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div id="fragrant" role="tabpanel" aria-labelledby="fragrant-tab" class="tab-pane fade px-4 py-5">
        <div class="card mb-4">
          <div class="row">
            <div class="col-md-6">
              <div class="block">
                <div class="block-content block-content-full">
                  <img src="{{ $product->image_url ?? img_holder() }}" class="img-thumbnail">
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="block">
                <div class="block-content block-content-full">
                  <h2>{{$product->name}} | {{ $product->code }}</h2>
                  <a href="{{ $product->sub_brand_reference->link }}" target="_blank">
                    <p>{{ $product->sub_brand_reference->name }}</p>
                  </a>
                  <div class="cell accord-box"><b>main accords</b></div>
                  <div class="cell accord-box">
                    <?php $frag = DB::table('master_product_fragrantica')->where('product_id', $product->id)->orderby('scent_range', 'DESC')->get(); ?>
                    @foreach($frag as $row)
                    <div class="accord-bar" style="color: rgb(255, 255, 255); background: {{$row->color_scent}}; opacity: 1; width: {{$row->scent_range}}%; text-align: center; opacity: 1;">{{$row->parfume_scent}}</div>
                    @endforeach
                  </div>
                  <br>
                  <p>{{$product->description ?? '-'}}</p>
                  <!-- <p>Alpha by <b>HMNS</b> is a fragrance for women and men. This is a new fragrance. Alpha was launched in 2019. The nose behind this fragrance is Agil Usman. Top notes are Citruses and Grass; middle notes are Green Tea and Woodsy Notes; base notes are Cedar and Vetiver. </p> -->
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="row mb-30">
          <div class="col-12">
            <a href="{{route('superuser.master.product.index')}}" class="btn btn-warning  btn-md text-white"><i class="fa fa-arrow-left"></i> Back</a>
          </div>
        </div>
      </div>
    </div>
    <!-- End rounded tabs -->
  </div>
</div>



@endsection

@include('superuser.asset.plugin.magnific-popup')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    $('#profile_table').DataTable({
      columnDefs: [
        { orderable: false, targets: [2, 3] }
      ]
    })

    $('#address_table').DataTable({
      columnDefs: [
        { orderable: false, targets: [3] }
      ]
    })

    $('#document_table').DataTable({
      columnDefs: [
        { orderable: false, targets: [3] }
      ]
    })

    $('a.img-lightbox').magnificPopup({
    type: 'image',
    closeOnContentClick: true,
    });

    var prosesBar = $('.progress-bar');
      var prosesAngka = 0;

      setInterval(function(){
        prosesAngka++;
        prosesBar.css('width', prosesAngka + '%');
        prosesBar.attr('aria-valuenow', prosesAngka);
      }, 100);

    Codebase.helpers('table-tools')
  })
</script>
@endpush
