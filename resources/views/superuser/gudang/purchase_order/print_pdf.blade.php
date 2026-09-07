<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style type="text/css">
  @page { size: A5 landscape; margin: 12mm 10mm 12mm 10mm; }
  body { color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 11px; margin: 0; }
  .title { text-align: center; font-size: 18px; font-weight: bold; text-decoration: underline; margin: 0 0 8px 0; }
  table.head { width: 100%; border-collapse: collapse; margin-bottom: 6px; font-size: 11px; }
  table.head td { padding: 1px 4px; vertical-align: top; }
  table.head .lbl { width: 110px; }
  table.head .sep { width: 10px; }
  table.head .val { font-weight: bold; }
  table.data { width: 100%; border-collapse: collapse; font-size: 11px; }
  table.data th, table.data td { border: 1px solid #000; padding: 3px 5px; }
  table.data th { text-align: center; font-weight: bold; }
  table.data td.c { text-align: center; }
  table.data td.r { text-align: right; }
  table.data tr.total td { font-weight: bold; border-top: 2px solid #000; }
  .note { margin-top: 8px; font-size: 11px; }
  .sign { margin-top: 34px; width: 100%; border-collapse: collapse; font-size: 11px; font-weight: bold; }
  .sign td { text-align: center; width: 50%; }
  .page-break { page-break-after: always; }
</style>
</head>
<body>
@php
  $limit = 12; // baris per halaman
  $totalRows = $rows->count();
  $totalPages = max(1, (int) ceil($totalRows / $limit));
  $poBrand = optional($po->brandLokal)->brand_name ?? '-';
  $no = 0;
@endphp

@for ($page = 0; $page < $totalPages; $page++)
  <div class="title">FORM PERMINTAAN BARANG</div>

  <table class="head">
    <tr>
      <td>
        <table class="head">
          <tr><td class="lbl">NO - PO</td><td class="sep">:</td><td class="val">{{ $po->code }}</td></tr>
          <tr><td class="lbl">TGL</td><td class="sep">:</td><td class="val">{{ \Carbon\Carbon::parse($po->created_at)->format('d-M-Y') }}</td></tr>
        </table>
      </td>
      <td>
        <table class="head">
          <tr><td class="lbl">ETD</td><td class="sep">:</td><td class="val">{{ $po->etd ? \Carbon\Carbon::parse($po->etd)->format('d-M-Y') : '-' }}</td></tr>
          <tr><td class="lbl">KIRIM GUDANG</td><td class="sep">:</td><td class="val">{{ optional($po->warehouse)->name ?? '-' }}</td></tr>
          <tr><td class="lbl">BRAND</td><td class="sep">:</td><td class="val">{{ $poBrand }}</td></tr>
        </table>
      </td>
    </tr>
  </table>

  <table class="data">
    <thead>
      <tr>
        <th style="width:4%;">NO</th>
        <th style="width:24%;">PRODUK</th>
        <th style="width:9%;">QTY (KG)</th>
        <th style="width:13%;">KEMASAN</th>
        <th style="width:25%;">NOTES</th>
        <th style="width:25%;">CUSTOMER</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($rows->slice($page * $limit, $limit)->values() as $row)
        @php
          $no++;
          $pack = $row->product_pack;
          $packName = ($pack && $pack->packaging) ? $pack->packaging->pack_name : '-';
        @endphp
        <tr>
          <td class="c">{{ $no }}</td>
          <td class="c">{{ optional($pack)->code ?? '' }} - {{ optional($pack)->name ?? '-' }}</td>
          <td class="c">{{ number_format((float) $row->quantity, 2, '.', '') }}</td>
          <td class="c">{{ $packName }}</td>
          <td class="c">{{ $row->note_produksi ?: '' }}</td>
          <td class="c">{{ $row->note_repack ?: '' }}</td>
        </tr>
      @endforeach
      @if ($page === $totalPages - 1)
        <tr class="total">
          <td colspan="2" class="r">TOTAL :</td>
          <td class="c">{{ number_format($total, 2, '.', '') }}</td>
          <td colspan="3"></td>
        </tr>
      @endif
    </tbody>
  </table>

  @if ($page === $totalPages - 1)
    <div class="note">NOTE : {{ $po->note ?: '' }}</div>

    <table class="sign">
      <tr>
        <td>MENGAJUKAN</td>
        <td>MENYETUJUI</td>
      </tr>
    </table>
  @endif

  @if ($page < $totalPages - 1)
    <div class="page-break"></div>
  @endif
@endfor
</body>
</html>
