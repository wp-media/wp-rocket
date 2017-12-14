<?php

namespace Cloudflare\Zone\WAF;

use Cloudflare\Api;
use Cloudflare\Zone;

/**
 * CloudFlare API wrapper
 *
 * WAF Rule Packages properties
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class Packages extends Api
{
    /**
     * List firewall packages (permission needed: #zone:read)
     * Retrieve firewall packages for a zone
     *
     * @param string      $zone_identifier
     * @param string|null $name            Name of the firewall package
     * @param int|null    $page            Page number of paginated results
     * @param int|null    $per_page        Number of packages per page
     * @param string|null $order           Field to order packages by
     * @param string|null $direction       Direction to order packages
     * @param string|null $match           Whether to match all search requirements or at least one (any)
     */
    public function rules($zone_identifier, $name = null, $page = null, $per_page = null, $order = null, $direction = null, $match = null)
    {
        $data = [
            'name'      => $name,
            'page'      => $page,
            'per_page'  => $per_page,
            'order'     => $order,
            'direction' => $direction,
            'match'     => $match,
        ];

        return $this->get('/zones/'.$zone_identifier.'/firewall/waf/packages', $data);
    }

    /**
     * Firewall package info (permission needed: #zone:read)
     * Get information about a single firewall package
     *
     * @param string $zone_identifier
     * @param string $identifier
     */
    public function info($zone_identifier, $identifier)
    {
        return $this->get('/zones/'.$zone_identifier.'/firewall/waf/packages/'.$identifier);
    }

    /**
     * Change anomaly-detection web application firewall package settings (permission needed: #zone:edit)
     * Change the sensitivity and action for an anomaly detection type WAF rule package
     *
     * @param string      $zone_identifier
     * @param string      $identifier
     * @param string|null $sensitivity     The sensitivity of the firewall package.
     * @param string|null $action_mode     The default action that will be taken for rules under the firewall package.
     */
    public function update($zone_identifier, $identifier, $sensitivity = null, $action_mode = null)
    {
        $data = [
            'sensitivity' => $sensitivity,
            'action_mode' => $action_mode,
        ];

        return $this->patch('/zones/'.$zone_identifier.'/firewall/waf/packages/'.$identifier, $data);
    }
}
