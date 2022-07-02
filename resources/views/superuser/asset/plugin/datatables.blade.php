@push('plugin-styles')
<link rel="stylesheet" href="{{ asset('superuser_assets/js/plugins/datatables/dataTables.bootstrap4.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('superuser_assets/js/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('superuser_assets/js/plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
@endpush