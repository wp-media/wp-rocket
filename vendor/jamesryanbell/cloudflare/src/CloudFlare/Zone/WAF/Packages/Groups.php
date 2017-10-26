<?php

namespace Cloudflare\Zone\WAF\Packages;

use Cloudflare\Api;
use Cloudflare\Zone;
use Cloudflare\Zone\WAF;

/**
 * CloudFlare API wrapper
 *
 * WAF Rule Groups properties
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class Groups extends Api
{
    /**
     * List rule groups (permission needed: #zone:read)
     * Search, list, and sort rule groups contained within a package
     *
     * @param string      $zone_identifier
     * @param string      $package_identifier
     * @param string|null $name               Name of the firewall rule group
     * @param string|null $mode               Whether or not the rules contained within this group are configurable/usable
     * @param int|null    $rules_count        How many rules are contained within this group
     * @param int|null    $page               Page number of paginated results
     * @param int|null    $per_page           Number of groups per page
     * @param string|null $order              Field to order groups by
     * @param string|null $direction          Direction to order groups
     * @param string|null $match              Whether to match all search requirements or at least one (any)
     */
    public function groups($zone_identifier, $package_identifier, $name = null, $mode = null, $rules_count = null, $page = null, $per_page = null, $order = null, $direction = null, $match = null)
    {
        $data = [
            'name'        => $name,
            'mode'        => $mode,
            'rules_count' => $rules_count,
            'page'        => $page,
            'per_page'    => $per_page,
            'order'       => $order,
            'direction'   => $direction,
            'match'       => $match,
        ];

        return $this->get('/zones/'.$zone_identifier.'/firewall/waf/packages/'.$package_identifier.'/groups', $data);
    }

    /**
     * Rule group info (permission needed: #zone:read)
     * Get a single rule group
     *
     * @param string $zone_identifier
     * @param string $package_identifier
     * @param string $identifier
     */
    public function info($zone_identifier, $package_identifier, $identifier)
    {
        return $this->get('/zones/'.$zone_identifier.'/firewall/waf/packages/'.$package_identifier.'/groups/'.$identifier);
    }

    /**
     * Update Rule group (permission needed: #zone:edit)
     * Update the state of a rule group
     *
     * @param string      $zone_identifier
     * @param string      $package_identifier
     * @param string      $identifier
     * @param string|null $mode               Whether or not the rules contained within this group are configurable/usable
     */
    public function update($zone_identifier, $package_identifier, $identifier, $mode = null)
    {
        $data = [
            'mode' => $mode,
        ];

        return $this->patch('/zones/'.$zone_identifier.'/firewall/waf/packages/'.$package_identifier.'/groups/'.$identifier, $data);
    }
}
