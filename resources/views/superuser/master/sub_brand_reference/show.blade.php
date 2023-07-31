@extends('superuser.app')

@section('content')
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Show Searah</h3>
  </div>
  <div class="block-content">
    
    
  <div class="row">
    <div class="col-lg-8 mx-auto">

      <!-- List group-->
      <ul class="list-group shadow">

        <!-- list group item-->
        <li class="list-group-item">
          <!-- Custom content-->
          <div class="media align-items-lg-center flex-column flex-lg-row p-3">
            <div class="media-body order-2 order-lg-1">
              <h5 class="mt-0 font-weight-bold mb-2">{{$sub_brand_reference->name}}</h5>
              <p class="font-italic text-muted mb-0 small">{{$sub_brand_reference->description}}</p>
              {{--<div class="d-flex align-items-center justify-content-between mt-1">
                <h6 class="font-weight-bold my-2">$120.00</h6>
                <ul class="list-inline small">
                  <li class="list-inline-item m-0"><i class="fa fa-star text-success"></i></li>
                  <li class="list-inline-item m-0"><i class="fa fa-star text-success"></i></li>
                  <li class="list-inline-item m-0"><i class="fa fa-star text-success"></i></li>
                  <li class="list-inline-item m-0"><i class="fa fa-star text-success"></i></li>
                  <li class="list-inline-item m-0"><i class="fa fa-star-o text-gray"></i></li>
                </ul>
              </div>--}}
            </div><img src="{{ $sub_brand_reference->image_botol_url }}" alt="Generic placeholder image" width="200" class="ml-lg-5 order-1 order-lg-2">
          </div>
          <!-- End -->
        </li>
        <!-- End -->

        <!-- list group item-->
        
        <!-- End -->

        <!-- list group item -->
        
        <!-- End -->

        <!-- list group item -->
        
        <!-- End -->

      </ul>
      <!-- End -->
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
