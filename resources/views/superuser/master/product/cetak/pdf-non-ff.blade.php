<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>
  {{ $jenisReportText . ' ' . \Carbon\Carbon::now()->isoFormat('MMMM - Y') }}
  </title>
  <style type="text/css">
    body{
      color: #333;
      font-size: 12px;
    }

    .column-float {
      float: left;
      width: 50%;
    }
    .row-float:after {
      content: "";
      display: block;
      clear: both;
    }

    /*Table*/
    table.table-data, .table-data td, .table-data th {
      /*border: 1px solid #333;*/
    }
    table.table-data th{
      font-size: 12px;
    }

    table.table-data {
      width: 100%;
      border-collapse: collapse;
      color: #333;
    }
    table.table-data tbody{
      text-align: left;
      font-size: 12px;
    }

    @page{
      margin: 75px 25px 25px 25px;
    }

    .page-break {
      page-break-inside:avoid; page-break-after:always;
    }

    #header {
      position: fixed;
      top: -30px;
      left: 0px;
      right: 0px;
      height: 50px;

      color: #aaa;
      font-size: 11px;
    }

    #footer {
      position: fixed; 
      bottom: -60px; 
      left: 0px; 
      right: 0px;
      height: 50px; 

      color: #aaa;
      font-size: 11px;
    }

    /*Header*/
    /*#header {
      position: fixed;
      left: 0;
      right: 0;
      color: #aaa;
      font-size: 11px;
      top: 0;
    }*/

    /*Footer*/
    
    .page-number:before {
      content: "{{ $jenisReportText }} | Page " counter(page);
    }

    .text-center{
      text-align: center;
    }

    .text-right{
      text-align: right;
    }
    
    .mt-25 {
      margin-top: 25px;
    }
  </style>
</head>

<body style="font-size: 0.7em;">

  <img style="max-width: 100%" src="{{ $banner }}" />
  <table class="mt-25" style="width:100%">
    <tr>
      <td style="width: 33.33%"></td>
      <td style="text-align: center; width: 33.33%">{{ \Carbon\Carbon::now()->isoFormat('MMMM - Y') }}</td>
      <td style="text-align: right; width: 33.33%">{{ $typeText }}</td>
    </tr>
  </table>

  
  <table style="width: 100%;" class="table-data">
    @php
    if ('tidak' == $groupBy) {
    @endphp
    <thead>
      <tr>
        <th class="text-center border-full">{{ '' != $typeText ? 'Kode' : 'Group - Kode' }}</th>
        <th class="text-center border-full">Nama</th>
        <th class="text-center border-full">Searah</th>
        @php
          if ($request->jenisReport == 'pl') {
        @endphp
        <th class="text-center border-full" style="text-align: right;">Per KG</th>
        @php
          }
        @endphp
      </tr>
    </thead>
    @php
    }
    @endphp
    <tbody>
      <div class="page-break"></div>
      <tr><td colspan="{{ ($request->jenisReport == 'pl') ? 4 : 3 }}"></td></tr>
      @php
        $prevRow = null;
        $maxRow = 27;
        $nextMaxRow = 40;
        $rowCount = 0;
        $minNewDataGroup = 3;
        $pagebreakdarisana = 0;

        $prevType = null;
      @endphp

      @foreach($products as $index => $row)
      @php
        if ('tidak' !== $groupBy && $rowCount >= $maxRow) {
          $maxRow = $nextMaxRow;
      @endphp
          <div class="page-break"></div>
          <tr><td colspan="{{ ($request->jenisReport == 'pl') ? 4 : 3 }}"></td></tr>
      @php
          $rowCount = 0;
        }
        if ('tidak' != $groupBy && ($prevRow == null || $prevRow != $row->group_value)) {
          $nextGroupCount = 0;
          foreach($products as $cekKedepan) {
            if ($row->group_value == $cekKedepan->group_value) {
              $nextGroupCount++;
            }
          }

          if (($maxRow - $rowCount) < $minNewDataGroup && $nextGroupCount > $minNewDataGroup) {
            $pagebreakdarisana = 1;
            $rowCount = 0;
            $maxRow = $nextMaxRow;
      @endphp
            <div class="page-break"></div>
            <tr><td colspan="{{ ($request->jenisReport == 'pl') ? 5 : 4 }}"></td></tr>
      @php
          }
          $rowCount++;

          if ($prevType !== null && $prevType !== $row->type) {
      @endphp
        <div class="page-break"></div>
        <tr><td colspan="{{ ($request->jenisReport == 'pl') ? 5 : 4 }}"></td></tr>
      @php
          }

          if ($prevType == null || $prevType !== $row->type) {
      @endphp
        <tr>
          <td colspan="{{ ($request->jenisReport == 'pl') ? 5 : 4 }}">{{ $row->type }}</td>
        </tr>
      @php
            $prevType = $row->type;
          }
      @endphp
        <tr>
          <td class="text-center border-full" style="background-color: #ccc"><strong>{{ ('searah' == $groupBy) ? $row->group_value : '' }}</strong></td>
          <td class="text-center border-full" style="background-color: #ccc">Searah</td>
          <td class="text-center border-full" style="background-color: #ccc">Kode</td>
          <td class="text-center border-full" style="background-color: #ccc">Nama</td>
          @php
            if ($request->jenisReport == 'pl') {
          @endphp
          <td class="text-center border-full" style="text-align: right; background-color: #ccc">Per KG</td>
          @php
            }
          @endphp
        </tr>
      @php
        }
      @endphp
      <tr>
        <td></td>
        <td></td>
        <td>@php echo $row->kode @endphp</td>
        <td><strong>{{ $row->name }}</strong></td>
        @php
          if ($request->jenisReport == 'pl') {
        @endphp
        <td style="text-align: right;">$ {{ $row->selling_price }}</td>
        @php
          }
        @endphp
      </tr>
      @php
        if (strpos($row->kode, '<br />') !== false || strpos($row->searah, '<br />') !== false) {
          $rowCount++;
        }
        $prevRow = $row->group_value;
        $rowCount++;
        $pagebreakdarisana = 0;
      @endphp
      @endforeach
    </tbody>
  </table>

  <table class="mt-25" style="background-color: #ccc; width: 100%">
    <tr>
      <td>Tidak termasuk pengiriman/ongkos kirim</td>
    </tr>
    <tr>
      <td>Kurs tidak mengikat</td>
    </tr>
  </table>

  <!--<div id="header">
    <div class="row-float">
      <div class="column-float">
        <div class="page-number"></div>
      </div>
    </div>
  </div>

  <div id="footer">
    <div class="row-float">
      <div class="column-float">
        <div class="page-number"></div>
      </div>
    </div>
  </div>-->
  <script type="text/php">
    if (isset($pdf)) {
        $text = "Page {PAGE_NUM} / {PAGE_COUNT}";
        $size = 8;
        $font = $fontMetrics->getFont("Verdana");
        $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
        $yTop = 20;
        $yBot = $pdf->get_height() - 35;

        $pdf->page_text(20, $yTop, "{{ $jenisReportText }}", $font, $size, array(.16, .16, .16));
        $pdf->page_text(20, $yBot, "{{ $jenisReportText }}", $font, $size, array(.16, .16, .16));

        $xTopMid = ($pdf->get_width() - ($width) / 2) / 2;
        $pdf->page_text($xTopMid, $yTop, "{{ \Carbon\Carbon::now()->isoFormat('MMMM - Y') }}", $font, $size, array(.16, .16, .16));
        $pdf->page_text($xTopMid, $yBot, "{{ \Carbon\Carbon::now()->isoFormat('MMMM - Y') }}", $font, $size, array(.16, .16, .16));

        $xTopRight = $pdf->get_width() - ($fontMetrics->get_text_width($text, $font, $size) / 2) + 10;
        $pdf->page_text($xTopRight, $yTop, $text, $font, $size, array(.16, .16, .16));
        $pdf->page_text($xTopRight, $yBot, $text, $font, $size, array(.16, .16, .16));

    }
</script>
</body>
</html>
