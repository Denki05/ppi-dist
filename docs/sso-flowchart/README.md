# Flowchart Login Google SSO

Dokumen alur proses pendaftaran, verifikasi, dan pemilihan modul.

## File hasil

| File | Keterangan |
|---|---|
| `sso-flowchart.pdf` | **Hasil final**, 2 halaman, optimal dilihat di layar/PDF |
| `halaman-1.png` | Halaman 1: pendaftaran, verifikasi, pemeriksaan status |
| `halaman-2.png` | Halaman 2: portal, pemilihan modul, akses langsung, logout |
| `page1.html` | Sumber diagram halaman 1 (Mermaid, dapat diedit) |
| `page2.html` | Sumber diagram halaman 2 (Mermaid, dapat diedit) |
| `mermaid.min.js` | Pustaka Mermaid lokal agar HTML dapat dibuka tanpa internet |
| `build.py` | Membuat ulang PNG final dan PDF dari hasil render |
| `page1.png`, `page2.png` | Hasil render mentah (bahan `build.py`) |

## Isi alur

**Halaman 1**
- 1A: Alur kandidat (login Google atau username/password), termasuk akun lama, domain email, dan pemeriksaan status.
- 1B: Proses samping admin (verifikasi kandidat dan penonaktifan akun).

**Halaman 2**
- 2A: Portal, pemeriksaan sesi, pemilihan modul, dan penerbitan token.
- 2B: User membuka URL modul secara langsung.
- 2C: Logout.

## Cara mengubah diagram

1. Edit isi Mermaid di `page1.html` atau `page2.html`.
2. Render ulang dengan Chrome headless:

```powershell
$chrome = "C:\Program Files (x86)\Google\Chrome\Application\chrome.exe"
$base = "C:\xampp\htdocs\ppi-dist\docs\sso-flowchart"

& $chrome --headless=new --disable-gpu --hide-scrollbars --window-size=2400,10000 `
  --virtual-time-budget=25000 --screenshot="$base\page1.png" `
  "file:///$($base -replace '\\','/')/page1.html"

& $chrome --headless=new --disable-gpu --hide-scrollbars --window-size=2400,9000 `
  --virtual-time-budget=25000 --screenshot="$base\page2.png" `
  "file:///$($base -replace '\\','/')/page2.html"
```

3. Buat ulang PNG final dan PDF:

```powershell
python "C:\xampp\htdocs\ppi-dist\docs\sso-flowchart\build.py"
```

## Catatan

- Warna mengikuti konvensi: hijau (mulai/selesai), biru (proses), kuning (keputusan), merah (penolakan).
- Ukuran halaman PDF 12 x 32 inci agar teks tetap besar saat diperbesar di layar.
- Untuk cetak kertas A4/A3, sebaiknya dipecah per bagian (1A, 1B, 2A, 2B, 2C).
- Modul APM masih perlu didefinisikan oleh manajemen sebelum dokumen dinyatakan final.
