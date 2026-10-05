<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Pencarian customer (existing + prospek) untuk form estimate AO.
 * GET /api/customers/search/name?q=
 * Response {success, customers:[{id,nama,city,address,phone,cp,owner,provinsi,kecamatan,kelurahan,pic,officer,kategori,zone,jenis}]}
 */
class ApiCustomerSearchController extends Controller
{
    public function searchByName(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['success' => true, 'customers' => []]);
        }

        $like = '%' . $q . '%';

        $existing = DB::table('master_customer_other_addresses as a')
            ->leftJoin('master_customers as c', 'c.id', '=', 'a.customer_id')
            ->leftJoin('master_customer_categories as cat', 'cat.id', '=', 'c.category_id')
            ->where(function ($w) use ($like) {
                $w->where('a.name', 'like', $like)
                  ->orWhere('a.text_kota', 'like', $like);
            })
            ->whereNull('a.deleted_at')
            ->select(
                'a.id',
                'a.name as nama',
                'a.text_kota as city',
                'a.address as address',
                'a.phone as phone',
                'a.contact_person as cp',
                'c.owner_name as owner',
                'a.provinsi as provinsi',
                'a.text_provinsi as text_provinsi',
                'c.provinsi as parent_provinsi',
                'a.kecamatan as kecamatan',
                'a.text_kecamatan as text_kecamatan',
                'c.kecamatan as parent_kecamatan',
                'a.kelurahan as kelurahan',
                'a.text_kelurahan as text_kelurahan',
                'c.kelurahan as parent_kelurahan',
                'c.pic as pic',
                'a.officer as officer',
                'cat.name as kategori',
                'a.zone as zone',
                DB::raw("'EXISTING' as jenis")
            )
            ->limit(50)
            ->get();

        $prospek = DB::table('master_customer_other_addresses_prospek as a')
            ->leftJoin('master_customers_prospek as c', 'c.id', '=', 'a.customer_id')
            ->leftJoin('master_customer_categories as cat', 'cat.id', '=', 'c.category_id')
            ->where(function ($w) use ($like) {
                $w->where('a.name', 'like', $like)
                  ->orWhere('a.text_kota', 'like', $like);
            })
            ->whereNull('a.deleted_at')
            ->select(
                'a.id',
                'a.name as nama',
                'a.text_kota as city',
                'a.address as address',
                'a.phone as phone',
                'a.contact_person as cp',
                'c.owner_name as owner',
                'a.provinsi as provinsi',
                'a.text_provinsi as text_provinsi',
                'c.provinsi as parent_provinsi',
                'a.kecamatan as kecamatan',
                'a.text_kecamatan as text_kecamatan',
                'c.kecamatan as parent_kecamatan',
                'a.kelurahan as kelurahan',
                'a.text_kelurahan as text_kelurahan',
                'c.kelurahan as parent_kelurahan',
                'c.pic as pic',
                'a.officer as officer',
                'cat.name as kategori',
                'a.zone as zone',
                DB::raw("'PROSPEK' as jenis")
            )
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'customers' => $existing->merge($prospek)->values(),
        ]);
    }
}
