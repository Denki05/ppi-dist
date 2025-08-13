@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Report</span>
  <span class="breadcrumb-item">Management</span>
  <span class="breadcrumb-item">Penjualan Sales</span>
  <span class="breadcrumb-item active">Omset Sales</span>
</nav>

@if($errors->any())
<div class="alert alert-danger alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Error</h3>
  @foreach ($errors->all() as $error)
  <p class="mb-0">{{ $error }}</p>
  @endforeach
</div>
@endif

<div id="alert-block"></div>

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

@if(session()->has('message'))
<div class="alert alert-success alert-dismissable" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
      <span aria-hidden="true">×</span>
  </button>
  <h3 class="alert-heading font-size-h4 font-w400">Success</h3>
  <p class="mb-0">{{ session()->get('message') }}</p>
</div>
@endif

<form action="{{ route('superuser.report.employee_performance.print_report') }}" method="POST">
    @csrf
    <div class="block">
        <hr class="my-20">
        <div class="block-content block-content-full">
            <div class="row mb-3">
                <div class="col-md-3 col-form-label required"><h5>Type Report:</h5></div>
                <div class="col-md-9">
                    <div class="d-flex">
                        <label class="form-check me-3">
                            <input class="form-check-input" type="radio" name="type" id="pic_report" value="1" checked>
                            <span>PIC</span>
                        </label>
                        <label class="form-check">
                            <input class="form-check-input" type="radio" name="type" id="officer_report" value="2">
                            <span>Officer</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="row mb-3" id="officer_dropdown" style="display: none;">
                <div class="col-md-3 col-form-label required"><h5>Officer</h5></div>
                <div class="col-md-9">
                    <select class="form-control js-select2" name="salesman_officer[]" id="salesman_officer" style="width: 50%;" multiple>
                        <option value="all">All</option>
                        <option value="Erick">Erick</option>
                        <option value="Lindy">Lindy</option>
                        <option value="Kantor">Kantor</option>
                        <option value="Vivi">Vivi</option>
                        <option value="Nia">Nia</option>
                        <option value="Ivan">Ivan</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 col-form-label required"><h5>Tampilkan Nominal:</h5></div>
                <div class="col-md-9">
                    <div class="d-flex">
                        <label class="form-check me-3">
                            <input class="form-check-input" type="radio" name="nominal" id="nominal_yes" value="1" checked>
                            <span>Yes</span>
                        </label>
                        <label class="form-check">
                            <input class="form-check-input" type="radio" name="nominal" id="nominal_no" value="2">
                            <span>No</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Set Period From</label>
                        <input type="date" name="period_from" id="period_from" class="form-control" value="{{ date('Y-m-01') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Set Period To</label>
                        <input type="date" name="period_to" id="period_to" class="form-control" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group">
                        <br>
                        <button type="submit" id="printReport" class="btn btn-success"><i class="fa fa-print"></i> Print</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')

@push('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        function toggleFields() {
            if ($('#pic_report').is(':checked')) {
                $('#pic_dropdown').show();
                $('#officer_dropdown').hide();
                $('#salesman_pic').prop('required', true);
                $('#salesman_officer').prop('required', false);
            } else {
                $('#pic_dropdown').hide();
                $('#officer_dropdown').show();
                $('#salesman_pic').prop('required', false);
                $('#salesman_officer').prop('required', true);
            }
        }

        $('input[name="type"]').change(toggleFields);
        toggleFields(); // Jalankan saat pertama kali halaman dimuat

        $('.js-select2').select2();
    });
</script>
@endpush