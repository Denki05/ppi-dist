<?php

namespace App\Entities\Master;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Entities\Master\CustomerOtherAddress;

class Customer extends Model
{
    use SoftDeletes;
    
    protected $appends = ['img_ktp', 'img_npwp', 'img_store'];
    protected $fillable = [
        'category_id', 'count_member', 'pic', 'code', 'name',
        'email', 'phone', 'npwp', 'ktp', 'has_ppn', 'has_tempo', 'tempo_limit', 'address',
        'owner_name', 'plafon_piutang', 'saldo', 'gps_latitude', 'gps_longitude', 'zone',
        'provinsi', 'kota', 'kecamatan', 'kelurahan',
        'text_provinsi', 'text_kota', 'text_kecamatan', 'text_kelurahan',
        'zipcode', 'image_npwp', 'image_ktp', 'image_store', 'notification_email', 'status', 'existence'
    ];
    protected $table = 'master_customers';
    public static $directory_image = 'superuser_assets/media/master/customer/';

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1,
        'INACTIVE' => 2,
    ];

    const EXISTENCE = [
        'DISABLED' => 0,
        'ENABLE' => 1,
    ];

    const PIC = [
        'KANTOR' => 1,
        'IVAN' => 2,
        'NIA' => 3,
    ];

    const HAS_TEMPO = [
        'NO' => 0,
        'YES' => 1
    ];

    public function category()
    {
        return $this->BelongsTo('App\Entities\Master\CustomerCategory');
    }

    public function types()
    {
        // return $this->BelongsTo('App\Entities\Master\CustomerType');
        return $this->belongsToMany('App\Entities\Master\CustomerType', 'master_customer_type_pivot', 'customer_id', 'type_id')->withPivot('id');
    }

    public function member()
    {
        return $this->hasMany('App\Entities\Master\CustomerOtherAddress', 'customer_id');
    }

    public function contacts()
    {
        return $this->belongsToMany('App\Entities\Master\Contact', 'master_customer_contacts', 'customer_id', 'contact_id')->withPivot('id');
    }

    // public function getImgStoreAttribute()
    // {
    //     if (!$this->image_store OR !file_exists(Self::$directory_image.$this->image_store)) {
    //       return img_holder();
    //     }

    //     return asset(Self::$directory_image.$this->image_store);
    // }

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

    public function getImgStoreAttribute()
    {
        if (!$this->image_store OR !file_exists(Self::$directory_image.$this->image_store)) {
          return img_holder();
        }

        return asset(Self::$directory_image.$this->image_store);
    }

    public function do(){
        return $this->hasMany('App\Entities\Penjualan\PackingOrder','customer_id');
    }

    public function customer_saldo()
    {
        return $this->hasMany('App\Entities\Master\CustomerSaldoLog');
    }

    public function document()
    {
        return $this->hasMany(Dokumen::class);
    }

   public function sales_order()
   {
    return $this->hasMany('App\Entities\Penjualan\SalesOrder', 'customer_id');
   }

   public function has_tempo()
    {
        return array_search($this->has_tempo, self::HAS_TEMPO);
    }

    public function existence()
    {
        return array_search($this->existence, self::EXISTENCE);
    }

    public function member_count()
    {
        $data = [];

        // dd($this->id);
        $get_member = CustomerOtherAddress::where('customer_id', $this->id)->get();

        foreach ($get_member as $item) {
            // dd($item->name);
            $data[] = [
                'member_id' => $item->id,
                'member_name' => $item->name,
                'member_city' => $item->text_kota,
                'member_default' => $item->default(),
                'member_latitude' => $item->gps_latitude,
                'member_longtitude' => $item->gps_longitude,
                'member_condition' => $item->condition(),
                'member_status_key' => $item->key_status(),
            ];
        }

        return $data;
    }

    public function pic()
    {
        return array_search($this->pic, self::PIC);
    }
}
