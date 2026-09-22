@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item active">Settings</span>
</nav>
<div id="alert-block"></div>
<div class="row">
  <div class="col-md-6">
    <form class="ajax" data-action="{{ route('superuser.utility.settings.website') }}" data-type="POST">
      <div class="block">
        <div class="block-header block-header-default">
          <h3 class="block-title">Website</h3>
          <div class="block-options">
            <button type="submit" class="btn-block-option">
              <i class="fa fa-check"></i> Save
            </button>
            <button type="reset" class="btn-block-option">
              <i class="fa fa-repeat"></i> Reset
            </button>
          </div>
        </div>
        <div class="block-content">
          <div class="form-group row">
            <label class="col-lg-4 col-form-label">Name</label>
            <div class="col-lg-7">
              <input type="text" class="form-control" name="name" placeholder="Website Name" value="{{ setting('website.name') }}">
            </div>
          </div>
          <div class="form-group row">
            <label class="col-lg-4 col-form-label">Color Themes</label>
            <input type="hidden" name="color_themes" value="{{ setting('website.color_themes') }}">
            <div class="col-lg-7">
              <div class="row text-center mb-5">
                <div class="col-2 mb-5">
                  <a class="text-default {{ setting('website.color_themes') == 'default' ? 'border-primary border-b-3' : '' }}" data-toggle="theme" data-theme="default" data-theme-url="default"><i class="fa fa-2x fa-circle"></i></a>
                </div>
                <div class="col-2 mb-5">
                  <a class="text-elegance {{ setting('website.color_themes') == 'superuser_assets/css/themes/elegance.min.css' ? 'border-primary border-b-3' : '' }}" data-toggle="theme" data-theme="{{ asset('superuser_assets/css/themes/elegance.min.css') }}" data-theme-url="superuser_assets/css/themes/elegance.min.css"><i class="fa fa-2x fa-circle"></i></a>
                </div>
                <div class="col-2 mb-5">
                  <a class="text-pulse {{ setting('website.color_themes') == 'superuser_assets/css/themes/pulse.min.css' ? 'border-primary border-b-3' : '' }}" data-toggle="theme" data-theme="{{ asset('superuser_assets/css/themes/pulse.min.css') }}" data-theme-url="superuser_assets/css/themes/pulse.min.css"><i class="fa fa-2x fa-circle"></i></a>
                </div>
                <div class="col-2 mb-5">
                  <a class="text-flat {{ setting('website.color_themes') == 'superuser_assets/css/themes/flat.min.css' ? 'border-primary border-b-3' : '' }}" data-toggle="theme" data-theme="{{ asset('superuser_assets/css/themes/flat.min.css') }}" data-theme-url="superuser_assets/css/themes/flat.min.css"><i class="fa fa-2x fa-circle"></i></a>
                </div>
                <div class="col-2 mb-5">
                  <a class="text-corporate {{ setting('website.color_themes') == 'superuser_assets/css/themes/corporate.min.css' ? 'border-primary border-b-3' : '' }}" data-toggle="theme" data-theme="{{ asset('superuser_assets/css/themes/corporate.min.css') }}" data-theme-url="superuser_assets/css/themes/corporate.min.css"><i class="fa fa-2x fa-circle"></i></a>
                </div>
                <div class="col-2 mb-5">
                  <a class="text-earth {{ setting('website.color_themes') == 'superuser_assets/css/themes/earth.min.css' ? 'border-primary border-b-3' : '' }}" data-toggle="theme" data-theme="{{ asset('superuser_assets/css/themes/earth.min.css') }}" data-theme-url="superuser_assets/css/themes/earth.min.css"><i class="fa fa-2x fa-circle"></i></a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
  <div class="col-md-6">
    
      <div class="block">
        <div class="block-header block-header-default">
          <h3 class="block-title">Maintenance</h3>
          @if(app()->isDownForMaintenance())
            <div class="block-options">
              <span class="badge badge-danger">DOWN TIME</span>
            </div>
          @else
            <div class="block-options">
              <span class="badge badge-success">UP TIME</span>
            </div>
          @endif
        </div>
        <div class="block-content">
          <div class="form-group row">
            <label class="col-lg-4 col-form-label">Status :</label>
            <div class="col-lg-7">
                <div class="alert {{ app()->isDownForMaintenance() ? 'alert-danger' : 'alert-success' }} mb-0" role="alert">
                    {{ app()->isDownForMaintenance() ? 'DOWN TIME — user biasa otomatis diberi popup + logout' : 'UP TIME — semua user bisa akses normal' }}
                </div>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-lg-4 col-form-label">Pesan untuk user :</label>
            <div class="col-lg-7">
              <textarea id="maintenance-message" class="form-control" rows="2" placeholder="Contoh: Sistem update fitur kasir, mohon simpan pekerjaan Anda.">Kami sedang dalam perbaikan. Coba lagi nanti!</textarea>
              <small class="form-text text-muted">Pesan ini tampil di popup user online & halaman 503.</small>
            </div>
          </div>
          <div class="form-group row">
              <label class="col-lg-4 col-form-label">Maintenance :</label>
              <div class="col-lg-7">
                  <button type="button" id="btn-toggle-maintenance"
                    class="btn {{ app()->isDownForMaintenance() ? 'btn-warning' : 'btn-danger' }}">
                      {{ app()->isDownForMaintenance() ? 'Disable Maintenance' : 'Enable Maintenance + Kick User' }}
                  </button>
                  <small class="form-text text-muted">
                    Saat Enable: user biasa dapat popup hitung mundur 15 detik lalu auto-logout.
                    Admin Developer/SuperAdmin tetap bisa akses untuk Disable kembali.
                  </small>
              </div>
          </div>
          <div class="form-group row">
            <label class="col-lg-4 col-form-label">Cek manual :</label>
            <div class="col-lg-7">
              <button type="button" id="btn-check-status" class="btn btn-sm btn-info">Lihat JSON status</button>
            </div>
          </div>
          <div class="form-group row">
            <label class="col-lg-4 col-form-label">Backup Full (DB + File) :</label>
            <div class="col-lg-3">
              <form action="{{ route('superuser.utility.settings.backupDatabase') }}" method="get">
                <button style="submit" class="btn btn-primary"><i class="fa fa-hdd-o" aria-hidden="true"></i></button>
              </form>
            </div>
          </div>
        </div>
      </div>
    
  </div>
</div>
@endsection

@include('superuser.asset.plugin.swal2')

@push('scripts')
<script src="{{ asset('utility/superuser/js/form.js') }}"></script>
<script>
$(document).ready(function () {
  $('[data-toggle=theme]').on('click', function (e) {
    e.preventDefault();

    let color_themes = $(this).data('theme-url')
    $('input[name=color_themes]').val(color_themes)
    $(this).parent().siblings().children().removeClass('border-primary border-b-3')
    $(this).addClass('border-primary border-b-3')
  })

  // Toggle maintenance dengan pesan custom + konfirmasi.
  $('#btn-toggle-maintenance').on('click', function () {
    var isDown = {{ app()->isDownForMaintenance() ? 'true' : 'false' }};
    var message = $('#maintenance-message').val() || '';
    var baseUrl = "{{ route('superuser.utility.settings.toggleMaintenanceMode') }}";
    var url = isDown ? baseUrl : baseUrl + '?message=' + encodeURIComponent(message);

    var confirmText = isDown
      ? 'Matikan maintenance? User bisa login kembali.'
      : 'Aktifkan maintenance? User online akan dapat popup + auto-logout dalam 15 detik.';

    Swal.fire({
      title: 'Are you sure?',
      text: confirmText,
      type: 'warning',
      showCancelButton: true,
      allowOutsideClick: false,
    }).then(function (result) {
      if (result.value) {
        Swal.fire({ title: 'Saving...', allowOutsideClick: false, onOpen: () => { Swal.showLoading() } });
        $.ajax({ url: url, type: 'GET' }).then(function (response) {
          Swal.fire({ title: 'Saved!', text: 'Maintenance status updated.', type: 'success' }).then(function () {
            if (response && response.data && response.data.redirect_to) {
              redirect(response.data.redirect_to);
            } else {
              location.reload();
            }
          });
        }).catch(function (error) {
          Swal.fire('Error!', (error && error.statusText) || 'Gagal update maintenance', 'error');
        });
      }
    });
  });

  // Lihat JSON status via popup (tidak buka tab baru).
  $('#btn-check-status').on('click', function () {
    var url = "{{ route('superuser.utility.settings.maintenanceStatus') }}";
    $.ajax({ url: url, type: 'GET', dataType: 'json', cache: false }).then(function (res) {
      var pretty = JSON.stringify(res, null, 2)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
      var badge = (res && res.down)
        ? '<span class="badge badge-danger">DOWN TIME</span>'
        : '<span class="badge badge-success">UP TIME</span>';
      Swal.fire({
        title: 'Maintenance Status ' + badge,
        html: '<pre style="text-align:left;background:#263238;color:#e0e0e0;border-radius:8px;padding:14px;max-height:300px;overflow:auto;font-size:12.5px;">' + pretty + '</pre>',
        width: 560,
        confirmButtonText: 'Tutup',
      });
    }).catch(function (error) {
      Swal.fire('Error!', (error && error.statusText) || 'Gagal mengambil status', 'error');
    });
  });
})
</script>
@endpush