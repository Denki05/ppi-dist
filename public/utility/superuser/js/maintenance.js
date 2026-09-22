/**
 * Maintenance Watcher
 * Polling status maintenance ke server setiap interval.
 * Jika maintenance ON -> tampilkan peringatan + hitung mundur,
 * lalu paksa logout otomatis.
 *
 * Konfigurasi via window.MaintenanceWatcher = { statusUrl, logoutUrl, ... }
 * atau otomatis membaca meta tag / default route Laravel.
 */
(function () {
    var cfg = window.MaintenanceWatcher || {};
    var statusUrl = cfg.statusUrl || (typeof base_url !== 'undefined' ? base_url + '/superuser/utility/settings/maintenance-status' : '/superuser/utility/settings/maintenance-status');
    var logoutUrl = cfg.logoutUrl || (typeof base_url !== 'undefined' ? base_url + '/superuser/logout' : '/superuser/logout');
    var intervalMs = cfg.intervalMs || 15000; // 15 detik
    var countdownSec = cfg.countdownSec || 15; // hitung mundur popup
    var alreadyWarned = false;
  
    function checkStatus() {
      if (alreadyWarned) return;
  
      $.ajax({
        url: statusUrl,
        type: 'GET',
        dataType: 'json',
        cache: false,
        success: function (res) {
          if (res && res.down === true && res.bypass !== true) {
            showWarning(res.message);
          }
        },
        error: function (xhr) {
          // 503 dari middleware CheckForMaintenanceMode = sedang maintenance.
          // xhr.responseJSON bisa berisi { maintenance: true, logout: true }.
          try {
            var body = xhr.responseJSON || (xhr.responseText ? JSON.parse(xhr.responseText) : null);
            if (xhr.status === 503 && body && (body.maintenance === true || body.down === true)) {
              showWarning(body.message || 'Sistem sedang maintenance.');
              return;
            }
          } catch (e) { /* abaikan */ }
          if (xhr.status === 503) {
            showWarning('Sistem sedang dalam pemeliharaan.');
          }
        }
      });
    }
  
    function showWarning(serverMessage) {
      alreadyWarned = true;
      var remaining = countdownSec;
      var msg = serverMessage || 'Sistem akan masuk mode maintenance untuk update fitur.';
  
      // Hentikan interaksi user: tutup dropdown/modal yang terbuka.
      try { $('.modal').modal('hide'); } catch (e) {}
  
      if (typeof Swal === 'undefined') {
        alert(msg + '\n\nAnda akan di-logout otomatis dalam ' + remaining + ' detik.');
        doLogout();
        return;
      }
  
      var timer = setInterval(function () {
        remaining -= 1;
        var el = document.getElementById('mm-countdown');
        if (el) el.textContent = remaining;
        if (remaining <= 0) {
          clearInterval(timer);
          doLogout();
        }
      }, 1000);
  
      Swal.fire({
        title: 'Pemberitahuan Maintenance',
        html: '<p>' + escapeHtml(msg) + '</p>' +
          '<p>Simpan pekerjaan Anda sekarang.<br>Anda akan di-logout otomatis dalam <b><span id="mm-countdown">' + remaining + '</span> detik</b>.</p>',
        type: 'warning',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showCancelButton: false,
        confirmButtonText: 'Simpan & Logout Sekarang',
        backdrop: true,
      }).then(function () {
        clearInterval(timer);
        doLogout();
      });
    }
  
    function doLogout() {
      window.location.href = logoutUrl;
    }
  
    function escapeHtml(s) {
      return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
    }
  
    // Jalankan polling pertama setelah 5 detik (beri waktu halaman load),
    // lalu rutin tiap intervalMs.
    setTimeout(function () {
      checkStatus();
      setInterval(checkStatus, intervalMs);
    }, 5000);
  })();
  