<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $table = 'payment_transactions';

    protected $fillable = [
        'account_id',
        'order_id',
        'gateway',
        'transaction_code',
        'amount',
        'status',
        'response_data',
        'payment_time',
    ];

    protected $casts = [
        'payment_time' => 'datetime',
        'response_data' => 'array',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }
}
