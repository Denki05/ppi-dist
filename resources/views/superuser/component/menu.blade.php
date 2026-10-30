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
   Notification Popup Modern
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

.notification-wrapper {
    position: relative;
}

/* Navbar memaksa semua dropdown gelap (!important); panel notifikasi harus putih */
.navbar .notification-dropdown {
    background-color: #fff !important;
}

.notification-dropdown {
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    left: auto;
    width: 440px;
    max-width: calc(100vw - 16px);
    margin-top: 0;
    border-radius: 16px;
    padding: 0;
    border: 1px solid #e9edf2;
    box-shadow: 0 18px 48px rgba(16, 24, 40, .16);
    overflow: hidden;
    background: #fff;
}

/* Header */
.notif-pop-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
    padding: 14px 14px 10px;
    background: #fff;
}

.notif-pop-title {
    font-size: 16px;
    font-weight: 700;
    color: #111827;
    line-height: 1.25;
}

.notif-pop-sub {
    font-size: 11.5px;
    color: #98a2ad;
    margin-top: 2px;
}

.notif-pill {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    margin-left: 6px;
    padding: 1px 8px;
    border-radius: 999px;
    background: #eef2ff;
    color: #4338ca;
    font-size: 11px;
    font-weight: 700;
    vertical-align: 2px;
}

.notif-mark-all {
    flex: 0 0 auto;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #374151;
    font-size: 12px;
    font-weight: 600;
    padding: 6px 10px;
    border-radius: 9px;
    white-space: nowrap;
}

.notif-mark-all:hover {
    background: #f3f6fa;
    border-color: #cfd8e3;
}

/* Filter chips */
.notif-filters {
    display: flex;
    gap: 6px;
    padding: 0 12px 10px;
    overflow-x: auto;
    scrollbar-width: none;
    background: #fff;
}

.notif-filters::-webkit-scrollbar {
    display: none;
}

.notif-chip {
    flex: 0 0 auto;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #4b5563;
    font-size: 12px;
    font-weight: 600;
    padding: 5px 11px;
    border-radius: 999px;
    line-height: 1.3;
}

.notif-chip:hover {
    background: #f5f7fa;
}

.notif-chip.active {
    background: #111827;
    border-color: #111827;
    color: #fff;
}

/* List */
.notification-body {
    max-height: min(64vh, 520px);
    overflow-y: auto;
    background: #fff;
    overscroll-behavior: contain;
    scrollbar-width: thin;
    scrollbar-color: #cfd6de transparent;
}

.notification-body::-webkit-scrollbar {
    width: 8px;
}

.notification-body::-webkit-scrollbar-thumb {
    background: #cfd6de;
    border-radius: 8px;
}

.notification-item {
    display: flex;
    align-items: flex-start;
    gap: 11px;
    padding: 13px 14px;
    border-bottom: 1px solid #f1f3f5;
    background: #fff;
    cursor: pointer;
    position: relative;
    transition: background .15s ease;
}

.notification-item:hover {
    background: #f8fafc;
}

.notification-item.unread {
    background: #f5f9ff;
}

.notification-item.unread:hover {
    background: #eef5ff;
}

/* Garis aksen kiri untuk yang belum dibaca */
.notification-item.unread::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: #0d6efd;
}

.notification-item.is-read .notif-title {
    color: #4b5563;
    font-weight: 600;
}

.notification-item.is-read .notif-icon {
    opacity: .55;
}

.notif-icon {
    width: 38px;
    height: 38px;
    flex: 0 0 38px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 17px;
    background: #eef2f7;
    color: #4a5563;
}

.notif-icon.type-do { background: #e8f1ff; color: #1d6ff2; }
.notif-icon.type-so { background: #e7f8ef; color: #12a150; }
.notif-icon.type-pay { background: #fff4e5; color: #d97706; }
.notif-icon.type-otp { background: #fff7e0; color: #b45309; }

.notif-main {
    flex: 1 1 auto;
    min-width: 0;
}

.notif-title {
    font-size: 14px;
    font-weight: 700;
    color: #111827;
    line-height: 1.35;
}

.notif-sub {
    font-size: 12.5px;
    color: #6b7280;
    margin-top: 2px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.notif-code {
    display: inline-block;
    margin-left: 5px;
    padding: 0 6px;
    border-radius: 5px;
    background: #eef1f4;
    color: #4b5563;
    font-size: 11px;
    font-weight: 700;
    vertical-align: 1px;
}

.notif-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 4px;
    font-size: 11px;
    color: #9aa4af;
}

.notif-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #0d6efd;
    flex: 0 0 8px;
    margin-top: 8px;
}

.notif-quick {
    flex: 0 0 auto;
    align-self: center;
    border: none;
    background: #fff;
    color: #6b7280;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity .15s ease, background .15s ease;
}

.notification-item:hover .notif-quick {
    opacity: 1;
}

.notif-quick:hover {
    background: #e9eef5;
    color: #0d6efd;
}

.notif-loadmore {
    width: 100%;
    border: none;
    border-top: 1px solid #f1f3f5;
    background: #f8fafc;
    color: #374151;
    font-size: 12.5px;
    font-weight: 600;
    padding: 10px;
}

.notif-loadmore:hover {
    background: #f1f5f9;
}

.notif-empty {
    padding: 40px 16px;
    text-align: center;
    color: #98a2ad;
    font-size: 13px;
}

.notif-empty i {
    display: block;
    font-size: 26px;
    margin-bottom: 6px;
    opacity: .55;
}

/* Footer */
.notif-pop-foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 9px 14px;
    border-top: 1px solid #eef1f4;
    background: #fff;
}

.notif-pop-foot .info {
    font-size: 11.5px;
    color: #98a2ad;
}

.notif-pop-foot a {
    font-size: 12.5px;
    font-weight: 600;
    color: #0d6efd;
    text-decoration: none;
}

.notif-pop-foot a:hover {
    text-decoration: underline;
}

/* Mobile: panel selebar layar, tetap bisa discroll */
@media (max-width: 575.98px) {
    .notification-dropdown {
        position: fixed !important;
        top: 62px;
        left: 8px;
        right: 8px;
        width: auto;
        max-width: none;
        transform: none !important;
    }

    .notification-body {
        max-height: calc(100vh - 268px);
    }
}

/* Kartu pengingat notifikasi (persisten sampai ditutup user) */
#notifToastStack {
    position: fixed;
    top: 70px;
    right: 18px;
    z-index: 2000;
    display: flex;
    flex-direction: column;
    gap: 10px;
    width: 360px;
    max-width: calc(100vw - 24px);
    pointer-events: none;
}

.notif-toast {
    pointer-events: auto;
    display: flex;
    align-items: flex-start;
    gap: 11px;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 14px 40px rgba(16, 24, 40, .18);
    padding: 13px 12px 13px 14px;
    border-left: 4px solid #0d6efd;
    animation: notifToastIn .22s ease-out;
    cursor: pointer;
}

.notif-toast.out {
    animation: notifToastOut .18s ease-in forwards;
}

.notif-toast-icon {
    color: #0d6efd;
    font-size: 18px;
    line-height: 1;
    margin-top: 2px;
}

.notif-toast-title {
    font-size: 14px;
    font-weight: 700;
    color: #111827;
    line-height: 1.35;
}

.notif-toast-sub {
    font-size: 12.5px;
    color: #6b7280;
    margin-top: 2px;
}

.notif-toast-hint {
    font-size: 11px;
    color: #9aa4af;
    margin-top: 6px;
}

.notif-toast-close {
    border: none;
    background: #f1f3f5;
    color: #6b7280;
    font-size: 16px;
    line-height: 1;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    margin-left: auto;
    flex: 0 0 26px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.notif-toast-close:hover {
    background: #e6eaee;
    color: #374151;
}

@keyframes notifToastIn {
    from { opacity: 0; transform: translateY(-6px); }
    to { opacity: 1; transform: none; }
}

@keyframes notifToastOut {
    to { opacity: 0; transform: translateY(-6px); }
}

/* Mobile: pengingat notifikasi tampil sebagai sheet layar penuh */
.notif-sheet-backdrop {
    position: fixed;
    inset: 0;
    z-index: 2060;
    background: rgba(15, 23, 42, .45);
    display: flex;
    align-items: flex-end;
    animation: notifFadeIn .18s ease-out;
}

.notif-sheet {
    width: 100%;
    max-height: 80vh;
    background: #fff;
    border-radius: 18px 18px 0 0;
    box-shadow: 0 -10px 40px rgba(15, 23, 42, .25);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: notifSheetUp .25s ease-out;
}

.notif-sheet-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
    padding: 16px 16px 10px;
    border-bottom: 1px solid #f1f3f5;
}

.notif-sheet-title {
    font-size: 16px;
    font-weight: 700;
    color: #111827;
}

.notif-sheet-sub {
    font-size: 12px;
    color: #98a2ad;
    margin-top: 2px;
}

.notif-sheet-close {
    border: none;
    background: #f1f3f5;
    color: #6b7280;
    width: 30px;
    height: 30px;
    flex: 0 0 30px;
    border-radius: 50%;
    font-size: 17px;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.notif-sheet-body {
    overflow-y: auto;
    padding: 8px;
    background: #fafbfc;
}

.notif-sheet-body .notification-item {
    border: 1px solid #eef1f4;
    border-radius: 12px;
    margin-bottom: 6px;
    background: #fff;
}

.notif-sheet-body .notification-item.unread {
    background: #f5f9ff;
}

.notif-sheet-actions {
    display: flex;
    gap: 8px;
    padding: 10px 16px calc(14px + env(safe-area-inset-bottom));
    border-top: 1px solid #eef1f4;
    background: #fff;
}

.notif-sheet-actions .btn {
    flex: 1;
    border-radius: 10px;
    font-size: 13.5px;
    font-weight: 600;
    padding: 10px;
}

@keyframes notifSheetUp {
    from { transform: translateY(26px); opacity: .65; }
    to { transform: none; opacity: 1; }
}

@keyframes notifFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
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
                                <li><a class="dropdown-item" href="{{ route('superuser.master.sh_produk.index') }}"><i class="fa-solid fa-certificate"></i> SH Produk</a></li>
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
                @if(!in_array($superuser->id, [41, 29]))
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
                                <li><a class="dropdown-item" href="{{ route('superuser.penjualan.pengajuan_proforma.index') }}"><i class="fa-solid fa-file-circle-plus"></i> Pengajuan Proforma</a></li>
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
                @endif

                <!-- Gudang -->
                @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Warehouse"  OR $superuser->division == "Finance")
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fa-solid fa-boxes-stacked"></i> Logistik
                    </a>
                    <ul class="dropdown-menu">

                        {{-- ================= DOCUMENT ================= --}}
                        @if(
                            ($superuser->can('superuser-manage') 
                            OR $superuser->division == "Admin" 
                            OR $superuser->division == "Management" 
                            OR $superuser->division == "Warehouse" 
                            OR $superuser->division == "Finance")
                            AND !in_array($superuser->id, [29, 41])
                        )
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
                                @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Warehouse" OR $superuser->division == "Finance")
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
                                @if($superuser->can('superuser-manage') OR $superuser->division == "Admin" OR $superuser->division == "Management" OR $superuser->division == "Warehouse" OR $superuser->division == "Finance")
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
                                        <!-- <li><a class="dropdown-item" href="{{ route('superuser.report.customer_order_variant_v2.index') }}">Customer - Produk</a></li> -->
                                        <li><a class="dropdown-item" href="{{ route('superuser.report.customer_order_variant.index') }}">Customer History</a></li>
                                        <li><a class="dropdown-item" href="{{ route('superuser.report.customer_type_zone.index') }}">Customer - Zoning</a></li>
                                        <li><a class="dropdown-item" href="{{ route('superuser.report.summary_customer_product.index') }}">Summary Customer - Produk</a></li>
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
                                        <li><a class="dropdown-item" href="{{ route('superuser.master.product.pageReport') }}">Produk - Material</a></li>
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
                                <li><a class="dropdown-item" href="{{ route('superuser.account.user.index') }}">Akun</a></li>
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
                                <li><a class="dropdown-item" href="{{ route('superuser.setting.menu.index') }}">Daftar Menu</a></li>
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
        <!-- Notification Popup -->
        <li class="nav-item dropdown notification-wrapper">
            <a class="nav-link position-relative" href="#" id="notificationDropdown"
            role="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">

                <i class="bi bi-bell fs-5"></i>

                <span id="notifCount"
                    class="notification-badge {{ $notifCount > 0 ? 'show' : '' }}">
                    {{ $notifCount > 99 ? '99+' : $notifCount }}
                </span>
            </a>

            <div class="dropdown-menu dropdown-menu-end notification-dropdown"
                aria-labelledby="notificationDropdown">

                <div class="notif-pop-head">
                    <div>
                        <div class="notif-pop-title">
                            Notifikasi
                            <span class="notif-pill">
                                <span id="notifHeaderCount">{{ $notifCount }}</span> baru
                            </span>
                        </div>
                        <div class="notif-pop-sub">Klik notifikasi untuk membuka detailnya</div>
                    </div>

                    <button type="button" class="notif-mark-all {{ $notifCount > 0 ? '' : 'd-none' }}" id="markAllAsReadBtn">
                        <i class="bi bi-check2-all"></i> Tandai dibaca
                    </button>
                </div>

                <div class="notif-filters" id="notifFilters">
                    <button type="button" class="notif-chip active" data-type="">Semua</button>
                    <button type="button" class="notif-chip" data-type="so">SO</button>
                    <button type="button" class="notif-chip" data-type="do">DO</button>
                    <button type="button" class="notif-chip" data-type="pay">Payment</button>
                    <button type="button" class="notif-chip" data-type="otp">OTP Revisi</button>
                </div>

                <div id="notifList" class="notification-body">
                    <!-- Loaded via AJAX -->
                </div>

                <button type="button" id="notifLoadMore" class="notif-loadmore" hidden>
                    Muat lebih banyak
                </button>

                <div class="notif-pop-foot">
                    <span class="info" id="notifTotalInfo"></span>
                    <a href="{{ route('superuser.penjualan.notification.index') }}">
                        Buka semua <i class="bi bi-arrow-right-short"></i>
                    </a>
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

    // Notifikasi baru (bukan backlog) -> toast + suara. Hanya untuk user
    // penerima notifikasi tersebut karena API sudah memfilter per user.
    const seenNotifIds = new Set();
    let notifFirstLoad = true;
    let notifAudioCtx = null;

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

    function esc(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    // Nilai data-type pada chip filter dipetakan ke FQCN notifikasi.
    const NOTIF_TYPES = {
        so: 'App\\Notifications\\SoNotification',
        do: 'App\\Notifications\\DoNotification',
        pay: 'App\\Notifications\\PayableNotification',
        otp: 'App\\Notifications\\InternalRevisionOtpNotification'
    };

    function notifMeta(type) {
        if (type.includes('DoNotification')) return { label: 'Delivery Order', short: 'DO', icon: 'bi-truck', color: 'primary', typeClass: 'type-do' };
        if (type.includes('SoNotification')) return { label: 'Sales Order', short: 'SO', icon: 'bi-cart', color: 'success', typeClass: 'type-so' };
        if (type.includes('PayableNotification')) return { label: 'Payment', short: 'Payment', icon: 'bi-credit-card', color: 'warning', typeClass: 'type-pay' };
        if (type.includes('ReceivingNotification')) return { label: 'Receiving', short: 'Receiving', icon: 'bi-box-seam', color: 'info', typeClass: 'type-pay' };
        if (type.includes('InternalRevisionOtp')) return { label: 'OTP Revisi', short: 'OTP', icon: 'bi-key', color: 'warning', typeClass: 'type-otp' };
        return { label: 'Notification', short: 'Info', icon: 'bi-bell', color: 'secondary', typeClass: '' };
    }

    function buildNotificationItem(notification) {

        notification = notification || {};

        const data = notification.data || {};
        const type = notification.type || '';
        const meta = notifMeta(type);
        const unread = !!notification.is_unread;

        // Klik selalu lewat rute baca universal (GET): tandai dibaca + redirect
        // ke halaman tujuan sesuai tipe.
        const readUrl = '{{ route('superuser.penjualan.notification.read', ['id' => '__NID__']) }}'.replace('__NID__', notification.id);

        const quick = unread
            ? `<button type="button" class="notif-quick" title="Tandai sudah dibaca" data-quick="${notification.id}"><i class="bi bi-check2"></i></button>`
            : '';

        const dot = unread ? '<span class="notif-dot"></span>' : '';

        if (type.includes('InternalRevisionOtp')) {
            // OTP revisi internal: tampil menonjol dengan kode besar agar tidak
            // tenggelam di antara notifikasi lain.
            return `
            <div class="notification-item ${unread ? 'unread' : 'is-read'}" data-url="${readUrl}">
                <div class="notif-icon type-otp"><i class="bi bi-key"></i></div>
                <div class="notif-main">
                    <div class="notif-title">OTP Revisi Internal</div>
                    <div class="text-center my-1" style="font-size:1.7em;font-weight:800;letter-spacing:6px;color:#b45309;">
                        ${esc(data.otp || '-')}
                    </div>
                    <div class="notif-sub" style="white-space:normal">${esc(data.message || '')}</div>
                    <div class="notif-meta"><span>${esc(notification.human || '')}</span></div>
                </div>
                ${dot}
                ${quick}
            </div>`;
        }

        let title = meta.label;
        if (type.includes('SoNotification')) {
            title = (data.kind === 'proforma' ? 'SO Proforma' : 'SO Lanjutan');
        }

        const code = data.code ? `<span class="notif-code">${esc(data.code)}</span>` : '';

        const parts = [];
        if (data.customer) parts.push(esc(data.customer));
        if (data.customer_kota) parts.push('(' + esc(data.customer_kota) + ')');
        if (data.type_transaction) parts.push(esc(data.type_transaction));
        const subtitle = parts.join(' &middot; ');

        return `
            <div class="notification-item ${unread ? 'unread' : 'is-read'}" data-url="${readUrl}">
                <div class="notif-icon ${meta.typeClass}"><i class="bi ${meta.icon}"></i></div>
                <div class="notif-main">
                    <div class="notif-title">${title}${code}</div>
                    ${subtitle ? `<div class="notif-sub">${subtitle}</div>` : ''}
                    <div class="notif-meta">
                        <span>${meta.short}</span>
                        <span>&middot;</span>
                        <span>${esc(notification.human || '')}</span>
                    </div>
                </div>
                ${dot}
                ${quick}
            </div>`;
    }


    function notifIsPriority(notification) {
        const type = notification.type || '';
        return type.includes('SoNotification') || type.includes('InternalRevisionOtp');
    }

    // Bunyi singkat memakai Web Audio (tanpa file audio). Kalau browser
    // memblokir autoplay sebelum ada interaksi, toast tetap muncul tanpa bunyi.
    function playNotifBeep(priority) {
        try {
            notifAudioCtx = notifAudioCtx || new (window.AudioContext || window.webkitAudioContext)();
            if (notifAudioCtx.state === 'suspended') {
                notifAudioCtx.resume();
            }

            const osc = notifAudioCtx.createOscillator();
            const gain = notifAudioCtx.createGain();
            const now = notifAudioCtx.currentTime;

            osc.type = 'sine';
            osc.frequency.value = priority ? 880 : 640;
            gain.gain.setValueAtTime(0.0001, now);
            gain.gain.exponentialRampToValueAtTime(0.10, now + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.32);

            osc.connect(gain);
            gain.connect(notifAudioCtx.destination);
            osc.start(now);
            osc.stop(now + 0.34);
        } catch (e) {
            // Suara opsional: jangan ganggu alur notifikasi.
        }
    }

    // Notifikasi yang kartunya sudah ditutup user tidak dimunculkan lagi
    // selama sesi ini (didengar dari polling berikutnya).
    const dismissedAlertIds = new Set();

    // Penanda "sudah ditutup sampai id ini" disimpan di browser supaya
    // pengingat tidak muncul lagi tiap halaman dimuat ulang.
    const REMINDER_KEY = 'notif_reminder_seen_id';
    let lastUnreadMaxId = 0;

    function reminderSeenId() {
        try { return Number(localStorage.getItem(REMINDER_KEY) || 0); } catch (e) { return 0; }
    }

    function rememberReminderClosed() {
        if (!lastUnreadMaxId) return;
        try { localStorage.setItem(REMINDER_KEY, String(lastUnreadMaxId)); } catch (e) {}
    }

    function closeNotifToast(el) {
        if (!el || !el.parentNode) return;
        el.classList.add('out');
        setTimeout(function () { el.remove(); }, 200);
    }

    // Kartu pengingat: sengaja tidak auto-hide. Hilang hanya kalau user
    // menekan tombol × atau membuka notifikasinya.
    function showNotifToast(notification) {

        const data = notification.data || {};
        const type = notification.type || '';
        const meta = notifMeta(type);

        const warna = {
            success: '#198754',
            info: '#0d6efd',
            warning: '#f59f00',
            secondary: '#6c757d',
            primary: '#0d6efd',
        }[meta.color] || '#0d6efd';

        let title = meta.label;
        if (type.includes('SoNotification')) {
            title = (data.kind === 'proforma' ? 'SO Proforma' : 'SO Lanjutan');
        }

        const code = data.code ? `<span class="notif-code">${esc(data.code)}</span>` : '';

        const parts = [];
        if (data.customer) parts.push(esc(data.customer));
        if (data.customer_kota) parts.push('(' + esc(data.customer_kota) + ')');
        const subtitle = parts.join(' ');

        const readUrl = '{{ route('superuser.penjualan.notification.read', ['id' => '__NID__']) }}'.replace('__NID__', notification.id);

        let stack = document.getElementById('notifToastStack');
        if (!stack) {
            stack = document.createElement('div');
            stack.id = 'notifToastStack';
            document.body.appendChild(stack);
        }

        // Maksimal 3 kartu agar tidak menutupi layar.
        const items = stack.querySelectorAll('.notif-toast');
        if (items.length >= 3) {
            items[0].remove();
        }

        const el = document.createElement('div');
        el.className = 'notif-toast';
        el.style.borderLeftColor = warna;
        el.innerHTML = `
            <div class="notif-toast-icon" style="color:${warna}"><i class="bi ${meta.icon}"></i></div>
            <div class="flex-grow-1">
                <div class="notif-toast-title">${title}${code}</div>
                ${subtitle ? `<div class="notif-toast-sub">${subtitle}</div>` : ''}
                <div class="notif-toast-hint">Klik untuk membuka &middot; × untuk menutup</div>
            </div>
            <button type="button" class="notif-toast-close" aria-label="Tutup">&times;</button>
        `;

        el.addEventListener('click', function (e) {
            if (e.target.closest('.notif-toast-close')) {
                e.stopPropagation();
                dismissedAlertIds.add(notification.id);
                lastUnreadMaxId = Math.max(lastUnreadMaxId, Number(notification.id) || 0);
                rememberReminderClosed();
                closeNotifToast(el);
                return;
            }
            window.location.href = readUrl;
        });

        stack.appendChild(el);
    }

    // Ringkasan untuk notifikasi yang masih belum dibaca (termasuk backlog
    // setelah halaman dimuat ulang). Tetap tampil sampai user menutupnya.
    function showReminderCard(count) {

        let stack = document.getElementById('notifToastStack');
        if (!stack) {
            stack = document.createElement('div');
            stack.id = 'notifToastStack';
            document.body.appendChild(stack);
        }

        const el = document.createElement('div');
        el.className = 'notif-toast';
        el.dataset.reminder = '1';
        el.innerHTML = `
            <div class="notif-toast-icon" style="color:#0d6efd"><i class="bi bi-bell"></i></div>
            <div class="flex-grow-1">
                <div class="notif-toast-title">${count} notifikasi belum dibaca</div>
                <div class="notif-toast-sub">Klik untuk membuka daftarnya</div>
                <div class="notif-toast-hint">&times; untuk menutup pengingat</div>
            </div>
            <button type="button" class="notif-toast-close" aria-label="Tutup">&times;</button>
        `;

        el.addEventListener('click', function (e) {
            if (e.target.closest('.notif-toast-close')) {
                e.stopPropagation();
                rememberReminderClosed();
                closeNotifToast(el);
                return;
            }
            window.location.href = '{{ route('superuser.penjualan.notification.index') }}';
        });

        stack.appendChild(el);
    }

    function maybeShowReminder(response) {

        const count = response.notifCount || 0;
        const list = response.notifications || [];

        if (count <= 0) return;

        const unread = list.filter(function (n) { return n.is_unread; });
        if (!unread.length) return;

        const maxId = unread.reduce(function (max, n) {
            return Math.max(max, Number(n.id) || 0);
        }, 0);

        lastUnreadMaxId = maxId;

        if (maxId <= reminderSeenId()) return;

        if (window.matchMedia('(max-width: 575.98px)').matches) {
            showMobileNotifSheet(unread);
            return;
        }

        // Kartu notifikasi baru sudah tampil -> tidak perlu ringkasan.
        const stack = document.getElementById('notifToastStack');
        if (stack && stack.querySelector('.notif-toast')) return;

        // Jangan menutupi panel notifikasi yang sedang dibuka.
        if (document.querySelector('.notification-dropdown.show')) return;

        showReminderCard(count);
    }

    // Di HP notifikasi baru tampil sebagai sheet layar penuh (mirip notifikasi
    // layar kunci), tetap bisa ditutup dan bisa langsung membuka daftar.
    function showMobileNotifSheet(list) {

        if (document.getElementById('notifSheetBackdrop')) return;

        const rows = list.slice(0, 6).map(buildNotificationItem).join('');

        const backdrop = document.createElement('div');
        backdrop.className = 'notif-sheet-backdrop';
        backdrop.id = 'notifSheetBackdrop';
        backdrop.innerHTML = `
            <div class="notif-sheet" role="dialog" aria-modal="true" aria-label="Notifikasi baru">
                <div class="notif-sheet-head">
                    <div>
                        <div class="notif-sheet-title">Notifikasi Baru</div>
                        <div class="notif-sheet-sub">${list.length} notifikasi masuk</div>
                    </div>
                    <button type="button" class="notif-sheet-close" aria-label="Tutup">&times;</button>
                </div>
                <div class="notif-sheet-body">${rows}</div>
                <div class="notif-sheet-actions">
                    <button type="button" class="btn btn-light" data-act="dismiss">Nanti</button>
                    <button type="button" class="btn btn-primary" data-act="open">Lihat semua</button>
                </div>
            </div>`;

        list.forEach(function (n) {
            dismissedAlertIds.add(n.id);
            lastUnreadMaxId = Math.max(lastUnreadMaxId, Number(n.id) || 0);
        });

        backdrop.addEventListener('click', function (e) {

            if (e.target.closest('[data-act="open"]')) {
                window.location.href = '{{ route('superuser.penjualan.notification.index') }}';
                return;
            }

            if (e.target === backdrop
                || e.target.closest('.notif-sheet-close')
                || e.target.closest('[data-act="dismiss"]')) {
                rememberReminderClosed();
                backdrop.remove();
                return;
            }

            // Klik baris notifikasi ditangani handler .notification-item (pindah halaman).
        });

        document.body.appendChild(backdrop);
    }

    function showNotifAlerts(list) {

        const fresh = list.filter(function (n) {
            return !dismissedAlertIds.has(n.id);
        });

        if (!fresh.length) return;

        if (window.matchMedia('(max-width: 575.98px)').matches) {
            showMobileNotifSheet(fresh);
        } else {
            fresh.slice(0, 3).forEach(showNotifToast);
        }

        // Bunyi hanya untuk tipe prioritas (SO/Proforma/OTP). DO senyap
        // agar tidak berbunyi puluhan kali sehari.
        if (fresh.some(notifIsPriority)) {
            playNotifBeep(true);
        }
    }

    function updateBadge(count) {

        $('#notifCount')
            .text(count > 99 ? '99+' : count)
            .toggleClass('show', count > 0);

        $('#notifHeaderCount').text(count);
        $('.notif-pill').toggle(count > 0);
        $('.notif-mark-all').toggleClass('d-none', count <= 0);
    }


    const notifState = { type: '', page: 1, hasMore: false };

    function notifListUrl(page) {
        let url = '{{ route('superuser.penjualan.notification.getNotifData') }}?page=' + page;
        if (notifState.type) {
            url += '&type=' + encodeURIComponent(NOTIF_TYPES[notifState.type] || '');
        }
        return url;
    }

    function notifEmptyHtml(message) {
        return `<div class="notif-empty"><i class="bi bi-bell-slash"></i>${message}</div>`;
    }

    function renderNotifList(rows, append) {

        const $list = $('#notifList');

        if (append) {
            $list.append(rows.map(buildNotificationItem).join(''));
        } else if (rows.length) {
            $list.html(rows.map(buildNotificationItem).join(''));
        } else {
            $list.html(notifEmptyHtml(notifState.type ? 'Tidak ada notifikasi di kategori ini.' : 'Belum ada notifikasi.'));
        }

        $('#notifLoadMore').prop('hidden', !notifState.hasMore);
    }

    // Notifikasi yang baru masuk sejak halaman dibuka -> kartu pengingat + suara.
    // Backlog saat pertama buka tidak dimunculkan agar tidak membanjiri layar.
    function detectIncoming(notifications) {

        const incoming = notifications.filter(function (n) {
            return n.is_unread && !seenNotifIds.has(n.id);
        });
        notifications.forEach(function (n) { seenNotifIds.add(n.id); });

        if (!notifFirstLoad && incoming.length) {
            showNotifAlerts(incoming);
        }

        notifFirstLoad = false;
    }

    function reloadNotifications(options) {

        options = options || {};
        const append = !!options.append;
        const page = append ? notifState.page + 1 : 1;

        if (currentRequest) {
            currentRequest.abort(); // prevent overlapping
        }

        if (append) {
            $('#notifLoadMore').prop('disabled', true).text('Memuat...');
        }

        currentRequest = $.ajax({
            url: notifListUrl(page),
            type: 'GET',
            dataType: 'json'
        });

        currentRequest.done(function (response) {

            updateBadge(response.notifCount);

            const notifications = response.notifications || [];

            notifState.page = response.page || page;
            notifState.hasMore = !!response.hasMore;

            detectIncoming(notifications);
            renderNotifList(notifications, append);

            $('#notifTotalInfo').text((response.total || 0) + ' notifikasi');

            if (!append) {
                maybeShowReminder(response);
            }

        }).fail(function (xhr, status) {

            if (status !== 'abort') {
                console.error('Notification error:', xhr.responseText);
            }

        }).always(function () {
            currentRequest = null;
            $('#notifLoadMore').prop('disabled', false).text('Muat lebih banyak');
        });
    }

    // Polling: badge + toast selalu jalan, tapi daftar hanya dirender ulang
    // saat user masih di halaman 1 supaya hasil "Muat lebih banyak" tidak hilang.
    function pollNotifications() {

        if (currentRequest) return;

        $.ajax({
            url: notifListUrl(1),
            type: 'GET',
            dataType: 'json'
        }).done(function (response) {

            updateBadge(response.notifCount);

            const notifications = response.notifications || [];
            detectIncoming(notifications);

            if (notifState.page === 1) {
                notifState.hasMore = !!response.hasMore;
                renderNotifList(notifications, false);
                $('#notifTotalInfo').text((response.total || 0) + ' notifikasi');
            }

            maybeShowReminder(response);
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


    // Filter kategori di dalam popup
    $(document).on('click', '.notif-chip', function () {

        const type = $(this).data('type') || '';
        if (notifState.type === type) return;

        notifState.type = type;
        $('#notifFilters .notif-chip').removeClass('active');
        $(this).addClass('active');

        $('#notifList').html('<div class="notif-empty">Memuat...</div>');
        reloadNotifications();
    });


    // Muat 10 notifikasi berikutnya di dalam popup
    $(document).on('click', '#notifLoadMore', function () {
        reloadNotifications({ append: true });
    });


    // Tandai satu notifikasi dibaca tanpa membuka halamannya
    $(document).on('click', '.notif-quick', function (e) {

        e.stopPropagation();

        const url = '{{ route('superuser.penjualan.notification.mark_as_read_only', ['id' => '__NID__']) }}'
            .replace('__NID__', $(this).data('quick'));

        $.post(url, { _token: '{{ csrf_token() }}' })
            .done(function () { reloadNotifications(); })
            .fail(function (xhr) { console.error('Mark read error:', xhr.responseText); });
    });


    // Saat popup dibuka, segarkan daftar (hanya kalau belum memuat halaman lanjutan)
    $('#notificationDropdown').on('show.bs.dropdown', function () {
        if (notifState.page === 1) {
            reloadNotifications();
        }
    });


    /* ================================
       SMART POLLING
    ================================= */

    function startPolling() {
        if (!notifInterval) {
            notifInterval = setInterval(pollNotifications, 10000);
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
            pollNotifications();
            startPolling();
        }
    });

    // Browser memblokir bunyi sebelum ada interaksi: siapkan audio setelah
    // klik pertama supaya notifikasi berikutnya bisa berbunyi.
    document.addEventListener('click', function () {
        try {
            if (!notifAudioCtx) {
                notifAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
            } else if (notifAudioCtx.state === 'suspended') {
                notifAudioCtx.resume();
            }
        } catch (e) {}
    }, { once: true });

    // Init
    reloadNotifications();
    startPolling();

})();
</script>
@endpush