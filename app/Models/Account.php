<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model implements Authenticatable
{
    use HasFactory;

    protected $table = 'account';

    public $timestamps = false; // We don't have standard created_at / updated_at in account

    protected $fillable = [
        'username',
        'password',
        'ban',
        'is_admin',
        'vnd',
        'tongnap',
        'reward',
        'active',
        'coin',
    ];

    protected $hidden = [
        'password',
    ];

    // Authenticatable implementation
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->id;
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    // Disable remember token as it doesn't exist in the database table
    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value)
    {
        // Do nothing
    }

    public function getRememberTokenName()
    {
        return '';
    }

    // Relationships
    public function players()
    {
        return $this->hasMany(Player::class, 'account_id', 'id');
    }

    // Get the first player (useful for inventory manipulation)
    public function getPlayerAttribute()
    {
        return $this->players()->first();
    }
}
