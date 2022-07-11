<ul class="nav-main">
  <li>
    <a href="{{ route('superuser.index') }}" class="{{ (Route::currentRouteName() == 'superuser.index') ? 'active' : '' }}">
      <i class="si si-home"></i>
      <span class="sidebar-mini-hide">Dashboard</span>
    </a>
  </li>

  <li class="{{ is_open_route('superuser/master') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#">
      <i class="si si-folder-alt"></i>
      <span class="sidebar-mini-hide">
        Master
      </span>
    </a>
    <ul>
      <li>
        <a href="{{ route('superuser.master.company.show') }}" class="{{ is_active_route('superuser.master.company.show') }}">
          Company
        </a>
      </li>
      <li>
        <a href="{{ route('superuser.master.branch_office.index') }}" class="{{ is_active_route('superuser.master.branch_office.index') }}">
          Branch Office
        </a>
      </li>
      <li>
        <a href="{{ route('superuser.master.warehouse.index') }}" class="{{ is_active_route('superuser.master.warehouse.index') }}">
          Warehouse
        </a>
      </li>
      <li>
        <a href="{{ route('superuser.master.product.index') }}" class="{{ is_active_route('superuser.master.product.index') }}">
          Product
        </a>
      </li>
      <li>
        <a href="{{ route('superuser.master.product_category.index') }}" class="{{ is_active_route('superuser.master.product_category.index') }}">
          Product Category
        </a>
      </li>
      <li>
        <a href="{{ route('superuser.master.product_type.index') }}" class="{{ is_active_route('superuser.master.product_type.index') }}">
          Product Type
        </a>
      </li>
      <li>
        <a href="{{ route('superuser.master.unit.index') }}" class="{{ is_active_route('superuser.master.unit.index') }}">
          Unit
        </a>
      </li>
      <li>
        <a href="{{ route('superuser.master.customer.index') }}" class="{{ is_active_route('superuser.master.customer.index') }}">
          Store
        </a>
      </li>
      <!-- <li>
        <a href="{{ route('superuser.master.customer_other_address.index') }}" class="{{ is_active_route('superuser.master.customer_other_address.index') }}">
          Member
        </a>
      </li> -->
      <li>
        <a href="{{ route('superuser.master.customer_category.index') }}" class="{{ is_active_route('superuser.master.customer_category.index') }}">
          Customer Category
        </a>
      </li>
      <li>
        <a href="{{ route('superuser.master.customer_type.index') }}" class="{{ is_active_route('superuser.master.customer_type.index') }}">
          Customer Type
        </a>
      </li>
      <li>
        <a href="{{ route('superuser.master.brand_reference.index') }}" class="{{ is_active_route('superuser.master.brand_reference.index') }}">
          Brand Reference
        </a>
      </li>
      <li>
        <a href="{{ route('superuser.master.sub_brand_reference.index') }}" class="{{ is_active_route('superuser.master.sub_brand_reference.index') }}">
          Sub Brand Reference
        </a>
      </li>
      <li>
        <a href="{{ route('superuser.master.question.index') }}" class="{{ is_active_route('superuser.master.question.index') }}">
          Question
        </a>
      </li>
      <li>
        <a href="{{ route('superuser.master.contact.index') }}" class="{{ is_active_route('superuser.master.contact.index') }}">
          Contact
        </a>
      </li>
      <li>
        <a href="{{ route('superuser.master.vendor.index') }}" class="{{ is_active_route('superuser.master.vendor.index') }}">
          Vendor
        </a>
      </li>
      <li>
        <a href="{{ route('superuser.master.ekspedisi.index') }}" class="{{ is_active_route('superuser.master.ekspedisi.index') }}">
          Ekspedisi
        </a>
      </li>
    </ul>
  </li>
  <li class="{{ is_open_route('superuser/penjualan') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#">
      <i class="fa fa-shopping-cart"></i>
      <span class="sidebar-mini-hide">
        Penjualan
      </span>
    </a>
    <ul>
      <li>
        <a href="{{ route('superuser.penjualan.setting_price.index') }}" class="{{ is_active_route('superuser.penjualan.setting_price.index') }}">
          Setting Price
        </a>
      </li>
      <li class="{{ is_open_route('superuser/penjualan/sales_order') }}">
        <a class="nav-submenu" data-toggle="nav-submenu" href="#">
          <span class="sidebar-mini-hide">
            Sales Order (SO)
          </span>
        </a>
        <ul>
          <li>
            <a href="{{ route('superuser.penjualan.sales_order.index_awal') }}" class="{{ is_active_route('superuser.penjualan.sales_order.index_awal') }}">
              Sales Order Awal (SOA)
            </a>
          </li>
          <li>
            <a href="{{ route('superuser.penjualan.sales_order.index_lanjutan') }}" class="{{ is_active_route('superuser.penjualan.sales_order.index_lanjutan') }}">
              Sales Order Lanjutan (SOL)
            </a>
          </li>
          <li>
            <a href="{{ route('superuser.penjualan.sales_order.index_mutasi') }}" class="{{ is_active_route('superuser.penjualan.sales_order.index_mutasi') }}">
              Sales Order Mutation (SOM)
            </a>
          </li>
        </ul>
      </li>
      <li>
        <a href="{{ route('superuser.penjualan.packing_order.index') }}" class="{{ is_active_route('superuser.penjualan.packing_order.index') }}">
          Packing Order (PO)
        </a>
      </li>
      <li class="{{ is_open_route('superuser/penjualan/delivery_order') == 'open' || is_open_route('superuser/penjualan/delivery_order_mutation') == 'open' ? 'open' : '' }}">
        <a class="nav-submenu" data-toggle="nav-submenu" href="#">
          <span class="sidebar-mini-hide">
          Delivery Order (DO)
          </span>
        </a>
        <ul>
          <li>
            <a href="{{ route('superuser.penjualan.delivery_order.index') }}" class="{{ is_active_route('superuser.penjualan.delivery_order.index') }}">
              Proses DO (DO)
            </a>
          </li>
          <li>
            <a href="{{ route('superuser.penjualan.delivery_order_mutation.index') }}" class="{{ is_active_route('superuser.penjualan.delivery_order_mutation.index') }}">
              Delivery Order Mutation (DOM)
            </a>
          </li>
        </ul>
      </li>
      <li>
        <a href="{{ route('superuser.penjualan.canvasing.index') }}" class="{{ is_active_route('superuser.penjualan.canvasing.index') }}">
          Canvasing (Sales Mutation)
        </a>
      </li>
    </ul>
  </li>

  <li class="{{ is_open_route('superuser/gudang') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#">
      <i class="fa fa-building"></i>
      <span class="sidebar-mini-hide">
        Gudang
      </span>
    </a>
    <ul>
      <li>
        <a href="{{ route('superuser.gudang.stock.index') }}" class="{{ is_active_route('superuser.gudang.stock.index') }}">
          Stock
        </a>
      </li>
      <li>
        <a href="{{ route('superuser.gudang.stock_adjustment.index') }}" class="{{ is_active_route('superuser.gudang.stock_adjustment.index') }}">
          Stock Adjustment
        </a>
      </li>
    </ul>
  </li>

  <li class="{{ is_open_route('superuser/finance') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#">
      <i class="fa fa-money"></i>
      <span class="sidebar-mini-hide">
        Finance
      </span>
    </a>
    <ul>
      <li>
        <a href="{{ route('superuser.finance.invoicing.index') }}" class="{{ is_active_route('superuser.finance.invoicing.index') }}">
          Invoicing
        </a>
      </li>
      <li>
        <a href="{{ route('superuser.finance.payable.index') }}" class="{{ is_active_route('superuser.finance.payable.index') }}">
          Payable
        </a>
      </li>
    </ul>
  </li>

  <li class="{{ is_open_route('superuser/report') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#">
      <i class="fa fa-file"></i>
      <span class="sidebar-mini-hide">
        Report
      </span>
    </a>
    <ul>
      <li>
        <a href="{{ route('superuser.report.sales.index') }}" class="{{ is_active_route('superuser.report.sales.index') }}">
          Sales
        </a>
      </li>
      <li>
        <a href="{{ route('superuser.report.revenue.index') }}" class="{{ is_active_route('superuser.report.revenue.index') }}">
          Revenue
        </a>
      </li>
      <li>
        <a href="{{ route('superuser.report.product_performance.index') }}" class="{{ is_active_route('superuser.report.product_performance.index') }}">
          Product Performance
        </a>
      </li>
    </ul>
  </li>
  @if($superuser->canAny(['superuser-manage', 'salesperson-manage']))
  <li class="{{ is_open_route('superuser/account') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#">
      <i class="si si-users"></i>
      <span class="sidebar-mini-hide">
        Account
      </span>
    </a>
    <ul>
      @if($superuser->can('superuser-manage'))
      <li>
        <a href="{{ route('superuser.account.superuser.index') }}" class="{{ is_active_route('superuser.account.superuser.index') }}">
          Superuser
        </a>
      </li>
      @endif
      <li>
        <a href="{{ route('superuser.account.user.index') }}" class="{{ is_active_route('superuser.account.user.index') }}">
          User
        </a>
      </li>
      @if($superuser->can('salesperson-manage'))
      <li>
        <a href="{{ route('superuser.account.sales_person.index') }}" class="{{ is_active_route('superuser.account.sales_person.index') }}">
          Sales Person
        </a>
      </li>
      @endif
    </ul>
  </li>
  @endif

  @role('Developer|SuperAdmin', 'superuser')
  <li class="{{ is_open_route('superuser/setting') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#">
      <i class="fa fa-database"></i>
      <span class="sidebar-mini-hide">
        Setting Menu
      </span>
    </a>
    <ul>
      <li>
        <a href="{{ route('superuser.setting.menu.index') }}" class="{{ is_active_route('superuser.setting.menu.index') }}">
          Menu
        </a>
      </li>
    </ul>
  </li>
  <li class="{{ is_open_route('superuser/utility') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#">
      <i class="si si-wrench"></i>
      <span class="sidebar-mini-hide">
        Utility
      </span>
    </a>
    <ul>
      <li>
        <a href="{{ route('superuser.utility.settings.index') }}" class="{{ is_active_route('superuser.utility.settings.index') }}">
          Settings
        </a>
      </li>
      <li>
        <a href="{{ route('superuser.utility.indonesian_teritory') }}" class="{{ is_active_route('superuser.utility.indonesian_teritory') }}">
          Indonesian Teritory
        </a>
      </li>
    </ul>
  </li>
  @endrole

  @role('Developer', 'superuser')
  <li>
    <a class="nav-submenu" data-toggle="nav-submenu" href="#">
      <i class="si si-shield"></i>
      <span class="sidebar-mini-hide">
        Developer
      </span>
    </a>
    <ul>
      <li>
        <a href="{{ route('superuser.boilerplate.index') }}" class="{{ is_active_route('superuser.boilerplate.index') }}">
          Boilerplate
        </a>
      </li>
      <li>
        <a href="{{ url('superuser/telescope') }}" target="_blank">
          Telescope
        </a>
      </li>
      <li>
        <a href="{{ route('superuser.terminal') }}" class="{{ is_active_route('superuser.terminal') }}">
          Terminal
        </a>
      </li>
      <li>
        <a href="{{ route('superuser.gate.index') }}" class="{{ is_active_route('superuser.gate.index') }}">
          Gate (Authorization)
        </a>
      </li>
    </ul>
  </li>
  @endrole

  {{-- <li class="{{ is_open_route('superuser/account') }}">
    <a class="nav-submenu" data-toggle="nav-submenu" href="#">
      <i class="si si-users"></i>
      <span class="sidebar-mini-hide">
        Account
      </span>
    </a>
    <ul>
      <li>
        <a href="{{ route('superuser.account.superuser.index') }}" class="{{ is_active_route('superuser.account.superuser.index') }}">
          Superuser
        </a>
      </li>
    </ul>
  </li> --}}

</ul>