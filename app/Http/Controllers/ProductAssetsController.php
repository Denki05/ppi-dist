<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductAssetsController extends Controller
{
    public function index(Request $request)
    {
        try {

            // =========================
            // PARAMETER
            // =========================
            $limit = max(1, (int) $request->get('limit', 50));
            $page  = max(1, (int) $request->get('page', 1));

            $offset = ($page - 1) * $limit;

            // =========================
            // QUERY
            // =========================
            $query = DB::table('product_assets')
                ->join('master_products', 'master_products.id', '=', 'product_assets.product_id')
                ->where('master_products.status', 1)      // 1 = ACTIVE
                ->where('master_products.on_order', 1)    // 1 = ORDER
                ->select('product_assets.*');

            // filter opsional yang sudah ada
            if ($request->has('brand')) {
                $query->where('product_assets.brand', $request->brand);
            }

            if ($request->has('merek')) {
                $query->where('product_assets.merek', $request->merek);
            }

            if ($request->has('product_code')) {
                $query->where('product_assets.product_code', $request->product_code);
            }

            // 👇👇👇 INI TAMBAHANNYA AGAR VARIANT/PRODUCT_NAME TERFILTER 👇👇👇
            if ($request->has('searah')) {
                $query->where('product_assets.searah', $request->searah);
            }

            if ($request->has('product_name')) {
                $query->where('product_assets.product_name', $request->product_name);
            }
            // 👆👆👆 ======================================================= 👆👆👆

            // =========================
            // TOTAL
            // =========================
            $total = $query->count();

            // =========================
            // DATA
            // =========================
            $data = $query
                ->orderBy('product_assets.updated_at', 'desc')
                ->offset($offset)
                ->limit($limit)
                ->get();

            // =========================
            // TRANSFORM DATA
            // =========================
            $data->transform(function ($item) {

                // 🔥 bangun ulang path dari field (lebih aman)
                $constructedPath = trim(
                    ($item->internal_folder ?? '') . '/' .
                    ($item->brand_folder ?? '') . '/' .
                    ($item->searah_folder ?? '') . '/' .
                    ($item->variant_folder ?? ''),
                    '/'
                );

                // fallback kalau base_path kosong / beda
                $finalPath = $item->base_path ?: $constructedPath;

                $encoded = base64_encode($finalPath);

                return [
                    'id' => $item->id,

                    // product
                    'product_id' => $item->product_id,
                    'product_code' => $item->product_code,
                    'product_name' => $item->product_name,

                    // original data
                    'merek' => $item->merek,
                    'brand' => $item->brand,
                    'searah' => $item->searah,

                    // folder mapping
                    'internal_folder' => $item->internal_folder,
                    'brand_folder' => $item->brand_folder,
                    'searah_folder' => $item->searah_folder,
                    'variant_folder' => $item->variant_folder,

                    // path
                    'base_path' => $finalPath,
                    'encoded_path' => $encoded,

                    // 🔥 siap pakai untuk frontend / sys-af
                    'drive_list_url' => 'https://drive.lssoft88.xyz/api/list?path=' . $encoded,
                    'drive_first_file_url' => 'https://drive.lssoft88.xyz/api/file?path=' . $encoded,

                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            });

            // =========================
            // RESPONSE
            // =========================
            return response()->json([
                'success' => true,
                'meta' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'total_page' => ceil($total / $limit)
                ],
                'data' => $data
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], 500);

        }
    }
}