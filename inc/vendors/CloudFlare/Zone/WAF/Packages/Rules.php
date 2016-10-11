<?php

namespace Cloudflare\Zone\WAF\Packages;

use Cloudflare\Api;
use Cloudflare\Zone;
use Cloudflare\Zone\WAF;

/**
 * CloudFlare API wrapper
 *
 * WAF Rules properties
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class Rules extends Api
{
    /**
     * List rule (permission needed: #zone:read)
     * Search, list, and filter rules within a package
     *
     * @param string      $zone_id
     * @param string      $package_id
     * @param string|null $description Public description of the rule
     * @param object|null $mode        The rule mode
     * @param int|null    $priority    The order in which the individual rule is executed within the related group
     * @param string|null $group_id    WAF group identifier tag
     * @param int|null    $page        Page number of paginated results
     * @param int|null    $per_page    Number of rules per page
     * @param string|null $order       Field to order rules by
     * @param string|null $direction   Direction to order rules
     * @param string|null $match       Whether to match all search requirements or at least one (any)
     */
    public function rules($zone_id, $package_id, $description = null, $mode = null, $priority = null, $group_id = null, $page = null, $per_page = null, $order = null, $direction = null, $match = null)
    {
        $data = [
            'description' => $description,
            'mode'        => $mode,
            'priority'    => $priority,
            'group_id'    => $group_id,
            'page'        => $page,
            'per_page'    => $per_page,
            'order'       => $order,
            'direction'   => $direction,
            'match'       => $match,
        ];

        return $this->get('/zones/'.$zone_id.'/firewall/waf/packages/'.$package_id.'/rules', $data);
    }

    /**
     * Rule info (permission needed: #zone:read)
     * Individual information about a rule
     *
     * @param string $zone_id
     * @param string $package_id
     * @param string $identifier
     */
    public function info($zone_id, $package_id, $identifier)
    {
        return $this->get('/zones/'.$zone_id.'/firewall/waf/packages/'.$package_id.'/rules/'.$identifier);
    }

    /**
     * Update Rule group (permission needed: #zone:edit)
     * Update the state of a rule group
     *
     * @param string      $zone_id
     * @param string      $package_id
     * @param string      $identifier
     * @param string|null $mode       The mode to use when the rule is triggered. Value is restricted based on the allowed_modes of the rule
     */
    public function update($zone_id, $package_id, $identifier, $mode = null)
    {
        $data = [
            'mode' => $mode,
        ];

        return $this->patch('/zones/'.$zone_id.'/firewall/waf/packages/'.$package_id.'/rules/'.$identifier, $data);
    }
}
