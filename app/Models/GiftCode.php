<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftCode extends Model
{
    use HasFactory;

    protected $table = 'gift';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'item_id',
        'item_quantity',
        'power',
        'list_user',
    ];

    protected $casts = [
        'item_id' => 'array',
        'item_quantity' => 'array',
        'list_user' => 'array',
    ];
}
