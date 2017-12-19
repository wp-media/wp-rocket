<?php

namespace Cloudflare\User;

use Cloudflare\Api;

/**
 * CloudFlare API wrapper
 *
 * Invites
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class Invites extends Api
{
    /**
     * List invitations (permission needed: #invites:read)
     * List all invitations associated with my user
     */
    public function invites()
    {
        return $this->get('/user/invites');
    }

    /**
     * Invitation details (permission needed: #invites:read)
     * Get the details of an invitation
     *
     * @param string $identifier
     */
    public function details($identifier)
    {
        return $this->get('/user/invites/'.$identifier);
    }

    /**
     * Respond to Invitation (permission needed: #invites:edit)
     * Respond to an invitation
     *
     * @param string $identifier
     * @param string $status     Status of your response to the invitation (rejected or accepted)
     */
    public function respond($identifier, $status)
    {
        $data = [
            'status' => $status,
        ];

        return $this->patch('/user/invites/'.$identifier, $data);
    }
}
