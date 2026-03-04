<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $receiving->code }}</title>
    @include('superuser.asset.css-pdf')
    <style>
      .page-break {
          page-break-after: always;
      }
      </style>
  </head>
  <body style="font-size: 0.8em;">
    @php
    $i = 1; 
    @endphp
    @foreach ($receiving->details as $detail)
      @php
      $i++;    
      $c = 1;
      @endphp
        
        @foreach ($detail->collys as $colly)
          @if ($colly->is_reject)
              @continue
          @endif
          @php
          $c++;    
          @endphp
          <table class="text-center" style="margin-top: 0px;margin-bottom: -20px;table-layout: fixed;">
            <tr>
              <td>
                <img src="data:image/png;base64,{{DNS1D::getBarcodePNG( $colly->code, 'C128',1.8,48,array(1,1,1))}}" alt="barcode"/>
                <span style="font-weight: bold;">{{ $colly->code }}</span>
              </td>
            </tr>
            <tr>
              <td style="overflow: hidden; white-space: nowrap;font-size: {{ strlen($colly->receiving_detail->product->code) > 18 ? '1.2em;': '1.5em;'  }}" class="text-bold">
                {{ $colly->receiving_detail->product->code }}
              </td>
            </tr>
            <tr>
              <td style="font-size: 0.9em;">
                {{ $colly->receiving_detail->receiving->code }} <br> {{ $colly->receiving_detail->receiving->pbm_date ? Carbon\Carbon::parse($colly->receiving_detail->receiving->pbm_date)->format('d/m/Y') : '-' }}
              </td>
            </tr>
            <tr>
              <td style="font-size: 1em;" class="text-bold">
                Qty : {{ $colly->quantity_ri }}
              </td>
            </tr>

          </table>

          @if ($i <= count($receiving->details) OR $c <= count($detail->collys))
            <div class="page-break"></div>
          @endif
        @endforeach

    @endforeach
    
  </body>
</html>
