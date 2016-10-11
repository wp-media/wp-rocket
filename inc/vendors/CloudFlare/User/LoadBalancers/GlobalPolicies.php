<?php

namespace Cloudflare\User\LoadBalancers;

use Cloudflare\Api;
use Cloudflare\User;

/**
 * CloudFlare API wrapper
 *
 * CTM Global Policy
 * User-level Cloud Traffic Manager Global Policy
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class GlobalPolicies extends Api
{
    /**
     * List global policies
     * List configured global policies
     */
    public function global_policies()
    {
        return $this->get('/user/load_balancers/global_policies');
    }

    /**
     * Create a global policy
     * Create a new global policy
     *
     * @param string      $fallback_pool    ID for a fallback pool to use when all pools are down.
     * @param string      $location_mapping ID of the location map object.
     * @param string|null $description      Object description.
     */
    public function create($fallback_pool, $location_mapping, $description = null)
    {
        $data = [
            'fallback_pool'    => $fallback_pool,
            'location_mapping' => $location_mapping,
            'description'      => $description,
        ];

        return $this->post('/user/load_balancers/global_policies', $data);
    }

    /**
     * Global policy details
     * Fetch a single configured global policy
     *
     * @param string $identifier
     */
    public function details($identifier)
    {
        return $this->get('/user/load_balancers/global_policies/'.$identifier);
    }

    /**
     * Modify a global policy
     * Modify a configured global policy
     *
     * @param string      $identifier
     * @param string|null $fallback_pool    ID for a fallback pool to use when all pools are down.
     * @param string|null $location_mapping ID of the location map object.
     * @param string|null $description      Object description.
     */
    public function update($identifier, $fallback_pool = null, $location_mapping = null, $description = null)
    {
        $data = [
            'fallback_pool'    => $fallback_pool,
            'location_mapping' => $location_mapping,
            'description'      => $description,
        ];

        return $this->patch('/user/load_balancers/global_policies/'.$identifier, $data);
    }

    /**
     * Delete a global policy
     * Delete a configured global policy
     *
     * @param string $identifier
     */
    public function delete_global_policy($identifier)
    {
        return $this->delete('/user/load_balancers/global_policies/'.$identifier);
    }
}
