{{-- Tombol Batalkan (Prospek) — dipakai bila proforma berasal dari mutasi prospek.
     Variabel: $pgId (id pengajuan). Mengarah ke halaman pengajuan tempat
     form pembatalan resmi berada (log + flag + kembali ke prospek + notif AO). --}}
<a href="{{ route('superuser.penjualan.pengajuan_proforma.show', $pgId) }}"
   class="btn btn-sm btn-circle btn-alt-warning"
   title="Batalkan (Prospek) — proforma dari mutasi, wajib lewat jalur resmi">
    <i class="fa fa-ban"></i>
</a>
