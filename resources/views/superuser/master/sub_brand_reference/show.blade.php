@extends('superuser.app')

@section('content')
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Show Searah</h3>
  </div>
  <div class="block-content">
    <!-- <div class="container mt-5 mb-5">
        <div class="row d-flex justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="images p-3">
                                <div class="text-center p-4"> <img id="main-image" src="{{ $sub_brand_reference->image_botol_url ?? img_holder() }}" width="120%" height="auto" /> </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="product p-4">
                                <div class="mt-4 mb-3"> <span class="text-uppercase text-muted brand">{{ $sub_brand_reference->brand_reference->name }}</span>
                                    <h5 class="text-uppercase">
                                      <a href="{{ $sub_brand_reference->link }}" target="_blank">
                                        {{ $sub_brand_reference->name }}
                                      </a>
                                    </h5>
                                </div>
                                <div class="price d-flex flex-row align-items-center"><span class="text-uppercase" style="font-weight: bold;">Notes :</span></div>
                                <p class="about">
                                  {{ $sub_brand_reference->description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->
    
    <div class="pop-up-container">
            
            <div class="product-details">
              <div class="product-left">
                <div class="product-info">
                  <div class="product-manufacturer">
                    {{ $sub_brand_reference->brand_reference->name }}
                  </div>
                  <div class="product-title">
                    <a href="{{ $sub_brand_reference->link }}" class="product-title">
                      {{ $sub_brand_reference->name }}
                    </a>
                  </div>
                </div>
                <div class="product-image">
                  <img src="{{ $sub_brand_reference->image_botol_url }}" />
                </div>
              </div>
              <div class="product-right">
                <div class="product-description">
                  <h5>Description</h5>
                  {{ $sub_brand_reference->description }}
                </div>
                <div class="product-available">
                  <!-- <h5 align="center">Main Accords</h5> -->
                  <img src="{{ $sub_brand_reference->image_table_botol_url }}" />
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
