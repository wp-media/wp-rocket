<?php

namespace Cloudflare\User\LoadBalancers;

use Cloudflare\Api;
use Cloudflare\User;

/**
 * CloudFlare API wrapper
 *
 * CTM Monitors
 * Cloud Traffic Manager Monitor
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class Monitors extends Api
{
    /**
     * List monitors
     * List configured monitors for a user
     */
    public function monitors()
    {
        return $this->get('/user/load_balancers/monitors');
    }

    /**
     * Create a monitor
     * Create a configured monitor
     *
     * @param string      $expected       A case-insensitive substring to match in the body of the probe
     *                                    response to declare an origin as up
     * @param string      $type           Monitor type
     * @param string      $expected_codes The expected HTTP response code or code range for the probe
     * @param string|null $method         The HTTP method for the probe
     * @param string|null $path           The endpoint path to probe
     * @param int|null    $interval       The interval in seconds for each PoP to send a probe request
     * @param int|null    $retries        The number of retries before declaring the origins to be dead
     * @param array|null  $headers        The HTTP headers to use in the probe
     * @param int|null    $probe_timeout  Timeout in seconds for each probe request
     * @param string|null $description    Object description
     */
    public function create($expected, $type, $expected_codes, $method = null, $path = null, $interval = null, $retries = null, $headers = null, $probe_timeout = null, $description = null)
    {
        $data = [
            'expected'       => $expected,
            'type'           => $type,
            'expected_codes' => $expected_codes,
            'method'         => $method,
            'path'           => $path,
            'interval'       => $interval,
            'retries'        => $retries,
            'headers'        => $headers,
            'probe_timeout'  => $probe_timeout,
            'description'    => $description,
        ];

        return $this->post('/user/load_balancers/monitors', $data);
    }

    /**
     * Monitor details
     * List a single configured CTM monitor for a user
     *
     * @param string $identifier
     */
    public function details($identifier)
    {
        return $this->get('/user/load_balancers/monitors/'.$identifier);
    }

    /**
     * Modify a monitor
     * Modify a configured monitor
     *
     * @param string      $identifier
     * @param string|null $expected       A case-insensitive substring to match in the body of the probe
     *                                    response to declare an origin as up
     * @param string|null $type           Monitor type
     * @param string|null $expected_codes The expected HTTP response code or code range for the probe
     * @param string|null $method         The HTTP method for the probe
     * @param string|null $path           The endpoint path to probe
     * @param int|null    $interval       The interval in seconds for each PoP to send a probe request
     * @param int|null    $retries        The number of retries before declaring the origins to be dead
     * @param array|null  $headers        The HTTP headers to use in the probe
     * @param int|null    $probe_timeout  Timeout in seconds for each probe request
     * @param string|null $description    Object description
     */
    public function update($identifier, $expected = null, $type = null, $expected_codes = null, $method = null, $path = null, $interval = null, $retries = null, $headers = null, $probe_timeout = null, $description = null)
    {
        $data = [
            'expected'       => $expected,
            'type'           => $type,
            'expected_codes' => $expected_codes,
            'method'         => $method,
            'path'           => $path,
            'interval'       => $interval,
            'retries'        => $retries,
            'headers'        => $headers,
            'probe_timeout'  => $probe_timeout,
            'description'    => $description,
        ];

        return $this->patch('/user/load_balancers/monitors/'.$identifier, $data);
    }

    /**
     * Delete a monitor
     * Delete a configured monitor
     *
     * @param string $identifier
     */
    public function delete_monitor($identifier)
    {
        return $this->delete('/user/load_balancers/monitors/'.$identifier);
    }
}
