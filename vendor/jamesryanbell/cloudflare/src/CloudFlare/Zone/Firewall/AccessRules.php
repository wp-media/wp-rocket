<?php

namespace Cloudflare\Zone\Firewall;

use Cloudflare\Api;
use Cloudflare\Zone;

/**
 * CloudFlare API wrapper
 *
 * Firewall access rules for a Zone
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class AccessRules extends Api
{
    /**
     * List access rules (permission needed: #zone:read)
     * Search, sort, and filter IP/country access rule
     *
     * @param string      $zone_id
     * @param string|null $scope_type           The scope of the rules
     * @param string|null $mode                 The action to apply to a matched request
     * @param string|null $configuration_target The rule configuration target
     * @param string|null $configuration_value  Search by IP, range, or country code
     * @param int|null    $page                 Page number of paginated results
     * @param int|null    $per_page             Number of rules per page
     * @param string|null $order                Field to order rules by
     * @param string|null $direction            Direction to order rules
     * @param string|null $match                Whether to match all search requirements or at least one (any)
     * @param string|null $notes                Search in the access rules by notes.
     */
    public function rules($zone_id, $scope_type = null, $mode = null, $configuration_target = null, $configuration_value = null, $page = null, $per_page = null, $order = null, $direction = null, $match = null, $notes = null)
    {
        $data = [
            'scope_type'           => $scope_type,
            'mode'                 => $mode,
            'configuration_target' => $configuration_target,
            'configuration_value'  => $configuration_value,
            'page'                 => $page,
            'per_page'             => $per_page,
            'order'                => $order,
            'direction'            => $direction,
            'match'                => $match,
            'notes'                => $notes,
        ];

        return $this->get('/zones/'.$zone_id.'/firewall/access_rules/rules', $data);
    }

    /**
     * Create access rule (permission needed: #zone:edit)
     * Make a new IP, IP range, or country access rule for the zone.
     * Note: If you would like to create an access rule that applies across all of your owned zones, use the user or organization firewall endpoints as appropriate.
     *
     * @param string      $zone_id
     * @param string      $mode          The action to apply to a matched request
     * @param object      $configuration Rule configuration
     * @param string|null $notes         A personal note about the rule. Typically used as a reminder or explanation for the rule.
     */
    public function create($zone_id, $mode, $configuration, $notes = null)
    {
        $data = [
            'mode'          => $mode,
            'configuration' => $configuration,
            'notes'         => $notes,
        ];

        return $this->post('/zones/'.$zone_id.'/firewall/access_rules/rules', $data);
    }

    /**
     * Update access rule (permission needed: #zone:edit)
     * Update rule state and/or configuration for the zone.
     * Note: you can only edit rules in the 'zone' group via this endpoint. Use the appropriate owner rules endpoint if trying to manage owner-level rules
     *
     * @param string      $zone_id
     * @param string      $identifier
     * @param string|null $mode       The action to apply to a matched request
     * @param string|null $notes      A personal note about the rule. Typically used as a reminder or explanation for the rule.
     */
    public function update($zone_id, $identifier, $mode = null, $notes = null)
    {
        $data = [
            'mode'  => $mode,
            'notes' => $notes,
        ];

        return $this->patch('/zones/'.$zone_id.'/firewall/access_rules/rules/'.$identifier, $data);
    }

    /**
     * Delete access rule (permission needed: #zone:edit)
     * Remove an access rule so it is no longer evaluated during requests.
     * Optionally, specify how to delete rules that match the mode and configuration across all other zones that this zone owner manages.
     * 'none' is the default, and will only delete this rule.
     * 'basic' will delete rules that match the same mode and configuration.
     * 'aggressive' will delete rules that match the same configuration.
     *
     * @param string      $zone_id
     * @param string      $identifier
     * @param string|null $cascade    The level to attempt to delete rules defined on other zones that are similar to this rule
     */
    public function delete_rule($zone_id, $identifier, $cascade = null)
    {
        $data = [
            'cascade' => $cascade,
        ];

        return $this->delete('/zones/'.$zone_id.'/firewall/access_rules/rules/'.$identifier, $data);
    }
}
