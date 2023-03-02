@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Report</span>
  <span class="breadcrumb-item active">Product Performance</span>
</nav>
@if(session('error') || session('success'))
<div class="alert alert-{{ session('error') ? 'danger' : 'success' }} alert-dismissible fade show" role="alert">
    @if (session('error'))
    <strong>Error!</strong> {!! session('error') !!}
    @elseif (session('success'))
    <strong>Berhasil!</strong> {!! session('success') !!}
    @endif
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif
<div class="block">
  <hr class="my-20">
  <div class="block-content block-content-full">
      <div class="row mb-30">
        <div class="col-12">
          <a href="#" class="btn btn-success btn-print"><i class="fa fa-print"></i> Print</a>
        </div>
      </div>
      <form>
        <div class="row">
          <div class="col-lg-3">
            <div class="form-group">
              <label>Set Period From</label>
              <input type="date" name="period_from" class="form-control">
            </div>   
          </div>
          <div class="col-lg-3">
            <div class="form-group">
              <label>Set Period To</label>
              <input type="date" name="period_to" class="form-control">
            </div>   
          </div>
          <div class="col-lg-3">
            <div class="form-group">
              <label>Customer</label>
              <select class="form-control js-select2" name="customer_id">
                <option value="">==All Customer==</option>
                @foreach($customer as $index => $row)
                <option value="{{$row->id}}">{{$row->name}}</option>
                @endforeach
              </select>
            </div>   
          </div>
          <div class="col-lg-3">
            <div class="form-group">
              <label>Warehouse</label>
              <select class="form-control js-select2" name="warehouse_id">
                <option value="">==All warehouse==</option>
                @foreach($warehouse as $index => $row)
                <option value="{{$row->id}}">{{$row->name}}</option>
                @endforeach
              </select>
            </div>   
          </div>
        </div>
        <div class="row">
          <div class="col-lg-3">
            <div class="form-group">
              <label>Brand Reference</label>
              <select class="form-control js-select2" name="brand_reference_id">
                <option value="">==All Brand Reference==</option>
                @foreach($brand_reference as $index => $row)
                <option value="{{$row->id}}">{{$row->name}}</option>
                @endforeach
              </select>
            </div>   
          </div>
          <div class="col-lg-3">
            <div class="form-group">
              <label>Product</label>
              <select class="form-control js-select2" name="product_id">
                <option value="">==All Product==</option>
                @foreach($product as $index => $row)
                <option value="{{$row->id}}">{{$row->code}} - {{$row->name}}</option>
                @endforeach
              </select>
            </div>   
          </div>
          <div class="col-lg-3">
            <div class="form-group">
              <label>Filter</label>
              <select class="form-control js-select2" name="filter_by">
                <option value="">==All==</option>
                <option value="inventory">Inventory</option>
                <option value="sales_order">Sales Order</option>
                <option value="delivery_order">Delivery Order</option>
              </select>
            </div>   
          </div>
          <div class="col-lg-3">
            <div class="form-group">
              <button class="btn btn-primary " type="submit" style="margin-top: 25px;"><i class="fa fa-search"></i> Filter</button>
            </div>   
          </div>
        </div>
      </form>

      <div class="row mt-10">
        <div class="col-12">
          <div class="row">
            <div class="col-lg-6 text-left pt-20">
              @if(!empty(request()->get('filter_by')))
                @if(request()->get('filter_by') == "sales_order")
                  Report By : Sales Order
                @elseif(request()->get('filter_by') == "delivery_order")
                  Report By : Delivery Order
                @elseif(request()->get('filter_by') == "inventory")
                  Report By : Inventory
                @else
                  Report By : All
                @endif
              @else
              Report By : All
              @endif
              @if(!empty(request()->get('customer_id')))
                <br>
                @if(!empty($customer_detail))
                    Customer : {{$customer_detail->name}}
                @endif
              @else
                <br>
                Customer : All Customer
              @endif
              @if(!empty(request()->get('warehouse_id')))
                <br>
                @if(!empty($warehouse_detail))
                    Warehouse : {{$warehouse_detail->name}}
                @endif
              @else
                <br>
                Warehouse : All Warehouse
              @endif
            </div>
            <div class="col-lg-6 text-right">
              <h3><b>Product Performance Report</b></h3>
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
      </div>

      <div class="row mt-20">
        <div class="col-12">
          <div class="table-responsive">
            <table class="table table-striped" id="datatables">
              <thead>
                <th>#</th>
                <th>Product Code</th>
                <th>Brand</th>
                <th>Product Name</th>
                <th>Stock Qty</th>
                <th>So Qty</th>
                <th>DO Qty</th>
              </thead>
              <tbody>
                @foreach($table as $index => $row)
                <tr>
                  <td>{{$table->firstItem() + $index}}</td>
                  <td>{{$row->code}}</td>
                  <td>{{$row->category->brand_lokal->brand_name ?? ''}}</td>
                  <td>{{$row->name}} - {{$row->category->type}}</td>
                  <td>{{$row->stock}}</td>
                  <td>{{$row->so}}</td>
                  <td>{{$row->do}}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="row mb-30">
        <div class="col-12">
          {{$table->links()}}
        </div>
      </div>
  </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.datatables')

@push('scripts')

  <script type="text/javascript">
    
    $(function(){
      
      $('#datatables').DataTable( {
        "paging":   false,
        "ordering": true,
        "info":     false,
        "searching" : false,
        "columnDefs": [{
          "targets": 0,
          "orderable": false
        }]
      });
      $('.js-select2').select2();

      $(document).on('click','.btn-print',function(){
        let period_to = '<?= $_GET["period_to"] ?? null ?>';
        let period_from = '<?= $_GET["period_from"] ?? null ?>';
        let customer_id = '<?= $_GET["customer_id"] ?? null ?>';
        let warehouse_id = '<?= $_GET["warehouse_id"] ?? null ?>';
        let product_id = '<?= $_GET["product_id"] ?? null ?>';
        let brand_reference_id = '<?= $_GET["brand_reference_id"] ?? null ?>';
        let filter_by = '<?= $_GET["filter_by"] ?? null ?>';

        window.open('{{route('superuser.report.product_performance.print')}}' + '?period_to='+period_to+'&period_from='+period_from+'&customer_id='+customer_id+'&product_id='+product_id+'&brand_reference_id='+brand_reference_id+'&filter_by='+filter_by+'&warehouse_id='+warehouse_id,'_blank');
      })
    });
  </script>
@endpush
