<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DownloadFile extends Model
{
    use HasFactory;

    protected $table = 'download_files';

    protected $fillable = [
        'name',
        'version',
        'url',
        'size',
        'is_active',
    ];
}
