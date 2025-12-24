<?php

namespace App\Session;

use Illuminate\Session\DatabaseSessionHandler;

class CentralDatabaseSessionHandler extends DatabaseSessionHandler
{
    /**
     * Override to force central connection for all session operations
     */
    protected function getConnection()
    {
        return $this->connection ?? \DB::connection('central');
    }

    /**
     * Override userId to return null to prevent user lookup queries
     * This prevents database queries when session is being saved,
     * which would fail if using a tenant database connection
     */
    protected function userId()
    {
        // Return null - don't store user_id in session payload
        // This prevents querying the users table during session save
        return null;
    }
}

