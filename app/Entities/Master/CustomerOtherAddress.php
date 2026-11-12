<?php

namespace App\Entities\Master;

use App\Entities\Model;
use App\Entities\Penjualan\SalesOrder;
use Auth;
use Illuminate\Support\Facades\Schema;

class CustomerOtherAddress extends Model
{
   
    protected $appends = ['img_ktp', 'img_npwp'];
    protected $guarded = ['free_shipping'];
    protected $fillable = [
        'customer_id', 'member_default', 'sm', 'ams', 'pic', 'officer', 'account_representative', 'account_representative_optional_1', 'account_representative_optional_2', 'name', 'contact_person', 'npwp', 'ktp', 'phone', 'address',
        'gps_latitude', 'gps_longitude',
        'provinsi', 'kota', 'kecamatan', 'kelurahan',
        'text_provinsi', 'text_kota', 'text_kecamatan', 'text_kelurahan',
        'zipcode', 'free_shipping', 'zone', 'setting_income_target', 'image_npwp', 'image_ktp', 'status', 'situation', 'status_key'
    ];
    protected $table = 'master_customer_other_addresses';
    public $incrementing = false;
    public static $directory_image = 'superuser_assets/media/master/member/';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1
    ];

    const SITUATION = [
        'INACTIVE' => 0,
        'ACTIVE' => 1
    ];

    const STATUS_KEY = [
        'DISABLED' => 0,
        'ENABLE' => 1
    ];

    const MEMBER_DEFAULT = [
        'NO' => 0,
        'YES' => 1
    ];

    const ZONING = [
        1 => 'JABODETABEK',
        2 => 'JABAR',
        3 => 'JATENG - JATIM',
        4 => 'SUMATERA',
        5 => 'BALI - KALIMANTAN - SULAWESI',
    ];

    const FREE_SHIPPING = [
        0 => 'NON FREE',
        1 => 'FREE',
    ];

    public function store()
    {
        return $this->BelongsTo('App\Entities\Master\Customer', 'customer_id');
    }

    public function dokumen(){
        return $this->hasMany('App\Entities\Master\Dokumen','customer_other_address_id');
    }

    public function getImgKtpAttribute()
    {
        if (!$this->image_ktp OR !file_exists(Self::$directory_image.$this->image_ktp)) {
          return img_holder();
        }

        return asset(Self::$directory_image.$this->image_ktp);
    }

    public function getImgNpwpAttribute()
    {
        if (!$this->image_npwp OR !file_exists(Self::$directory_image.$this->image_npwp)) {
          return img_holder();
        }

        return asset(Self::$directory_image.$this->image_npwp);
    }

    public function do(){
        return $this->hasMany('App\Entities\Penjualan\PackingOrder','customer_other_address_id');
    }
    
    public function customer_contact()
    {
        return $this->hasMany('App\Entities\Master\CustomerContact', 'customer_other_address_id');
    }

    public function routeNotificationForWhatsApp()
    {
        return $this->phone;
    }

    public function default()
    {
        return array_search($this->member_default, self::MEMBER_DEFAULT);
    }

    public function condition()
    {
        return array_search($this->situation, self::SITUATION);
    }

    public function key_status()
    {
        return array_search($this->status_key, self::STATUS_KEY);
    }

    public function free_ongkir()
    {
        return array_search($this->free_shipping, self::FREE_SHIPPING);
    }

    public function checkStore()
    {
        $customer = $this->store;

        if (!$customer) {
            return false;
        }

        return $customer->status == Customer::STATUS['ACTIVE'];
    }

    /**
     * Nama kolom sales/AR bisa berbeda antar database (sm/am, asm/ams).
     * Kembalikan kolom yang benar-benar ada agar penulisan tidak salah kolom.
     */
    public static function salesColumnMap()
    {
        $pick = function ($table, array $columns) {
            foreach ($columns as $column) {
                if (Schema::hasColumn($table, $column)) {
                    return $column;
                }
            }

            return null;
        };

        return [
            'customer_sm'      => $pick('master_customers', ['sm', 'am']),
            'customer_asm'     => $pick('master_customers', ['asm', 'ams']),
            'customer_pic'     => $pick('master_customers', ['pic']),
            'member_sm'        => $pick('master_customer_other_addresses', ['sm', 'am']),
            'member_asm'       => $pick('master_customer_other_addresses', ['ams', 'asm']),
            'member_pic'       => $pick('master_customer_other_addresses', ['pic']),
            'member_officer'   => $pick('master_customer_other_addresses', ['officer']),
            'member_ar1'       => $pick('master_customer_other_addresses', ['account_representative']),
            'member_ar2'       => $pick('master_customer_other_addresses', ['account_representative_optional_1']),
            'member_ar3'       => $pick('master_customer_other_addresses', ['account_representative_optional_2']),
        ];
    }
}
