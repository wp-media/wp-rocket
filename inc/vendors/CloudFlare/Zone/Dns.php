<?php

namespace Cloudflare\Zone;

use Cloudflare\Api;

/**
 * CloudFlare API wrapper
 *
 * DNS Record
 * CloudFlare DNS records
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class Dns extends Api
{
    /**
     * Create DNS record (permission needed: #dns_records:edit)
     * Create a new DNS record for a zone. See the record object definitions for required attributes for each record type
     *
     * @param string     $zone_identifier
     * @param string     $type            DNS record type (A, AAAA, CNAME, TXT, SRV, LOC, MX, NS, SPF)
     * @param string     $name            DNS record name
     * @param string     $content         DNS record content
     * @param int|null   $ttl             Time to live for DNS record. Value of 1 is 'automatic'
     * @param bool|null  $proxied         Whether to proxy the domain through CloudFlare or not
     * @param int|null   $priority        MX record priority value
     * @param array|null $data            Additional data required for SRV record
     */
    public function create($zone_identifier, $type, $name, $content, $ttl = null, $proxied = null, $priority = null, $data = null)
    {
        $data = [
            'type'     => strtoupper($type),
            'name'     => $name,
            'content'  => $content,
            'ttl'      => $ttl,
            'proxied'  => $proxied,
            'priority' => $priority,
            'data'     => $data,
        ];

        return $this->post('zones/'.$zone_identifier.'/dns_records', $data);
    }

    /**
     * List DNS Records (permission needed: #dns_records:read)
     * List, search, sort, and filter a zones' DNS records.
     *
     * @param string      $zone_identifier
     * @param string|null $type                      DNS record type (A, AAAA, CNAME, TXT, SRV, LOC, MX, NS, SPF)
     * @param string|null $name                      DNS record name
     * @param string|null $content                   DNS record content
     * @param string|null $vanity_name_server_record Flag for records that were created for the vanity name server feature (true, false)
     * @param int|null    $page                      Page number of paginated results
     * @param int|null    $per_page                  Number of DNS records per page
     * @param string|null $order                     Field to order records by (type, name, content, ttl, proxied)
     * @param string|null $direction                 Direction to order domains (asc, desc)
     * @param string|null $match                     Whether to match all search requirements or at least one (any) (any, all)
     */
    public function list_records($zone_identifier, $type = null, $name = null, $content = null, $vanity_name_server_record = null, $page = null, $per_page = null, $order = null, $direction = null, $match = null)
    {
        $data = [
            'type'                      => $type,
            'name'                      => $name,
            'content'                   => $content,
            'vanity_name_server_record' => $vanity_name_server_record,
            'page'                      => $page,
            'per_page'                  => $per_page,
            'order'                     => $order,
            'direction'                 => $direction,
            'match'                     => $match,
        ];

        return $this->get('zones/'.$zone_identifier.'/dns_records', $data);
    }

    /**
     * DNS record details (permission needed: #dns_records:read)
     *
     * @param string $zone_identifier
     * @param string $identifier      API item identifier tag
     */
    public function details($zone_identifier, $identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/dns_records/'.$identifier);
    }

    /**
     * Update DNS record (permission needed: #dns_records:edit)
     *
     * @param string      $zone_identifier
     * @param string      $identifier      API item identifier tag
     * @param string|null $type            DNS record type (A, AAAA, CNAME, TXT, SRV, LOC, MX, NS, SPF)
     * @param string|null $name            DNS record name
     * @param string|null $content         DNS record content
     * @param string|null $ttl             Time to live for DNS record. Value of 1 is 'automatic'
     * @param bool|null   $proxied         Whether to proxy the domain through CloudFlare or not
     * @param array|null  $data            Additional data required for SRV record
     * @param int|null    $priority        MX record priority value
     */
    public function update($zone_identifier, $identifier, $type = null, $name = null, $content = null, $ttl = null, $proxied = null, $data = null, $priority = null)
    {
        $data = [
            'type'     => $type,
            'name'     => $name,
            'content'  => $content,
            'ttl'      => $ttl,
            'proxied'  => $proxied,
            'priority' => $priority,
            'data'     => $data,
        ];

        return $this->put('zones/'.$zone_identifier.'/dns_records/'.$identifier, $data);
    }

    /**
     * Update DNS record (permission needed: #dns_records:edit)
     *
     * @param string $zone_identifier
     * @param string $identifier      API item identifier tag
     */
    public function delete_record($zone_identifier, $identifier)
    {
        return $this->delete('zones/'.$zone_identifier.'/dns_records/'.$identifier);
    }
}
