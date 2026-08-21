<style>
@page { size: A5 landscape; margin: 5mm; } 
body { 
    font-family: Arial, sans-serif; 
    font-size: 10.5px; 
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
    position:fixed; top:45%; left:0; width:100%;
    text-align:center;
    transform:rotate(-20deg); 
    font-size:80px; color:#999; opacity:0.1; 
    z-index: -1; 
}

/* Tabel Header Judul */
.header-table { width: 100%; margin-bottom: 6px; border: none; }
.header-table td { padding: 0; border: none; vertical-align: bottom; }

/* Tabel Produk */
.item-table { margin-top: 2px; width:100%; table-layout:fixed; }
.item-table th { border-top:1.5px solid #000; border-bottom:1.5px solid #000; padding: 3px 2px; }
.item-table td { border-bottom:1px dashed #ccc; padding: 2px; height: 16px; word-wrap:break-word; }

/* Tabel Kalkulasi & Footer */
.footer-container { margin-top: 5px; width: 100%; page-break-inside: avoid; }
.kalkulasi-table td { padding: 2.5px 0; }
</style>

<div class="container">
    <div class="watermark">ESTIMATE</div>

    <!-- HEADER: JUDUL (KIRI) & NOTE ESTIMASI (KANAN) -->
    <table class="header-table">
        <tr>
            <td style="width: 40%; vertical-align: bottom; padding-bottom: 14px;" class="text-left">
                <span style="font-size: 22px; font-weight: bold; text-decoration: underline;">SALES ESTIMATE</span>
            </td>
            <td style="width: 60%; vertical-align: bottom; padding-bottom: 2px;" class="text-right">
                <span style="color: red; font-weight: bold; font-size: 12px; font-style: italic; line-height: 1.3;">
                    * Dokumen ini hanya estimasi harga bukan transaksi<br>
                    * Belum termasuk biaya pengiriman.<br>
                    * Stock dan Kurs bersifat tidak mengikat
                </span>
            </td>
        </tr>
    </table>

    @php
        $items = $data_kalkulasi['items'];
        $maxRows = 14; 
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

    <!-- BAGIAN BAWAH: CATATAN (KIRI) & KALKULASI + TTD (KANAN) -->
    <table class="footer-container">
        <tr>
            <!-- SISI KIRI: TERBILANG & KURS SAJA -->
            <td width="65%" style="vertical-align: top; padding-right: 15px;">
                <div style="margin-bottom: 8px;">
                    Terbilang: <br>
                    <i># {{ $terbilang ?? '-' }} Rupiah #</i><br><br>
                    <b>* Kurs USD: {{ number_format($idr_rate, 2) }}</b>
                </div>
            </td>

            <!-- SISI KANAN: KALKULASI & TANDA TANGAN -->
            <td width="35%" style="vertical-align: top;">
                <table class="kalkulasi-table" style="width: 100%;">
                    <tr>
                        <td>Sub total:</td>
                        <td class="text-right">{{ number_format($data_kalkulasi['subtotal'], 2) }}</td>
                    </tr>
                    <tr>
                        <td>Disc %:</td>
                        <td class="text-right">{{ number_format($data_kalkulasi['disc_agen_idr'], 2) }}</td>
                    </tr>
                    <!-- GARIS BAWAH SEBELUM GRAND TOTAL -->
                    <tr>
                        <td style="font-weight: bold; border-top: 1.5px solid #000; padding-top: 4px;">Grand Total:</td>
                        <td class="text-right" style="font-weight: bold; border-top: 1.5px solid #000; padding-top: 4px;">
                            {{ number_format($data_kalkulasi['grand_total'], 2) }}
                        </td>
                    </tr>
                </table>

                <div class="text-center" style="margin-top: 25px;">
                    Hormat kami,
                    <br><br><br>
                    <b><u>{{ $so->createdBySuperuser() }}</u></b>
                </div>
            </td>
        </tr>
    </table>
</div>