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
     * @param string      $name            A hostname of the record that should provide load balancing capabilities.
     *                                     If this name already exists as a DNS record in your CloudFlare DNS,
     *                                     the existing record will take precedence over the Load Balancer.
     * @param string      $global_policy   ID of the Global Policy object.
     * @param string|null $description     Object description.
     * @param int|null    $ttl             Time to live (TTL) of the DNS entry for the IP address returned
     *                                     by this load balancer.
     * @param bool|null   $proxied         Whether the hostname should be grey clouded (False) or orange clouded (True).
     */
    public function create($zone_identifier, $name, $global_policy, $description = null, $ttl = null, $proxied = null)
    {
        $data = [
            'name'          => $name,
            'global_policy' => $global_policy,
            'description'   => $description,
            'ttl'           => $ttl,
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
    public function details($identifier)
    {
        return $this->get('/zones/'.$zone_identifier.'/load_balancers/'.$identifier);
    }

    /**
     * Modify a load balancer
     * Modify a configured load balancer
     *
     * @param string      $zone_identifier
     * @param string      $identifier
     * @param string|null $name            A hostname of the record that should provide load balancing capabilities.
     *                                     If this name already exists as a DNS record in your CloudFlare DNS,
     *                                     the existing record will take precedence over the Load Balancer.
     * @param string|null $global_policy   ID of the Global Policy object.
     * @param string|null $description     Object description.
     * @param int|null    $ttl             Time to live (TTL) of the DNS entry for the IP address returned
     *                                     by this load balancer.
     * @param bool|null   $proxied         Whether the hostname should be grey clouded (False) or orange clouded (True).
     */
    public function update($zone_identifier, $identifier, $name = null, $global_policy = null, $description = null, $ttl = null, $proxied = null)
    {
        $data = [
            'name'          => $name,
            'global_policy' => $global_policy,
            'description'   => $description,
            'ttl'           => $ttl,
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
