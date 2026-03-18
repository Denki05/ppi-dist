<style>
    /* Navbar with black background */
.navbar {
    background-color: #343a40!important; /* Black background */
    color: white !important; /* White text */
}

.navbar .navbar-brand, 
.navbar .nav-link, 
.navbar .dropdown-item {
    color: white !important; /* Make text white */
}

.navbar .navbar-toggler-icon {
    background-color: white; /* White icon for the toggler */
}

/* Hover and active states for links */
.navbar .nav-link:hover, 
.navbar .nav-link:focus,
.navbar .dropdown-item:hover,
.navbar .dropdown-item:focus {
    background-color: #444; /* Darker background on hover */
    color: white !important; /* Ensure text stays white */
}

.navbar .dropdown-menu {
    background-color: #343a40!important; /* Ensure dropdown has black background */
}

.navbar .dropdown-item:hover {
    background-color: #555; /* Darken hover effect for dropdown items */
}

/* Adjust color of any borders or dividers to match the black theme */
.dropdown-divider {
    border-color: #333; /* Darken the divider line */
}

/* Profile and Notification dropdown aligned to the right */
.navbar .navbar-nav.ml-auto {
    margin-left: auto; /* Align navbar items to the right */
}

.navbar .dropdown-menu {
    left: auto;
    right: 0;
}

/* Customize profile and notification icons */
.navbar .dropdown-menu .dropdown-item {
    display: flex;
    align-items: center;
}

/* Notification and profile icons */
.navbar .dropdown-item i {
    margin-right: 8px;
}

/* Center main menu items */
.navbar .navbar-nav.centered {
    margin-left: auto;
    margin-right: auto;
    text-align: center;
}

.navbar .navbar-nav.centered .nav-item {
    margin: 0 15px; /* Add spacing between items */
}

@media (min-width: 768px) {
    /* Profile and Notification icons on the right */
    .navbar .ml-auto .nav-item {
        margin-left: 20px;
    }

    /* Center the navbar items only on large screens */
    .navbar .navbar-nav.centered {
        display: flex;
        justify-content: center;
        flex-grow: 1;
    }
}

/* ===============================
   Notification Dropdown Modern
================================= */

.notification-wrapper .notification-badge {
    position: absolute;
    top: 2px;
    right: 2px;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 50px;
    background: #ff3b30;
    color: #fff;
    display: none;
    align-items: center;
    justify-content: center;
}

.notification-wrapper .notification-badge.show {
    display: inline-flex;
}

.notification-dropdown {
    width: 380px;
    border-radius: 14px;
    padding: 0;
    border: none;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    overflow: hidden;
}

.notification-header {
    padding: 14px 16px;
    background: #fff;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notification-sub {
    font-size: 12px;
    color: #888;
}

.notification-body {
    max-height: 420px;
    overflow-y: auto;
    background: #fafafa;
}

.notification-footer {
    padding: 10px;
    background: #fff;
    border-top: 1px solid #eee;
}

.notification-item {
    padding: 14px 16px;
    border-bottom: 1px solid #eee;
    background: #fff;
    transition: all 0.2s ease;
    cursor: pointer;
}

.notification-item:hover {
    background: #f4f6f9;
}

.notification-item.unread {
    background: #eef5ff;
}

.notification-title {
    font-size: 14px;
    font-weight: 600;
}

.notification-text {
    font-size: 13px;
    color: #666;
    margin-top: 3px;
}

.notification-time {
    font-size: 11px;
    color: #999;
    margin-top: 6px;
}
</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">UNIFRA</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav centered">
                <li class="nav-item active">
                    <a class="nav-link" href="{{ route('superuser.index') }}">
                        <i class="fa-solid fa-house"></i> Dashboard
                    </a>
                </li>

                <!-- Master -->
                @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management")
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fa-solid fa-folder-open"></i> Master
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('superuser.master.warehouse.index') }}"><i class="fa-solid fa-warehouse"></i> Gudang</a></li>
                        <li><a class="dropdown-item" href="{{ route('superuser.master.contact.index') }}"><i class="fa-solid fa-address-book"></i> Kontak</a></li>
                        <li><a class="dropdown-item" href="{{ route('superuser.master.mitra.index') }}"><i class="fa-solid fa-code-branch"></i> Mitra</a></li>
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-copyright"></i> Produk
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('superuser.master.product.index') }}"><i class="fa-solid fa-list"></i> List</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.master.product_category.index') }}"><i class="fa-solid fa-layer-group"></i> Kategori</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.master.sub_brand_reference.index') }}"><i class="fa-solid fa-timeline"></i> Searah</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.master.product_type.index') }}"><i class="fa-solid fa-file-lines"></i> Tipe</a></li>
                            </ul>
                        </li>
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-store"></i> Customer
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('superuser.master.customer.index') }}"><i class="fa-solid fa-list"></i> List</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.master.customer_category.index') }}"><i class="fa-solid fa-layer-group"></i> Kategori</a></li>
                            </ul>
                        </li>
                        <li><a class="dropdown-item" href="{{ route('superuser.master.vendor.index') }}"><i class="fa-solid fa-building-columns"></i> Vendor</a></li>
                    </ul>
                </li>
                @endif

                <!-- Penjualan -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fa-solid fa-basket-shopping"></i> Penjualan
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('superuser.finance.invoicing.index') }}"><i class="fa-solid fa-warehouse"></i> Invoice</a></li>
                        @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management")
                        <li><a class="dropdown-item" href="{{ route('superuser.master.contact.index') }}"><i class="fa-solid fa-address-book"></i> Pengaturan Harga</a></li>
                        @endif
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-cart-plus"></i> Sales Order
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('superuser.penjualan.sales_order_indent.index') }}"><i class="fa-solid fa-indent"></i> Indent</a></li>
                                @if($superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Developer")
                                <li><a class="dropdown-item" href="{{ route('superuser.penjualan.sales_order_kontrak.index') }}"><i class="fa-solid fa-file-signature"></i> Kontrak</a></li>
                                @endif
                                @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management")
                                <li><a class="dropdown-item" href="{{ route('superuser.penjualan.so_proforma.index') }}"><i class="fa-solid fa-file-prescription"></i> Proforma</a></li>
                                @endif
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li class="submenu submenu-md dropend">
                                    <a class="dropdown-item dropdown-toggle" role="button"
                                        data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-file-invoice"></i> Regular</a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('superuser.penjualan.sales_order.index_awal') }}"><i class="fa-solid fa-arrow-up-wide-short"></i> Awal</a></li>
                                        @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management")
                                        <li><a class="dropdown-item" href="{{ route('superuser.penjualan.sales_order.index_lanjutan') }}"><i class="fa-solid fa-arrow-down-wide-short"></i> Lanjutan</a></li>
                                        @endif
                                    </ul>
                                </li>
                                @if(
                                    $superuser->can('superuser-manage') || 
                                    $superuser->division == "Admin" || 
                                    $superuser->division == "Management" || 
                                    $superuser->id == 38 ||
                                    $superuser->id == 35
                                )
                                    <li class="submenu submenu-md dropend">
                                        <a class="dropdown-item dropdown-toggle" role="button"
                                            data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-file-invoice"></i> PPN</a>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="{{ route('superuser.penjualan.sales_order_ppn.index_ppn_awal') }}"><i class="fa-solid fa-arrow-up-wide-short"></i> Awal</a></li>
                                            @if(
                                                $superuser->can('superuser-manage') || 
                                                $superuser->division == "Admin" || 
                                                $superuser->division == "Management"
                                            )
                                            <li><a class="dropdown-item" href="{{ route('superuser.penjualan.sales_order_ppn.index_ppn_lanjutan') }}"><i class="fa-solid fa-arrow-down-wide-short"></i> Lanjutan</a></li>
                                            @endif
                                        </ul>
                                    </li>
                                @endif
                            </ul>
                        </li>
                        
                        @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management")
                        <li><a class="dropdown-item" href="{{ route('superuser.penjualan.sale_return.index') }}"><i class="fa-solid fa-file-prescription"></i> Nota Kredit</a></li>
                        @endif
                    </ul>
                </li>

                <!-- Gudang -->
                @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Warehouse")
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fa-solid fa-boxes-stacked"></i> Logistik
                    </a>
                    <ul class="dropdown-menu">

                        {{-- ================= DOCUMENT ================= --}}
                        @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Warehouse")
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-file-lines"></i> Document
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ route('superuser.gudang.purchase_order_spk.index') }}">
                                        Order Industri (SPK)
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('superuser.gudang.purchase_order.index') }}">
                                        Order Pusat (PO)
                                    </a>
                                </li>
                                <li class="submenu submenu-md dropend">
                                    <a class="dropdown-item dropdown-toggle" role="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">Mutasi</a>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('superuser.gudang.mutasi_out.index') }}">
                                                Gudang Utama
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('superuser.gudang.mutasi_showroom.index') }}">
                                                Showroom
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        @endif


                        {{-- ================= WAREHOUSE ================= --}}
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-warehouse"></i> Warehouse
                            </a>
                            <ul class="dropdown-menu">
                                @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Warehouse")
                                <li>
                                    <a class="dropdown-item" href="{{ route('superuser.penjualan.delivery_order.index') }}">
                                        Checker Transaksi (DO)
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('superuser.gudang.sj_mutasi_internal.index') }}">
                                        Checker Mutasi
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('superuser.gudang.receiving.index') }}">
                                        Receiving
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('superuser.gudang.quality_control.index') }}">
                                        Receiving - Transaksi
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('superuser.gudang.quality_control_2.index') }}">
                                        Receiving - Komplain
                                    </a>
                                </li>
                                
                                @endif
                            </ul>
                        </li>


                        {{-- ================= INVENTORY ================= --}}
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-cubes"></i> Inventory
                            </a>
                            <ul class="dropdown-menu">
                                @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Warehouse")
                                <li>
                                    <a class="dropdown-item" href="{{ route('superuser.gudang.stock.index') }}">
                                        Stock
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('superuser.gudang.stock_adjustment.index') }}">
                                        Stock Adjustment
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </li>

                    </ul>
                </li>
                @endif

                <!-- FAT -->
                @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Finance")
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fa-solid fa-chart-line"></i> FAT
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('superuser.finance.payable.index') }}"><i class="fa-solid fa-credit-card"></i> Payment</a></li>
                        <li><a class="dropdown-item" href="{{ route('superuser.finance.nota_kredit_finance.index') }}"><i class="fa-solid fa-file-prescription"></i> Nota TT</a></li>
                        <li><a class="dropdown-item" href="{{ route('superuser.finance.nota_kredit_finance.refund_page') }}"><i class="fa-solid fa-file-prescription"></i> Refund</a></li>
                        <li><a class="dropdown-item" href="{{ route('superuser.finance.cashback.index') }}"><i class="fa-solid fa-code-branch"></i> Araya</a></li>
                        <!-- <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-chart-simple"></i> Accounting
                            </a>
                            <ul class="dropdown-menu">
                                <li class=s"ubmenu submenu-md dropend">
                                    <a class="dropdown-item dropdown-toggle" role="button"
                                        data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-code-branch"></i> Unifra</a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('superuser.accounting.invoice_tax.index_jual') }}">Jual</a></li>
                                        <li><a class="dropdown-item" href="{{ route('superuser.accounting.invoice_tax.index_beli') }}">Beli</a></li>
                                    </ul>
                                </li>
                                
                            </ul>
                        </li> -->
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-repeat"></i> CV
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('superuser.accounting.product_finance.index') }}"><i class="fa-solid fa-copyright"></i> Product UV</a></li>
                                <li class="submenu submenu-md dropend">
                                    <a class="dropdown-item dropdown-toggle" role="button"
                                        data-bs-toggle="dropdown" aria-expanded="false"> Araya</a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('superuser.accounting.finance_simulation.index_araya') }}">Invoice</a></li>
                                        <li><a class="dropdown-item" href="">Payment</a></li>
                                        <li><a class="dropdown-item" href="">Report</a></li>
                                    </ul>
                                </li>
                                <li class="submenu submenu-md dropend">
                                    <a class="dropdown-item dropdown-toggle" role="button"
                                        data-bs-toggle="dropdown" aria-expanded="false"> Unifra</a>
                                    <ul class="dropdown-menu">
                                        <!-- <li><a class="dropdown-item" href="">Set Mitra</a></li> -->
                                        <li><a class="dropdown-item" href="{{ route('superuser.accounting.finance_simulation.index_mitra') }}">Invoice</a></li>
                                        <li><a class="dropdown-item" href="">Payment</a></li>
                                        <li><a class="dropdown-item" href="">Report</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
                @endif

                <!-- Laporan -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fa-solid fa-file-export"></i> Laporan
                    </a>
                    <ul class="dropdown-menu">
                        @if($superuser->division == "Developer" OR $superuser->division == "Management" OR $superuser->division == "Finance")
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-chart-simple"></i> Accounting
                            </a>
                            <ul class="dropdown-menu">
                                <li class="submenu submenu-md dropend">
                                    <a class="dropdown-item dropdown-toggle" role="button"
                                        data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-code-branch"></i> Unifra Report</a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('superuser.accounting.invoice_tax.pageReportJual') }}">Jual</a></li>
                                        <li><a class="dropdown-item" href="{{ route('superuser.accounting.invoice_tax.pageReportBeli') }}">Beli</a></li>
                                    </ul>
                                </li>
                                <li><a class="dropdown-item" href="{{ route('superuser.report.revenue.index') }}"> Laporan Pendapatan</a></li>
                            </ul>
                        </li>
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-coins"></i> Finance
                            </a>
                            <ul class="dropdown-menu">
                                <li class="submenu submenu-md dropend">
                                    <a class="dropdown-item dropdown-toggle" role="button"
                                        data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-code-branch"></i> Araya Report</a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('superuser.finance.cashback.pageReport') }}">Cashback</a></li>
                                        <li><a class="dropdown-item" href="{{ route('superuser.finance.cashback.pageReportBeli') }}">Beli</a></li>
                                        <li><a class="dropdown-item" href="{{ route('superuser.finance.cashback.pageReportJual') }}">Jual</a></li>
                                    </ul>
                                </li>
                                <li><a class="dropdown-item" href="{{ route('superuser.finance.payable.pageReport') }}">Laporan Pembayaran</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.finance.invoicing.pageReport') }}">Piutang Faktur</a></li>
                            </ul>
                        </li>
                        @endif
                        @if($superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Developer" OR $superuser->division == "Finance")
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-file-import"></i> Operasional
                            </a>
                            <ul class="dropdown-menu">
                                <li class="submenu submenu-md dropend">
                                    <a class="dropdown-item dropdown-toggle" role="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">Customer</a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('superuser.report.customer_order_variant.index') }}">Customer - Produk</a></li>
                                        <li><a class="dropdown-item" href="{{ route('superuser.report.customer_order_variant.index') }}">Customer History</a></li>
                                        <li><a class="dropdown-item" href="{{ route('superuser.report.customer_type_zone.index') }}">Customer - Zoning</a></li>
                                        <li><a class="dropdown-item" href="{{ route('superuser.report.sales.index') }}">Penjualan</a></li>
                                    </ul>
                                </li>
                                <li class="submenu submenu-md dropend">
                                    <a class="dropdown-item dropdown-toggle" role="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">Produk</a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('superuser.report.product_performance.index') }}">Produk - Customer</a></li>
                                        <li><a class="dropdown-item" href="{{ route('superuser.report.product_high_sell.index') }}">Produk Penjualan Tertinggi</a></li>
                                        @if($superuser->division == "Management" OR $superuser->division == "Developer")
                                        <li><a class="dropdown-item" href="">Produk - Material</a></li>
                                        @endif
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        @endif

                        @if($superuser->division == "Developer" OR $superuser->division == "Management")
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-file-import"></i> Management
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('superuser.report.forecast_supplier.index') }}">Forecasting Principle</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.report.customer_type_brand.index') }}">Register Customer</a></li>
                                <li class="submenu submenu-md dropend">
                                    <a class="dropdown-item dropdown-toggle" role="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">Penjualan Sales</a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('superuser.report.employee_performance.index') }}">Omset Sales</a></li>
                                        <li><a class="dropdown-item" href="{{ route('superuser.report.employee_performance_product.index') }}">Kinerja Sales</a></li>
                                    </ul>
                                </li>
                                <li><a class="dropdown-item" href="{{ route('superuser.report.customer_type_brand_uv.index') }}">UV</a></li>
                            </ul>
                        </li>
                        @endif
                    </ul>
                </li>

                <!-- Setting -->
                @if($superuser->canAny(['superuser-manage', 'salesperson-manage']))
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fa-solid fa-gears"></i> Pengaturan
                    </a>
                    <ul class="dropdown-menu">
                        @if($superuser->can('superuser-manage'))
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-user-gear"></i> Akun
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('superuser.account.log_activity.index') }}">Aktivitas Log</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.account.superuser.index') }}">Superuser</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.account.user.index') }}">User</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.account.sales_person.index') }}">Sales Person</a></li>
                            </ul>
                        </li>
                        @endif
                        @role('Developer|SuperAdmin', 'superuser')
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-bars"></i> Menu
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('superuser.setting.menu.index') }}">Halaman</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.account.superuser.index') }}">Kegunaan</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.account.user.index') }}">Wilayah indonesia</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.utility.settings.index') }}">Maintenance Mode</a></li>
                            </ul>
                        </li>
                        @endrole
                        @role('Developer|SuperAdmin', 'superuser')
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-brands fa-connectdevelop"></i> Zona Pengembang
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('superuser.backup.index') }}">Backup DB</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.boilerplate.index') }}">Boilrplate</a></li>
                                <li><a class="dropdown-item" href="{{ url('superuser/telescope') }}">Telescope</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.terminal') }}">Terminal</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.gate.index') }}">Gate (Authorization)</a></li>
                            </ul>
                        </li>
                        @endrole
                    </ul>
                </li>
                @endif
            </ul>
      <ul class="navbar-nav ml-auto"> <!-- ml-auto aligns to the right -->
        <!-- Notification Dropdown -->
        <li class="nav-item dropdown notification-wrapper">
            <a class="nav-link position-relative" href="#" id="notificationDropdown"
            role="button" data-bs-toggle="dropdown" aria-expanded="false">

                <i class="bi bi-bell fs-5"></i>

                <span id="notifCount"
                    class="notification-badge {{ $notifCount > 0 ? 'show' : '' }}">
                    {{ $notifCount > 99 ? '99+' : $notifCount }}
                </span>
            </a>

            <div class="dropdown-menu dropdown-menu-end notification-dropdown"
                aria-labelledby="notificationDropdown">

                <div class="notification-header">
                    <div>
                        <strong>Notifications</strong>
                        <div class="notification-sub">
                            <span id="notifHeaderCount">{{ $notifCount }}</span> unread
                        </div>
                    </div>

                    <button type="button"
                            class="btn btn-sm btn-link text-decoration-none"
                            id="markAllAsReadBtn">
                        Mark all
                    </button>
                </div>

                <div id="notifList" class="notification-body">
                    <!-- Loaded via AJAX -->
                </div>

                <div class="notification-footer text-center">
                    <a href="#" class="text-muted small">View All</a>
                </div>
            </div>
        </li>

        <!-- Profile Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle"></i> {{ $superuser->username }}
          </a>
          <ul class="dropdown-menu" aria-labelledby="profileDropdown">
            <li><a class="dropdown-item" href="{{ route('superuser.profile.index') }}">My Profile</a></li>
            <li><a class="dropdown-item" href="{{ route('superuser.logout') }}">Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

@push('scripts')
<script>
(function () {

    let notifInterval = null;
    let currentRequest = null;

    /* ================================
       SUBMENU AUTO COLLAPSE
    ================================= */
    const autoCollapseSubmenu = (event) => {

        if (!event.target.matches('li.submenu>a.dropdown-item.dropdown-toggle')) return;

        event.stopPropagation();

        const navbar = event.target.closest('nav.navbar');
        const targetSubmenu = event.target.parentElement.querySelector('ul.dropdown-menu');

        if (!targetSubmenu) return;

        navbar.querySelectorAll('li.submenu>ul.dropdown-menu.show')
            .forEach((subMenu) => {

                if (!subMenu.contains(targetSubmenu)) {

                    subMenu.classList.remove('show');

                    const toggle = subMenu.parentElement
                        .querySelector('a[aria-expanded="true"].dropdown-item.dropdown-toggle');

                    if (toggle) {
                        toggle.classList.remove('show');
                        toggle.setAttribute('aria-expanded', 'false');
                    }
                }
            });
    };

    document.querySelectorAll('nav.navbar')
        .forEach(navbar => navbar.addEventListener('click', autoCollapseSubmenu));


    /* ================================
       NOTIFICATION CORE
    ================================= */

    function buildNotificationItem(notification) {

        let notifData;

        try {
            notifData = JSON.parse(notification.data);
        } catch (e) {
            console.error('Invalid notification data', notification);
            return '';
        }

        const items = (notifData.code || 'No code').split(',');

        let config = {
            title: 'Notification',
            icon: 'bi-bell',
            url: '#'
        };

        if (notification.type.includes('DoNotification')) {
            config = {
                title: 'Delivery Order',
                icon: 'bi-truck',
                url: `/superuser/penjualan/notification/mark_as_read_do/${notification.id}/${notifData.id}`
            };
        }
        else if (notification.type.includes('SoNotification')) {
            config = {
                title: 'Sales Order',
                icon: 'bi-cart',
                url: `/superuser/penjualan/notification/mark_as_read_so/${notification.id}/${notifData.id}`
            };
        }
        else if (notification.type.includes('PayableNotification')) {
            config = {
                title: 'Payment',
                icon: 'bi-credit-card',
                url: `/superuser/penjualan/notification/mark_as_read_payable/${notification.id}`
            };
        }
        else if (notification.type.includes('ReceivingNotification')) {
            config = {
                title: 'Receiving',
                icon: 'bi-box-seam',
                url: `/superuser/penjualan/notification/mark_as_read_only/${notification.id}`
            };
        }

        return items.map(item => `
            <div class="notification-item unread" data-url="${config.url}">
                <div class="d-flex align-items-start gap-2">
                    <i class="bi ${config.icon} fs-5 text-primary"></i>
                    <div class="flex-grow-1">
                        <div class="notification-title">
                            ${config.title} - ${item.trim()}
                        </div>
                        <div class="notification-text">
                            ${notifData.customer ?? ''} (${notifData.customer_kota ?? ''})
                        </div>
                        <div class="notification-time">
                            ${new Date(notification.created_at).toLocaleString()}
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }


    function updateBadge(count) {

        $('#notifCount')
            .text(count > 99 ? '99+' : count)
            .toggleClass('show', count > 0);

        $('#notifHeaderCount').text(count);
    }


    function reloadNotifications() {

        if (currentRequest) {
            currentRequest.abort(); // prevent overlapping
        }

        currentRequest = $.ajax({
            url: '{{ route('superuser.penjualan.notification.getNotifData') }}',
            type: 'GET',
            dataType: 'json'
        });

        currentRequest.done(function (response) {

            updateBadge(response.notifCount);

            const notifications = response.notifications || [];

            if (!notifications.length) {
                $('#notifList').html(`
                    <div class="notification-item text-center text-muted">
                        No new notifications
                    </div>
                `);
                return;
            }

            const html = notifications.map(buildNotificationItem).join('');
            $('#notifList').html(html);

        }).fail(function (xhr, status) {

            if (status !== 'abort') {
                console.error('Notification error:', xhr.responseText);
            }

        }).always(function () {
            currentRequest = null;
        });
    }


    /* ================================
       EVENT BINDING (NO INLINE CLICK)
    ================================= */

    $(document).on('click', '.notification-item', function () {
        const url = $(this).data('url');
        if (url) window.location.href = url;
    });


    $(document).on('click', '#markAllAsReadBtn', function (e) {

        e.preventDefault();

        $.post(
            '{{ route('superuser.penjualan.notification.unread_all_notif') }}',
            { _token: '{{ csrf_token() }}' }
        )
        .done(function () {
            reloadNotifications();
        })
        .fail(function (xhr) {
            console.error('Mark all error:', xhr.responseText);
        });
    });


    /* ================================
       SMART POLLING
    ================================= */

    function startPolling() {
        if (!notifInterval) {
            notifInterval = setInterval(reloadNotifications, 10000);
        }
    }

    function stopPolling() {
        if (notifInterval) {
            clearInterval(notifInterval);
            notifInterval = null;
        }
    }

    // Stop polling when tab not active
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            stopPolling();
        } else {
            reloadNotifications();
            startPolling();
        }
    });

    // Init
    reloadNotifications();
    startPolling();

})();
</script>
@endpush