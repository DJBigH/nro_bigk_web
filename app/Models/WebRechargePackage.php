<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebRechargePackage extends Model
{
    use HasFactory;

    protected $table = 'web_recharge_packages';

    protected $fillable = [
        'name',
        'amount_vnd',
        'bonus_vnd',
        'is_popular',
        'is_active',
    ];
}
