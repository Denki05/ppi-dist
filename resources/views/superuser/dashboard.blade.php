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
                        <h3>Penjualan Variant Tertinggi - {{ \Carbon\Carbon::create()->month($selectedMonth)->format('F') }}</h3>

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
                        <h3>Jumlah Penjualan Total</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart" style="max-height: 400px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Total Pendapatan Penjualan</h3>
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
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    });
</script>
@endpush