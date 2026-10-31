<?php

namespace App\Entities\Enrichment;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FragranceNote extends Model
{
    use SoftDeletes;

    protected $table = 'fragrance_notes';

    protected $fillable = ['slug', 'name', 'status'];

    const STATUS = [
        'DELETED' => 0,
        'ACTIVE' => 1,
        'INACTIVE' => 2
    ];

    public function references()
    {
        return $this->belongsToMany('App\Entities\Enrichment\FragranceReference', 'fragrance_reference_notes', 'fragrance_note_id', 'fragrance_reference_id')
                    ->withPivot('position');
    }
}
