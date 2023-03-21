<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $delivery_order->code }}</title>
    @include('superuser.asset.css-pdf')
  </head>
  <body style="font-size: 0.8em;">
    <table class="">
      <tr>
        <td class="text-center">PACKING PLAN</td>
      </tr>
    </table>
    <table class="mt-10">
      <tr>
        <td width="32%">Warehouse</td>
        <td>: {{ $warehouse }}</td>
      </tr>
      <tr>
        <td width="32%">Store Name</td>
        <td>: {{ $store_name }}</td>
      </tr>
      <tr>
        <td width="32%">Ekspedisi</td>
        <td>: {{ $ekspedisi_marketplace }}</td>
      </tr>
      <tr>
        <td width="32%">No Pack</td>
        <td>: {{ $delivery_order->code }}</td>
      </tr>
      <tr>
        <td width="32%">Create</td>
        <td>: {{ $delivery_order->createdBySuperuser() }} / {{ $delivery_order->created_at }}</td>
      </tr>
      <tr>
        <td width="32%">Total Order</td>
        <td>: {{ $total_so }}</td>
      </tr>
      <tr>
        <td width="32%">Total Product</td>
        <td>: {{ $total_quantity }}</td>
      </tr>
    </table>

    <table class="mt-10" style="border-collapse: collapse;">
      <tr class="text-bold text-center">
          <td class="border-full" width="60%">Product</td>
          <td class="border-full" width="20%">Qty</td>
          <td class="border-full" width="20%">Check</td>
      </tr>
      @php
      $i = 1;  
      $total = 0;  
      @endphp
      @foreach ($collect as $key => $value)
        @php
        $total = $total + $value['quantity'];    
        @endphp
        <tr>
          <td class="border-full text-left">{{ $i++ }}. {{ $value['sku'] }} / {{ $value['name'] }}</td>
          <td class="border-full text-center">{{ $value['quantity'] }}</td>
          <td class="border-full"></td>
        </tr>
      @endforeach
      <tr class="text-bold text-center">
          <td class="text-right">Total Qty</td>
          <td class="border-full">{{ $total }}</td>
          <td></td>
      </tr>
    </table>
  </body>
</html>
