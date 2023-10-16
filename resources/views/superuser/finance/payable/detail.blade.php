@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Finance</span>
  <span class="breadcrumb-item">Payable</span>
  <span class="breadcrumb-item active">Detail</span>
</nav>
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
  <div class="block-content block-content-full">
    <div class="row">
      <div class="col-12">
        <div class="table-responsive">
          <table class="table table-striped table->bordered" id="datatables">
            <thead>
              <th>#</th>
              <th>Created</th>
              <th>Code</th>
              <th>Total</th>
              <th>Status</th>
              <th>Action</th>
            </thead>
            <tbody>
              @foreach($result as $index => $row)
                <tr>
                  <input type="hidden" name="id" value="{{ $row->id }}">
                  <td>{{ $index+1 }}</td>
                  <td>{{ $row->created_at }}</td>
                  <td>{{ $row->code }}</td>
                  <td>{{number_format($row->total,0,',','.')}}</td>
                  <td>{{ $row->status() }}</td>
                  <td>
                    @if($row->status == 1)
                    <a href="{{route('superuser.finance.payable.approve', $row->id)}}" class="btn btn-success" title="Approve"><i class="fa fa-check"></i></a>
                    <a href="{{ route('superuser.finance.payable.edit', $row->id) }}" class="btn btn-warning" title="Edit"><i class="fa fa-pencil"></i></a>
                    <a href="#" class="btn btn-danger" title="Delete"><i class="fa fa-trash"></i></a>
                    @endif
                    @if($row->status == 2)
                      <a href="#" class="btn btn-info" title="Show"><i class="fa fa-eye"></i></a>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="row mb-30">
      <div class="col-12">
        <a href="{{route('superuser.finance.payable.create', $member->id)}}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Back</a>
      </div>
    </div>
  </div>
</div>
@endsection


@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.daterangepicker')

@push('scripts')
@include('superuser.finance.payable.js')
@endpush
