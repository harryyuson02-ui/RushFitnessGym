<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GcashPayment extends Model
{
    protected $fillable = [
        'transaction_id', 'reference', 'receipt_path', 'verification_status',
        'verified_by', 'verified_at', 'verification_notes',
    ];

    protected function casts(): array
    {
        return ['verified_at' => 'datetime'];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
