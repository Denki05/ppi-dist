<?php

/*
|--------------------------------------------------------------------------
| Paket Akses Jabatan -> Menu Transaksi
|--------------------------------------------------------------------------
| Fondasi Fase 2 (disetujui Okt 2026). Sumber: pola checklist nyata
| per divisi di DB dummy produksi.
|
| Format per menu: [read, create, update, delete, approve, print] (1/0).
| Enforcement di controller TIDAK berubah — paket ini hanya menentukan
| baris setting_user_menu apa yang ditulis saat approve (pengganti
| centangan manual). Checklist lama jadi preview readonly.
|
| - SM  = base Marketing + laporan tim + approve + PPN
| - ASM = SM minus approve & minus PPN
*/

$full = [1, 1, 1, 1, 1, 1];
$readPrint = [1, 0, 0, 0, 0, 1];
$readOnly = [1, 0, 0, 0, 0, 0];

$smMenus = [
    // Base Marketing (pola nyata lindy/santi/eric/mala)
    'superuser.penjualan.sales_order' => $full,
    'superuser.penjualan.sales_order_indent' => $full,
    'superuser.penjualan.sales_order_ppn' => $full,
    'superuser.finance.invoicing' => [1, 1, 0, 0, 1, 1],
    'superuser.penjualan.delivery_order' => $readOnly,
    'superuser.gudang.stock' => $readOnly,
    // Tambahan SM: kontrol kinerja tim via laporan
    'superuser.report.sales' => $readPrint,
    'superuser.report.employee_performance' => $readPrint,
    'superuser.report.product_performance' => $readPrint,
    'superuser.report.customer_order_variant' => $readPrint,
    'superuser.master.customer' => $readOnly,
    'superuser.master.customer_other_address' => $readOnly,
];

$asmMenus = $smMenus;
unset($asmMenus['superuser.penjualan.sales_order_ppn']);
foreach ($asmMenus as $route => $flags) {
    // ASM: tanpa approve di semua menu (index 4 = can_approve)
    $asmMenus[$route][4] = 0;
}

return [
    'SM' => [
        'label' => 'Sales Manager',
        'division' => 'Marketing',
        'is_superuser' => 0,
        'menus' => $smMenus,
    ],
    'ASM' => [
        'label' => 'Ass Sales Manager',
        'division' => 'Marketing',
        'is_superuser' => 0,
        'menus' => $asmMenus,
    ],
];
