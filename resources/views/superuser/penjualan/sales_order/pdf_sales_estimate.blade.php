<style>
@page { size: A5 landscape; margin: 3mm 5mm; } 
body { 
    font-family: Arial, sans-serif; 
    font-size: 12px; 
    color: #333; 
    margin: 0;
    padding: 0;
}
.container { width:100%; }
.text-center { text-align:center; }
.text-right { text-align:right; }
.text-left { text-align:left; }
table { width:100%; border-collapse:collapse; }
th { background:#e5e5e5; font-weight:bold; }

.watermark { 
    position:fixed; top:40%; left:0; width:100%;
    text-align:center;
    transform:rotate(-20deg); 
    font-size:75px; color:#999; opacity:0.1; 
    z-index: -1; 
}

/* Tabel Header Judul */
.header-table { width: 100%; margin-bottom: 2px; border: none; }
.header-table td { padding: 0; border: none; }

/* Tabel Produk */
.item-table { margin-top: 2px; width:100%; table-layout:fixed; }
.item-table th { border-top:1.5px solid #000; border-bottom:1.5px solid #000; padding: 2.5px 2px; font-size: 11px; }
/* Tinggi baris dikunci di 14px agar 12 baris muat sempurna di 1 lembar A5 */
.item-table td { border-bottom:1px dashed #ccc; padding: 1.5px 2px; height: 14px; word-wrap:break-word; font-size: 11px; }

/* Menghindari pemotongan halaman di tengah blok */
.footer-container { page-break-inside: avoid; margin-top: 4px; width: 100%; }
</style>

<div class="container">
    <div class="watermark">ESTIMATE</div>

    <!-- HEADER: DIBAGI 2 BARIS AGAR PAGE NUMBER SEJAJAR DENGAN JUDUL -->
    <table class="header-table">
        <!-- BARIS 1: JUDUL & PAGE NUMBER -->
        <tr>
            <td style="width: 45%; vertical-align: top;" class="text-left">
                <div style="padding-bottom: 2px;">
                    <span style="font-size: 22px; font-weight: bold; text-decoration: underline;">SALES ESTIMATE</span>
                </div>
            </td>
            <td style="width: 55%; vertical-align: top;" class="text-right">
                <!-- Page Number di kanan sendiri, font kecil dan elegan -->
                <div style="font-size: 10px; color: #666; font-weight: bold; padding-top: 4px; font-style: italic;">
                    Page 1 of 1
                </div>
            </td>
        </tr>
        <!-- BARIS 2: INFO CUSTOMER (Format Kolom) & NOTE POINT A -->
        <tr>
            <td style="width: 45%; vertical-align: bottom; padding-top: 4px;" class="text-left">
                <!-- FORMAT KOLOM BARU: Sejajar dan rapi -->
                <table style="width: 100%; font-size: 11.5px; color: #444; line-height: 1.4; border-collapse: collapse; border: none;">
                    
                    <tr>
                        <td style="width: 25%; font-weight: bold; padding: 1px 0; border: none;">Kode Est.</td>
                        <td style="width: 75%; padding: 1px 0; border: none;">: {{ $so->estimate_code ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 25%; font-weight: bold; padding: 1px 0; border: none;">Customer</td>
                        <td style="width: 75%; padding: 1px 0; border: none;">: {{ optional($so->member)->name ?? '-' }} {{ optional($so->member)->text_kota ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 1px 0; border: none;">Tanggal SO</td>
                        <td style="padding: 1px 0; border: none;">: {{ $so->so_date ? \Carbon\Carbon::parse($so->so_date)->format('d/m/Y') : ($so->created_at ? \Carbon\Carbon::parse($so->created_at)->format('d/m/Y') : '-') }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 1px 0; border: none;">AO / Sales</td>
                        <td style="padding: 1px 0; border: none;">: {{ $so->createdBySuperuser() }}</td>
                    </tr>
                </table>
            </td>
            <!-- POINT A: Padding bottom dinaikkan jadi 16px agar tidak menempel tabel -->
            <td style="width: 55%; vertical-align: bottom; padding-bottom: 16px;" class="text-right">
                <div style="color: red; font-weight: bold; font-size: 12px; font-style: italic; line-height: 1.4; text-align: right;">
                    1. Pastikan pesanan anda sesuai.<br>
                    2. Pesanan tidak dapat dirubah setelah proforma invoice diterbitkan.
                </div>
            </td>
        </tr>
    </table>

    @php
        $items = $data_kalkulasi['items'];
        /* Dikembalikan menjadi 12 baris */
        $maxRows = count($items) > 12 ? count($items) : 12; 
    @endphp

    <!-- TABEL PRODUK -->
    <table class="item-table">
        <thead>
            <tr>
                <th style="width:4%; text-align:center;">No</th>
                <th style="width:22%;">Produk</th>
                <th style="width:5%; text-align:center;">Qty</th>
                <th style="width:10%; text-align:center;">Kemasan</th>
                <th style="width:12%;" class="text-right">Harga</th>
                <th style="width:10%;" class="text-right">Disc</th>
                <th style="width:11%;" class="text-right">Netto</th>
                <th style="width:13%;" class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @for ($i = 0; $i < $maxRows; $i++)
                @if(isset($items[$i]))
                    @php 
                        $item = $items[$i]; 
                        $netto = $item['price_idr'] - $item['disc_idr']; 
                    @endphp
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td class="text-center">{{ $item['code']}} - <b>{{ $item['name']}}</b></td>
                        <td class="text-center">{{ number_format($item['qty'], 2) }}</td>
                        <td class="text-center">{{ $item['packaging'] }}</td>
                        <td class="text-right">{{ number_format($item['price_idr'], 2) }}</td>
                        <td class="text-right">{{ number_format($item['disc_idr'], 2) }}</td>
                        <td class="text-right">{{ number_format($netto, 2) }}</td>
                        <td class="text-right">{{ number_format($item['total_idr'], 2) }}</td>
                    </tr>
                @else
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>&nbsp;</td>
                        <td class="text-center">&nbsp;</td>
                        <td class="text-center">&nbsp;</td>
                        <td class="text-right">&nbsp;</td>
                        <td class="text-right">&nbsp;</td>
                        <td class="text-right">&nbsp;</td>
                        <td class="text-right">&nbsp;</td>
                    </tr>
                @endif
            @endfor
        </tbody>
    </table>

    <!-- BARIS BAWAH: Terbilang + Catatan (kiri) | Kalkulasi + TTD (kanan) -->
    <table class="footer-container" style="font-size: 11.5px; border-collapse: collapse;">
        <tr>
            <!-- KIRI: Terbilang + Kurs + Catatan -->
            <td style="width: 60%; vertical-align: top; padding-right: 5px;">
                <div style="line-height: 1.4;">
                    <b>Terbilang:</b><br>
                    <i># {{ $terbilang ?? '-' }} Rupiah #</i><br>
                    <b>* Kurs USD: {{ number_format($idr_rate, 2) }}</b>
                </div>
                
                <!-- POINT B -->
                <div style="margin-top: 42px; color: red; font-weight: bold; line-height: 1.3;">
                    <table style="width: 100%; border: none; margin-top: 0; font-size: 12px;">
                        <tr>
                            <td style="vertical-align: top; width: 16px; padding: 0;">1.</td>
                            <td style="vertical-align: top; padding: 0;">Jangan melakukan pembayaran apapun atas dasar dokumen ini.</td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top; width: 16px; padding: 2px 0 0 0;">2.</td>
                            <td style="vertical-align: top; padding: 2px 0 0 0;">Pembayaran dilakukan setelah proforma invoice diterbitkan.</td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top; width: 16px; padding: 2px 0 0 0;">3.</td>
                            <td style="vertical-align: top; padding: 2px 0 0 0;">Penawaran ini bersifat terbuka <br>(Stock &amp; Kurs Tidak Mengikat, Belum Termasuk Ongkir).</td>
                        </tr>
                    </table>
                </div>
            </td>
            
            <!-- KANAN: Kalkulasi + Tanda Tangan -->
            <td style="width: 40%; vertical-align: top;">
                <!-- Kalkulasi -->
                <table style="width: 100%; font-size: 11.5px; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 1.5px 0;">Sub total:</td>
                        <td class="text-right" style="padding: 1.5px 0;">{{ number_format($data_kalkulasi['subtotal'], 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 1.5px 0;">Disc %:</td>
                        <td class="text-right" style="padding: 1.5px 0;">{{ number_format($data_kalkulasi['disc_agen_idr'], 2) }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; border-top: 1.5px solid #000; padding-top: 4px;">Grand Total:</td>
                        <td class="text-right" style="font-weight: bold; border-top: 1.5px solid #000; padding-top: 4px;">
                            {{ number_format($data_kalkulasi['grand_total'], 2) }}
                        </td>
                    </tr>
                </table>
                
                <!-- Tanda Tangan -->
                <div style="width: 100%; text-align: right; margin-top: 42px; padding-right: 5px;">
                    Hormat kami,<br><br>
                    <b><u>{{ $so->createdBySuperuser() }}</u></b>
                </div>
            </td>
        </tr>
    </table>
</div>