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
        'options',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    protected static $templateCache = [];

    // Get the item template details from the game DB (item_template) with request-level caching
    public function getTemplateAttribute()
    {
        if (!array_key_exists($this->item_template_id, self::$templateCache)) {
            self::$templateCache[$this->item_template_id] = DB::table('item_template')->where('id', $this->item_template_id)->first();
        }
        return self::$templateCache[$this->item_template_id];
    }
}
