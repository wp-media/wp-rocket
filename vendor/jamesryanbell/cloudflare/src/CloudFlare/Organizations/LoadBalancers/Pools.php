<?php

namespace Cloudflare\Organizations\LoadBalancers;

use Cloudflare\Api;
use Cloudflare\Organizations;

/**
 * CloudFlare API wrapper
 *
 * CTM Pool
 * User-level Cloud Traffic Manager Pool
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class Pools extends Api
{
    /**
     * List pools
     * List configured pools
     *
     * @param string $organization_identifier
     */
    public function pools($organization_identifier)
    {
        return $this->get('/organizations/'.$organization_identifier.'/load_balancers/pools');
    }

    /**
     * Create a pool
     * Create a new pool
     *
     * @param string      $organization_identifier
     * @param string      $name                    Object name
     * @param array       $origins                 A list of origins contained in the pool.
     *                                             Traffic destined to the pool is balanced across all
     *                                             available origins contained in the pool (as long as the pool
     *                                             is considered available).
     * @param string|null $description             Object description
     * @param bool|null   $enabled                 Whether this pool is enabled or not.
     * @param string|null $monitor                 ID of the monitor object to use for monitoring the health
     *                                             status of origins inside this pool.
     * @param string|null $notification_email      ID of the notifier object to use for notifications relating
     *                                             to the health status of origins inside this pool.
     */
    public function create($organization_identifier, $name, $origins, $description = null, $enabled = null, $monitor = null, $notification_email = null)
    {
        $data = [
            'name'               => $name,
            'origins'            => $origins,
            'description'        => $description,
            'enabled'            => $enabled,
            'monitor'            => $monitor,
            'notification_email' => $notification_email,
        ];

        return $this->post('/organizations/'.$organization_identifier.'/load_balancers/pools', $data);
    }

    /**
     * Pool details
     * Fetch a single configured pool
     *
     * @param string $organization_identifier
     * @param string $identifier
     */
    public function details($organization_identifier, $identifier)
    {
        return $this->get('/organizations/'.$organization_identifier.'/load_balancers/pools/'.$identifier);
    }

    /**
     * Modify a pool
     * Modify a configured pool
     *
     * @param string      $organization_identifier
     * @param string      $identifier
     * @param string      $name                    Object name
     * @param array       $origins                 A list of origins contained in the pool.
     *                                             Traffic destined to the pool is balanced across all
     *                                             available origins contained in the pool (as long as the pool
     *                                             is considered available).
     * @param string|null $description             Object description
     * @param bool|null   $enabled                 Whether this pool is enabled or not.
     * @param string|null $monitor                 ID of the monitor object to use for monitoring the health
     *                                             status of origins inside this pool.
     * @param string|null $notification_email      ID of the notifier object to use for notifications relating
     *                                             to the health status of origins inside this pool.
     */
    public function update($organization_identifier, $identifier, $name, $origins, $description = null, $enabled = null, $monitor = null, $notification_email = null)
    {
        $data = [
            'name'               => $name,
            'origins'            => $origins,
            'description'        => $description,
            'enabled'            => $enabled,
            'monitor'            => $monitor,
            'notification_email' => $notification_email,
        ];

        return $this->patch('/organizations/'.$organization_identifier.'/load_balancers/pools/'.$identifier, $data);
    }

    /**
     * Delete a pool
     * Delete a configured pool
     *
     * @param string $identifier
     */
    public function delete_pool($organization_identifier, $identifier)
    {
        return $this->delete('/organizations/'.$organization_identifier.'/load_balancers/pools/'.$identifier);
    }
}
