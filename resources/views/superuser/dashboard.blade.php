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
@endsection

@include('superuser.asset.plugin.select2')
@include('superuser.asset.plugin.swal2')
@include('superuser.asset.plugin.datatables')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const salesData = @json($sales);

        // Create an object to hold data grouped by month and brand
        const groupedData = {};

        // Check the structure of salesData
        console.log(salesData);

        // Group data by month and brand
        salesData.forEach(sale => {
            if (!groupedData[sale.month_name]) {
                groupedData[sale.month_name] = {};
            }
            if (!groupedData[sale.month_name][sale.brand]) {
                groupedData[sale.month_name][sale.brand] = 0;
            }
            groupedData[sale.month_name][sale.brand] += sale.total_qty;
        });

        const labels = Object.keys(groupedData);  // Get unique months
        const datasets = [];

        // Map of brand names to their respective colors
        const colorMap = {
            'GCF': 'rgba(255, 236, 0, 0.8)',
            'Nginden': 'rgba(0, 19, 255, 0.8)',
            'PPI NON FF': 'rgba(112, 112, 112, 0.8)',
            'PPI FF': 'rgba(0, 255, 77, 0.8)',
            'PPI X': 'rgba(0, 0, 0, 0.8)',
            'Senses': 'rgba(255, 0, 0, 0.8)',
        };

        // Map brands to datasets and assign data
        const brandMap = new Map(); // Track datasets by brand
        Object.keys(groupedData).forEach((month) => {
            Object.keys(groupedData[month]).forEach((brand) => {
                if (!brandMap.has(brand)) {
                    brandMap.set(brand, {
                        label: brand,
                        data: Array(labels.length).fill(0),  // Initialize data array with zeros
                        backgroundColor: colorMap[brand] || 'rgba(150, 150, 150, 1)', // Default color if brand not found
                        borderColor: colorMap[brand] || 'rgba(150, 150, 150, 1)', // Default color if brand not found
                        borderWidth: 1,
                        fill: true,
                    });
                    datasets.push(brandMap.get(brand));  // Add new dataset for brand
                }
                const datasetIndex = datasets.findIndex(ds => ds.label === brand);
                if (datasetIndex > -1) {
                    const monthIndex = labels.indexOf(month);
                    if (monthIndex > -1) {
                        datasets[datasetIndex].data[monthIndex] = groupedData[month][brand];  // Set the correct total
                    }
                }
            });
        });

        // Log datasets and labels to debug if necessary
        console.log('Labels:', labels);
        console.log('Datasets:', datasets);

        // Rendering chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: datasets,
            },
            options: {
                scales: {
                    x: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Month',
                        },
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total Qty (KG)',
                        },
                    },
                },
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                },
            },
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
        const revenueData = @json($revenue);

        // Create an object to hold data grouped by month
        const groupedData = {};

        // Check the structure of revenueData
        console.log(revenueData);

        // Group data by month
        revenueData.forEach(revenue => {
            if (!groupedData[revenue.month_name]) {
                groupedData[revenue.month_name] = 0;
            }
            groupedData[revenue.month_name] += revenue.total_purchase;
        });

        const labels = Object.keys(groupedData);  // Get unique months
        const data = Object.values(groupedData);  // Get total_purchase for each month

        // Log labels and data to debug if necessary
        console.log('Labels:', labels);
        console.log('Data:', data);

        // Rendering chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const purchaseChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels, // Months
                datasets: [{
                    label: 'Total Purchase (IDR)',
                    data: data, // Total Purchase data for each month
                    backgroundColor: 'rgba(75, 192, 192, 0.2)', // Area under the line
                    borderColor: 'rgba(75, 192, 192, 1)', // Line color
                    borderWidth: 2,
                    fill: true, // Fill under the line (optional)
                    tension: 0.3, // Curve smoothness (set to 0 for straight lines)
                    pointStyle: 'circle', // Shape of the data points
                    pointRadius: 5, // Size of the data points
                    pointBackgroundColor: 'rgba(255, 99, 132, 1)', // Color of points
                    hoverRadius: 7, // Size of point on hover
                    pointHoverBackgroundColor: 'rgba(255, 99, 132, 1)', // Point hover color
                    pointHoverBorderWidth: 2, // Border size on hover
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total Purchase (IDR)',
                        },
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Month',
                        },
                    },
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                },
            },
        });
    });

    document.getElementById('monthSelect').addEventListener('change', function() {
        // Reload the page with the selected month as a query parameter
        window.location.href = '?month=' + this.value;
    });

    var datatable = $('#datatables').DataTable({
        language: {
            processing: "<span class='fa-stack fa-lg'>\n\
                                    <i class='fa fa-spinner fa-spin fa-stack-2x fa-fw'></i>\n\
                            </span>",
        },
        processing: true,
        serverSide: false,
    })
</script>
@endpush