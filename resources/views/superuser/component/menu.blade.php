<div class="horizontal-menu">
      <nav class="bottom-navbar">
        <div class="container">
            <ul class="nav page-navigation">
              <li class="nav-item">
                <a class="nav-link {{ (Route::currentRouteName() == 'superuser.index') ? 'active' : '' }}" href="{{ route('superuser.index') }}">
                  <i class="mdi mdi-file-document-box menu-icon"></i>
                  <span class="menu-title">Dashboard</span>
                </a>
              </li>
              <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="mdi mdi-cube-outline menu-icon"></i>
                    <span class="menu-title">Master</span>
                    <i class="menu-arrow"></i>
                  </a>
                  <div class="submenu">
                      <ul>
                          <li class="nav-item"><a class="nav-link" href="{{ route('superuser.master.branch_office.index') }}">Branch Office</a></li>
                          <li class="nav-item"><a class="nav-link" href="{{ route('superuser.master.company.show') }}">Company</a></li>
                          <li class="nav-item"><a class="nav-link" href="{{ route('superuser.master.contact.index') }}">Contact</a></li>
                          <li class="nav-item"><a class="nav-link" href="{{ route('superuser.master.customer.index') }}">Customer</a></li>
                          <li class="nav-item"><a class="nav-link" href="{{ route('superuser.master.product.index') }}">Product</a></li>
                          <li class="nav-item"><a class="nav-link" href="{{ route('superuser.master.vendor.index') }}">Vendor</a></li>
                          <li class="nav-item"><a class="nav-link" href="{{ route('superuser.master.warehouse.index') }}">Warehouse</a></li>
                      </ul>
                  </div>
              </li>
              <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="mdi mdi-sale menu-icon"></i>
                    <span class="menu-title">Sales Order</span>
                    <i class="menu-arrow"></i>
                  </a>
                  <div class="submenu">
                      <ul>
                          <li class="nav-item"><a class="nav-link" href="{{ route('superuser.penjualan.sales_order.index_awal') }}">Sales Order Non PPN</a></li>
                          <li class="nav-item"><a class="nav-link" href="{{ route('superuser.penjualan.sales_order_ppn.index') }}">Sales Order PPN</a></li>
                      </ul>
                  </div>
              </li>
              <li class="nav-item">
                  <a href="{{ route('superuser.penjualan.delivery_order.index') }}" class="nav-link">
                    <i class="mdi mdi-truck-delivery menu-icon"></i>
                    <span class="menu-title">Delivery Order</span>
                    <i class="menu-arrow"></i>
                  </a>
                  <!-- <div class="submenu">
                      <ul>
                          <li class="nav-item"><a class="nav-link" href="{{ route('superuser.penjualan.sales_order.index_awal') }}">Sales Order Non PPN</a></li>
                          <li class="nav-item"><a class="nav-link" href="{{ route('superuser.penjualan.sales_order_ppn.index') }}">Sales Order PPN</a></li>
                      </ul>
                  </div> -->
              </li>
              <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="mdi mdi-home-variant menu-icon"></i>
                    <span class="menu-title">Warehouse</span>
                    <i class="menu-arrow"></i>
                  </a>
                  <div class="submenu">
                      <ul>
                          <li class="nav-item"><a class="nav-link" href="{{ route('superuser.gudang.stock.index') }}">Stock</a></li>
                          <li class="nav-item"><a class="nav-link" href="{{ route('superuser.gudang.stock_adjustment.index') }}">Stock Adjustment</a></li>
                          <li class="nav-item"><a class="nav-link" href="{{ route('superuser.gudang.purchase_order.index') }}">Purchase Order</a></li>
                      </ul>
                  </div>
              </li>
              <li class="nav-item">
                  <a href="#" class="nav-link">
                    <i class="mdi mdi-bank menu-icon"></i>
                    <span class="menu-title">Finance</span>
                    <i class="menu-arrow"></i>
                  </a>
                  <div class="submenu">
                      <ul>
                          <li class="nav-item"><a class="nav-link" href="{{ route('superuser.finance.invoicing.index') }}">Invoicing</a></li>
                          <li class="nav-item"><a class="nav-link" href="{{ route('superuser.finance.payable.index') }}">Payable</a></li>
                      </ul>
                  </div>
              </li>
              <li class="nav-item nav-profile dropdown">
                  <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" id="profileDropdown">
                    <span class="nav-profile-name">{{ $superuser->username }}</span>
                    <span class="online-status"></span>
                    <img src="{{ asset('superuser_assets/new_ui/images/img_avatar.png') }}" alt="Avatar" class="avatar" style="vertical-align: middle; width: 50px; height: 50px; border-radius: 50%;">
                  </a>
                  <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
                      <a class="dropdown-item">
                        <i class="mdi mdi-settings text-primary"></i>
                        Settings
                      </a>
                      <a class="dropdown-item" href="{{ route('superuser.logout') }}">
                        <i class="mdi mdi-logout text-primary"></i>
                        Logout
                      </a>
                  </div>
                </li>
            </ul>
        </div>
      </nav>
    </div>