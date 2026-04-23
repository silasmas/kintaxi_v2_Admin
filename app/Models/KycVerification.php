<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KycVerification extends Model
{
    public $timestamps = true;

    protected $table = 'kyc_verifications';

    protected $fillable = [
        'user_id',
        'job_id',
        'product_type',
        'document_type',
        'country_code',
        'status',
        'smile_result_json',
        'callback_payload_json',
        'submitted_at',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
