<?php

namespace Cloudflare\Organizations;

use Cloudflare\Api;

/**
 * CloudFlare API wrapper
 *
 * Organization Railgun
 * CloudFlare Railgun for Organizations
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class Railguns extends Api
{
    /**
     * Create Railgun (permission needed: #organization:edit)
     *
     * @param string $organization_identifier Organization identifier tag
     * @param string $name                    Readable identifier of the railgun
     */
    public function create($organization_identifier, $name)
    {
        $data = [
            'name' => $name,
        ];

        return $this->post('/organizations/'.$organization_identifier.'/railguns', $data);
    }

    /**
     * List Railguns (permission needed: #organization:read)
     * List, search, sort and filter your Railguns
     *
     * @param string      $organization_identifier Organization identifier tag
     * @param int|null    $page                    Page number of paginated results
     * @param int|null    $per_page                Number of items per page
     * @param string|null $direction               Direction to order Railguns (asc, desc)
     */
    public function railguns($organization_identifier, $page = null, $per_page = null, $direction = null)
    {
        $data = [
            'page'      => $page,
            'per_page'  => $per_page,
            'direction' => $direction,
        ];

        return $this->get('/organizations/'.$organization_identifier.'/railguns', $data);
    }

    /**
     * Railgun details (permission needed: #organization:read)
     *
     * @param string $organization_identifier Organization identifier tag
     * @param string $identifier              API item identifier tag
     */
    public function details($organization_identifier, $identifier)
    {
        return $this->get('/organizations/'.$organization_identifier.'/railguns/'.$identifier);
    }

    /**
     * Get zones connected to a Railgun (permission needed: #organization:read)
     * The zones that are currently using this Railgun
     *
     * @param string $organization_identifier Organization identifier tag
     * @param string $identifier              API item identifier tag
     */
    public function zones($organization_identifier, $identifier)
    {
        return $this->get('/organizations/'.$organization_identifier.'/railguns/'.$identifier.'/zones');
    }

    /**
     * Enable or disable a Railgun (permission needed: #organization:edit)
     * Enable or disable a Railgun for all zones connected to it
     *
     * @param string    $organization_identifier Organization identifier tag
     * @param string    $identifier              API item identifier tag
     * @param bool|null $enabled                 Flag to determine if the Railgun is accepting connections
     */
    public function enabled($organization_identifier, $identifier, $enabled = null)
    {
        $data = [
            'enabled' => $enabled,
        ];

        return $this->patch('/organizations/'.$organization_identifier.'/railguns/'.$identifier, $data);
    }

    /**
     * Delete Railgun (permission needed: #organization:edit)
     * Disable and delete a Railgun. This will immediately disable the Railgun for any connected zones
     *
     * @param string $organization_identifier Organization identifier tag
     * @param string $identifier              API item identifier tag
     */
    public function delete_railgun($organization_identifier, $identifier)
    {
        return $this->delete('/organizations/'.$organization_identifier.'/railguns/'.$identifier);
    }
}
