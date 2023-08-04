@extends('superuser.app')

@section('content')
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Show Searah</h3>
  </div>
  <div class="block-content">

  <div class="row d-flex justify-content-center">
        <div class="col-md-10">
            <div class="card mb-4 border-0">
                <div class="row">
                    <div class="col-md-6">
                        <div class="images p-3">
                            <!-- <div class="text-center p-4"> <img id="main-image" src="https://i.imgur.com/Dhebu4F.jpg" width="250" /> </div> -->
                            <div class="text-center p-4">
                              <a href="{{ $sub_brand_reference->image_botol_url ?? img_holder() }}" class="img-link img-link-zoom-in img-thumb img-lightbox">
                                <img id="main-image" src="{{ $sub_brand_reference->image_botol_url }}" alt="Generic placeholder image" width="200" class="img-fluid">
                              </a>
                            </div>
                            <div class="thumbnail text-center"> 
                              <img onclick="change_image(this)" src="{{ $sub_brand_reference->image_botol_url }}" width="70"> 
                              <img onclick="change_image(this)" src="{{ $sub_brand_reference->image_botol_url }}" width="70"> 
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="product p-4">
                            
                            <div class="mt-4 mb-3"> <span class="text-uppercase text-muted brand">{{$sub_brand_reference->code}} - <b>{{$sub_brand_reference->brand_reference->name}}</b></span>
                                <h5 class="text-uppercase">{{$sub_brand_reference->name}}</h5>
                                <!-- <div class="price d-flex flex-row align-items-center"> <span class="act-price">$20</span>
                                    <div class="ml-2"> <small class="dis-price">$59</small> <span>40% OFF</span> </div>
                                </div> -->
                            </div>
                            <p class="about">{{$sub_brand_reference->description}}</p>
                            
                            <div class="sizes mt-5">
                                <h6 class="text-uppercase">Visit</h6> 
                            </div>
                            <div class="cart mt-4 align-items-center"> <button onclick="location.href='{{$sub_brand_reference->link}}'" class="btn btn-danger text-uppercase mr-2 px-4"><i class="fa fa-link"></i></button></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row pt-30 mb-15">
      <div class="col-md-6">
        <a href="{{ route('superuser.master.sub_brand_reference.index') }}">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
      </div>
      @if($sub_brand_reference->status != $sub_brand_reference::STATUS['DELETED'])
      <div class="col-md-6 text-right">
        <a href="javascript:deleteConfirmation('{{ route('superuser.master.sub_brand_reference.destroy', $sub_brand_reference->id) }}', true)">
          <button type="button" class="btn bg-gd-pulse border-0 text-white">
            Delete <i class="fa fa-trash ml-10"></i>
          </button>
        </a>
        <a href="{{ route('superuser.master.sub_brand_reference.edit', $sub_brand_reference->id) }}">
          <button type="button" class="btn bg-gd-leaf border-0 text-white">
            Edit <i class="fa fa-pencil ml-10"></i>
          </button>
        </a>
      </div>
      @endif
    </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.magnific-popup')

@push('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    $('a.img-lightbox').magnificPopup({
      type: 'image',
      closeOnContentClick: true,
    });

    function change_image(image){
      var container = document.getElementById("main-image");
      container.src = image.src;
      }
      
      document.addEventListener("DOMContentLoaded", function(event) {
    });
  });
</script>
@endpush
