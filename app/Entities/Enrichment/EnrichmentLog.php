<?php

namespace App\Entities\Enrichment;

use App\Entities\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnrichmentLog extends Model
{
    use SoftDeletes;

    protected $table = 'enrichment_logs';

    protected $fillable = [
        'fragrance_reference_id', 'driver', 'model', 'use_search',
        'status', 'http_status', 'error_message', 'raw_response', 'duration_ms',
    ];

    protected $casts = [
        'use_search' => 'boolean',
    ];

    const STATUS = [
        'SUCCESS' => 'success',
        'ERROR' => 'error',
    ];

    public function reference()
    {
        return $this->belongsTo('App\Entities\Enrichment\FragranceReference', 'fragrance_reference_id');
    }
}
