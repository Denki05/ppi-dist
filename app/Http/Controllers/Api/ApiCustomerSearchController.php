<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pencarian customer (existing + prospek) untuk form estimate AO.
 * GET /api/customers/search/name?q=
 * Response {success, customers:[{id,nama,city,address,phone,cp,owner,provinsi,kecamatan,kelurahan,pic,officer,sm,kategori,zone,jenis}]}
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

        // Area manager: `sm` di satu environment, `am` di environment lain.
        $areaColumn         = $this->areaColumn('master_customers');
        $prospekAreaColumn  = $this->areaColumn('master_customers_prospek');

        // Scope opsional dari AO: pic=customer sendiri, area=sm/am (sales manager).
        // Tanpa parameter ini respons tetap seperti sebelumnya (semua).
        $picKeys  = $this->scopeKeys($request->query('pic'));
        $areaKeys = $this->scopeKeys($request->query('area'));

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
                DB::raw($areaColumn ? 'c.`' . $areaColumn . '` as sm' : 'NULL as sm'),
                'cat.name as kategori',
                'a.zone as zone',
                DB::raw("'EXISTING' as jenis")
            );

        $this->applyScope($existing, $picKeys, $areaKeys, $areaColumn);

        $existing = $existing->limit(50)->get();

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
                DB::raw($prospekAreaColumn ? 'c.`' . $prospekAreaColumn . '` as sm' : 'NULL as sm'),
                'cat.name as kategori',
                'a.zone as zone',
                DB::raw("'PROSPEK' as jenis")
            );

        $this->applyScope($prospek, $picKeys, $areaKeys, $prospekAreaColumn);

        $prospek = $prospek->limit(50)->get();

        return response()->json([
            'success' => true,
            'customers' => $existing->merge($prospek)->values(),
        ]);
    }

    /**
     * Nama kolom area manager pada tabel induk: `sm` atau `am`
     * tergantung environment. Null bila tidak ada.
     */
    private function areaColumn(string $table): ?string
    {
        $columns = Schema::getColumnListing($table);

        foreach (['sm', 'am'] as $candidate) {
            if (in_array($candidate, $columns, true)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Normalisasi parameter scope: `pic=erick,erick2` atau `pic[]=...`.
     */
    private function scopeKeys($raw): array
    {
        if (is_string($raw)) {
            $raw = explode(',', $raw);
        }

        if (!is_array($raw)) {
            return [];
        }

        $keys = [];
        foreach ($raw as $value) {
            $value = strtolower(trim((string) $value));
            if ($value !== '' && !in_array($value, $keys, true)) {
                $keys[] = $value;
            }
        }

        return $keys;
    }

    /**
     * Filter scope di SQL sebelum limit, supaya hasil tidak terpotong
     * saat satu SM memegang ratusan customer.
     */
    private function applyScope($query, array $picKeys, array $areaKeys, ?string $areaColumn): void
    {
        $useArea = $areaKeys !== [] && $areaColumn !== null;

        if ($picKeys === [] && !$useArea) {
            return;
        }

        $query->where(function ($w) use ($picKeys, $useArea, $areaKeys, $areaColumn) {
            if ($picKeys !== []) {
                $w->whereIn(DB::raw('LOWER(TRIM(c.pic))'), $picKeys);
            }

            if ($useArea) {
                $w->orWhereIn(DB::raw('LOWER(TRIM(c.`' . $areaColumn . '`))'), $areaKeys);
            }
        });
    }
}