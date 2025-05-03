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
</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="{{ route('superuser.index') }}">UNIFRA</a>
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
                        <li><a class="dropdown-item" href="{{ route('superuser.penjualan.setting_price.index') }}"><i class="fa-solid fa-address-book"></i> Pengaturan Harga</a></li>
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
                    </ul>
                </li>

                <!-- Gudang -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fa-solid fa-warehouse"></i> Gudang
                    </a>
                    <ul class="dropdown-menu">
                        @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Warehouse")
                        <li><a class="dropdown-item" href="{{ route('superuser.penjualan.delivery_order.index') }}"><i class="fa-solid fa-truck"></i> Delivery Order (DO)</a></li>
                        <li><a class="dropdown-item" href="{{ route('superuser.gudang.purchase_order.index') }}"><i class="fa-solid fa-shop"></i> Purchase order (PO)</a></li>
                        <li><a class="dropdown-item" href="{{ route('superuser.gudang.receiving.index') }}"><i class="fa-solid fa-receipt"></i> Receiving</a></li>
                        @endif
                        <li><a class="dropdown-item" href="{{ route('superuser.gudang.stock.index') }}"><i class="fa-solid fa-cubes"></i> Stock</a></li>
                        @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Warehouse")
                        <li><a class="dropdown-item" href="{{ route('superuser.gudang.stock_adjustment.index') }}"><i class="fa-solid fa-sliders"></i> Stock Adjustment</a></li>
                        @endif
                    </ul>
                </li>

                <!-- FAT -->
                @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Finance")
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fa-solid fa-chart-line"></i> FAT
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('superuser.finance.payable.index') }}"><i class="fa-solid fa-credit-card"></i> Payment</a></li>
                        <li><a class="dropdown-item" href="{{ route('superuser.finance.cashback.index') }}"><i class="fa-solid fa-code-branch"></i> Araya</a></li>
                        <!-- <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-chart-simple"></i> Accounting
                            </a>
                            <ul class="dropdown-menu">
                                <li class="submenu submenu-md dropend">
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

                                <li><a class="dropdown-item" href="{{ route('superuser.report.customer_type_brand_uv.index') }}">Report Register UV</a></li>
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
                                <!-- <li><a class="dropdown-item" href="{{ route('superuser.accounting.finance_simulation.page_report') }}"> Finance Simulation UV Report</a></li> -->
                            </ul>
                        </li>
                        <li class="submenu submenu-md dropend">
                            <a class="dropdown-item dropdown-toggle" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-coins"></i> Finance
                            </a>
                            <ul class="dropdown-menu">
                                <!-- <li><a class="dropdown-item" href="{{ route('superuser.finance.cashback.pageReport') }}">Araya Report</a></li> -->
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
                                        <li><a class="dropdown-item" href="{{ route('superuser.report.customer_order_variant_v2.index') }}">Customer - Produk</a></li>
                                        <li><a class="dropdown-item" href="{{ route('superuser.report.customer_order_variant.index') }}">Customer History</a></li>
                                        <li><a class="dropdown-item" href="{{ route('superuser.report.customer_type_zone.index') }}">Customer - Zoning</a></li>
                                        <li><a class="dropdown-item" href="{{ route('superuser.report.summary_customer_product.index') }}">Summary Customer - Produk</a></li>
                                        
                                    </ul>
                                </li>
                                <li class="submenu submenu-md dropend">
                                    <a class="dropdown-item dropdown-toggle" role="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">Produk</a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('superuser.report.product_performance.index') }}">Produk - Customer</a></li>
                                        <li><a class="dropdown-item" href="{{ route('superuser.report.product_high_sell.index') }}">Produk Penjualan Tertinggi</a></li>
                                        @if($superuser->division == "Management" OR $superuser->division == "Developer")
                                        <li><a class="dropdown-item" href="{{ route('superuser.master.product.pageReport') }}">Produk - Material</a></li>
                                        @endif
                                    </ul>
                                </li>
                                <li class="submenu submenu-md dropend">
                                    <a class="dropdown-item dropdown-toggle" role="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">Sales</a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('superuser.report.employee_performance.index') }}">Penjualan Salesman</a></li>
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
                                <li><a class="dropdown-item" href="{{ route('superuser.report.forecast_supplier.index') }}">Forcasting Principal</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.report.customer_type_brand.index') }}">Register Customer</a></li>
                                <li><a class="dropdown-item" href="{{ route('superuser.report.sales.index') }}">Penjualan</a></li>      
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
                                <!-- <li><a class="dropdown-item" href="{{ route('superuser.account.user.index') }}">Wilayah indonesia</a></li> -->
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
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell"></i> <span id="notifCount" class="badge badge-danger">{{ $notifCount > 0 ? $notifCount : '0' }}</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notificationDropdown">
                <div class="card" style="width: 45rem;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Last updates</h3>
                        <!-- Button for "Mark All as Read" -->
                        <form action="{{ route('superuser.penjualan.notification.unread_all_notif') }}" method="POST" id="markAllAsReadForm">
                            @csrf
                            <button type="button" class="btn btn-link btn-sm" id="markAllAsReadBtn">Mark All as Read</button>
                        </form>
                    </div>
                    <div class="list-group list-group-flush list-group-hoverable" id="notifList">
                        <!-- Notifications will be dynamically loaded here -->
                    </div>
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
<script type="text/javascript">
    const autoCollapseSubmenu = (event) => {
    if (event.target.matches('li.submenu>a.dropdown-item.dropdown-toggle')) {
        // prevent parent dropdown menu from collapsing on click
        event.stopPropagation();
        // find parent navbar element
        const navbar = event.target.closest('nav.navbar');
        // get the target submenu (the ul.dropdown-menu sibling of the clicked item)
        targetSubmenu = event.target.parentElement.querySelector('ul.dropdown-menu');
        // find any open submenu items
        // set class and aria attributes to closed unless element is clicked element or direct ancestor
        if (targetSubmenu) {
            navbar.querySelectorAll('li.submenu>ul.dropdown-menu.show').forEach((subMenu) => {
                if (!subMenu.contains(targetSubmenu)) {
                    // dropdown toggle link - remove 'show' class, set aria-expanded to fale
                    subMenu.classList.remove('show');
                    // Get the sibling ul.dropdown-menu
                    const dropDownToggle = subMenu.parentElement.querySelector('a[aria-expanded="true"].dropdown-item.dropdown-toggle');
                    if (dropDownToggle) {
                        // Remove the 'show' class
                        dropDownToggle.classList.remove('show');
                        dropDownToggle.setAttribute('aria-expanded', 'false');
                    }
                }
            });
        };
        }
    };

    document.querySelectorAll('nav.navbar').forEach((navbar) => {
        navbar.addEventListener('click', autoCollapseSubmenu);
    });

    function reloadNotifications() {
        $.ajax({
            url: '{{ route('superuser.penjualan.notification.getNotifData') }}',
            type: 'GET',
            success: function(response) {
                // Update notification count
                $('#notifCount').text(response.notifCount > 0 ? response.notifCount : '0');

                // Update notification list
                var notifications = response.notifications;
                var notifHtml = '';

                if (notifications.length > 0) {
                    notifications.forEach(function(notification) {
                        var notifData = JSON.parse(notification.data);
                        var items = (notifData.code || 'No code').split(',');
                        var alertType;
                        var notifType;
                        var actionUrl;

                        if (notification.type === 'App\\Notifications\\DoNotification') {
                            if (notifData.status === 2) {
                                alertType = 'alert-success';
                                notifType = 'New DO:';
                                actionUrl = `/superuser/penjualan/notification/mark_as_read_do/${notification.id}/${notifData.id}`;
                            } else if (notifData.status === 6) {
                                alertType = 'alert-info';
                                notifType = 'DO Update Resi:';
                                actionUrl = `/superuser/penjualan/notification/mark_as_read_only/${notification.id}`;
                            }
                        } else if (notification.type === 'App\\Notifications\\SoNotification') {
                            alertType = 'alert-info';
                            notifType = 'New SO:';
                            actionUrl = `/superuser/penjualan/notification/mark_as_read_so/${notification.id}/${notifData.id}`;
                        } else if (notification.type === 'App\\Notifications\\PayableNotification') {
                            if (notifData.status === 2) {
                                alertType = 'alert-success';
                                notifType = 'Approved Payable:';
                            } else {
                                alertType = 'alert-warning';
                                notifType = 'New Payable:';
                            }
                            actionUrl = `/superuser/penjualan/notification/mark_as_read_payable/${notification.id}`;
                        } else if (notification.type === 'App\\Notifications\\ReceivingNotification') {
                            alertType = 'alert-info';
                            notifType = 'Receiving Approved:';
                            actionUrl = `/superuser/penjualan/notification/mark_as_read_only/${notification.id}`;
                        }

                        items.forEach(function(item) {
                            notifHtml += `
                                <div class="list-group-item">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <span class="status-dot status-dot-animated bg-red d-block"></span>
                                        </div>
                                        <div class="col text-truncate">
                                            <div class="alert ${alertType}" role="alert">
                                                [${new Date(notifData.created_at).toLocaleDateString()}] Customer ${notifData.customer} (${notifData.customer_kota}) ${notifType} <b>${item}</b>
                                                <form action="${actionUrl}" method="POST">
                                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                    <button type="submit" class="btn btn-link">Process</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
                        });
                    });
                } else {
                    notifHtml = `
                        <div class="list-group-item">
                            <div class="row align-items-center">
                                <div class="col text-truncate">
                                    <span class="text-body d-block">No notifications</span>
                                </div>
                            </div>
                        </div>`;
                }

                $('#notifList').html(notifHtml);
            },
            error: function(xhr) {
                console.error('An error occurred:', xhr.responseText);
            }
        });
    }

    // Reload notifications every 5 seconds
    setInterval(reloadNotifications, 5000);

    // Initial load
    reloadNotifications();

    $(document).on('click', '#markAllAsReadBtn', function(e) {
        e.preventDefault();

        // Submit the form to mark all notifications as read
        $.ajax({
            url: '{{ route('superuser.penjualan.notification.unread_all_notif') }}', // URL diperbaiki
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                // Reload notifications after success
                alert('Semua notifikasi telah ditandai sebagai telah dibaca.');
                reloadNotifications(); // Panggil fungsi reload untuk memperbarui daftar notifikasi
            },
            error: function(xhr) {
                console.error('An error occurred:', xhr.responseText);
                alert('Gagal menandai semua notifikasi sebagai telah dibaca.');
            }
        });
    });
</script>
@endpush