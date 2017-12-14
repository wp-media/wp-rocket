<?php

namespace Cloudflare\Organizations;

use Cloudflare\Api;

/**
 * CloudFlare API wrapper
 *
 * Organization Invites
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class Invites extends Api
{
    /**
     * Create invitation (permission needed: #organization:read)
     * Invite a User to become a Member of an Organization
     *
     * @param string $organization_identifier
     * @param string $invited_member_email    Email address of the user to be added to the Organization
     * @param array  $roles                   Array of Roles associated with the invited user
     */
    public function create($organization_identifier, $invited_member_email, array $roles)
    {
        $data = [
            'invited_member_email' => $invited_member_email,
            'roles'                => $roles,
        ];

        return $this->post('/organizations/'.$organization_identifier.'/members', $data);
    }

    /**
     * List invitations (permission needed: #organization:read)
     * List all invitations associated with an organization
     *
     * @param string $organization_identifier
     */
    public function invitations($organization_identifier)
    {
        return $this->get('/organizations/'.$organization_identifier.'/invites');
    }

    /**
     * Invitation details (permission needed: #organization:read)
     * Get the details of an invitation
     *
     * @param string $organization_identifier
     * @param string $identifier
     */
    public function details($organization_identifier, $identifier)
    {
        return $this->get('/organizations/'.$organization_identifier.'/invites/'.$identifier);
    }

    /**
     * Update invitation roles (permission needed: #organization:edit)
     * Change the Roles of a Pending Invite
     *
     * @param string     $organization_identifier
     * @param string     $identifier
     * @param array|null $roles                   Array of Roles associated with the invited user
     */
    public function update($organization_identifier, $identifier, array $roles = null)
    {
        $data = [
            'roles' => $roles,
        ];

        return $this->patch('/organizations/'.$organization_identifier.'/invites/'.$identifier, $data);
    }

    /**
     * Cancel Invitation (permission needed: #organization:edit)
     * Cancel an existing invitation
     *
     * @param string $organization_identifier
     * @param string $identifier
     */
    public function delete_invitation($organization_identifier, $identifier)
    {
        return $this->delete('/organizations/'.$organization_identifier.'/invites/'.$identifier);
    }
}
