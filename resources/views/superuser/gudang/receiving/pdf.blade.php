<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $receiving->code }}</title>
    @include('superuser.asset.css-pdf')
  </head>
  <body style="font-size: 0.7em;">
    <table>
      <tr>
        {{-- <td width="20%"><img width="150px" src="superuser_assets/media/master/company/IAX3P14R71QVM4UPNK.jpg"></td> --}}
        <td width="20%"><img width="150px" src="{{ $company->logo_url ?? img_holder() }}"></td>
        <td width="50%">
            <table>
                <tr>
                  <td class="text-bold">{{ $company->name ?? '' }}</td>
                </tr>
                <tr>
                  <td>{{ $company->address . ', ' . $company->text_provinsi . ', ' . $company->text_kota }}</td>
                </tr>
                <tr>
                  <td>No Telp : {{ $company->phone ?? '-' }} &nbsp;&nbsp;&nbsp;&nbsp; Email : {{ $company->email ?? '-' }}</td>
                </tr>
                <tr>
                  <td>Note : {{ $receiving->description ?? '-' }}</td>
                </tr>
            </table>
        </td>
        <td width="30%">
          <table class="text-center">
            <tr>
              <td class="text-bold" style="font-size: 5em; text-decoration: underline;">RI</td>
            </tr>
            <tr>
              <td class="text-bold">{{ $receiving->code }} | {{ Carbon\Carbon::parse($receiving->created_at)->format('j-m-Y') }}</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
    <table class="mt-10" style="border-collapse: collapse;">
        <tr class="text-bold text-center" style="background-color: yellow;">
            <td class="border-full">PPB No</td>
            <td class="border-full">SKU</td>
            <td class="border-full">PPB Qty</td>
            <td class="border-full">RI Qty</td>
            <td class="border-full">Colly Qty</td>
            <td class="border-full">Unit Price</td>
            <td class="border-full">Domestic Cost</td>
            <td class="border-full">Komisi</td>
            <td class="border-full">Total Price (RMB)</td>
            <td class="border-full">Kurs
            <td class="border-full">Total Price (IDR)</td>
            <td class="border-full">Sea Freight</td>
            <td class="border-full">Grand Total</td>
            <td class="border-full">HPP</td>
            <td class="border-full">No Container</td>
            <td class="border-full">Notes</td>
            </td>
        </tr>
        @php
            $price_rmb = 0;
            $price_idr = 0;
            $total_ri = 0;
            $total_domestic = 0;
            $total_komisi = 0;
            $total_sea = 0;
            $grand_total = 0;
        @endphp
        @foreach ($receiving->details as $detail)
          @php
            $price_rmb += $detail->ppb_detail->total_price_rmb * $detail->total_quantity_ri / $detail->ppb_detail->quantity;
            $price_idr += $detail->ppb_detail->total_price_idr * $detail->total_quantity_ri / $detail->ppb_detail->quantity;
            $total_ri += $detail->total_quantity_ri;
            $total_domestic += $detail->ppb_detail->local_freight_cost * $detail->total_quantity_ri / $detail->ppb_detail->quantity;
            $total_komisi += $detail->ppb_detail->komisi * $detail->total_quantity_ri / $detail->ppb_detail->quantity;
            $total_sea += $detail->delivery_cost;
            $grand_total += $detail->ppb_detail->total_price_idr * $detail->total_quantity_ri / $detail->ppb_detail->quantity + $detail->delivery_cost;
            
            $total_rmb = ($detail->ppb_detail->total_price_rmb * $detail->total_quantity_ri) / $detail->ppb_detail->quantity;
            $tax = $total_rmb * $detail->purchase_order->tax / 100;
          @endphp
          <tr class="text-center">
            <td class="border-full" class="1">{{ $detail->purchase_order->code }}</td>
            <td class="border-full" class="2">{{ $detail->product->code }}</td>
            <td class="border-full" class="3">{{ $detail->quantity }}</td>
            <td class="border-full" class="4">{{ $detail->total_quantity_ri }}</td>
            <td class="border-full" class="5">{{ $detail->total_quantity_colly }}</td>
            <td class="border-full" class="6">{{ $detail->ppb_detail->unit_price }}</td>
            <td class="border-full" class="7">{{ number_format(($detail->ppb_detail->local_freight_cost * $detail->total_quantity_ri) / $detail->ppb_detail->quantity,2,",",".") }}</td>
            <td class="border-full" class="8">{{ number_format(($detail->ppb_detail->komisi * $detail->total_quantity_ri) / $detail->ppb_detail->quantity,2,",",".") }}</td>
            <td class="border-full" class="10">{{ number_format( $total_rmb,2,",",".") }}</td>
            <td class="border-full" class="11">{{ $detail->ppb_detail->kurs }}</td>
            <td class="border-full" class="12">{{ number_format(($detail->ppb_detail->total_price_idr * $detail->total_quantity_ri) / $detail->ppb_detail->quantity,2,",",".") }}</td>
            <td class="border-full" class="13">{{ number_format($detail->delivery_cost,2,",",".") }}</td>
            <td class="border-full" class="14">{{ number_format((($detail->ppb_detail->total_price_idr * $detail->total_quantity_ri) / $detail->ppb_detail->quantity) + $detail->delivery_cost,2,",",".") }}</td>
            @if($detail->total_quantity_ri == 0 || is_null($detail->total_quantity_ri))
                <td class="border-full" class="15">0</td>
            @else
                <td class="border-full" class="15">{{ number_format(($detail->ppb_detail->total_price_idr / $detail->ppb_detail->quantity) + ($detail->delivery_cost / $detail->total_quantity_ri),2,",",".") }}</td>
            @endif
            <td class="border-full" class="16">{{ $detail->no_container }}</td>
            <td class="border-full" class="17">{{ $detail->description }}</td>
          </tr>
        @endforeach
          <tr class="text-bold text-center">
            <td class="border-full" class="1">TOTAL</td>
            <td class="border-full" class="2"></td>
            <td class="border-full" class="3"></td>
            <td class="border-full" class="4">{{ $total_ri }}</td>
            <td class="border-full" class="5"></td>
            <td class="border-full" class="6"></td>
            <td class="border-full" class="7">{{ number_format($total_domestic ,2,",",".") }}</td>
            <td class="border-full" class="8">{{ number_format($total_komisi ,2,",",".") }}</td>
            <td class="border-full" class="9">{{ number_format($price_rmb ,2,",",".") }}</td>
            <td class="border-full" class="10"></td>
            <td class="border-full" class="11">{{ number_format($price_idr ,2,",",".") }}</td>
            <td class="border-full" class="12">{{ number_format($total_sea ,2,",",".") }}</td>
            <td class="border-full" class="13">{{ number_format($grand_total ,2,",",".") }}</td>
            <td class="border-full" class="14"></td>
            <td class="border-full" class="15"></td>
            <td class="border-full" class="16"></td>
        </tr>
    </table>

    <table class="mt-25">
        <tr>
            <td width="40%">Note : {{ $receiving->description ?? '-' }}</td>
            <td  width="22.5%" class="text-center">Creator</td>
            <td  width="22.5%" class="text-center">Acknowledge</td>
            <td width="15%"></td>
        </tr>
    </table>
    <table class="mt-50">
        <tr>
            <td width="40%"></td>
            <td  width="22.5%" class="text-center">{{ $receiving->createdBySuperuser() }}</td>
            <td  width="22.5%" class="text-center">{{ $receiving->accBySuperuser() }}</td>
            <td width="15%"></td>
        </tr>
    </table>
        <script type="text/php">
        if (isset($pdf)) {
            $x = 400;
            $y = 560;
            $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
            $font = null;
            $size = 10;
            $color = array(0,0,0);
            $word_space = 0.0;  //  default
            $char_space = 0.0;  //  default
            $angle = 0.0;   //  default
            $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
        }
    </script>
  </body>
</html>