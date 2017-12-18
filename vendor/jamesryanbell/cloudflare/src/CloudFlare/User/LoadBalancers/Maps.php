<?php

namespace Cloudflare\User\LoadBalancers;

use Cloudflare\Api;
use Cloudflare\User;

/**
 * CloudFlare API wrapper
 *
 * CTM Map
 * User-level Cloud Traffic Manager Map
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class Maps extends Api
{
    /**
     * List maps
     * List configured maps
     */
    public function maps()
    {
        return $this->get('/user/load_balancers/maps');
    }

    /**
     * Create a map
     * Create a new map
     *
     * @param array       $global_pools Sorted list of pool IDs that will be utilized
     *                                  if a CF PoP cannot be assigned to a configured region.
     * @param string|null $description  Object description.
     */
    public function create($global_pools, $description = null)
    {
        $data = [
            'global_pools' => $global_pools,
            'description'  => $description,
        ];

        return $this->post('/user/load_balancers/maps', $data);
    }

    /**
     * Map details
     * Fetch a single configured map
     *
     * @param string $identifier
     */
    public function details($identifier)
    {
        return $this->get('/user/load_balancers/maps/'.$identifier);
    }

    /**
     * Modify a map
     * Modify a configured map
     *
     * @param string      $identifier
     * @param array       $global_pools Sorted list of pool IDs that will be utilized
     *                                  if a CF PoP cannot be assigned to a configured region.
     * @param string|null $description  Object description.
     */
    public function update($identifier, $global_pools = null, $description = null)
    {
        $data = [
            'global_pools' => $global_pools,
            'description'  => $description,
        ];

        return $this->patch('/user/load_balancers/maps/'.$identifier, $data);
    }

    /**
     * Delete a map
     * Delete a configured map
     *
     * @param string $identifier
     */
    public function delete_map($identifier)
    {
        return $this->delete('/user/load_balancers/maps/'.$identifier);
    }
}
