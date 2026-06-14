<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WebShopItem extends Model
{
    use HasFactory;

    protected $table = 'web_shop_items';

    protected $fillable = [
        'item_template_id',
        'price_vnd',
        'quantity',
        'is_active',
        'image_override',
    ];

    // Get the item template details from the game DB (item_template)
    public function getTemplateAttribute()
    {
        return DB::table('item_template')->where('id', $this->item_template_id)->first();
    }
}
