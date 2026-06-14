<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpinWheelHistory extends Model
{
    use HasFactory;

    protected $table = 'spin_wheel_history';

    public $timestamps = false;

    protected $fillable = [
        'account_id',
        'spin_item_id',
        'cost_vnd',
        'spun_at',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }

    public function spinItem()
    {
        return $this->belongsTo(SpinWheelItem::class, 'spin_item_id', 'id');
    }
}
