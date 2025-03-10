@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Account</span>
  <span class="breadcrumb-item active">Log Activity</span>
</nav>

<div class="block">
  <div class="block-content block-content-full">
    <table id="datatable" class="table table-striped">
      <thead>
        <tr>
			<th>No</th>
			<th>Subject</th>
			<th>URL</th>
			<th>Method</th>
			<th>IP</th>
			<th>User Agent</th>
			<th>User Id</th>
			<th>Created At</th>
        </tr>
      </thead>
	  <tbody>
		@if($logs->count())
			@foreach($logs as $key => $log)
				
				<tr>
					<td>{{ ++$key }}</td>
					<td>{{ $log->subject }}</td>
					<td><textarea class="form-control" rows="3" readonly>{{ $log->url }}</textarea></td>
					<td>{{ $log->method }}</td>
					<td >{{ $log->ip }}</td>
					<td>
						<textarea class="form-control" rows="3" readonly>{{ $log->agent }}</textarea>
					</td>
					<td>{{ $log->created_by->username }}</td>
					<td>{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s') }}</td>
				</tr>
			@endforeach
		@endif
	  </tbody>
    </table>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
  $('#datatable').DataTable({

  });
});
</script>
@endpush