<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentInformation extends Model
{
    protected $fillable = [
        'user_id',
        'bank',
        'account_number',
        'account_holder',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
