@extends('superuser.app')

@section('content')
<nav class="breadcrumb bg-white push">
  <span class="breadcrumb-item">Pages</span>
  <span class="breadcrumb-item active">Dashboard</span>
</nav>

@if($errors->any())
<div class="alert alert-danger alert-dismissable" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
    <h3 class="alert-heading font-size-h4 font-w400">Error</h3>
    @foreach ($errors->all() as $error)
    <p class="mb-0">{{ $error }}</p>
    @endforeach
</div>
@endif

<div id="alert-block"></div>

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

@if(session()->has('message'))
<div class="alert alert-success alert-dismissable" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
    <h3 class="alert-heading font-size-h4 font-w400">Success</h3>
    <p class="mb-0">{{ session()->get('message') }}</p>
</div>
@endif

@if($is_see == true)
<div class="block">
    <div class="block-content">
    <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h3>Approval Sales Order MOU</h3>
                    </div>
                    <div class="card-body">
                        <table class="datatable table table-striped" id="so_approval">
                            <thead>
                                <tr>
                                    <td class="text-center">#</td>
                                    <td class="text-center">So Code</td>
                                    <td class="text-center">Brand</td>
                                    <td class="text-center">Customer</td>
                                    <td class="text-center">Approval</td>
                                    <td class="text-center">Status</td>
                                    <td class="text-center">Action</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($approval_so as $so)
                                    @if($so->approval_mou == 1)
                                    <tr>
                                        <th class="text-center">{{ $loop->iteration }}</th>
                                        <th class="text-center">{{ $so->so_code }}</th>
                                        <th class="text-center">{{ $so->brand_name }}</th>
                                        <th class="text-center">{{ $so->customer_name }} {{ $so->customer_city }}</th>
                                        <th class="text-center">
                                            @if($so->approval_mou == 1)
                                                <span class="badge badge-success">YES</span>
                                            @else
                                                <span class="badge badge-danger">NO</span>
                                            @endif
                                        </th>
                                        <th class="text-center">
                                            @if($so->approval_mou_status == 1)
                                                <span class="badge badge-success">Approved</span>
                                            @else
                                                <span class="badge badge-danger">No Approved</span>
                                            @endif
                                        </th>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-info btn-view" data-toggle="modal" data-target="#modalViewSo" data-id="{{$so->id}}" title="Show SO">
                                                <i class="fa fa-eye"></i> View
                                            </button>
                                            @if($so->approval_mou == 1 && $so->approval_mou_status != 1)
                                                <button 
                                                    class="btn btn-sm btn-success btn-approval-mou" 
                                                    data-id="{{ $so->id }}">
                                                    <i class="fa fa-check"></i> Proses
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-secondary" disabled>
                                                    <i class="fa fa-check"></i> Approved
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="block">
    <div class="block-content">
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h3>Top Sale Variant - {{ \Carbon\Carbon::create()->month($selectedMonth)->format('F') }}</h3>

                        <select id="monthSelect" class="form-select" aria-label="Select month" style="width: 25%;">
                            @foreach (range(1, 12) as $month)
                                <option value="{{ $month }}" {{ $month == ($selectedMonth ?? now()->month) ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($month)->format('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="card-body">
                        <table class="datatable table table-striped" id="datatables">
                            <thead>
                                <tr>
                                    <td class="text-center">#</td>
                                    <td class="text-center">Variant</td>
                                    <td class="text-center">Quantity (KG)</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($top_sell_variant AS $key)
                                    <tr>
                                        <th class="text-center">{{ $loop->iteration }}</th>
                                        <th class="text-center">{{ $key->product }}</th>
                                        <th class="text-center">{{ $key->total_qty }}</th>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br>
</div>

<div class="block">
    <div class="block-content">
        <div class="row">
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Total Sales Quantity</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart" style="max-height: 400px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Total Sales Revenue</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueChart" style="max-height: 400px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br>
</div>
@endif

<!-- Modal approval SO -->
 <div class="modal fade bd-example-modal-xl" id="modalViewSo" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">#View SO <span id="so_code_display"></span></span></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div id="modal-data">
              <!-- Invoice and Customer Details -->
              <div class="row">
                  <div class="col">
                      <div class="block">
                          <div class="block-header block-header-default">
                              <h3 class="block-title">#Detail Nota</h3>
                          </div>
                          <div class="block-content">
                              <div class="form-row">
                                  <div class="form-group col-md-6">
                                      <label for="invoice_date">Tanggal Nota</label>
                                      <input type="text" id="invoice_date" class="form-control" readonly>
                                  </div>
                                  <div class="form-group col-md-6">
                                      <label for="invoice_code">Code</label>
                                      <input type="text" id="invoice_code" class="form-control" readonly>
                                  </div>
                              </div>

                              <div class="form-row">
                                  <div class="form-group col-md-6">
                                      <label for="kurs">Kurs</label>
                                      <input type="text" id="kurs" class="form-control" readonly>
                                  </div>
                                  <div class="form-group col-md-6">
                                      <label for="disc_percent">Disc %</label>
                                      <input type="text" id="disc_percent" class="form-control" readonly>
                                  </div>
                              </div>

                              <div class="form-row">
                                  <div class="form-group col-md-4">
                                      <label for="type_transaction">Type Transaksi</label>
                                      <input type="text" id="type_transaction" class="form-control" readonly>
                                  </div>
                                  <div class="form-group col-md-8" id="note-container">
                                      <label for="note">Note</label>
                                      <textarea class="form-control" style="height:auto; min-height:50px; overflow:hidden;" id="note" rows="1" readonly></textarea>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
                  <div class="col">
                      <div class="block">
                          <div class="block-header block-header-default">
                              <h3 class="block-title">#Customer</h3>
                          </div>
                          <div class="block-content">
                              <div class="form-row">
                                  <div class="form-group col-md-6">
                                      <label for="customer_name">Customer</label>
                                      <input type="text" id="customer_name" class="form-control" readonly>
                                  </div>
                                  <div class="form-group col-md-6">
                                      <label for="customer_address">Alamat Kirim</label>
                                      <textarea class="form-control" id="customer_address" rows="1" readonly></textarea>
                                  </div>
                              </div>

                              <div class="form-row">
                                  <div class="form-group col-md-6">
                                      <label for="customer_city">Kota</label>
                                      <input type="text" id="customer_city" class="form-control" readonly>
                                  </div>
                                  <div class="form-group col-md-6">
                                      <label for="customer_area">Provinsi</label>
                                      <input type="text" id="customer_area" class="form-control" readonly>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>

              <!-- Product Details Table -->
              <div class="row">
                  <div class="col">
                      <table class="table">
                          <thead>
                              <tr>
                                  <th>#</th>
                                  <th>Kode</th>
                                  <th>Product</th>
                                  <th>Kemasan</th>
                                  <th>Qty</th>
                                  <th>Harga</th>
                                  <th>Free</th>
                              </tr>
                          </thead>
                          <tbody id="product-details">
                              <!-- Product details will be injected here -->
                          </tbody>
                      </table>
                  </div>
              </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
</div>
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function () {
    $('.btn-view').click(function () {
        var id = $(this).data('id');
        // var url = "{{ url('/') }}/" + id + "/viewSalesOrderDetail";
        var url = "{{ route('superuser.penjualan.sales_order.viewSalesOrderDetail', ['id' => '__id__']) }}".replace('__id__', id);

        $.ajax({
            url: url,
            method: 'GET',
            success: function (data) {
                // Header
                $('#invoice_date').val(data.so_date ?? '-');
                $('#invoice_code').val(data.so_code ?? '-');
                $('#kurs').val(data.idr_rate ?? '-');
                $('#disc_percent').val(data.disc_percent ?? '-');
                $('#type_transaction').val(data.type_transaction ?? '-');
                $('#note').val(data.note ?? '-');

                // Customer
                $('#customer_name').val(data.customer_name ?? '-');
                $('#customer_address').val(data.customer_address ?? '-');
                $('#customer_city').val(data.customer_city ?? '-');
                $('#customer_area').val(data.customer_province ?? '-');

                // Items
                var html = '';
                data.so_items.forEach((item, index) => {
                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item.product_code ?? '-'}</td>
                            <td>${item.product_name ?? '-'}</td>
                            <td>${item.packaging_name ?? '-'}</td>
                            <td>${item.qty}</td>
                            <td>${item.price}</td>
                            <td>${item.free_product}</td>
                        </tr>`;
                });
                $('#product-details').html(html);
            },
            error: function () {
                alert('Gagal memuat data Sales Order.');
            }
        });
    });
});
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const salesData = @json($sales);
        const revenueData = @json($revenue);

        // Group sales data by month and brand
        const salesGrouped = {};
        salesData.forEach(sale => {
            if (!salesGrouped[sale.month_name]) {
                salesGrouped[sale.month_name] = {};
            }
            if (!salesGrouped[sale.month_name][sale.brand]) {
                salesGrouped[sale.month_name][sale.brand] = 0;
            }
            salesGrouped[sale.month_name][sale.brand] += sale.total_qty;
        });

        const labels = Object.keys(salesGrouped); // Get unique months
        const salesDatasets = [];
        const colorMap = {
            'GCF': 'rgba(255, 236, 0, 0.8)',
            'Nginden': 'rgba(0, 19, 255, 0.8)',
            'PPI NON FF': 'rgba(112, 112, 112, 0.8)',
            'PPI FF': 'rgba(0, 255, 77, 0.8)',
            'PPI X': 'rgba(0, 0, 0, 0.8)',
            'Senses': 'rgba(255, 0, 0, 0.8)',
        };

        const brandMap = new Map(); // Track datasets by brand
        Object.keys(salesGrouped).forEach((month) => {
            Object.keys(salesGrouped[month]).forEach((brand) => {
                if (!brandMap.has(brand)) {
                    brandMap.set(brand, {
                        label: brand,
                        data: Array(labels.length).fill(0),
                        backgroundColor: colorMap[brand] || 'rgba(150, 150, 150, 1)',
                        borderColor: colorMap[brand] || 'rgba(150, 150, 150, 1)',
                        borderWidth: 1,
                        fill: true,
                    });
                    salesDatasets.push(brandMap.get(brand));
                }
                const datasetIndex = salesDatasets.findIndex(ds => ds.label === brand);
                if (datasetIndex > -1) {
                    const monthIndex = labels.indexOf(month);
                    if (monthIndex > -1) {
                        salesDatasets[datasetIndex].data[monthIndex] = salesGrouped[month][brand];
                    }
                }
            });
        });

        // Create the sales chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: salesDatasets,
            },
            options: {
                scales: {
                    x: {
                        title: { display: true, text: 'Month' },
                    },
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Total Qty (KG)' },
                    },
                },
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                },
            },
        });

        // Group revenue data by month
        const revenueGrouped = {};
        revenueData.forEach(revenue => {
            if (!revenueGrouped[revenue.month_name]) {
                revenueGrouped[revenue.month_name] = 0;
            }
            revenueGrouped[revenue.month_name] += revenue.total_purchase;
        });

        const revenueLabels = Object.keys(revenueGrouped);
        const revenueDataPoints = Object.values(revenueGrouped);

        // Create the revenue chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: revenueLabels,
                datasets: [{
                    label: 'Total Purchase (IDR)',
                    data: revenueDataPoints,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointStyle: 'circle',
                    pointRadius: 5,
                    pointBackgroundColor: 'rgba(255, 99, 132, 1)',
                    hoverRadius: 7,
                    pointHoverBackgroundColor: 'rgba(255, 99, 132, 1)',
                    pointHoverBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Total Purchase (IDR)' },
                    },
                    x: {
                        title: { display: true, text: 'Month' },
                    },
                },
                plugins: {
                    legend: { position: 'top' },
                },
            },
        });

        // Change month and reload page
        document.getElementById('monthSelect').addEventListener('change', function() {
            window.location.href = '?month=' + this.value;
        });

        // Initialize DataTable
        var datatable = $('#datatables').DataTable({
            language: {
                processing: "<span class='fa-stack fa-lg'>\n\
                                <i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\
                            </span>",
            },
            processing: true,
            serverSide: false,
        });

        var datatable_so = $('#so_approval').DataTable({
            language: {
                processing: "<span class='fa-stack fa-lg'>\n\
                                <i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\
                            </span>",
            },
            processing: true,
            serverSide: false,
        });

        $('.btn-approval-mou').on('click', function() {
            const id = $(this).data('id');

            if (!confirm('Apakah Anda yakin ingin memproses approval ini?')) return;

            $.ajax({
                url: "{{ route('superuser.penjualan.sales_order.approvalMouSo', ['id' => '__id__']) }}".replace('__id__', id),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                beforeSend: function() {
                    // Optional: tampilkan loader
                },
                success: function(response) {
                    if(response.notification && response.notification.type === 'success') {
                        alert(response.notification.content);
                        location.reload(); // atau redirect jika perlu
                    }
                },
                error: function(xhr) {
                    alert('Terjadi kesalahan saat memproses approval.');
                    console.error(xhr.responseText);
                }
            });
        });
    });
</script>
@endpush