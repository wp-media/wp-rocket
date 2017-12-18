<?php

namespace Cloudflare\Organizations;

use Cloudflare\Api;

/**
 * CloudFlare API wrapper
 *
 * Organization Roles
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class Roles extends Api
{
    /**
     * List roles (permission needed: #organization:read)
     * Get all available roles for an organization
     *
     * @param string $organization_identifier
     */
    public function roles($organization_identifier)
    {
        return $this->get('/organizations/'.$organization_identifier.'/roles');
    }

    /**
     * Role details (permission needed: #organization:read)
     * Get information about a specific role for an organization
     *
     * @param string $organization_identifier
     * @param string $identifier
     */
    public function details($organization_identifier, $identifier)
    {
        return $this->get('/organizations/'.$organization_identifier.'/roles/'.$identifier);
    }
}
