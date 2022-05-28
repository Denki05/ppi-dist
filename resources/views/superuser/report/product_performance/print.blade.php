<!DOCTYPE html>
<html>
<head>
  <title>Product Performance Report</title>
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
      border: 1px solid #333;
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
      text-align: center;
      font-size: 12px;
    }

    @page{
      margin-top: 15px;
    }

    /*Footer*/
    #footer {
      position: fixed;
      left: 0;
      right: 0;
      color: #aaa;
      font-size: 11px;
      bottom: 0;
    }
    
    .page-number:before {
      content: "Product Performance Report | Page " counter(page);
    }

    .text-right{
      text-align: right;
    }
  </style>
</head>
<body>
  <div class="row-float" style="margin-bottom: 20px !important;">
    <div class="column-float note" style="width: 45%;">
        <div style="height: 100px;">
          <img src="<?= base_path('public/superuser_assets/media/master/company/'.$company->logo) ?>" style="width: 100%;height: 100%;">
        </div>
    </div>
    <div class="column-float" style="width: 55%;margin-top: 10px;font-size: 15px;">
      <h3 style="text-align: right;margin: 0;padding: 0;margin-bottom: 5 !important;padding-bottom: 0 !important;">Product Performance Report</h3>
      <h5 style="text-align: right;margin: 0;padding: 0;margin-bottom: 5 !important;padding-bottom: 0 !important;"><?= date('d-m-Y'); ?></h5>
    </div>
  </div>

  <div class="row-float" style="font-size: 13px;">
    <div class="column-float">
      @if(!empty(request()->get('period_from')))
      Period From <?= date('d F Y',strtotime(request()->get('period_from'))) ?>
      @endif
      @if(!empty(request()->get('period_from')) && !empty(request()->get('period_to')))
      -
      @endif
      @if(!empty(request()->get('period_to')))
      <?= date('d F Y',strtotime(request()->get('period_to'))) ?>
      @endif
      @if(!empty(request()->get('period_from')) || !empty(request()->get('period_to')))
      <br>
      @endif
      @if(!empty(request()->get('product_id')))
        @if(!empty($product_detail))
            Product : {{$product_detail->name}}<br>
        @endif
      @else
        Product : All Product<br>
      @endif
      @if(!empty(request()->get('brand_reference_id')))
        @if(!empty($brand_reference_detail))
            Brand Reference : {{$brand_reference_detail->name}}<br>
        @endif
      @else
        Brand Reference : All Brand Reference<br>
      @endif
    </div>
    <div class="column-float text-right">
      @if(!empty(request()->get('customer_id')))
        @if(!empty($customer_detail))
            Customer : {{$customer_detail->name}}<br>
        @endif
      @else
        Customer : All Customer<br>
      @endif
      @if(!empty(request()->get('filter_by')))
        @if(request()->get('filter_by') == "sales_order")
          Report By : Sales Order<br>
        @elseif(request()->get('filter_by') == "delivery_order")
          Report By : Delivery Order<br>
        @elseif(request()->get('filter_by') == "inventory")
          Report By : Inventory
        @else
          Report By : All
        @endif
      @else
      Report By : All<br>
      @endif
      @if(!empty(request()->get('warehouse_id')))
        @if(!empty($warehouse_id))
            Warehouse : {{$warehouse_detail->name}}<br>
        @endif
      @else
        Warehouse : All Warehouse<br>
      @endif
    </div>
  </div>

  <div>
    <table style="width: 100%;" class="table-data">

      <thead>
        <tr class="text-center">
          <th style="width: 5%;">#</th>
          <th style="width: 15%;">Product Code</th>
          <th style="width: 25%;">Brand</th>
          <th style="width: 25%;">Product Name</th>
          <th style="width: 10%;">Stock Qty</th>
          <th style="width: 10%;">So Qty</th>
          <th style="width: 10%;">DO Qty</th>
        </tr>
      </thead>
      <tbody class="text-center">
        @if(count($table) <= 0)
        <tr>
          <td colspan="7">Data tidak ditemukan</td>
        </tr>
        @endif
        @foreach($table as $i => $row)
          <tr>
            <td>{{$i+1}}</td>
            <td>{{$row->code}}</td>
            <td>{{$row->brand_reference->name ?? ''}}</td>
            <td>{{$row->name}}</td>
            <td>{{$row->stock}}</td>
            <td>{{$row->so}}</td>
            <td>{{$row->do}}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div id="footer">
    <div class="row-float">
      <div class="column-float">
        <div class="page-number"></div>
      </div>
      <div class="column-float text-right">
        @if(!empty(request()->get('period_from')))
        Period From <?= date('d F Y',strtotime(request()->get('period_from'))) ?>
        @endif
        @if(!empty(request()->get('period_from')) && !empty(request()->get('period_to')))
        -
        @endif
        @if(!empty(request()->get('period_to')))
        <?= date('d F Y',strtotime(request()->get('period_to'))) ?>
        @endif
      </div>
    </div>
  </div>

</body>
</html>