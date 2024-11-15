<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="{{ route('superuser.index') }}">
        <img src="{{ asset('superuser_assets/media/logo_ppi.png') }}" alt="MyApp Logo" width="50" height="50" class="d-inline-block align-top">
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavDropdown">
        <div class="mx-auto d-flex justify-content-center flex-grow-1">
            <ul class="navbar-nav">
                <li class="nav-item active">
                    <a class="nav-link" href="{{ route('superuser.index') }}">
                        <i class="fa-solid fa-house"></i> Dashboard <span class="sr-only">(current)</span>
                    </a>
                </li>

                <!-- Master -->
                @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management")
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa-solid fa-folder-open"></i> Master
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                        <a class="dropdown-item" href="{{ route('superuser.master.warehouse.index') }}"><i class="fa-solid fa-warehouse"></i> Gudang</a>
                        <a class="dropdown-item" href="{{ route('superuser.master.contact.index') }}"><i class="fa-solid fa-address-book"></i> Kontak</a>
                        <a class="dropdown-item" href="{{ route('superuser.master.mitra.index') }}"><i class="fa-solid fa-code-branch"></i> Mitra</a>
                        <a class="dropdown-item dropdown-toggle" href="#"><i class="fa-solid fa-copyright"></i> Produk</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('superuser.master.product.index') }}"><i class="fa-solid fa-list"></i> Index</a></li>
                            <li><a class="dropdown-item" href="{{ route('superuser.master.product_category.index') }}"><i class="fa-solid fa-layer-group"></i> Kategori</a></li>
                            <li><a class="dropdown-item" href="{{ route('superuser.master.sub_brand_reference.index') }}"><i class="fa-solid fa-timeline"></i> Searah</a></li>
                            <li><a class="dropdown-item" href="{{ route('superuser.master.product_type.index') }}"><i class="fa-solid fa-file-lines"></i> Tipe</a></li>
                        </ul>
                        <a class="dropdown-item dropdown-toggle" href="#"><i class="fa-solid fa-store"></i> Customer</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('superuser.master.customer.index') }}"><i class="fa-solid fa-list"></i> Index</a></li>
                            <li><a class="dropdown-item" href="{{ route('superuser.master.customer_category.index') }}"><i class="fa-solid fa-layer-group"></i> Kategori</a></li>
                        </ul>
                        <a class="dropdown-item" href="{{ route('superuser.master.vendor.index') }}"><i class="fa-solid fa-building-columns"></i> Vendor</a>
                    </div>
                </li>
                @endif

                <!-- Penjualan -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa-solid fa-basket-shopping"></i> Penjualan
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                        <a class="dropdown-item" href="{{ route('superuser.finance.invoicing.index') }}"><i class="fa-solid fa-file-invoice-dollar"></i> Invoice</a>
                        @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management")
                        <a class="dropdown-item" href="{{ route('superuser.penjualan.setting_price.index') }}"><i class="fa-solid fa-money-check-dollar"></i> Pengaturan Harga</a>
                        @endif
                        <a class="dropdown-item dropdown-toggle" href="#"><i class="fa-solid fa-cart-plus"></i> Sales Order</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('superuser.penjualan.sales_order_indent.index') }}"><i class="fa-solid fa-indent"></i> Indent</a></li>
                            @if($superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Developer")
                            <li><a class="dropdown-item" href="{{ route('superuser.penjualan.sales_order_kontrak.index') }}"><i class="fa-solid fa-file-signature"></i> Kontrak</a></li>
                            @endif
                            @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management")
                            <li><a class="dropdown-item" href="{{ route('superuser.penjualan.so_proforma.index') }}"><i class="fa-solid fa-file-prescription"></i> Proforma</a></li>
                            @endif
                            <li class="dropdown-submenu">
                                <a class="dropdown-item dropdown-toggle" href="#"><i class="fa-solid fa-file-invoice"></i> Regular</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('superuser.penjualan.sales_order.index_awal') }}"><i class="fa-solid fa-arrow-up-wide-short"></i> Awal</a></li>
                                    @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management")
                                    <li><a class="dropdown-item" href="{{ route('superuser.penjualan.sales_order.index_lanjutan') }}"><i class="fa-solid fa-arrow-down-wide-short"></i> Lanjutan</a></li>
                                    @endif
                                </ul>
                            </li>
                            @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management")
                            <li class="dropdown-submenu">
                                <a class="dropdown-item dropdown-toggle" href="#"><i class="fa-solid fa-file-invoice"></i> PPN</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('superuser.penjualan.sales_order_ppn.index_ppn_awal') }}"><i class="fa-solid fa-arrow-up-wide-short"></i> Awal</a></li>
                                    <li><a class="dropdown-item" href="{{ route('superuser.penjualan.sales_order_ppn.index_ppn_lanjutan') }}"><i class="fa-solid fa-arrow-down-wide-short"></i> Lanjutan</a></li>
                                </ul>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>

                <!-- Gudang DO -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa-solid fa-warehouse"></i> Gudang
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                    @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Warehouse")
                        <a class="dropdown-item" href="{{ route('superuser.penjualan.delivery_order.index') }}"><i class="fa-solid fa-truck"></i> Delivery Order (DO)</a>
                        <a class="dropdown-item" href="{{ route('superuser.gudang.purchase_order.index') }}"><i class="fa-solid fa-shop"></i> Purchase Order (PO)</a>
                        <a class="dropdown-item" href="{{ route('superuser.gudang.receiving.index') }}"><i class="fa-solid fa-receipt"></i> Receiving</a>
                        @endif
                        <a class="dropdown-item" href="{{ route('superuser.gudang.stock.index') }}"><i class="fa-solid fa-cubes"></i> Stock</a>
                        @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Warehouse")
                        <a class="dropdown-item" href="{{ route('superuser.gudang.stock_adjustment.index') }}"><i class="fa-solid fa-sliders"></i> Stock Adjustment</a>
                        @endif
                    </div>
                </li>

                <!-- FAT / Accounting & Finance -->
                @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Finance")
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa-solid fa-chart-line"></i> FAT
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                        <a class="dropdown-item dropdown-toggle" href="#"><i class="fa-solid fa-chart-simple"></i> Accounting</a>
                        <ul class="dropdown-menu">
                            <li class="dropdown-submenu">
                                <a class="dropdown-item dropdown-toggle" href="#"><i class="fa-solid fa-code-branch"></i> Unifra</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('superuser.accounting.invoice_tax.index_jual') }}">Jual</a></li>
                                    <li><a class="dropdown-item" href="{{ route('superuser.accounting.invoice_tax.index_beli') }}">Beli</a></li>
                                </ul>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('superuser.accounting.product_finance.index') }}"><i class="fa-solid fa-copyright"></i> Product PPN</a></li>
                        </ul>
                        <a class="dropdown-item dropdown-toggle" href="#"><i class="fa-solid fa-coins"></i> Finance</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('superuser.finance.cashback.index') }}"><i class="fa-solid fa-code-branch"></i> Araya</a></li>
                            <li><a class="dropdown-item" href="{{ route('superuser.finance.payable.index') }}"><i class="fa-solid fa-credit-card"></i> Pembayaran</a></li>
                        </ul>
                    </div>
                </li>
                @endif
                
                <!-- Laporan -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa-solid fa-file-export"></i> Laporan
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                        @if($superuser->division == "Developer" OR $superuser->division == "Management")
                        <a class="dropdown-item dropdown-toggle" href="#"><i class="fa-solid fa-chart-simple"></i> Accounting</a>
                        <ul class="dropdown-menu">
                            <li class="dropdown-submenu">
                                <a class="dropdown-item dropdown-toggle" href="#"> UNIFRA Report</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('superuser.accounting.invoice_tax.pageReportBeli') }}">Beli</a></li>
                                    <li><a class="dropdown-item" href="{{ route('superuser.accounting.invoice_tax.pageReportJual') }}">Jual</a></li>
                                </ul>
                            </li>
                            <li><a class="dropdown-item" href="{{ route('superuser.report.revenue.index') }}">Laporan Pendapatan</a></li>
                        </ul>
                        @endif
                        @if($superuser->division == "Developer" OR $superuser->division == "Management" OR $superuser->division == "Finance")
                        <a class="dropdown-item dropdown-toggle" href="#"><i class="fa-solid fa-coins"></i> Finance</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('superuser.finance.cashback.pageReport') }}">Araya Report</a></li>
                            <li><a class="dropdown-item" href="{{ route('superuser.finance.payable.pageReport') }}">Laporan Pembayaran</a></li>
                            <li><a class="dropdown-item" href="{{ route('superuser.finance.invoicing.pageReport') }}">Piutang Faktur</a></li>
                            
                        </ul>
                        @endif
                        @if($superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Developer" OR $superuser->division == "Finance")
                        <a class="dropdown-item dropdown-toggle" href="#"><i class="fa-solid fa-file-import"></i> Oprasional</a>
                        <ul class="dropdown-menu">
                            <li class="dropdown-submenu">
                                <a class="dropdown-item dropdown-toggle" href="#">Customer</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('superuser.report.customer_order_variant_v2.index') }}">Customer - Produk</a></li>
                                    <li><a class="dropdown-item" href="{{ route('superuser.report.customer_order_variant.index') }}">Customer History</a></li>
                                    <li><a class="dropdown-item" href="{{ route('superuser.report.customer_type_zone.index') }}">Customer - Zoning</a></li>
                                    <li><a class="dropdown-item" href="{{ route('superuser.report.sales.index') }}">Penjualan</a></li>
                                </ul>
                            </li>
                            <li class="dropdown-submenu">
                                <a class="dropdown-item dropdown-toggle" href="#">Produk</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('superuser.report.product_performance.index') }}">Produk - Customer</a></li>
                                    <li><a class="dropdown-item" href="{{ route('superuser.report.product_high_sell.index') }}">Produk Tertinggi</a></li>
                                    @if($superuser->division == "Management" OR $superuser->division == "Developer")
                                    <li><a class="dropdown-item" href="{{ route('superuser.master.product.pageReport') }}">Produk - Material</a></li>
                                    @endif
                                </ul>
                            </li>
                            <li class="dropdown-submenu">
                                <a class="dropdown-item dropdown-toggle" href="#">Sales</a>
                                <ul class="dropdown-menu">
                                    <!-- <li><a class="dropdown-item" href="#">Sales - Produk</a></li>
                                    <li><a class="dropdown-item" href="#">Sales - Customer</a></li> -->
                                    <li><a class="dropdown-item" href="{{ route('superuser.report.employee_performance.index') }}">Penjualan Sales</a></li>
                                </ul>
                            </li>
                        </ul>
                        @endif
                        @if($superuser->division == "Developer" OR $superuser->division == "Management")
                        <a class="dropdown-item dropdown-toggle" href="#"><i class="fa-solid fa-file-import"></i> Management</a>
                        <ul class="dropdown-menu">
                            <li class="dropdown-submenu">
                                <a class="dropdown-item" href="{{ route('superuser.report.forecast_supplier.index') }}">Forcasting Principal</a>
                                <a class="dropdown-item" href="{{ route('superuser.report.customer_type_brand.index') }}">Register Customer</a>
                            </li>
                        </ul>
                        @endif
                    </div>
                </li>

                <!-- Setting -->
                @if($superuser->canAny(['superuser-manage', 'salesperson-manage']))
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa-solid fa-gears"></i> Pengaturan
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                        <div class="dropdown-divider"></div>
                        @if($superuser->can('superuser-manage'))
                        <a class="dropdown-item dropdown-toggle" href="#"><i class="fa-solid fa-user-gear"></i> Akun</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('superuser.account.log_activity.index') }}">Aktivitas Log</a></li>
                            <li><a class="dropdown-item" href="{{ route('superuser.account.superuser.index') }}">Superuser</a></li>
                            <li><a class="dropdown-item" href="{{ route('superuser.account.user.index') }}">User</a></li>
                            <li><a class="dropdown-item" href="{{ route('superuser.account.sales_person.index') }}">Sales Person</a></li>
                        </ul>
                        @endif
                        @role('Developer|SuperAdmin', 'superuser')
                        <a class="dropdown-item dropdown-toggle" href="#"><i class="fa-solid fa-bars"></i> Menu</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Halaman</a></li>
                            <li><a class="dropdown-item" href="#">kegunaan</a></li>
                            <li><a class="dropdown-item" href="#">Wilayah Indonesia</a></li>
                        </ul>
                        @endrole
                        @role('Developer', 'superuser')
                        <a class="dropdown-item dropdown-toggle" href="#"><i class="fa-brands fa-connectdevelop"></i> Zona Pengembang</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('superuser.backup.index') }}">Backup Database</a></li>
                            <li><a class="dropdown-item" href="{{ route('superuser.boilerplate.index') }}">Boilerplate</a></li>
                            <li><a class="dropdown-item" href="{{ url('superuser/telescope') }}">Telescope</a></li>
                            <li><a class="dropdown-item" href="{{ route('superuser.terminal') }}">Terminal</a></li>
                            <li><a class="dropdown-item" href="{{ route('superuser.gate.index') }}">Gate (Authorization)</a></li>
                        </ul>
                        @endrole
                    </div>
                </li>
                @endif
            </ul>
        </div>

        <!-- User Login & Notification -->
        

        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-bell"></i> <!-- FontAwesome icon for bell -->
                    <span class="badge badge-danger">{{ $notifCount > 0 ? $notifCount : '0' }}</span> <!-- Example notification count -->
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notificationDropdown">
                    <div class="card" style="width: 45rem;">
                        <div class="card-header">
                            <h3 class="card-title">Last updates</h3>
                        </div>
                        <div class="list-group list-group-flush list-group-hoverable" id="notifList">
                              <!-- Notifications will be dynamically loaded here -->
                        </div>
                    </div>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa-solid fa-user-tie"></i>
                    {{ $superuser->username }}
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="profileDropdown">
                    <a class="dropdown-item" href="{{ route('superuser.profile.index') }}"><i class="fa-solid fa-id-card"></i> Profile</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="{{ route('superuser.logout') }}"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                </div>
            </li>
        </ul>
    </div>
</nav>

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
        $('.dropdown-menu a.dropdown-toggle').on('click', function(e) {
            var $el = $(this);
            var $subMenu = $el.next(".dropdown-menu");

            // Always add the 'show' class to keep the submenu visible
            $subMenu.addClass('show');
            $el.parent("li").addClass('show');

            // Prevent any parent dropdowns from hiding the submenu
            $el.parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function(e) {
                e.preventDefault(); // Prevent hiding
                e.stopPropagation(); // Stop the event from propagating
                $subMenu.addClass('show');
            });

            return false; // Prevent the default action
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
                          var pecah = (notifData.code || 'No code').split(',');
                          var alertType = notification.type === 'App\\Notifications\\DoNotification' ? 'alert-success' : 'alert-info';
                          var notifType = notification.type === 'App\\Notifications\\DoNotification' ? 'New DO:' : 'New SO:';
                          var actionUrl = notification.type === 'App\\Notifications\\DoNotification' 
                              ? `/superuser/penjualan/notification/unread_notif_do/${notification.id}/${notifData.id}`
                              : `/superuser/penjualan/notification/unread_notif_so/${notification.id}/${notifData.id}`;

                          pecah.forEach(function(item) {
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
        };

        // Reload notifications every 5 seconds
        setInterval(reloadNotifications, 5000);

        // Initial load
        reloadNotifications();
    });
</script>
@endpush