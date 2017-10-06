<?php

namespace Cloudflare\Organizations\LoadBalancers;

use Cloudflare\Api;
use Cloudflare\Organizations;

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
     *
     * @param string $organization_identifier
     */
    public function monitors($organization_identifier)
    {
        return $this->get('/organizations/'.$organization_identifier.'/load_balancers/monitors');
    }

    /**
     * Create a monitor
     * Create a configured monitor
     *
     * @param string      $organization_identifier
     * @param string      $expected_body           A case-insensitive substring to match in the body of the probe
     *                                             response to declare an origin as up
     * @param string      $expected_codes          The expected HTTP response code or code range for the probe
     * @param string|null $method                  The HTTP method to use for the health check.
     * @param int|null    $timeout                 The timeout (in seconds) before marking the health check as failed
     * @param string|null $path                    The endpoint path to health check against.
     * @param int|null    $interval                The interval between each health check. Shorter intervals may improve failover
     *                                             time, but will increase load on the origins as we check from multiple locations.
     * @param int|null    $retries                 The number of retries to attempt in case of a timeout before marking the origin
     *                                             as unhealthy. Retries are attempted immediately.
     * @param array|null  $header                  The HTTP request headers to send in the health check. It is recommended you set
     *                                             a Host header by default. The User-Agent header cannot be overridden.
     * @param int|null    $type                    The protocol to use for the healthcheck. Currently supported protocols are
     *                                             'HTTP' and 'HTTPS'.
     * @param string|null $description             Object description
     */
    public function create($organization_identifier, $expected_body, $expected_codes, $method = null, $timeout = null, $path = null, $interval = null, $retries = null, $header = null, $type = null, $description = null)
    {
        $data = [
            'expected_body'  => $expected_body,
            'expected_codes' => $expected_codes,
            'method'         => $method,
            'timeout'        => $timeout,
            'path'           => $path,
            'interval'       => $interval,
            'retries'        => $retries,
            'header'         => $header,
            'type'           => $type,
            'description'    => $description,
        ];

        return $this->post('/organizations/'.$organization_identifier.'/load_balancers/monitors', $data);
    }

    /**
     * Monitor details
     * List a single configured CTM monitor for a user
     *
     * @param string $organization_identifier
     * @param string $identifier
     */
    public function details($organization_identifier, $identifier)
    {
        return $this->get('/organizations/'.$organization_identifier.'/load_balancers/monitors/'.$identifier);
    }

    /**
     * Modify a monitor
     * Modify a configured monitor
     *
     * @param string      $organization_identifier
     * @param string      $identifier
     * @param string      $expected_body           A case-insensitive substring to match in the body of the probe
     *                                             response to declare an origin as up
     * @param string      $expected_codes          The expected HTTP response code or code range for the probe
     * @param string|null $method                  The HTTP method to use for the health check.
     * @param int|null    $timeout                 The timeout (in seconds) before marking the health check as failed
     * @param string|null $path                    The endpoint path to health check against.
     * @param int|null    $interval                The interval between each health check. Shorter intervals may improve failover
     *                                             time, but will increase load on the origins as we check from multiple locations.
     * @param int|null    $retries                 The number of retries to attempt in case of a timeout before marking the origin
     *                                             as unhealthy. Retries are attempted immediately.
     * @param array|null  $header                  The HTTP request headers to send in the health check. It is recommended you set
     *                                             a Host header by default. The User-Agent header cannot be overridden.
     * @param int|null    $type                    The protocol to use for the healthcheck. Currently supported protocols are
     *                                             'HTTP' and 'HTTPS'.
     * @param string|null $description             Object description
     */
    public function update($organization_identifier, $identifier, $expected_body, $expected_codes, $method = null, $timeout = null, $path = null, $interval = null, $retries = null, $header = null, $type = null, $description = null)
    {
        $data = [
            'expected_body'  => $expected_body,
            'expected_codes' => $expected_codes,
            'method'         => $method,
            'timeout'        => $timeout,
            'path'           => $path,
            'interval'       => $interval,
            'retries'        => $retries,
            'header'         => $header,
            'type'           => $type,
            'description'    => $description,
        ];

        return $this->patch('/organizations/'.$organization_identifier.'/load_balancers/monitors/'.$identifier, $data);
    }

    /**
     * Delete a monitor
     * Delete a configured monitor
     *
     * @param string $organization_identifier
     * @param string $identifier
     */
    public function delete_monitor($organization_identifier, $identifier)
    {
        return $this->delete('/organizations/'.$organization_identifier.'/load_balancers/monitors/'.$identifier);
    }
}
