
        <nav class="bottom-navbar">
          <div class="container">
            <ul class="nav page-navigation">
              <li class="nav-item">
                <a class="nav-link {{ (Route::currentRouteName() == 'superuser.index') ? 'active' : '' }}" href="{{ route('superuser.index') }}">
                  <i class="mdi mdi-home-variant menu-icon"></i>
                  <span class="menu-title">Dashboard</span>
                </a>
              </li>
              <li class="nav-item {{ is_open_route('superuser/master') }}">
                <a href="#" class="nav-link">
                  <i class="mdi mdi mdi-folder-multiple menu-icon"></i>
                  <span class="menu-title">Master</span>
                  <i class="menu-arrow"></i>
                </a>
                <div class="submenu">
                  <ul class="submenu-item">
                    <li class="nav-item">
                      <a class="nav-link" href="{{ route('superuser.master.company.show') }}" target="_blank">Company</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="{{ route('superuser.master.branch_office.index') }}" target="_blank">Branch Office</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="{{ route('superuser.master.warehouse.index') }}" target="_blank">Warehouse</a>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Product <span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="{{ route('superuser.master.product.index') }}" target="_blank">Product List</a></li>
                            <li><a href="{{ route('superuser.master.product_category.index') }}" target="_blank">Product Category</a></li>
                            {{--<li><a href="{{ route('superuser.master.product_type.index') }}" target="_blank">Product Type</a></li>--}}
                            {{--<li><a href="{{ route('superuser.master.brand_lokal.index') }}" target="_blank">Brand Lokal</a></li>--}}
                            <li><a href="{{ route('superuser.master.brand_reference.index') }}" target="_blank">Brand Fragrantica</a></li>
                            <li><a href="{{ route('superuser.master.sub_brand_reference.index') }}" target="_blank">Searah</a></li>
                            <li><a href="{{ route('superuser.master.unit.index') }}" target="_blank">Unit</a></li>
                        </ul>
                    </li>
                    {{--<li class="nav-item">
                      <a class="nav-link" href="{{ route('superuser.master.unit.index') }}" target="_blank">Unit</a>
                    </li>--}}
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Store <span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="{{ route('superuser.master.customer.index') }}" target="_blank">Store List</a></li>
                            <li><a href="{{ route('superuser.master.customer_category.index') }}" target="_blank">Store Category</a></li>
                            <li><a href="{{ route('superuser.master.customer_type.index') }}" target="_blank">Store Type</a></li>
                            <!-- <li><a href="{{ route('superuser.master.customer_contact.index') }}" target="_blank">Contact</a></li> -->
                        </ul>
                    </li>
                    {{--<li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Brand <span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            
                        </ul>
                    </li>--}}
                    <li class="nav-item">
                      <a class="nav-link" href="{{ route('superuser.master.question.index') }}" target="_blank">Question</a>
                    </li>
                    <li class="nav-item">
                      <a href="{{ route('superuser.master.contact.index') }}" target="_blank" class="nav-link {{ is_active_route('superuser.master.contact.index') }}">
                        Contact
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="{{ route('superuser.master.vendor.index') }}" target="_blank" class="nav-link {{ is_active_route('superuser.master.vendor.index') }}">
                        Vendor
                      </a>
                    </li>
                    {{--<li class="nav-item">
                      <a href="{{ route('superuser.master.ekspedisi.index') }}" target="_blank" class="nav-link {{ is_active_route('superuser.master.ekspedisi.index') }}">
                        Ekspedisi
                      </a>
                    </li>--}}
                  </ul>
                </div>
              </li>

              <li class="nav-item {{ is_open_route('superuser/penjualan') }}">
                <a href="#" class="nav-link">
                  <i class="mdi mdi-shopping menu-icon"></i>
                  <span class="menu-title">Penjualan</span>
                  <i class="menu-arrow"></i>
                </a>
                <div class="submenu">
                  <ul class="submenu-item">
                    <li class="nav-item">
                      <a href="{{ route('superuser.penjualan.setting_price.index') }}"  target="_blank" class="nav-link {{ is_active_route('superuser.master.ekspedisi.index') }}">
                        Setting Price
                      </a>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Sales Order <span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            <li class="nav-item"><a href="{{ route('superuser.penjualan.sales_order.index_awal') }}" target="_blank">Sales Order Awal (SOA)</a></li>
                            <li class="nav-item"><a href="{{ route('superuser.penjualan.sales_order.index_lanjutan') }}" target="_blank">Sales Order Lanjutan (SOL)</a></li>
                            <li class="nav-item"><a href="{{ route('superuser.penjualan.sales_order.index_mutasi') }}" target="_blank">Sales Order Mutation (SOM)</a></li>
                            <li class="nav-item"><a href="{{ route('superuser.penjualan.canvasing.index') }}" target="_blank">Canvasing (Sales Mutation)</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                      <a href="{{ route('superuser.penjualan.packing_order.index') }}" target="_blank" class="nav-link {{ is_active_route('superuser.master.ekspedisi.index') }}">
                        Packing Order (PO)
                      </a>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Delivery Order (DO) <span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            <li class="nav-item"><a href="{{ route('superuser.penjualan.delivery_order.index') }}" target="_blank">Proses DO (DO)</a></li>
                            <li class="nav-item"><a href="{{ route('superuser.penjualan.delivery_order_mutation.index') }}" target="_blank">Delivery Order Mutation (DOM)</a></li>
                        </ul>
                    </li>
                  </ul>
                </div>
              </li>

              <li class="nav-item {{ is_open_route('superuser/gudang') }}">
                <a href="#" class="nav-link">
                  <i class="mdi mdi-home-modern menu-icon"></i>
                  <span class="menu-title">Warehouse</span>
                  <i class="menu-arrow"></i>
                </a>
                <div class="submenu">
                  <ul class="submenu-item">
                    <li class="nav-item">
                      <a class="nav-link" href="{{ route('superuser.gudang.stock.index') }}" target="_blank">Stock</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="{{ route('superuser.gudang.stock_adjustment.index') }}" target="_blank">Stock Adjustment</a>
                    </li>
                    
                  </ul>
                </div>
              </li>

              <li class="nav-item {{ is_open_route('superuser/finance') }}">
                <a href="#" class="nav-link">
                  <i class="mdi mdi-credit-card-multiple menu-icon"></i>
                  <span class="menu-title">Finance</span>
                  <i class="menu-arrow"></i>
                </a>
                <div class="submenu">
                  <ul class="submenu-item">
                    <li class="nav-item">
                      <a class="nav-link" href="{{ route('superuser.finance.invoicing.index') }}" target="_blank">Invoicing</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="{{ route('superuser.finance.payable.index') }}" target="_blank">Payable</a>
                    </li>
                  </ul>
                </div>
              </li>

              <li class="nav-item {{ is_open_route('superuser/report') }}">
                <a href="#" class="nav-link">
                  <i class="mdi mdi-chart-bar menu-icon"></i>
                  <span class="menu-title">Report</span>
                  <i class="menu-arrow"></i>
                </a>
                <div class="submenu">
                  <ul class="submenu-item">
                    <li class="nav-item">
                      <a class="nav-link" href="{{ route('superuser.report.sales.index') }}" target="_blank">Sales</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="{{ route('superuser.report.revenue.index') }}" target="_blank">Revenue</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="{{ route('superuser.report.product_performance.index') }}" target="_blank">Product Performance</a>
                    </li>
                  </ul>
                </div>
              </li>

              @if($superuser->canAny(['superuser-manage', 'salesperson-manage']))
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="mdi mdi-settings menu-icon"></i>
                  <span class="menu-title">Setting</span>
                  <i class="menu-arrow"></i>
                </a>
                <div class="submenu">
                  <ul class="submenu-item">
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Account<span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            @if($superuser->can('superuser-manage'))
                            <li class="nav-item"><a href="{{ route('superuser.account.superuser.index') }}">Superuser</a></li>
                            @endif
                            <li class="nav-item"><a href="{{ route('superuser.account.user.index') }}">User</a></li>
                            @if($superuser->can('salesperson-manage'))
                            <li class="nav-item"><a href="{{ route('superuser.account.sales_person.index') }}">Sales Person</a></li>
                            @endif
                        </ul>
                    </li>
                    @role('Developer|SuperAdmin', 'superuser')
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Menus<span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="{{ route('superuser.setting.menu.index') }}">Pages</a></li>
                            <li><a href="{{ route('superuser.utility.settings.index') }}">Utility</a></li>
                            <li><a href="{{ route('superuser.utility.indonesian_teritory') }}">Indonesian Teritory</a></li>
                        </ul>
                    </li>
                    @endrole
                    @role('Developer', 'superuser')
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Developer Zone<span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="{{ route('superuser.boilerplate.index') }}">Boilerplate</a></li>
                            <li><a href="{{ url('superuser/telescope') }}">Telescope</a></li>
                            <li><a href="{{ route('superuser.terminal') }}">Terminal</a></li>
                            <li><a href="{{ route('superuser.gate.index') }}">Gate (Authorization)</a></li>
                        </ul>
                    </li>
                    @endrole
                  </ul>
                </div>
              </li>
              @endif



              <li class="nav-item">
                <div class="nav-link d-flex">
                  <i class="mdi mdi-account-settings-variant menu-icon"></i>
                  <div class="nav-item dropdown">
                    <a class="nav-link count-indicator dropdown-toggle text-white font-weight-semibold" id="notificationDropdown" href="#" data-bs-toggle="dropdown"><i class="mdi mdi-account"></i>{{ $superuser->username }} </a>
                    <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="notificationDropdown">
                      <a class="dropdown-item" href="{{ route('superuser.profile.index') }}">
                        <i class="mdi mdi-face"></i> Profile </a>
                      <div class="dropdown-divider"></div>
                      <a class="dropdown-item" href="{{ route('superuser.logout') }}">
                        <i class="mdi mdi-logout me-2 text-primary"></i> Logout </a>
                    </div>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </nav>

