<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Penjualan\PackingOrderItem;
use App\Entities\Penjualan\DeliveryOrderMutationItem;
use App\Helpers\LogActivity;

class SalesOrderDestroyService
{
    public function destroy($salesOrder)
    {
        $update = SalesOrder::where('id', $salesOrder->id)->update(['deleted_by' => Auth::id()]);
        $destroy = SalesOrder::where('id', $salesOrder->id)->delete();

        $so_item = SalesOrderItem::where('so_id', $salesOrder->id)->get();

        foreach ($so_item as $value) {
            $check_do_item = PackingOrderItem::where('so_item_id', $value->id)->first();
            $check_do_mutation_item = DeliveryOrderMutationItem::where('so_item_id', $value->id)->first();
            if ($check_do_item || $check_do_mutation_item) {
                return ['success' => false, 'message' => 'Gagal menghapus Item. Item SO ini sudah digunakan di Packing Order / Delivery Order Mutation'];
            }
        }

        $destroy_item = SalesOrderItem::where('so_id', $salesOrder->id)->delete();

        return ['success' => true, 'message' => 'Success'];
    }

    public function destroyItem($request)
    {
        $request->validate(['id' => 'required']);
        $post = $request->all();

        $check_do_item = PackingOrderItem::where('so_item_id', $post["id"])->first();
        $check_do_mutation_item = DeliveryOrderMutationItem::where('so_item_id', $post["id"])->first();

        if ($check_do_item || $check_do_mutation_item) {
            return ['success' => false, 'message' => 'Gagal menghapus SO. Item di SO sudah digunakan di Packing Order / Delivery Order Mutation'];
        }

        $update = SalesOrderItem::where('id', $post["id"])->update(['deleted_by' => Auth::id()]);
        $destroy = SalesOrderItem::where('id', $post["id"])->delete();

        return ['success' => true, 'message' => 'Item berhasil dihapus'];
    }

    public function destroyLanjutan($salesOrder)
    {
        if ($salesOrder->count_rev > 0) {
            return ['success' => false, 'message' => 'Invoice sudah terbuat!'];
        }

        $salesOrder->deleted_by = Auth::id();
        $salesOrder->delete();

        if ($salesOrder->save()) {
            foreach ($salesOrder->so_detail as $detail) {
                SalesOrderItem::where('id', $detail->id)->delete();
            }

            LogActivity::addToLog('Deleted SO-Lanjutan: ' . $salesOrder->so_code);
            return ['success' => true, 'message' => 'Success'];
        }

        return ['success' => false, 'message' => 'Gagal menghapus SO'];
    }
}
