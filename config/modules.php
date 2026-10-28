<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Modul yang bisa dipilih setelah login
    |--------------------------------------------------------------------------
    |
    | Kode modul harus sama dengan isi kolom superusers.modul_akses:
    | MIS, CRM, AO, APM, TRS, DSG.
    |
    | internal_route : modul yang berjalan di aplikasi ini (tidak perlu handoff).
    | sso_path       : endpoint penerima token SSO di aplikasi modul.
    |                  Kosongkan jika modul belum mendukung login otomatis.
    |
    */

    'items' => [

        'MIS' => [
            'label'       => 'MIS',
            'name'        => 'MIS (marketing Information System)',
            'description' => 'Laporan tim, doctor, customer, dan prospek.',
            'url'         => env('MODULE_MIS_URL', 'https://crm.lsfragrance.id'),
            'sso_path'    => '/module-sso',
            'icon'        => 'fa-line-chart',
            'color'       => '#2563eb',
        ],

        'AO' => [
            'label'       => 'AO',
            'name'        => 'AO (Account Officer)',
            'description' => 'Agenda kunjungan, file doctor, dan sales order awal.',
            'url'         => env('MODULE_AO_URL', 'https://sys-af.lsfragrance.id'),
            'sso_path'    => '/module-sso',
            'icon'        => 'fa-calendar-check-o',
            'color'       => '#0891b2',
        ],

        'APM' => [
            'label'       => 'APM',
            'name'        => 'APM',
            'description' => 'Modul APM.',
            'url'         => env('MODULE_APM_URL', 'https://apm.lsfragrance.id'),
            'sso_path'    => null,
            'icon'        => 'fa-users',
            'color'       => '#7c3aed',
        ],

        'CRM' => [
            'label'       => 'CRM',
            'name'        => 'Customer',
            'description' => 'Portal customer: prospek, customer, dan kontak (admin/SPV).',
            'url'         => env('MODULE_CRM_URL', env('MODULE_MIS_URL', 'https://crm.lsfragrance.id')),
            'sso_path'    => '/module-sso',
            'icon'        => 'fa-address-card',
            'color'       => '#0ea5e9',
        ],

        'TRS' => [
            'label'          => 'TRS',
            'name'           => 'Transaksi',
            'description'    => 'Sales order, gudang, keuangan, dan laporan.',
            'url'            => env('MODULE_TRS_URL', 'https://trans.lssoft88.xyz'),
            'sso_path'       => null,
            'internal_route' => 'superuser.index',
            'icon'           => 'fa-shopping-cart',
            'color'          => '#16a34a',
        ],

        'DSG' => [
            'label'       => 'DSG',
            'name'        => 'Design Apps',
            'description' => 'Design drive dan aset produk.',
            'url'         => env('MODULE_DSG_URL', 'https://drive.lssoft88.xyz'),
            'sso_path'    => '/module-sso',
            'icon'        => 'fa-paint-brush',
            'color'       => '#db2777',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | SSO antar modul
    |--------------------------------------------------------------------------
    |
    | MODULE_SSO_KEY harus sama di ppi-dist, MIS, dan AO.
    | Token hanya berlaku beberapa menit dan terikat ke user + modul.
    |
    */

    'sso' => [
        'key' => env('MODULE_SSO_KEY', ''),
        'ttl' => (int) env('MODULE_SSO_TTL', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Modul default akun lama
    |--------------------------------------------------------------------------
    |
    | Akun yang sudah ada sebelum kolom modul_akses diisi dianggap
    | pengguna transaksi agar tidak kehilangan akses saat fitur ini aktif.
    |
    */

    'legacy_default' => ['TRS'],

];
