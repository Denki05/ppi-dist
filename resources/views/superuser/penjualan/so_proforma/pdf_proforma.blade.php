<style>

@page {
    size: A5 landscape;
    margin: 2mm;
}

body{
    font-family: Arial, sans-serif;
    font-size:11px;
    color:#333;
}

.container{
    width:100%;
}

.text-center{
    text-align:center;
}

.text-right{
    text-align:right;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#e5e5e5;
    font-weight:bold;
}

th, td{
    padding:3px;
}

.watermark{
    position:fixed;
    top:50%;
    left:50%;
    transform:translate(-50%, -50%) rotate(-20deg);
    font-size:120px;
    color:#999;
    opacity:0.2;
}

.page-break{
    page-break-after:always;
}

.header-table td{
    padding:2px;
}

.item-table{
    margin-top:5px;
}

.item-table th{
    border-top:1px solid #000;
    border-bottom:1px solid #000;
}

.footer-table{
    margin-top:10px;
}

.note{
    font-size:9px;
    font-weight:bold;
}

.item-table{
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
}

.item-table th,
.item-table td{
    padding:3px;
    border-bottom:1px solid #ccc;
    word-wrap:break-word;
}

.text-center{
    text-align:center;
}

.text-right{
    text-align:right;
}

</style>


@php
$limit = 10;
$items = $so->items;
$totalItems = $items->count();
$totalPages = ceil($totalItems / $limit);
@endphp


@for ($page = 0; $page < $totalPages; $page++)

<div class="container">

<div class="watermark">COPY</div>

<h1 class="text-center"><u>PROFORMA INVOICE</u></h1>


<table class="header-table">

<tr>

<td width="55%">

<table>

<tr>
<td width="110">Pelanggan</td>
<td width="10">:</td>
<td>
    <b>
        @if ($so->existinng_customer == 0)
            {{ $so->member->name ?? '-' }} {{ $so->member->text_kota ?? '' }}
        @else
            {{ $so->customer_name ?? '-' }}
        @endif
    </b>
</td>
</tr>

<tr>
<td>UP</td>
<td>:</td>
<td>
    @if ($so->existinng_customer == 0)
        {{ $so->member->contact_person ?? '-' }}
    @else
        {{ $so->customer_contact_person ?? '-' }}
    @endif
</td>
</tr>

<tr>
<td>Alamat kirim</td>
<td>:</td>
<td>
    @if ($so->existinng_customer == 0)
        {{ $so->member->address ?? '-' }}
    @else
        {{ $so->customer_address ?? '-' }}
    @endif
</td>
</tr>

<tr>
<td>Telp</td>
<td>:</td>
<td>
    @if ($so->existinng_customer == 0)
        {{ $so->member->phone ?? '-' }}
    @else
        {{ $so->customer_phone ?? '-' }}
    @endif
</td>
</tr>

</table>

</td>


<td width="45%">

<table>

<tr>
<td width="110">No. Invoice</td>
<td width="10">:</td>
<td>{{ $so->code }}</td>
</tr>

<tr>
<td>Tanggal Terbit</td>
<td>:</td>
<td>{{ \Carbon\Carbon::parse($so->so_date)->format('d/m/Y') }}</td>
</tr>

<tr>
<td>Officer</td>
<td>:</td>
<td>{{ $so->createdBySuperuser() }}</td>
</tr>

<tr>
<td colspan="3" style="color:red; font-weight:bold;">
Masa Berlaku Hingga 
{{ \Carbon\Carbon::parse($so->so_date)->addDays(2)->format('d/m/Y') }}
(2 Hari Kerja)
</td>
</tr>

</table>

</td>

</tr>

</table>



<table class="item-table">

<thead>

<tr>
<th style="width:4%">No</th>
<th style="width:10%">Kode</th>
<th style="width:25%">Nama</th>
<th style="width:8%; text-align:center">Qty</th>
<th style="width:12%">Kemasan</th>
<th style="width:15%" class="text-right">Harga</th>
<th style="width:12%" class="text-right">Disc</th>
<th style="width:18%" class="text-right">Jumlah</th>
</tr>

</thead>

<tbody>

@foreach ($items->slice($page * $limit, $limit)->values() as $index => $item)
@php
    $harga = ($item->price * $so->so_idr_rate) * $item->qty;
    $disc = $item->disc_usd * $so->so_idr_rate;
@endphp
<tr>

<td class="text-center">
{{ ($page*$limit)+$index+1 }}
</td>

<td class="text-center">
{{ $item->productPack->code ?? '-' }}
</td>

<td class="text-center">
{{ $item->productPack->name ?? '-' }}
</td>

<td class="text-center">
{{ number_format($item->qty,2) }}
</td>

<td class="text-center">
{{ $item->packaging->pack_name ?? '-' }}
</td>

<td class="text-right">
{{ number_format($harga,2) }}
</td>

<td class="text-right">
{{ number_format($disc,2) }}
</td>

<td class="text-right">
{{ number_format($item->total_item,2) }}
</td>

</tr>

@endforeach

</tbody>

</table>



<table class="footer-table">

<tr>

<td width="60%">

Terbilang :  
<i># {{ $terbilang ?? '-' }} Rupiah #</i>

<br><br>

<b>
*Kurs USD : {{ number_format($so->so_idr_rate,2) }}
</b>

</td>


<td width="40%">

<table>

<tr>
<td class="text-right">Sub Total</td>
<td class="text-right">
{{ number_format($so->details_cost->purchase_total_idr,2) }}
</td>
</tr>

<tr>
<td class="text-right">
Disc persen ({{ $so->details_cost->discount_1_percent }})
</td>
<td class="text-right">
{{ number_format($so->details_cost->discount_1,2) }}
</td>
</tr>

<tr>
<td class="text-right">Ongkos Kirim</td>
<td class="text-right">
{{ number_format($so->details_cost->delivery_cost_idr,2) }}
</td>
</tr>

<tr>
<td class="text-right"><b>Grand Total</b></td>
<td class="text-right">
<b>{{ number_format($so->details_cost->grand_total_idr,2) }}</b>
</td>
</tr>

</table>

</td>

</tr>

</table>



<br>

<table>

<tr>

<td width="70%" class="note">

- Untuk Proforma customer tempo, kurs tidak mengikat (kurs sesuai
   saat pengiriman)<br>
- Proforma invoice hanya berlaku H+2 setelah proforma di terbitkan,<br>
  apabila pembayaran belum di terima sampai H+2 maka proforma
  diangap batal<br>
- Pembayaran Cheque/Wesel/BG dianggap sah bila telah diuangkan<br>
- Pembayaran TUNAI wajib disertai Tanda Terima Tunai resmi dari kantor<br>
- Pembayaran diluar ketentuan diatas tidak diakui<br>
- Barang yang sudah dibeli tidak dapat ditukar/dikembalikan<br>
- Stock bisa berubah sewaktu -waktu<br>

<b style="color:red">
<h2>Harap transfer melalui rekening perorangan</h2>
</b>

</td>


<td width="30%" class="text-center">

<br><br>

.........................

<br>

Hormat kami

</td>

</tr>

</table>

</div>


@if ($page < $totalPages-1)
<div class="page-break"></div>
@endif

@endfor