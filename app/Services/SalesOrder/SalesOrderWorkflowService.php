<?php

namespace App\Services\SalesOrder;

use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SalesOrderItem;
use App\Entities\Penjualan\SalesOrderProforma;
use App\Entities\Penjualan\SalesOrderProformaItem;
use App\Entities\Penjualan\PackingOrder;
use App\Repositories\CodeRepo;
use Auth;
use DB;
use Log;

class SalesOrderWorkflowService
{
    /**
     * Lanjutkan SO: Proforma (CASH+estimate) atau SO Lanjutan (normal)
     * Returns ['type' => 'proforma'|'lanjutan', 'sales_order' => ...]
     */
    public function lanjutkan($salesOrder)
    {
        // CASE 1: PROFORMA (CASH + ESTIMATE) -> MASUK MODUL SO PROFORMA
        if ($salesOrder->is_estimate == 1 && trim(strtoupper($salesOrder->type_transaction)) == 'CASH' && $salesOrder->status_proforma == 0) {
            
            $salesOrder->is_proforma = 1;
            $salesOrder->status_proforma = 1;
            $salesOrder->status = 1; // Tetap di tahan di index AWAL
            $salesOrder->save();

            // Auto create proforma if not exists
            $existingProforma = SalesOrderProforma::where('so_id', $salesOrder->id)->first();

            if (!$existingProforma) {
                $proforma = new SalesOrderProforma();
                $proforma->so_id = $salesOrder->id;
                $proforma->code = CodeRepo::generateSoProforma();
                $proforma->customer_name = null;
                $proforma->customer_address = null;
                $proforma->customer_region = null;
                $proforma->customer_city = null;
                $proforma->customer_phone = null;
                $proforma->customer_owner = null;
                $proforma->so_date = null;
                $proforma->so_brand_name = $salesOrder->brand_name;
                $proforma->so_type_transaction = 1; // CASH
                $proforma->so_idr_rate = $salesOrder->idr_rate;
                $proforma->note = $salesOrder->note;
                $proforma->so_lanjutan = 0;
                $proforma->status = 1;
                $proforma->transfer_verified = 0;
                $proforma->customer_other_address_id = $salesOrder->customer_other_address_id;
                $proforma->warehouse_id = null;
                $proforma->rekening_id = null;
                $proforma->vendor_id = null;
                $proforma->sales_senior_id = null;
                $proforma->sales_id = null;
                $proforma->exsisting_customer = 1;
                $proforma->created_by = Auth::id();
                $proforma->save();

                // Copy items
                $soItems = SalesOrderItem::where('so_id', $salesOrder->id)->get();
                foreach ($soItems as $item) {
                    $proformaItem = new SalesOrderProformaItem();
                    $proformaItem->so_proforma_id = $proforma->id;
                    $proformaItem->product_packaging_id = $item->product_packaging_id;
                    $proformaItem->price = $item->price;
                    $proformaItem->qty = $item->qty;
                    $proformaItem->disc_usd = $item->disc_usd;
                    $proformaItem->packaging_id = $item->packaging_id;
                    $proformaItem->total_item = 0;
                    $proformaItem->free_product = $item->free_product;
                    $proformaItem->save();
                }
            }

            $this->notifyAdminSales($salesOrder, 'proforma');

            return ['type' => 'proforma', 'sales_order' => $salesOrder];
        }

        // CASE 2: SO NORMAL ATAU "TEMPO + ESTIMATE" -> MASUK SO LANJUTAN
        $salesOrder->status = 2;
        // Catat waktu submit ke admin (basis tanggal tab SO Lanjutan).
        $salesOrder->submitted_at = date('Y-m-d H:i:s');
        $salesOrder->save();

        $this->notifyAdminSales($salesOrder, 'lanjutan');

        return ['type' => 'lanjutan', 'sales_order' => $salesOrder];
    }

    /**
     * Kembalikan SO ke revisi (status=3)
     */
    public function kembali($salesOrder)
    {
        $salesOrder->status = 3;
        $salesOrder->updated_by = Auth::id();
        $salesOrder->save();

        return $salesOrder;
    }

    /**
     * Tandai SO sebagai tidak dilanjutkan
     */
    public function tidakLanjut($salesOrder, $keterangan)
    {
        $salesOrder->keterangan_tidak_lanjut = trim(htmlentities($keterangan));
        $salesOrder->status = 3;
        $salesOrder->save();

        return $salesOrder;
    }

    /**
     * Tandai SO sebagai indent (status=6)
     */
    public function indent($salesOrder)
    {
        $salesOrder->status = 6;
        $salesOrder->code = null;
        $salesOrder->indent_status = 1;
        $salesOrder->updated_by = Auth::id();
        $salesOrder->save();

        return $salesOrder;
    }

    /**
     * Kembalikan SO dari indent hold (status=5, indent_status=2)
     * Returns ['success' => bool, 'errors' => array]
     */
    public function kembaliHold($salesOrder, $catatan = null)
    {
        $errors = [];

        // Check invoice apakah sudah terbuat?
        $do = PackingOrder::where('so_id', $salesOrder->id)->first();
        if (!empty($do->invoicing)) {
            $errors[] = 'Invoice sudah terbuat, tidak bisa melakukan indent!';
        }

        $salesOrder->status = 5;
        $salesOrder->indent_status = 2;
        $salesOrder->catatan = $catatan;
        $salesOrder->updated_by = Auth::id();
        $salesOrder->save();

        return ['success' => empty($errors), 'errors' => $errors, 'sales_order' => $salesOrder];
    }

    /**
     * Approve MOU pada SO
     */
    public function approvalMou($salesOrder)
    {
        $salesOrder->approval_mou_status = 1;
        $salesOrder->approval_mou_date = date('Y-m-d H:i:s');
        $salesOrder->approval_mou_by = Auth::id();
        $salesOrder->status = 2;
        // Kembali masuk antrean admin -> perbarui waktu submit.
        $salesOrder->submitted_at = date('Y-m-d H:i:s');
        $salesOrder->save();

        return $salesOrder;
    }

    /**
     * Notifikasi SO masuk ke admin sales.
     *
     * Dipanggil dari lanjutkan() supaya semua jalur ikut terpicu: submit dari
     * modul AO, kirim ulang setelah dikembalikan, dan lanjutkan manual admin.
     * Penerima = user ber-profile Admin Sales, ditambah user id 33 sebagai
     * penerima tetap (permintaan manajer: notif pasti sampai ke admin sales).
     */
    public function notifyAdminSales($salesOrder, $kind = 'lanjutan')
    {
        try {
            foreach ($this->adminSalesRecipients() as $user) {
                $user->notify(new \App\Notifications\SoNotification($salesOrder, $kind));
            }
        } catch (\Exception $e) {
            // Notifikasi tidak boleh menggagalkan penyimpanan SO.
            Log::error('Gagal kirim notifikasi SO: ' . $e->getMessage());
        }
    }

    /**
     * Notifikasi pengajuan proforma baru (estimate dari menu Prospek AO).
     * Dipanggil dari endpoint terima pengajuan, bukan dari lanjutkan(), karena
     * jalur itu belum membuat SalesOrder.
     */
    public function notifyAdminSalesPengajuan($pengajuan)
    {
        try {
            foreach ($this->adminSalesRecipients() as $user) {
                $user->notify(new \App\Notifications\PengajuanProformaNotification($pengajuan));
            }
        } catch (\Exception $e) {
            Log::error('Gagal kirim notifikasi pengajuan proforma: ' . $e->getMessage());
        }
    }

    /**
     * Penerima notifikasi admin sales: user ber-profile Admin Sales, ditambah
     * user id 33 sebagai penerima tetap (permintaan manajer: notif pasti
     * sampai ke admin sales).
     */
    private function adminSalesRecipients()
    {
        $recipients = \App\Entities\Account\Superuser::query()->get()
            ->filter(function ($user) {
                return \App\Services\MenuAccessService::profileKeyForUser($user) === 'Admin Sales';
            });

        $fixed = \App\Entities\Account\Superuser::find(33);
        if ($fixed) {
            $recipients->push($fixed);
        }

        return $recipients->unique('id');
    }
}
