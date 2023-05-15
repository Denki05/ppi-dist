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
  <hr class="my-20">
  <div class="block-content block-content-full">
    <div class="row">
      <div class="col-12">
        <div class="table-responsive">
          <table class="table table-striped table->bordered">
            <thead>
              <th>#</th>
              <th>Created</th>
              <th>Code</th>
              <th>Total</th>
              <th>Action</th>
            </thead>
            <tbody>
              @foreach($result as $index => $row)
                <tr>
                  <td>{{ $index+1 }}</td>
                  <td>{{ $row->created_at }}</td>
                  <td>{{ $row->code }}</td>
                  <td>{{number_format($row->total,0,',','.')}}</td>
                  <td></td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="row mb-30">
      <div class="col-12">
        <a href="{{route('superuser.finance.payable.index')}}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> Back</a>
      </div>
    </div>
  </div>
</div>
@endsection

<!-- Modal -->


@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')
@include('superuser.asset.plugin.swal2')

@push('scripts')

  <script type="text/javascript">
  </script>
@endpush
