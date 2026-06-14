<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebAccSale extends Model
{
    use HasFactory;

    protected $table = 'web_acc_sales';

    protected $fillable = [
        'seller_id',
        'buyer_id',
        'username_sold',
        'password_sold',
        'price',
        'sale_price',
        'description',
        'images',
        'status',
        'payout_status',
        'cancel_reason',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    /**
     * Get the seller of the account.
     */
    public function seller()
    {
        return $this->belongsTo(Account::class, 'seller_id', 'id');
    }

    /**
     * Get the buyer of the account.
     */
    public function buyer()
    {
        return $this->belongsTo(Account::class, 'buyer_id', 'id');
    }

    /**
     * Get the final display price (returns sale_price if configured, otherwise normal price).
     */
    public function getDisplayPriceAttribute()
    {
        return $this->sale_price !== null ? $this->sale_price : $this->price;
    }
}
