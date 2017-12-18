<?php

namespace Cloudflare\User;

use Cloudflare\Api;

/**
 * CloudFlare API wrapper
 *
 * Organizations
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class Organizations extends Api
{
    /**
     * List organizations (permission needed: #organizations:read)
     * List organizations the user is associated with
     *
     * @param string|null $status    Whether or not the user is a member of the organization or has an inivitation pending
     * @param string|null $name      Organization Name
     * @param int|null    $page      Page number of paginated results
     * @param int|null    $per_page  Number of organizations per page
     * @param string|null $order     Field to order organizations by
     * @param string|null $direction Direction to order organizations
     * @param string|null $match     Whether to match all search requirements or at least one (any)
     */
    public function organizations($status = null, $name = null, $page = null, $per_page = null, $order = null, $direction = null, $match = null)
    {
        $data = [
            'status'    => $status,
            'name'      => $name,
            'page'      => $page,
            'per_page'  => $per_page,
            'order'     => $order,
            'direction' => $direction,
            'match'     => $match,
        ];

        return $this->get('/user/organizations', $data);
    }

    /**
     * Organization details (permission needed: #organizations:read)
     * Get a specific organization the user is associated with
     *
     * @param string $identifier
     */
    public function details($identifier)
    {
        return $this->get('/user/organizations/'.$identifier);
    }

    /**
     * Leave organization (permission needed: #organizations:edit)
     * Remove association to an organization
     *
     * @param string $identifier
     */
    public function leave($identifier)
    {
        return $this->delete('/user/organizations/'.$identifier);
    }
}
