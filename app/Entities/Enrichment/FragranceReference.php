<?php

namespace App\Entities\Enrichment;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FragranceReference extends Model
{
    use SoftDeletes;

    protected $table = 'fragrance_references';

    protected $fillable = [
        'ref_key', 'brand', 'searah',
        'match_status', 'matched_name', 'matched_brand', 'concentration', 'match_note',
        'release_year', 'release_year_confidence', 'perfumers',
        'gender_target', 'scent_family', 'main_accords', 'notes_top', 'notes_middle', 'notes_base',
        'tags_mood', 'tags_season', 'tags_time_of_day', 'description_id',
        'confidence', 'sources', 'reference_notes_status', 'notes_differences',
        'needs_review', 'review_reasons', 'status', 'verified_at', 'verified_by',
    ];

    protected $casts = [
        'perfumers' => 'array',
        'main_accords' => 'array',
        'notes_top' => 'array',
        'notes_middle' => 'array',
        'notes_base' => 'array',
        'tags_mood' => 'array',
        'tags_season' => 'array',
        'tags_time_of_day' => 'array',
        'sources' => 'array',
        'notes_differences' => 'array',
        'review_reasons' => 'array',
        'needs_review' => 'boolean',
        'verified_at' => 'datetime',
    ];

    const STATUS = [
        'PENDING' => 0,
        'VERIFIED' => 1,
        'REJECTED' => 2,
    ];

    public function notes()
    {
        return $this->belongsToMany('App\Entities\Enrichment\FragranceNote', 'fragrance_reference_notes', 'fragrance_reference_id', 'fragrance_note_id')
                    ->withPivot('position');
    }

    public function accords()
    {
        return $this->belongsToMany('App\Entities\Enrichment\FragranceAccord', 'fragrance_reference_accords', 'fragrance_reference_id', 'fragrance_accord_id')
                    ->withPivot('prominence');
    }

    public function products()
    {
        return $this->belongsToMany('App\Entities\Master\Product', 'product_reference_map', 'fragrance_reference_id', 'product_id');
    }

    public function logs()
    {
        return $this->hasMany('App\Entities\Enrichment\EnrichmentLog');
    }
}
