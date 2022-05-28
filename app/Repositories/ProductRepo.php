<?php

namespace App\Repositories;

use App\Entities\Master\ProductType;

class ProductRepo
{
    public static function productType($id = null, $condition = [])
    {
        if ($id === false) {
            return;
        }

        $product_type = ProductType::where('status', ProductType::STATUS['ACTIVE'])
            ->orderBy('name')
            ->when($id != null, function ($query1) use ($id) {
                if (is_array($id)) {
                    return $query1->whereIn('id', $id);
                }
                return $query1->where('id', $id);
            })
            ->when(!empty($condition), function ($query2) use ($condition) {
                return $query2->where($condition);
            })
            ->get();

        if (count($product_type) == 1 AND !is_array($id)) {
            return $product_type->first();
        }
        
        return $product_type;
    }
}