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
    position:fixed; top:45%; left:0; width:100%;
    text-align:center;
    transform:rotate(-20deg); 
    font-size:80px; color:#999; opacity:0.1; 
    z-index: -1; 
}

/* Tabel Header Judul */
.header-table { width: 100%; margin-bottom: 4px; border: none; }
.header-table td { padding: 0; border: none; vertical-align: bottom; }

/* Tabel Produk */
.item-table { margin-top: 2px; width:100%; table-layout:fixed; }
.item-table th { border-top:1.5px solid #000; border-bottom:1.5px solid #000; padding: 2px 2px; font-size: 9px; }
.item-table td { border-bottom:1px dashed #ccc; padding: 1.5px 2px; height: 14px; word-wrap:break-word; font-size: 9.5px; }

/* Tabel Kalkulasi & Footer */
.footer-container { margin-top: 4px; width: 100%; page-break-inside: avoid; }
.kalkulasi-table td { padding: 2px 0; font-size: 9.5px; }
</style>

<div class="container">
    <div class="watermark">ESTIMATE</div>

    <!-- HEADER: JUDUL (KIRI) & NOTE ESTIMASI (KANAN) -->
    <table class="header-table">
        <tr>
            <td style="width: 45%; vertical-align: bottom; padding-bottom: 0;" class="text-left">
                <div style="padding-bottom: 2px;">
                    <span style="font-size: 20px; font-weight: bold; text-decoration: underline;">SALES ESTIMATE</span>
                </div>
                <div style="font-size: 9.5px; color: #444; line-height: 1.5;">
                    @if(!empty($so->estimate_code))
                    <b>Kode Est.:</b> {{ $so->estimate_code }}<br>
                    @endif
                    <b>Customer:</b> {{ optional($so->member)->name ?? '-' }} {{ optional($so->member)->text_kota ?? '' }}<br>
                    <b>Tanggal SO:</b> {{ $so->so_date ? \Carbon\Carbon::parse($so->so_date)->format('d/m/Y') : ($so->created_at ? \Carbon\Carbon::parse($so->created_at)->format('d/m/Y') : '-') }}<br>
                    <b>AO / Sales:</b> {{ $so->createdBySuperuser() }}
                </div>
            </td>
            <td style="width: 55%; vertical-align: bottom; padding-bottom: 0;" class="text-right">
                <span style="color: red; font-weight: bold; font-size: 12px; font-style: italic; line-height: 1.6;">
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

    <!-- BARIS 1: Terbilang + Kurs (kiri) | Kalkulasi (kanan) -->
    <table style="width: 100%; margin-top: 4px; font-size: 9px; border-collapse: collapse;">
        <tr>
            <!-- KIRI: Terbilang + Kurs -->
            <td style="width: 60%; vertical-align: top; padding-right: 10px;">
                <b>Terbilang:</b><br>
                <i># {{ $terbilang ?? '-' }} Rupiah #</i><br>
                <b>* Kurs USD: {{ number_format($idr_rate, 2) }}</b>
            </td>
            <!-- KANAN: Kalkulasi -->
            <td style="width: 40%; vertical-align: top;">
                <table style="width: 100%; font-size: 9px; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 1px 0;">Sub total:</td>
                        <td class="text-right" style="padding: 1px 0;">{{ number_format($data_kalkulasi['subtotal'], 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0;">Disc %:</td>
                        <td class="text-right" style="padding: 1px 0;">{{ number_format($data_kalkulasi['disc_agen_idr'], 2) }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; border-top: 1.5px solid #000; padding-top: 2px;">Grand Total:</td>
                        <td class="text-right" style="font-weight: bold; border-top: 1.5px solid #000; padding-top: 2px;">
                            {{ number_format($data_kalkulasi['grand_total'], 2) }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- BARIS 2: Tanda Tangan (paling bawah) -->
    <div style="width: 100%; text-align: right; margin-top: 20px; padding-right: 10px; font-size: 9px;">
        Hormat kami,<br><br><br>
        <b><u>{{ $so->createdBySuperuser() }}</u></b>
    </div>
</div>