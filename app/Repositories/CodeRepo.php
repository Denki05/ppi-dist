<?php

namespace App\Repositories;

use App\Entities\Master\BranchOffice;
use App\Entities\Master\BrandReference;
use App\Entities\Master\Customer;
use App\Entities\Master\CustomerCategory;
use App\Entities\Master\CustomerType;
use App\Entities\Master\ProductCategory;
use App\Entities\Master\ProductType;
use App\Entities\Master\SubBrandReference;
use App\Entities\Master\Unit;
use App\Entities\Master\Vendor;
use App\Entities\Master\Warehouse;
use App\Entities\Penjualan\SalesOrder;
use App\Entities\Penjualan\SaleReturn;
use App\Entities\Penjualan\SalesOrderProforma;
use App\Entities\Penjualan\SalesOrderKontrak;
use App\Entities\Penjualan\PackingOrder;
use App\Entities\Penjualan\DeliveryOrderMutation;
use App\Entities\Penjualan\Canvasing;
use App\Entities\Finance\Invoicing;
use App\Entities\Finance\Payable;
use App\Entities\Gudang\StockAdjustment;
use App\Entities\Gudang\Receiving;
use App\Entities\Master\BrandLokal;
use App\Entities\Gudang\PurchaseOrder;
use App\Entities\Gudang\MutasiShowroom;
use DB;

class CodeRepo
{
    private static function generate($pre = '', $class)
    {
        $count = $class::withTrashed()->count() + 1;
        $code = $pre . sprintf('%05d', $count);

        return $code;
    }

    public static function generateBranchOffice()
    {
        return self::generate('B', BranchOffice::class);
    }

    public static function generateBrandReference()
    {
        return self::generate('BR', BrandReference::class);
    }

    public static function generateSubBrandReference()
    {
        return self::generate('SBR', SubBrandReference::class);
    }

    public static function generateCustomer()
    {
        return self::generate('C', Customer::class);
    }

    public static function generateCustomerCategory()
    {
        return self::generate('CC', CustomerCategory::class);
    }

    public static function generateCustomerType()
    {
        return self::generate('CT', CustomerType::class);
    }

    public static function generateProductCategory()
    {
        return self::generate('PC', ProductCategory::class);
    }

    public static function generateProductType()
    {
        return self::generate('PT', ProductType::class);
    }

    public static function generateWarehouse()
    {
        return self::generate('WH', Warehouse::class);
    }

    public static function generateVendor()
    {
        return self::generate('V', Vendor::class);
    }

    // So PPN
    public static function generatePPN(){
        $count = SalesOrder::withTrashed()
                              ->where('condition', '>', 0)
                              ->whereYear('created_at',date('Y'))
                              ->whereMonth('created_at',date('m'))
                              ->get();
                                   
        if(count($count) > 0 ){
            $count = count($count) + 1;

            $code = 'SO-PPN/' .date('my')."-".sprintf('%03d', $count);
        }
        else{
            $code = 'SO-PPN/' .date('my')."-".sprintf('%03d', 1);
        }
        return $code;

    }

    // Generate So code awal
    public static function generateSoAwal(){
        $count = SalesOrder::withTrashed()
                              ->where('status', '>', 0)
                              ->whereYear('created_at',date('Y'))
                              ->whereMonth('created_at',date('m'))
                              ->get();
                                   
        if(count($count) > 0 ){
            $count = count($count) + 1;

            $code = 'SO-' .date('ym').sprintf('%03d', $count);
        }
        else{
            $code = 'SO-' .date('ym').sprintf('%03d', 1);
        }
        return $code;

    }

    // Generate code so awal ppn
    public static function generateSoAwalPpn(){
        $count = SalesOrder::withTrashed()
                              ->where('status', '>', 0)
                              ->where('type_so', 'ppn')
                              ->whereYear('created_at',date('Y'))
                              ->whereMonth('created_at',date('m'))
                              ->get();
                                   
        if(count($count) > 0 ){
            $count = count($count) + 1;

            $code = 'SO-PPN-' .date('ym').sprintf('%03d', $count);
        }
        else{
            $code = 'SO-PPN-' .date('ym').sprintf('%03d', 1);
        }
        return $code;

    }

    // Generate CTG
    public static function generateCTG(){
        $count = Catalog::withTrashed()
                              ->where('status', '>', 0)
                              ->whereYear('created_at',date('Y'))
                              ->whereMonth('created_at',date('m'))
                              ->get();
                                   
        if(count($count) > 0 ){
            $count = count($count) + 1;

            $code = 'CTG-' .date('my')."-".sprintf('%03d', $count);
        }
        else{
            $code = 'CTG-' .date('my')."-".sprintf('%03d', 1);
        }
        return $code;

    }

    public static function generatePO(){
        return self::generate('PRE', PackingOrder::class);   
    }
    public static function generateDO(){
        $count = PackingOrder::withTrashed()
                              ->where('status','>',1)
                              ->whereYear('updated_at',date('Y'))
                              ->whereMonth('updated_at',date('m'))
                              ->get();
                                   
        if(count($count) > 0 ){
            $count = count($count) + 1;

            $code = 'DO' .date('my')."".sprintf('%05d', $count);
        }
        else{
            $code = 'DO' .date('my')."".sprintf('%05d', 1);
        }
        return $code;

    }
    public static function generateDOM(){
        return self::generate('MT', DeliveryOrderMutation::class);   
    }
    public static function generateCanvasing(){
        return self::generate('SM', Canvasing::class);   
    }
    public static function generateInvoicing($do_code){
        $split = explode("-", $do_code);

        if(count($split) == 1){

            $split = explode("DO", $do_code);
            $code = 'INV'.$split[1];    
        }
        else{
            $split = explode("-", $do_code);
            $code = 'INV-' .$split[1]."-".$split[2];
        }
        return $code;
    }
   
    public static function generateStockAdjustment(){
        return self::generate('STADJ', StockAdjustment::class);   
    }
    
    public static function generatePayable(){ 
        $count = Payable::where('status','>',1)
                              ->whereYear('created_at',date('Y'))
                              ->whereMonth('created_at',date('m'))
                              ->get();
                                   
        if(count($count) > 0 ){
            $count = count($count) + 1;

            $code = 'PY' .date('my')."".sprintf('%03d', $count);
        }
        else{
            $code = 'PY' .date('my')."".sprintf('%03d', 1);
        }
        return $code;
    }

    public static function generateReceiving()
    {
        return self::generate('RC', Receiving::class);
    }

    // Generate SO code
    public static function generateSO()
    {
        $parts = explode('-', date("d-m-Y"));
        $p1 = substr($parts[2], (strlen($parts[2]) - 1) );
        $abjadMonth = array( '-', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L');
        $p2 = $abjadMonth[date('n')];
        $yearMonth = $p1.$p2;
        $latestNumber = "";

        $get_max = DB::table('penjualan_so')->where('code', 'LIKE', '%'.$yearMonth.'%')->where('deleted_at', null)->max('code');

        if($get_max == 'false'){
            $latestNumber = $yearMonth . '001';
        }else{
            $latestNumber = $get_max;
            $id = (int) substr($latestNumber, strlen($yearMonth)) + 1;
            $latestNumber = $yearMonth . str_pad($id, 3, 0, STR_PAD_LEFT);
        }
        return $latestNumber;
    }

    // Generate PO code
    public static function generatePurchaseOrder()
    {
        // $get_max = Purchaseorder::max('code');
        $parts = explode('-', date("d-m-Y"));
        $p1 = substr($parts[2], (strlen($parts[2]) - 2) );
        $abjadMonth = array( '-', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L');
        $p2 = $abjadMonth[date('n')];
        $str_code = "B";
        $yearMonth = $str_code.$p1.$p2;
        $latestNumber = "";

        $get_max = DB::table('purchase_order')->where('code', 'LIKE', '%'.$yearMonth.'%')->where('deleted_at', null)->max('code');

        if($get_max == 'false'){
            $latestNumber = $yearMonth . '001';
        }else{
            $latestNumber = $get_max;
            $id = (int) substr($latestNumber, strlen($yearMonth)) + 1;
            $latestNumber = $yearMonth . str_pad($id, 3, 0, STR_PAD_LEFT);
        }
        return $latestNumber;
    }

    public static function generatePurchaseOrderSPK()
    {
        // $get_max = Purchaseorder::max('code');
        $parts = explode('-', date("d-m-Y"));
        $p1 = substr($parts[2], (strlen($parts[2]) - 2) );
        $abjadMonth = array( '-', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L');
        $p2 = $abjadMonth[date('n')];
        $str_code = "SPK";
        $yearMonth = $str_code.$p1.$p2;
        $latestNumber = "";

        $get_max = DB::table('purchase_order')->where('type', 0)->where('code', 'LIKE', '%'.$yearMonth.'%')->where('deleted_at', null)->max('code');

        if($get_max == 'false'){
            $latestNumber = $yearMonth . '001';
        }else{
            $latestNumber = $get_max;
            $id = (int) substr($latestNumber, strlen($yearMonth)) + 1;
            $latestNumber = $yearMonth . str_pad($id, 3, 0, STR_PAD_LEFT);
        }
        return $latestNumber;
    }

    // generate so ppn
    public static function generateSOPPN()
    {
        $parts = explode('-', date("d-m-Y"));
        $p1 = substr($parts[2], (strlen($parts[2]) - 1) );
        $abjadMonth = array( '-', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L');
        $p2 = $abjadMonth[date('n')];
        $index_so = 'P';
        $yearMonth = $index_so.$p1.$p2;
        $latestNumber = "";
        
        $get_max = DB::table('penjualan_so')->where('code', 'LIKE', '%'.$yearMonth.'%')->where('deleted_at', null)->max('code');

        if($get_max == 'false'){
            $latestNumber = $yearMonth . '001';
        }else{
            $latestNumber = $get_max;
            $id = (int) substr($latestNumber, strlen($yearMonth)) + 1;
            $latestNumber = $yearMonth . str_pad($id, 3, 0, STR_PAD_LEFT);
        }

        // dd($latestNumber);
        return $latestNumber;
    }

    // Generate invoice tax
    public static function generateINVTAX()
    {
        $parts = explode('-', date("d-m-Y"));
        $p1 = substr($parts[2], (strlen($parts[2]) - 1) );
        $abjadMonth = array( '-', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L');
        $p2 = $abjadMonth[date('n')];
        $str_code = "T";
        $yearMonth = $str_code.$p1.$p2;
        $latestNumber = "";

        $get_max = DB::table('finance_invoice_mitra')->where('code', 'LIKE', '%'.$yearMonth.'%')->where('deleted_at', null)->max('code');

        if($get_max == 'false'){
            $latestNumber = $yearMonth . '001';
        }else{
            $latestNumber = $get_max;
            $id = (int) substr($latestNumber, strlen($yearMonth)) + 1;
            $latestNumber = $yearMonth . str_pad($id, 3, 0, STR_PAD_LEFT);
        }

        // dd($latestNumber);
        return $latestNumber;
    }

    public static function generateSoKontrak(){
       $get_max_id = DB::table('penjualan_so_kontrak')->where('deleted_at', null)->max('id');
    
       if($get_max_id == null){
            $latestNumber = '001';
       }else{
            $parts1 = explode('_', $get_max_id);
            $parts2 = explode('.', $parts1[5]);
        
            $get_code = $parts2[0];
            $latestNumber = "";

            if($get_code == false){
                    $latestNumber = '001';
            }else{
                    $latestNumber = $get_code;
                    $id = (int) $latestNumber + 1;
                    $latestNumber = str_pad($id, 3, 0, STR_PAD_LEFT);
            }
       }

       return $latestNumber;
    }

    public static function generateSoProforma(){
        // Retrieve the count of proforma sales orders for the current year and month
        $count = Salesorderproforma::withTrashed()
                                    ->where('status', '>', 0)
                                    ->whereYear('created_at', date('Y'))
                                    ->whereMonth('created_at', date('m'))
                                    ->count();
    
        // Increment the count if there are existing orders, otherwise initialize to 1
        $count = ($count > 0) ? $count + 1 : 1;
    
        // Generate the proforma code
        $code = 'SOPRO-' . date('ym') . sprintf('%03d', $count);
    
        // Return the generated code
        return $code;
    }

    public static function generateReturCode()
    {
        // Ambil tanggal sekarang
        $day   = date('d');
        $month = date('n'); // 1-12
        $year  = date('Y');
    
        // Tahun: ambil 2 digit terakhir
        $p1 = substr($year, -2);
    
        // Konversi bulan ke huruf
        $abjadMonth = [ '-', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
        $p2 = $abjadMonth[$month];
    
        $yearMonth = $p1 . $p2; // contoh: 25H
    
        // Ambil kode terakhir bulan ini
        $get_max = DB::table('penjualan_retur')
            ->where('code', 'LIKE', 'R' . $yearMonth . '%')
            ->whereNull('deleted_at')
            ->max('code');
    
        if ($get_max === null) {
            // Belum ada kode bulan ini
            $latestNumber = 'R' . $yearMonth . '001';
        } else {
            // Ambil nomor urut dari kode terakhir
            $id = (int) substr($get_max, strlen('R' . $yearMonth)) + 1;
            $latestNumber = 'R' . $yearMonth . str_pad($id, 3, '0', STR_PAD_LEFT);
        }
    
        return $latestNumber;
    }

    public static function generateMutasiOutCode()
    {
        // Ambil tanggal sekarang
        $day   = date('d');
        $month = date('n'); // 1-12
        $year  = date('Y');
    
        // Tahun: ambil 2 digit terakhir
        $p1 = substr($year, -2);
    
        // Konversi bulan ke huruf
        $abjadMonth = [ '-', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
        $p2 = $abjadMonth[$month];
    
        $yearMonth = $p1 . $p2; // contoh: 25H
    
        // Ambil kode terakhir bulan ini
        $get_max = DB::table('gudang_mutasi_out')
            ->where('code', 'LIKE', 'S' . $yearMonth . '%')
            ->whereNull('deleted_at')
            ->max('code');
    
        if ($get_max === null) {
            // Belum ada kode bulan ini
            $latestNumber = 'S' . $yearMonth . '001';
        } else {
            // Ambil nomor urut dari kode terakhir
            $id = (int) substr($get_max, strlen('S' . $yearMonth)) + 1;
            $latestNumber = 'S' . $yearMonth . str_pad($id, 3, '0', STR_PAD_LEFT);
        }
    
        return $latestNumber;
    }

    public static function generateQualityControl2Code()
    {
        // Ambil tanggal sekarang
        $day   = date('d');
        $month = date('n'); // 1-12
        $year  = date('Y');
    
        // Tahun: ambil 2 digit terakhir
        $p1 = substr($year, -2);
    
        // Konversi bulan ke huruf
        $abjadMonth = [ '-', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
        $p2 = $abjadMonth[$month];
    
        $yearMonth = $p1 . $p2; // contoh: 25H
    
        // Ambil kode terakhir bulan ini
        $get_max = DB::table('receiving_komplain')
            ->where('code', 'LIKE', 'RK' . $yearMonth . '%')
            ->whereNull('deleted_at')
            ->max('code');
    
        if ($get_max === null) {
            // Belum ada kode bulan ini
            $latestNumber = 'RK' . $yearMonth . '001';
        } else {
            // Ambil nomor urut dari kode terakhir
            $id = (int) substr($get_max, strlen('RK' . $yearMonth)) + 1;
            $latestNumber = 'RK' . $yearMonth . str_pad($id, 3, '0', STR_PAD_LEFT);
        }
    
        return $latestNumber;
    }

    public static function generateMutasiShowroom(int $type) 
    {
        // ===============================
        // FORMAT TANGGAL
        // ===============================
        $monthMap = ['-', 'A','B','C','D','E','F','G','H','I','J','K','L'];

        $year  = date('y');              // 26
        $month = $monthMap[date('n')];   // A
        $ym    = $year . $month;         // 26A

        // ===============================
        // PREFIX BERDASARKAN TYPE
        // ===============================
        $prefix = MutasiShowroom::getKodePrefixByType($type);
        $base   = $prefix . $ym;

        // ===============================
        // AMBIL KODE TERAKHIR (AMAN)
        // ===============================
        $lastCode = DB::table('penjualan_showroom')
            ->where('kode', 'LIKE', $base . '%')
            ->whereNull('deleted_at')
            ->lockForUpdate()
            ->max('kode');

        if (!$lastCode) {
            $number = 1;
        } else {
            preg_match('/(\d{3})(\/|$)/', $lastCode, $match);
            $number = ((int)$match[1]) + 1;
        }

        // ===============================
        // FINAL KODE
        // ===============================
        $kode = $base . str_pad($number, 3, '0', STR_PAD_LEFT);

        if ($type === MutasiShowroom::TYPE_SYSTEM_FREE_SO) {
            $kode;

            // dd($kode);
        }

        return $kode;
    }

    public static function generateMutasiGudangutamaCode()
    {
        // Ambil tanggal sekarang
        $day   = date('d');
        $month = date('n'); // 1-12
        $year  = date('Y');
    
        // Tahun: ambil 2 digit terakhir
        $p1 = substr($year, -2);
    
        // Konversi bulan ke huruf
        $abjadMonth = [ '-', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
        $p2 = $abjadMonth[$month];
    
        $yearMonth = $p1 . $p2; // contoh: 25H
    
        // Ambil kode terakhir bulan ini
        $get_max = DB::table('gudang_mutasi_out')
            ->where('code', 'LIKE', 'RS-' . $yearMonth . '%')
            ->whereNull('deleted_at')
            ->max('code');
    
        if ($get_max === null) {
            // Belum ada kode bulan ini
            $latestNumber = 'RS-' . $yearMonth . '001';
        } else {
            // Ambil nomor urut dari kode terakhir
            $id = (int) substr($get_max, strlen('RS-' . $yearMonth)) + 1;
            $latestNumber = 'RS-' . $yearMonth . str_pad($id, 3, '0', STR_PAD_LEFT);
        }
    
        return $latestNumber;
    }
}