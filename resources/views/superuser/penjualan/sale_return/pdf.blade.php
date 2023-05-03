<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $data->code }}</title>
    @include('superuser.asset.css-pdf')
    <style type="text/css">
        @font-face {
            font-family: 'Broadway';
            src: url('{{ asset('superuser_assets/fonts/broadway/BROADW.TTF') }}') format('truetype');
        }

        .table-body {
            border: 1px solid black;
            border-collapse: collapse;
        }

        .padding-between {
            padding-top: 8px;
            padding-bottom: 8px;
        }

    </style>
</head>

<body style="font-size: 0.7em;">
    <table class="table-body">
        <tr>
            <td>
                <table style="border-collapse: collapse">
                    <tr>
                        <td colspan="2" style="background: rgb(243, 199, 207); font-size: 20px;font-family: 'Broadway', sans-serif;" class="text-center">NOTA
                            RETUR
                            PENJUALAN</td>
                    </tr>
                    <tr>
                        <td width="60%" style="line-height: 16px;padding-top: 10px; padding-left: 40px; vertical-align: top;">
                            {{ $data->delivery_order->sales_order->code }}<br>
                            {{ $data->delivery_order->sales_order->order_date ? \Carbon\Carbon::parse($data->delivery_order->sales_order->order_date)->format('d/m/Y H:i') : '-' }}<br>
                            {{ $data->delivery_order->sales_order->customer_name() }}
                        </td>
                        <td style="line-height: 16px; padding-top: 10px; padding-bottom: 10px;">
                            {{ $data->code }}<br>
                            {{ $data->return_date ? \Carbon\Carbon::parse($data->return_date)->format('d/m/Y H:i') : '-' }}<br>
                            {{ $data->warehouse->name }}
                        </td>
                    </tr>
                </table>
                <table style="border-collapse: collapse;text-align: center;margin-left: -1px;margin-right: -1px;">
                    <thead>
                        <tr>
                            <th class="border-full padding-between" width="3%">NO</th>
                            <th class="border-full padding-between">SKU</th>
                            <th class="border-full padding-between">PRODUCT</th>
                            <th class="border-full padding-between" width="10%">QTY</th>
                            <th class="border-full padding-between" width="50%">DESCRIPTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data->sale_return_details as $item)
                            <tr>
                                <td class="border-full">{{ $loop->iteration }}.</td>
                                <td class="border-full">{{ $item->product->code }}</td>
                                <td class="border-full">{{ $item->product->name }}</td>
                                <td class="border-full">{{ $item->quantity }}</td>
                                <td class="border-full">{{ $item->description }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <table style="margin-top: 10px;text-align: center;">
                    <tr>
                        <td width="15%">Created by,</td>
                        <td width="10%"></td>
                        <td width="20%">Approved by,</td>
                        <td width="20%">Checked by,</td>
                        <td width="10%"></td>
                        <td width="15%">Received by,</td>
                        <td width="10%"></td>
                    </tr>
                    <tr>
                        <td style="padding-top: 50px; padding-bottom: 20px">{{ $data->createdBySuperuser() }}</td>
                        <td colspan="6"></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
