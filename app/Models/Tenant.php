<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $fillable = [
        'id',
        'name',
        'owner_email',
        'data',
    ];

    protected $casts = [
        'data' => 'json',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'owner_email',
        ];
    }

    /**
     * Get the domains associated with the tenant
     */
    public function domains()
    {
        return $this->hasMany(\Stancl\Tenancy\Database\Models\Domain::class, 'tenant_id');
    }

    /**
     * Get the template connection name for this tenant.
     *
     * Overrides the trait method to ensure we always have a valid connection name.
     * Falls back to the template connection if the tenant's db_connection is empty.
     */
    public function getTemplateConnectionName(): string
    {
        $connection = $this->getInternal('db_connection');

        // If db_connection is empty or null, use template connection
        if (empty($connection)) {
            return config('tenancy.database.template_tenant_connection', 'tenant');
        }

        return $connection;
    }
}
