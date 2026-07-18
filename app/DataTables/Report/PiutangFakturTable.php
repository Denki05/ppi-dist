<?php

namespace App\DataTables\Report;

use App\DataTables\Table;
use App\Entities\Finance\Invoicing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;

class PiutangFakturTable extends Table
{
    /**
     * Get query source of dataTable.
     *
     */
    private function query(Request $request)
{
    $customer = $request->customer;

    // Jika customer bisa lebih dari 1 (array atau csv), ubah ke array
    if ($customer !== 'all') {
        if (!is_array($customer)) {
            $customer = explode(',', $customer);
        }
    }

    $model = Invoicing::where('finance_invoicing.status', 1)
        ->leftJoin('finance_payable_detail', 'finance_invoicing.id', '=', 'finance_payable_detail.invoice_id')
        ->leftJoin('finance_payable', 'finance_payable_detail.payable_id', '=', 'finance_payable.id')
        ->leftJoin('master_customer_other_addresses', 'finance_invoicing.customer_other_address_id', '=', 'master_customer_other_addresses.id')
        ->leftJoin('penjualan_do', 'finance_invoicing.do_id', '=', 'penjualan_do.id')
        ->leftJoin('penjualan_so', 'penjualan_do.so_id', '=', 'penjualan_so.id')
        ->leftJoin('penjualan_do_details', 'penjualan_do.id', '=', 'penjualan_do_details.do_id')
        ->leftJoin('master_customers', 'master_customer_other_addresses.customer_id', '=', 'master_customers.id')
        ->select(
            'master_customer_other_addresses.name AS customer_name',
            'master_customer_other_addresses.text_kota AS customer_kota',
            'finance_invoicing.code AS no_faktur',

            // ✅ FINAL FIX: subquery item * kurs - diskon + ongkir
            DB::raw('
                ROUND(
                    (
                        (
                            SELECT COALESCE(SUM(doi.total * penjualan_do.idr_rate), 0)
                            FROM penjualan_do_item doi
                            WHERE doi.do_id = penjualan_do.id
                        )
                        - COALESCE(penjualan_do_details.discount_1_idr, 0)
                        - COALESCE(penjualan_do_details.discount_2_idr, 0)
                        - COALESCE(penjualan_do_details.discount_idr, 0)
                        + (
                            CASE
                                WHEN penjualan_so.so_date >= \'2025-01-01\'
                                THEN COALESCE(penjualan_do_details.delivery_cost_idr, 0)
                                ELSE 0
                            END
                        )
                    )
                ,0) AS nilai_faktur
            '),

            'penjualan_do_details.delivery_cost_idr AS ongkos_kirim',
            'penjualan_so.so_date AS tanggal_faktur',
            'master_customers.tempo_limit AS tempo_limit',
            DB::raw('
            (
                SELECT COALESCE(SUM(fpd.total),0)
                FROM finance_payable_detail fpd
                INNER JOIN finance_payable fp 
                    ON fp.id = fpd.payable_id
                WHERE fpd.invoice_id = finance_invoicing.id
                AND fpd.deleted_at IS NULL
                AND fp.deleted_at IS NULL
                AND fp.status IN (1,2)
            ) AS pembayaran
            '),

            // ✅ FINAL FIX: status_faktur ikut formula yang sama
            DB::raw('
                CASE
                    WHEN (
                        ROUND(
                            (
                                (
                                    SELECT COALESCE(SUM(doi.total * penjualan_do.idr_rate), 0)
                                    FROM penjualan_do_item doi
                                    WHERE doi.do_id = penjualan_do.id
                                )
                                - COALESCE(penjualan_do_details.discount_1_idr, 0)
                                - COALESCE(penjualan_do_details.discount_2_idr, 0)
                                - COALESCE(penjualan_do_details.discount_idr, 0)
                                + (
                                    CASE
                                        WHEN penjualan_so.so_date >= \'2025-01-01\'
                                        THEN COALESCE(penjualan_do_details.delivery_cost_idr, 0)
                                        ELSE 0
                                    END
                                )
                            )
                        ,0)
                        - (
                            SELECT COALESCE(SUM(fpd.total), 0)
                            FROM finance_payable_detail fpd
                            INNER JOIN finance_payable fp 
                                ON fp.id = fpd.payable_id
                            WHERE fpd.invoice_id = finance_invoicing.id
                        )
                    ) <= 100
                    THEN "PAID"
                    ELSE "UNPAID"
                END AS status_faktur
            ')
        )
        ->whereBetween('penjualan_so.so_date', [$request->startDate, $request->endDate])
        ->when($customer !== 'all', function ($q) use ($customer) {
            $q->whereIn('master_customer_other_addresses.id', $customer);
        })
        ->where('penjualan_so.type_so', 'nonppn')
        ->groupBy('finance_invoicing.id')
        ->havingRaw('
            (
                nilai_faktur - pembayaran
            ) > 100

            AND NOT (
                ABS(
                    (nilai_faktur - pembayaran)
                    - COALESCE(penjualan_do_details.delivery_cost_idr,0)
                ) <= 100
            )
            ')
            ->orderBy('master_customer_other_addresses.name', 'ASC') 
            ->orderBy('finance_invoicing.code', 'ASC')
            ->get();

    return $model;
}

    

    /**
     * Build DataTable class.
     */
    public function build(Request $request)
    {
        $table = Table::of($this->query($request));

        $table->addIndexColumn();

        $table->addColumn('account_customer', function (Invoicing $model) {
            return $model->customer_name . ' ' . $model->customer_kota;
        });

        $table->editColumn('tanggal_faktur', function (Invoicing $model) {
            return [
            'display' => Carbon::parse($model->tanggal_faktur)->format('d-m-Y'),
            'timestamp' => $model->created_at
            ];
        });

        $table->addColumn('jatuh_tempo', function (Invoicing $model) {
            // Calculate due date based on tanggal_buat and tempo_limit
            $tanggalBuat = Carbon::parse($model->tanggal_faktur);
            $tempoLimit = $model->tempo_limit;  // Assuming tempo_limit is in days

            if ($tempoLimit > 0) {
                $jatuhTempo = $tanggalBuat->addDays($tempoLimit);
                return $jatuhTempo->format('d-m-Y');
            } else {
                return 'N/A';  // Handle cases where tempo_limit is 0 or negative
            }
        });

        $table->addColumn('nilai_faktur', function (Invoicing $model) {
            return $model->nilai_faktur;
        });

        $table->addColumn('hutang_asing', function (Invoicing $model) {
            return $model->nilai_faktur - $model->pembayaran;
        });

        $table->addColumn('diff_days', function (Invoicing $model) use ($request) {
            // Calculate the difference in days between jatuh_tempo and request->endDate
            $tanggalBuat = Carbon::parse($model->tanggal_faktur);
            $tempoLimit = $model->tempo_limit;
        
            if ($tempoLimit > 0) {
                $jatuhTempo = $tanggalBuat->addDays($tempoLimit);
                $endDate = Carbon::parse($request->endDate);
        
                // Calculate the difference in days
                $diffDays = $jatuhTempo->diffInDays($endDate, false); // false for signed difference
        
                return $diffDays. ' Hari';
            } else {
                return 'N/A';  // Handle cases where tempo_limit is 0 or negative
            }
        });
        
        return $table->make(true);
    }

}