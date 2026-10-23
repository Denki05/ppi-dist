# Flowchart Login Google SSO

Dokumen alur pendaftaran, verifikasi, dan login pengguna.

## Struktur alur

| Bagian | Isi | Keputusan |
|---|---|---|
| **A1** | Cara pendaftaran melalui akun Google | 2 |
| **A2** | Verifikasi oleh Administrator/Developer dan penonaktifan akun | 1 |
| **A3** | Cara pengguna login melalui Google atau username/password | 2 |

Setiap bagian dibuat sederhana: maksimal dua keputusan, istilah seragam, dan tanpa
rincian teknis yang tidak diperlukan oleh manajemen.

## File hasil

| File | Keterangan |
|---|---|
| `sso-flowchart.pdf` | **Hasil final**, 3 halaman ukuran A3, siap dibagikan |
| `A1-Pendaftaran.png` | Diagram A1 |
| `A2-Verifikasi.png` | Diagram A2 |
| `A3-Login.png` | Diagram A3 |
| `a1-pendaftaran.html` | Sumber diagram A1 (Mermaid, dapat diedit) |
| `a2-verifikasi.html` | Sumber diagram A2 (Mermaid, dapat diedit) |
| `a3-login.html` | Sumber diagram A3 (Mermaid, dapat diedit) |
| `flowchart.css` | Gaya tampilan seluruh diagram |
| `flowchart.js` | Konfigurasi Mermaid |
| `mermaid.min.js` | Pustaka Mermaid lokal agar HTML dapat dibuka tanpa internet |
| `build.py` | Membuat ulang PNG final dan PDF |
| `render-a1.png`, `render-a2.png`, `render-a3.png` | Hasil render mentah untuk `build.py` |

## Cara mengubah diagram

1. Edit isi Mermaid pada file HTML yang sesuai.
2. Render ulang dengan Chrome headless:

```powershell
$chrome = "C:\Program Files (x86)\Google\Chrome\Application\chrome.exe"
$base = "C:\xampp\htdocs\ppi-dist\docs\sso-flowchart"
$urlBase = "file:///" + ($base -replace '\\','/')

$items = @(
  @{ h = "a1-pendaftaran"; o = "render-a1" },
  @{ h = "a2-verifikasi"; o = "render-a2" },
  @{ h = "a3-login";       o = "render-a3" }
)

foreach ($item in $items) {
  $profile = Join-Path $env:TEMP ("opencode\chrome-" + $item.o + "-" + (Get-Random))
  & $chrome --headless=new --disable-gpu --no-sandbox --hide-scrollbars `
    --user-data-dir="$profile" --window-size=1600,9000 `
    --virtual-time-budget=25000 `
    --screenshot="$base\$($item.o).png" "$urlBase/$($item.h).html"
  Start-Sleep -Seconds 2
}
```

3. Buat ulang PNG final dan PDF:

```powershell
python "C:\xampp\htdocs\ppi-dist\docs\sso-flowchart\build.py"
```

## Catatan

- Warna mengikuti konvensi: hijau (mulai/selesai), biru (proses), kuning (keputusan), merah (penolakan).
- PDF menggunakan ukuran A3 portrait agar teks tetap terbaca saat dicetak.
- Pendaftaran mandiri hanya melalui akun Google. Domain yang diizinkan: gmail.com, googlemail.com, lssoft88.xyz, dan lsfragrance.id.
- Paket jabatan yang tersedia: SM dan ASM. Modul akses: MIS, AO, APM, TRS, dan DSG.
- Status modul APM masih menunggu definisi dari manajemen.
