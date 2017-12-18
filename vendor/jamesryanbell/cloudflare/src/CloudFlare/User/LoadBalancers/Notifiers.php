<?php

namespace Cloudflare\User\LoadBalancers;

use Cloudflare\Api;
use Cloudflare\User;

/**
 * CloudFlare API wrapper
 *
 * CTM Notifiers
 * User-level Cloud Traffic Manager Notifier
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class Notifiers extends Api
{
    /**
     * List notifiers
     * List configured notifiers for a user
     */
    public function notifiers()
    {
        return $this->get('/user/load_balancers/notifiers');
    }

    /**
     * Create a notifier
     * Create a configured notifier
     *
     * @param string      $address Notifier address
     * @param string|null $type    Notifier type
     */
    public function create($address, $type = null)
    {
        $data = [
            'address' => $address,
            'type'    => $type,
        ];

        return $this->post('/user/load_balancers/notifiers', $data);
    }

    /**
     * Notifier details
     * Fetch a single configured CTM notifier for a user
     *
     * @param string $identifier
     */
    public function details($identifier)
    {
        return $this->get('/user/load_balancers/notifiers/'.$identifier);
    }

    /**
     * Modify a notifier
     * Modify a configured notifier
     *
     * @param string      $identifier
     * @param string|null $address    Notifier address
     * @param string|null $type       Notifier type
     */
    public function update($identifier, $address = null, $type = null)
    {
        $data = [
            'address' => $address,
            'type'    => $type,
        ];

        return $this->patch('/user/load_balancers/notifiers/'.$identifier, $data);
    }

    /**
     * Delete a notifier
     * Delete a configured notifier
     *
     * @param string $identifier
     */
    public function delete_notifier($identifier)
    {
        return $this->delete('/user/load_balancers/notifiers/'.$identifier);
    }
}
