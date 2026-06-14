<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SpinWheelItem extends Model
{
    use HasFactory;

    protected $table = 'spin_wheel_items';

    protected $fillable = [
        'item_template_id',
        'display_name',
        'image',
        'reward_type',
        'reward_value',
        'ratio',
        'color',
        'is_active',
        'sort_order',
    ];

    // Get the item template details from the game DB (item_template)
    public function getTemplateAttribute()
    {
        if (!$this->item_template_id) {
            return null;
        }
        return DB::table('item_template')->where('id', $this->item_template_id)->first();
    }
}
