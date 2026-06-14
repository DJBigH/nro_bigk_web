<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $table = 'post';

    protected $primaryKey = 'id_post';

    public $timestamps = false;

    protected $fillable = [
        'code_post',
        'title',
        'description',
        'id', // User id of poster
        'type_post',
        'time_post',
        'up_top',
        'type',
        'category',
    ];

    protected $casts = [
        'time_post' => 'datetime',
    ];
}
