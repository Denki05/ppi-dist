<?php

$read = [1, 0, 0, 0, 0, 0];
$readPrint = [1, 0, 0, 0, 0, 1];
$manage = [1, 1, 1, 0, 0, 1];
$manageApprove = [1, 1, 1, 0, 1, 1];
$full = [1, 1, 1, 1, 1, 1];

$groups = [
    'master' => [
        'superuser.master.warehouse',
        'superuser.master.contact',
        'superuser.master.mitra',
        'superuser.master.product',
        'superuser.master.sh_produk',
        'superuser.master.product_category',
        'superuser.master.sub_brand_reference',
        'superuser.master.product_type',
        'superuser.master.customer',
        'superuser.master.customer_category',
        'superuser.master.vendor',
        'superuser.master.branch_office',
        'superuser.master.company',
        'superuser.master.unit',
        'superuser.master.question',
        'superuser.master.brand_reference',
        'superuser.master.product_min_stock',
        'superuser.master.customer_type',
        'superuser.master.customer_contact',
        'superuser.master.customer_other_address',
        'superuser.master.vendor_contact',
        'superuser.master.product_category_type',
        'superuser.master.customer_category_type',
    ],
    'penjualan' => [
        'superuser.finance.invoicing',
        'superuser.penjualan.price_setting',
        'superuser.penjualan.sales_order',
        'superuser.penjualan.sales_order_indent',
        'superuser.penjualan.sales_order_kontrak',
        'superuser.penjualan.so_proforma',
        'superuser.penjualan.pengajuan_proforma',
        'superuser.penjualan.sales_order_ppn',
        'superuser.penjualan.sale_return',
    ],
    'logistik' => [
        'superuser.gudang.purchase_order',
        'superuser.gudang.purchase_order.detail',
        'superuser.gudang.purchase_order_spk',
        'superuser.gudang.purchase_order_spk.detail',
        'superuser.gudang.mutasi_out',
        'superuser.gudang.mutasi_showroom',
        'superuser.penjualan.delivery_order',
        'superuser.gudang.sj_mutasi_internal',
        'superuser.gudang.receiving',
        'superuser.gudang.receiving.detail',
        'superuser.gudang.receiving.detail.colly',
        'superuser.gudang.quality_control',
        'superuser.gudang.quality_control_2',
        'superuser.gudang.stock',
        'superuser.gudang.stock_adjustment',
    ],
    'fat' => [
        'superuser.finance.payable',
        'superuser.finance.nota_kredit_finance',
        'superuser.finance.cashback',
        'superuser.accounting.product_finance',
        'superuser.accounting.finance_simulation',
        'superuser.accounting.invoice_tax',
        'superuser.finance.void',
    ],
    'laporan' => [
        'superuser.report.revenue',
        'superuser.report.customer_order_variant',
        'superuser.report.customer_type_zone',
        'superuser.report.sales',
        'superuser.report.product_performance',
        'superuser.report.product_high_sell',
        'superuser.report.report_delivery_cost',
        'superuser.report.forecast_supplier',
        'superuser.report.customer_type_brand',
        'superuser.report.employee_performance',
        'superuser.report.employee_performance_product',
        'superuser.report.customer_type_brand_uv',
    ],
];

$masterFull = array_fill_keys($groups['master'], $full);
$penjualanFull = array_fill_keys($groups['penjualan'], $full);
$logistikFull = array_fill_keys($groups['logistik'], $full);
$fatFull = array_fill_keys($groups['fat'], $full);

$adminSales = array_merge($masterFull, $penjualanFull, $logistikFull);
$adminSales['superuser.gudang.receiving'] = $readPrint;
$adminSales['superuser.gudang.receiving.detail'] = $readPrint;
$adminSales['superuser.gudang.receiving.detail.colly'] = $readPrint;
$adminSales['superuser.gudang.quality_control'] = $readPrint;
$adminSales['superuser.gudang.quality_control_2'] = $readPrint;

$marketing = [
    'superuser.penjualan.sales_order' => $full,
    'superuser.penjualan.sales_order_indent' => $full,
    'superuser.finance.invoicing' => $manageApprove,
];

$sales = $marketing;

$accountOfficer = [
    'superuser.penjualan.sales_order' => $full,
    'superuser.penjualan.sales_order_indent' => $full,
    'superuser.penjualan.sales_order_ppn' => $full,
];

$customerService = [
    'superuser.master.customer' => $full,
    'superuser.master.customer_category' => $read,
    'superuser.master.customer_contact' => $full,
    'superuser.master.customer_other_address' => $full,
    'superuser.penjualan.sales_order' => $full,
];

$salesManager = $sales + [
    'superuser.penjualan.sales_order_ppn' => $full,
    'superuser.report.sales' => $readPrint,
    'superuser.report.employee_performance' => $readPrint,
    'superuser.report.product_performance' => $readPrint,
    'superuser.report.customer_type_brand' => $readPrint,
    'superuser.report.customer_type_brand_uv' => $readPrint,
];

$warehouse = [
    'superuser.penjualan.delivery_order' => $full,
    'superuser.gudang.sj_mutasi_internal' => $full,
    'superuser.gudang.receiving' => $full,
    'superuser.gudang.receiving.detail' => $full,
    'superuser.gudang.receiving.detail.colly' => $full,
    'superuser.gudang.quality_control' => $full,
    'superuser.gudang.quality_control_2' => $full,
    'superuser.gudang.stock' => $full,
    'superuser.gudang.stock_adjustment' => $full,
];

$finance = [
    'superuser.finance.invoicing' => $manage,
    'superuser.finance.payable' => $full,
    'superuser.finance.nota_kredit_finance' => $readPrint,
    'superuser.finance.cashback' => $readPrint,
    'superuser.accounting.invoice_tax' => $read,
    'superuser.gudang.mutasi_showroom' => [1, 0, 1, 0, 1, 1],
    'superuser.report.sales' => $read,
    'superuser.report.revenue' => $read,
];

$financeStaff = [
    'superuser.finance.invoicing' => $full,
    'superuser.finance.payable' => $full,
];

$management = [
    '*' => $read,
    'superuser.master.mitra' => $full,
    'superuser.master.product' => $read,
    'superuser.penjualan.sales_order' => $manage,
    'superuser.penjualan.sales_order_indent' => $read,
    'superuser.penjualan.sales_order_ppn' => $manage,
    'superuser.finance.invoicing' => $full,
    'superuser.finance.payable' => $full,
    'superuser.finance.cashback' => $full,
    'superuser.accounting.invoice_tax' => $full,
    'superuser.accounting.product_finance' => $full,
    'superuser.accounting.finance_simulation' => $full,
    'superuser.report.customer_type_brand' => $full,
    'superuser.report.customer_type_brand_uv' => $full,
    'superuser.penjualan.delivery_order' => $read,
    'superuser.gudang.stock' => $read,
    'superuser.gudang.mutasi_out' => $read,
    'superuser.gudang.mutasi_showroom' => $read,
];

return [
    'divisions' => [
        'Admin',
        'Customer Service',
        'Developer',
        'Finance',
        'IT',
        'Logistik & GA',
        'Management',
        'Marketing',
        'Marketing Communication',
        'Sales',
        'Warehouse',
    ],

    'groups' => $groups,

    'profiles' => [
        'Admin' => [
            '*' => $full,
        ],
        'Developer' => [
            '*' => $full,
        ],
        'Management' => $management,
        'Admin Sales' => $adminSales,
        'Marketing' => $marketing,
        'Sales' => $sales,
        'Sales Manager' => $salesManager,
        'Account Officer' => $accountOfficer,
        'Customer Service' => $customerService,
        'Warehouse' => $warehouse,
        'Logistik' => $warehouse,
        'Picker' => [
            'superuser.penjualan.delivery_order' => $full,
            'superuser.penjualan.packing_order' => $full,
        ],
        'Finance' => $finance,
        'Finance Staff' => $financeStaff,
        'Design' => [],
    ],

    'profile_labels' => [
        'Admin' => 'Admin (penuh)',
        'Developer' => 'Developer (penuh)',
        'Management' => 'Management',
        'Admin Sales' => 'Admin Sales',
        'Marketing' => 'Marketing',
        'Sales' => 'Sales',
        'Sales Manager' => 'Sales Manager',
        'Account Officer' => 'Account Officer',
        'Customer Service' => 'Customer Service',
        'Warehouse' => 'Warehouse',
        'Logistik' => 'Logistik (Warehouse + Inventory)',
        'Picker' => 'Picker',
        'Finance' => 'Finance',
        'Finance Staff' => 'Finance Staff',
        'Design' => 'Design (tanpa menu transaksi)',
    ],

    'profile_capabilities' => [
        'Admin' => ['*'],
        'Developer' => ['*'],
        'Management' => ['so_lanjutan', 'ppn_lanjutan'],
        'Admin Sales' => ['so_lanjutan', 'ppn_lanjutan'],
        'Sales Manager' => ['so_lanjutan', 'ppn_lanjutan'],
    ],

    'addons' => [
        'so_lanjutan' => [
            'label' => 'SO Lanjutan',
            'description' => 'Menambah akses Sales Order Lanjutan.',
            'capabilities' => ['so_lanjutan'],
            'permissions' => [
                'superuser.penjualan.sales_order' => $full,
            ],
        ],
        'ppn_awal' => [
            'label' => 'PPN Awal',
            'description' => 'Menambah akses Sales Order PPN Awal.',
            'capabilities' => [],
            'permissions' => [
                'superuser.penjualan.sales_order_ppn' => $full,
            ],
        ],
        'ppn_lanjutan' => [
            'label' => 'PPN Lanjutan',
            'description' => 'Menambah akses Sales Order PPN Lanjutan.',
            'capabilities' => ['ppn_lanjutan'],
            'permissions' => [
                'superuser.penjualan.sales_order_ppn' => $full,
            ],
        ],
        'delivery_order_read' => [
            'label' => 'Delivery Order (baca)',
            'description' => 'Melihat delivery order tanpa hak ubah.',
            'capabilities' => [],
            'permissions' => [
                'superuser.penjualan.delivery_order' => $read,
            ],
        ],
        'stock_read' => [
            'label' => 'Stock (baca)',
            'description' => 'Melihat stock gudang tanpa hak ubah.',
            'capabilities' => [],
            'permissions' => [
                'superuser.gudang.stock' => $read,
            ],
        ],
        'sales_reports' => [
            'label' => 'Laporan Sales',
            'description' => 'Laporan sales, performa produk, dan customer.',
            'capabilities' => [],
            'permissions' => [
                'superuser.report.sales' => $readPrint,
                'superuser.report.employee_performance' => $readPrint,
                'superuser.report.product_performance' => $readPrint,
                'superuser.report.customer_type_brand' => $readPrint,
                'superuser.report.customer_type_brand_uv' => $readPrint,
            ],
        ],
    ],

    'overrides' => [
        [
            'profile' => 'Picker',
            'division' => 'Logistik & GA',
            'jabatan_contains' => ['picker', 'picking'],
        ],
        [
            'profile' => 'Picker',
            'division' => 'Warehouse',
            'jabatan_contains' => ['picker', 'picking'],
        ],
        [
            'profile' => 'Sales Manager',
            'division' => 'Sales',
            'jabatan_contains' => ['manager', 'kepala', 'head'],
        ],
        [
            'profile' => 'Sales Manager',
            'division' => 'Marketing',
            'jabatan_contains' => ['manager', 'kepala', 'head'],
        ],
    ],
];
