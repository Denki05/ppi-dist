@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Master</span>
  <a class="breadcrumb-item" href="{{ route('superuser.master.warehouse.index') }}">Warehouse</a>
  <span class="breadcrumb-item active">{{ $warehouse->id }}</span>
</nav>
<div class="block">
  <div class="block-header block-header-default">
    <h3 class="block-title">Show Warehouse</h3>
  </div>
  <div class="block-content">
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Code</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $warehouse->code }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Type</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $warehouse->type() }}</div>
      </div>
    </div>
    {{-- @if($warehouse->type == \App\Entities\Master\Warehouse::TYPE['BRANCH_OFFICE'])
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Warehouse</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">
          <a href="{{ route('superuser.master.branch_office.show', $warehouse->branch_office->id) }}">
            {{ $warehouse->branch_office->name }}
          </a>
        </div>
      </div>
    </div>
    @endif --}}
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Name</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $warehouse->name }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Contact Person</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $warehouse->contact_person }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Phone</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $warehouse->phone }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Address</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $warehouse->address }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Description</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $warehouse->description }}</div>
      </div>
    </div>
    <div class="row">
      <label class="col-md-3 col-form-label text-right">Status</label>
      <div class="col-md-7">
        <div class="form-control-plaintext">{{ $warehouse->status() }}</div>
      </div>
    </div>
    <div class="row pt-30 mb-15">
      <div class="col-md-6">
        <a href="{{ route('superuser.master.warehouse.index') }}">
          <button type="button" class="btn bg-gd-cherry border-0 text-white">
            <i class="fa fa-arrow-left mr-10"></i> Back
          </button>
        </a>
      </div>
      @if($warehouse->status != $warehouse::STATUS['DELETED'])
      <div class="col-md-6 text-right">
        @if($warehouse->type != $warehouse::TYPE['HEAD_OFFICE'])
        <a href="javascript:deleteConfirmation('{{ route('superuser.master.warehouse.destroy', $warehouse->id) }}', true)">
          <button type="button" class="btn bg-gd-pulse border-0 text-white">
            Delete <i class="fa fa-trash ml-10"></i>
          </button>
        </a>
        @endif
        <a href="{{ route('superuser.master.warehouse.edit', $warehouse->id) }}">
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
