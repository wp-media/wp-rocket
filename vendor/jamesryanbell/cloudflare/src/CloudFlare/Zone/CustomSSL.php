<?php

namespace Cloudflare\Zone;

use Cloudflare\Api;

/**
 * CloudFlare API wrapper
 *
 * Custom SSL for a Zone
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class CustomSSL extends Api
{
    /**
     * List SSL configurations (permission needed: #ssl:edit)
     * List, search, sort, and filter all of your custom SSL certificates
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string|null $status          Status of the zone's custom SSL
     * @param int|null    $page            Page number of paginated results
     * @param int|null    $per_page        Number of zones per page
     * @param string|null $order           Field to order certificates by (status, issuer, priority, expires_on)
     * @param string|null $direction       Direction to order domains (asc, desc)
     * @param string|null $match           Whether to match all search requirements or at least one (any) (any, all)
     */
    public function list_certificates($zone_identifier, $status = null, $page = null, $per_page = null, $order = null, $direction = null, $match = null)
    {
        $data = [
            'status'    => $status,
            'page'      => $page,
            'per_page'  => $per_page,
            'order'     => $order,
            'direction' => $direction,
            'match'     => $match,
        ];

        return $this->get('zones/'.$zone_identifier.'/custom_certificates', $data);
    }

    /**
     * Create SSL configuration (permission needed: #ssl:edit)
     * Upload a new SSL certificate for a zone
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string      $certificate     The zone's private key
     * @param string      $private_key     The zone's SSL certificate or certificate and the intermediate(s)
     * @param string|null $bundle_method   A ubiquitous bundle is a bundle that has a higher probability of being verified everywhere,
     *                                     even by clients using outdated or unusual trust stores. An optimal bundle is a bundle with the shortest chain and newest
     *                                     intermediates. A forced method attempt to use the certificate/chain as defined by the input "ubiquitous"
     */
    public function create($zone_identifier, $certificate, $private_key, $bundle_method = null)
    {
        $data = [
            'certificate'   => $certificate,
            'private_key'   => $private_key,
            'bundle_method' => $bundle_method,
        ];

        return $this->post('zones/'.$zone_identifier.'/custom_certificates', $data);
    }

    /**
     * SSL configuration details (permission needed: #ssl:read)
     *
     * @param string $zone_identifier API item identifier tag
     * @param string $identifier
     */
    public function details($zone_identifier, $identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/custom_certificates/'.$identifier);
    }

    /**
     * Create a Keyless SSL configuration (permission needed: #ssl:edit)
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string      $identifier
     * @param string      $private_key     The zone's SSL certificate or certificate and the intermediate(s)
     * @param string      $certificate     The zone's private key
     * @param string|null $bundle_method   A ubiquitous bundle is a bundle that has a higher probability of being verified everywhere,
     *                                     even by clients using outdated or unusual trust stores. An optimal bundle is a bundle with the shortest chain and newest
     *                                     intermediates. A forced method attempt to use the certificate/chain as defined by the input "ubiquitous"
     */
    public function update($zone_identifier, $identifier, $private_key, $certificate, $bundle_method = null)
    {
        $data = [
            'certificate'   => $certificate,
            'private_key'   => $private_key,
            'bundle_method' => $bundle_method,
        ];

        return $this->patch('zones/'.$zone_identifier.'/custom_certificates/'.$identifier, $data);
    }

    /**
     * Re-prioritize SSL certificates (permission needed: #ssl:edit)
     * If a zone has multiple SSL certificates, you can set the order in which they should be used during a request.
     *
     * @param string $zone_identifier API item identifier tag
     * @param array  $certificates    Array of ordered certificates.
     */
    public function prioritize($zone_identifier, array $certificates)
    {
        $data = [
            'certificates' => $certificates,
        ];

        return $this->put('zones/'.$zone_identifier.'/custom_certificates/prioritize', $data);
    }

    /**
     * Delete an SSL certificate (permission needed: #ssl:edit)
     *
     * @param string $zone_identifier API item identifier tag
     * @param string $identifier
     */
    public function delete_ssl($zone_identifier, $identifier)
    {
        return $this->delete('zones/'.$zone_identifier.'/custom_certificates/'.$identifier);
    }
}
