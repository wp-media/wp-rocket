<?php

namespace Cloudflare\Organizations;

use Cloudflare\Api;

/**
 * CloudFlare API wrapper
 *
 * Organization Members
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class Members extends Api
{
    /**
     * List members (permission needed: #organization:read)
     * List all members of a organization
     *
     * @param string $organization_identifier
     */
    public function members($organization_identifier)
    {
        return $this->get('/organizations/'.$organization_identifier.'/members');
    }

    /**
     * Member details (permission needed: #organization:read)
     * Get information about a specific member of an organization
     *
     * @param string $organization_identifier
     * @param string $identifier
     */
    public function details($organization_identifier, $identifier)
    {
        return $this->get('/organizations/'.$organization_identifier.'/members/'.$identifier);
    }

    /**
     * Update member roles (permission needed: #organization:edit)
     * Change the Roles of an Organization's Member
     *
     * @param string     $organization_identifier
     * @param string     $identifier
     * @param array|null $roles                   Array of Roles associated with this Member
     */
    public function update($organization_identifier, $identifier, array $roles = null)
    {
        $data = [
            'roles' => $roles,
        ];

        return $this->patch('/organizations/'.$organization_identifier.'/members/'.$identifier, $data);
    }

    /**
     * Remove member (permission needed: #organization:edit)
     * Remove a member from an organization
     *
     * @param string $organization_identifier
     * @param string $identifier
     */
    public function delete_member($organization_identifier, $identifier)
    {
        return $this->delete('/organizations/'.$organization_identifier.'/members/'.$identifier);
    }
}
