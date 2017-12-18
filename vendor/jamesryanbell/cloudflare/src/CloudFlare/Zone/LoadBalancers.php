<?php

namespace Cloudflare\Zone;

use Cloudflare\Api;

/**
 * CloudFlare API wrapper
 *
 * CTM Load Balancer
 * User-level Cloud Traffic Manager Load Balancer
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class LoadBalancers extends Api
{
    /**
     * List load balancers
     * List configured load balancers
     *
     * @param string $zone_identifier
     */
    public function load_balancers($zone_identifier)
    {
        return $this->get('/zones/'.$zone_identifier.'/load_balancers');
    }

    /**
     * Create a load balancer
     * Create a new load balancer
     *
     * @param string      $zone_identifier
     * @param string      $name            The DNS hostname to associate with your Load Balancer. If this hostname already
     *                                     exists as a DNS record in Cloudflare's DNS, the Load Balancer will take
     *                                     precedence and the DNS record will not be used.
     * @param string      $fallback_pool   The pool ID to use when all other pools are detected as unhealthy.
     * @param array       $default_pools   A list of pool IDs ordered by their failover priority. Pools defined here are
     *                                     used by default, or when region_pools are not configured for a given region.
     * @param string|null $description     Object description.
     * @param int|null    $ttl             Time to live (TTL) of the DNS entry for the IP address returned by this load
     *                                     balancer. This only applies to gray-clouded (unproxied) load balancers.
     * @param object|null $region_pools    A mapping of region/country codes to a list of pool IDs (ordered by their
     *                                     failover priority) for the given region. Any regions not explicitly defined
     *                                     will fall back to using default_pools.
     * @param int|null    $pop_pools       (Enterprise only): A mapping of Cloudflare PoP identifiers to a list of pool IDs
     *                                     (ordered by their failover priority) for the PoP (datacenter). Any PoPs not
     *                                     explicitly defined will fall back to using default_pools.
     *                                     balancer. This only applies to gray-clouded (unproxied) load balancers.
     * @param bool|null   $proxied         Whether the hostname should be gray clouded (false) or orange clouded (true).
     */
    public function create($zone_identifier, $name, $fallback_pool, $default_pools, $description = null, $ttl = null, $region_pools = null, $pop_pools = null, $proxied = null)
    {
        $data = [
            'name'          => $name,
            'fallback_pool' => $fallback_pool,
            'default_pools' => $default_pools,
            'description'   => $description,
            'ttl'           => $ttl,
            'region_pools'  => $region_pools,
            'pop_pools'     => $pop_pools,
            'proxied'       => $proxied,
        ];

        return $this->post('/zones/'.$zone_identifier.'/load_balancers', $data);
    }

    /**
     * Load balancer details
     * Fetch a single configured load balancer
     *
     * @param string $zone_identifier
     * @param string $identifier
     */
    public function details($zone_identifier, $identifier)
    {
        return $this->get('/zones/'.$zone_identifier.'/load_balancers/'.$identifier);
    }

    /**
     * Modify a load balancer
     * Modify a configured load balancer
     *
     * @param string      $zone_identifier
     * @param string      $identifier
     * @param string      $name            The DNS hostname to associate with your Load Balancer. If this hostname already
     *                                     exists as a DNS record in Cloudflare's DNS, the Load Balancer will take
     *                                     precedence and the DNS record will not be used.
     * @param string      $fallback_pool   The pool ID to use when all other pools are detected as unhealthy.
     * @param array       $default_pools   A list of pool IDs ordered by their failover priority. Pools defined here are
     *                                     used by default, or when region_pools are not configured for a given region.
     * @param string|null $description     Object description.
     * @param int|null    $ttl             Time to live (TTL) of the DNS entry for the IP address returned by this load
     *                                     balancer. This only applies to gray-clouded (unproxied) load balancers.
     * @param object|null $region_pools    A mapping of region/country codes to a list of pool IDs (ordered by their
     *                                     failover priority) for the given region. Any regions not explicitly defined
     *                                     will fall back to using default_pools.
     * @param int|null    $pop_pools       (Enterprise only): A mapping of Cloudflare PoP identifiers to a list of pool IDs
     *                                     (ordered by their failover priority) for the PoP (datacenter). Any PoPs not
     *                                     explicitly defined will fall back to using default_pools.
     *                                     balancer. This only applies to gray-clouded (unproxied) load balancers.
     * @param bool|null   $proxied         Whether the hostname should be gray clouded (false) or orange clouded (true).
     */
    public function update($zone_identifier, $identifier, $name, $fallback_pool, $default_pools, $description = null, $ttl = null, $region_pools = null, $pop_pools = null, $proxied = null)
    {
        $data = [
            'name'          => $name,
            'fallback_pool' => $fallback_pool,
            'default_pools' => $default_pools,
            'description'   => $description,
            'ttl'           => $ttl,
            'region_pools'  => $region_pools,
            'pop_pools'     => $pop_pools,
            'proxied'       => $proxied,
        ];

        return $this->patch('/zones/'.$zone_identifier.'/load_balancers/'.$identifier, $data);
    }

    /**
     * Delete a load balancer
     * Delete a configured load balancer
     *
     * @param string $zone_identifier
     * @param string $identifier
     */
    public function delete_load_balancer($zone_identifier, $identifier)
    {
        return $this->delete('/zones/'.$zone_identifier.'/load_balancers/'.$identifier);
    }
}
