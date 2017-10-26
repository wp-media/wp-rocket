<?php

namespace Cloudflare\Zone;

use Cloudflare\Api;

/**
 * CloudFlare API wrapper
 *
 * Keyless SSL for a Zone
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class KeylessSSL extends Api
{
    /**
     * Create a Keyless SSL configuration (permission needed: #ssl:edit)
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string      $host            The keyless SSL host
     * @param int         $port            The keyless SSL port used to commmunicate between CloudFlare and the client's Keyless SSL server
     * @param string      $name            The keyless SSL name
     * @param string      $certificate     The zone's SSL certificate or SSL certificate and intermediate(s)
     * @param string|null $bundle_method   A ubiquitous bundle is a bundle that has a higher probability of being verified everywhere, even by clients using outdated or unusual trust stores.
     *                                     An optimal bundle is a bundle with the shortest chain and newest intermediates. A forced method attempt to use the certificate/chain as defined by the input
     */
    public function create($zone_identifier, $host, $port, $name, $certificate, $bundle_method = null)
    {
        $data = [
            'host'          => $host,
            'port'          => $port,
            'name'          => $name,
            'certificate'   => $certificate,
            'bundle_method' => $bundle_method,
        ];

        return $this->post('zones/'.$zone_identifier.'/keyless_certificates', $data);
    }

    /**
     * List Keyless SSLs (permission needed: #ssl:read)
     *
     * @param string $zone_identifier API item identifier tag
     */
    public function certificates($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/keyless_certificates');
    }

    /**
     * Keyless SSL details (permission needed: #ssl:read)
     *
     * @param string $zone_identifier API item identifier tag
     * @param string $identifier
     */
    public function details($zone_identifier, $identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/keyless_certificates/'.$identifier);
    }

    /**
     * Update SSL configuration (permission needed: #ssl:edit)
     *
     * @param string    $zone_identifier API item identifier tag
     * @param string    $identifier
     * @param string    $host            The keyless SSL hostname
     * @param string    $name            The keyless SSL name
     * @param int       $port            The keyless SSL port used to commmunicate between CloudFlare and the client's Keyless SSL server
     * @param bool|null $enabled         Whether or not the Keyless SSL is on or off
     */
    public function update($zone_identifier, $identifier, $host, $name, $port, $enabled = null)
    {
        $data = [
            'host'    => $host,
            'port'    => $port,
            'name'    => $name,
            'enabled' => $enabled,
        ];

        return $this->patch('zones/'.$zone_identifier.'/keyless_certificates/'.$identifier, $data);
    }

    /**
     * Delete an SSL certificate (permission needed: #ssl:edit)
     *
     * @param string $zone_identifier API item identifier tag
     * @param string $identifier
     */
    public function delete_ssl($zone_identifier, $identifier)
    {
        return $this->delete('zones/'.$zone_identifier.'/keyless_certificates/'.$identifier);
    }
}
