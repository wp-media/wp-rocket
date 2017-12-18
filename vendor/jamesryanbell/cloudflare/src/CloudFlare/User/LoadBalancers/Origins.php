<?php

namespace Cloudflare\User\LoadBalancers;

use Cloudflare\Api;
use Cloudflare\User;

/**
 * CloudFlare API wrapper
 *
 * CTM Origin
 * User-level Cloud Traffic Manager Origin
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class Origins extends Api
{
    /**
     * List origins
     * List configured origins for a user
     */
    public function origins()
    {
        return $this->get('/user/load_balancers/origins');
    }

    /**
     * Create a origin
     * Create a new origin
     *
     * @param string      $name     Object name
     * @param string      $address  Origin server IPv4 or IPv6 address
     * @param bool|null   $enabled  Whether this origin is enabled or not
     * @param string|null $notifier The ID of the notifier object to use for
     *                              notifications relating to the health status of this origin.
     */
    public function create($name, $address, $enabled = null, $notifier = null)
    {
        $data = [
            'name'     => $name,
            'address'  => $address,
            'enabled'  => $enabled,
            'notifier' => $notifier,
        ];

        return $this->post('/user/load_balancers/origins', $data);
    }

    /**
     * Origin details
     * Fetch a single configured CTM origin for a user
     *
     * @param string $identifier
     */
    public function details($identifier)
    {
        return $this->get('/user/load_balancers/origins/'.$identifier);
    }

    /**
     * Modify an origin
     * Modify a configured origin
     *
     * @param string      $identifier
     * @param string|null $name       Object name
     * @param string|null $address    Origin server IPv4 or IPv6 address
     * @param bool|null   $enabled    Whether this origin is enabled or not
     * @param string|null $notifier   The ID of the notifier object to use for
     *                                notifications relating to the health status of this origin.
     */
    public function update($identifier, $name = null, $address = null, $enabled = null, $notifier = null)
    {
        $data = [
            'name'     => $name,
            'address'  => $address,
            'enabled'  => $enabled,
            'notifier' => $notifier,
        ];

        return $this->patch('/user/load_balancers/origins/'.$identifier, $data);
    }

    /**
     * Delete an origin
     * Delete a configured origin
     *
     * @param string $identifier
     */
    public function delete_origin($identifier)
    {
        return $this->delete('/user/load_balancers/origins/'.$identifier);
    }
}
