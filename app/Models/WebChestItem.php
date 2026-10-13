<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WebChestItem extends Model
{
    use HasFactory;

    protected $table = 'web_chest';

    protected $fillable = [
        'account_id',
        'item_template_id',
        'quantity',
        'options',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

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
