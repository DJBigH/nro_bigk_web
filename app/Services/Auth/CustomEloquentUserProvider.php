<?php

namespace App\Services\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class CustomEloquentUserProvider extends EloquentUserProvider
{
    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        if (is_null($plain = $credentials['password'])) {
            return false;
        }

        $hash = $user->getAuthPassword();

        // 1. Check plain text (Primary check for NRO game compatibility)
        if ($plain === $hash) {
            return true;
        }

        // 2. Fallback: Check Bcrypt
        if ($this->hasher->check($plain, $hash)) {
            return true;
        }

        // 3. Fallback: Check MD5
        if (md5($plain) === $hash) {
            return true;
        }

        return false;
    }
}
