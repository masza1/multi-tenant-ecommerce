<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;

class TenantAwareEloquentUserProvider extends EloquentUserProvider
{
    // Custom provider yang memastikan queries menggunakan connection yang benar
    // Sekarang default connection sudah di-set di InitializeTenancyByDomain middleware
    // Jadi kita bisa gunakan parent's implementation
    
    /**
     * Retrieve a user by their unique identifier.
     * Now uses the correct database connection set by middleware.
     */
    public function retrieveById($id)
    {
        return parent::retrieveById($id);
    }

    /**
     * Retrieve a user by their credentials.
     * Now uses the correct database connection set by middleware.
     */
    public function retrieveByCredentials(array $credentials)
    {
        return parent::retrieveByCredentials($credentials);
    }
}




