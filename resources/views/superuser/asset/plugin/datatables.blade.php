@push('plugin-styles')
<link rel="stylesheet" href="{{ asset('superuser_assets/js/plugins/datatables/dataTables.bootstrap4.css') }}">
<link href="https://cdn.datatables.net/rowgroup/1.1.3/css/rowGroup.bootstrap4.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="{{ asset('superuser_assets/js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('superuser_assets/js/plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script src="https://cdn.datatables.net/rowgroup/1.1.3/js/dataTables.rowGroup.min.js"></script>
@endpush