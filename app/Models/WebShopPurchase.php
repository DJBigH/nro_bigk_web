<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WebShopPurchase extends Model
{
    use HasFactory;

    protected $table = 'web_shop_purchases';

    protected $fillable = [
        'account_id',
        'item_template_id',
        'price_vnd',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }

    public function getTemplateAttribute()
    {
        return DB::table('item_template')->where('id', $this->item_template_id)->first();
    }
}
