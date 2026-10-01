$('form').attr('autocomplete', 'off');

$('form.ajax').submit(function (e) {
  e.preventDefault();

  var form = $(this);

  form.find("button[type='submit']").prop('disabled', true);

  let data = new FormData(this);

  $.ajax({
    url: $(this).data('action'),
    data: data,
    contentType: false,
    cache: false,
    processData: false,
    type: $(this).data('type'),
    beforeSend: function () {
      Codebase.layout('header_loader_on')
    },
    complete: function () {
      Codebase.layout('header_loader_off')
    }
  }).done(function (response) {
    if (objHasProp(response, 'data.notification')) {
      responded(response.data.notification);
    } else if (typeof response === 'string' && response.length) {
      // Server membalas HTML/string padahal form.ajax selalu mengharapkan JSON
      // (misal redirect akses-ditolak, 404 abort, 419 CSRF, 503 maintenance).
      // Sebelumnya kasus ini diam tanpa error. Tampilkan pesan generik.
      responded({
        alert: 'block',
        type: 'alert-danger',
        header: 'Error',
        content: 'Server membalas di luar format JSON (kemungkinan: sesi habis / tidak punya akses / CSRF expired / maintenance). Silakan refresh halaman, login ulang bila perlu, lalu coba lagi. Cek tab Network > tutup_so > Headers (status) & Response untuk detail.'
      });
    }

    if (objHasProp(response, 'data.redirect_to')) {
      redirect(response.data.redirect_to, 3000);
    } else {
      form.find("button[type='submit']").prop('disabled', false);
    }
  }).fail(function (request, status, error) {
    if (objHasProp(request, 'responseJSON.data.notification')) {
      responded(request.responseJSON.data.notification);
    } else if (objHasProp(request, 'responseJSON.maintenance') || objHasProp(request, 'responseJSON.message')) {
      // Balasan JSON maintenance (503) dari MaintenanceForceLogout /
      // CheckForMaintenanceMode: tampilkan pesannya agar user paham.
      var msg = (request.responseJSON && (request.responseJSON.message || request.responseJSON.maintenance)) || 'Sistem sedang maintenance.';
      responded({
        alert: 'block',
        type: 'alert-danger',
        header: 'Error',
        content: String(msg) + ' (HTTP ' + request.status + '). Simpan pekerjaan Anda lalu login ulang bila diminta.'
      });
    } else {
      // Balasan HTML (419/404/500/redirect login dsb): responseJSON kosong
      // sehingga sebelumnya tidak ada notifikasi sama sekali (silent).
      var hint = 'Cek tab Network > tutup_so > Headers (status) & Response untuk detail.';
      if (request.status == 419) {
        hint = 'CSRF token expired/kadaluarsa atau sesi berubah. Refresh halaman lalu coba lagi. (' + hint + ')';
      } else if (request.status == 401 || request.status == 403) {
        hint = 'Sesi habis atau tidak punya akses. Login ulang / minta akses, lalu coba lagi. (' + hint + ')';
      } else if (request.status == 404) {
        hint = 'Endpoint atau data tidak ditemukan (404). ' + hint;
      } else if (request.status == 503) {
        hint = 'Sistem sedang maintenance (503). Simpan pekerjaan Anda dan coba lagi nanti. (' + hint + ')';
      } else if (request.status == 500) {
        hint = 'Kesalahan server (500). Lihat storage/logs/laravel-*.log di server. (' + hint + ')';
      }
      responded({
        alert: 'block',
        type: 'alert-danger',
        header: 'Error',
        content: 'Gagal menyimpan (HTTP ' + request.status + ' ' + (error || '') + '). ' + hint
      });
    }

    if (objHasProp(request, 'responseJSON.data.redirect_to')) {
      redirect(request.responseJSON.data.redirect_to, 3000);
    } else {
      form.find("button[type='submit']").prop('disabled', false);
    }

    if (objHasProp(request, 'status') && request.status == 429) {
      Codebase.helpers('notify', {
        align: 'right',
        from: 'top',
        type: 'warning',
        icon: 'fa fa-ban mr-5',
        message: "Too many attempts, try again later"
      });
    }
  });
})