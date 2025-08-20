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
                // Perubahan di sini: Menggunakan DB::raw untuk menjumlahkan kolom
                DB::raw('SUM(penjualan_do_details.purchase_total_idr + penjualan_do_details.delivery_cost_idr) AS nilai_faktur'),
                'penjualan_do_details.delivery_cost_idr AS ongkos_kirim',
                'penjualan_so.so_date AS tanggal_faktur',
                'master_customers.tempo_limit AS tempo_limit',
                DB::raw('IFNULL(SUM(finance_payable_detail.total), 0) AS pembayaran'),
                DB::raw('
                    CASE
                        -- Pastikan Anda juga menggunakan nilai_faktur yang baru di sini untuk perhitungan status
                        WHEN (SUM(penjualan_do_details.purchase_total_idr + penjualan_do_details.delivery_cost_idr)) - IFNULL(SUM(finance_payable_detail.total), 0) <= 0 THEN "PAID"
                        ELSE "UNPAID"
                    END AS status_faktur
                ')
            )
            ->whereBetween('penjualan_so.so_date', [$request->startDate, $request->endDate])
            ->where(function ($query) use ($request) {
                if ($request->customer != 'all') {
                    $query->where('master_customer_other_addresses.id', $request->customer);
                }
            })
            ->where('penjualan_so.type_so', 'nonppn')
            ->groupBy('finance_invoicing.code')
            ->having('status_faktur', '=', 'UNPAID')
            ->get();

        // dd($model);
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