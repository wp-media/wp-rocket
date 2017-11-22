<?php

namespace Cloudflare\Organizations\Firewall\AccessRules;

use Cloudflare\Api;
use Cloudflare\Organizations;
use Cloudflare\Organizations\Firewall;

/**
 * CloudFlare API wrapper
 *
 * Organization-level Firewall access rule
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class Rules extends Api
{
    /**
     * List access rules (permission needed: #organization:read)
     * Search, sort, and filter IP/country access rules
     *
     * @param string      $organization_id
     * @param string|null $mode                 The action to apply to a matched request
     * @param string|null $configuration_target The rule configuration target
     * @param string|null $configuration_value  Search by IP, range, or country code
     * @param int|null    $page                 Page number of paginated results
     * @param int|null    $per_page             Number of rules per page
     * @param string|null $order                Field to order rules by
     * @param string|null $direction            Direction to order rules
     * @param string|null $match                Whether to match all search requirements or at least one (any)
     */
    public function rules($organization_id, $mode = null, $configuration_target = null, $configuration_value = null, $page = null, $per_page = null, $order = null, $direction = null, $match = null)
    {
        $data = [
            'mode'                 => $mode,
            'configuration_target' => $configuration_target,
            'configuration_value'  => $configuration_value,
            'page'                 => $page,
            'per_page'             => $per_page,
            'order'                => $order,
            'direction'            => $direction,
            'match'                => $match,
        ];

        return $this->get('organizations/'.$organization_id.'/firewall/access_rules/rules', $data);
    }

    /**
     * Create access rule (permission needed: #organization:edit)
     * Make a new IP, IP range, or country access rule for all zones owned by the organization.
     * Note: If you would like to create an access rule that applies to a specific zone only, use the zone firewall endpoints.
     *
     * @param string      $organization_id
     * @param string      $mode            The action to apply to a matched request
     * @param object      $configuration   Rule configuration
     * @param string|null $notes           A personal note about the rule. Typically used as a reminder or explanation for the rule.
     */
    public function create($organization_id, $mode, $configuration, $notes = null)
    {
        $data = [
            'mode'          => $mode,
            'configuration' => $configuration,
            'notes'         => $notes,
        ];

        return $this->post('organizations/'.$organization_id.'/firewall/access_rules/rules', $data);
    }

    /**
     * Update access rule (permission needed: #organization:edit)
     * Update rule state and/or configuration. This will be applied across all zones owned by the organization.
     *
     * @param string      $organization_id
     * @param string      $identifier
     * @param string|null $mode            The action to apply to a matched request
     * @param object|null $configuration   Rule configuration
     * @param string|null $notes           A personal note about the rule. Typically used as a reminder or explanation for the rule.
     */
    public function update($organization_id, $identifier, $mode = null, $configuration = null, $notes = null)
    {
        $data = [
            'mode'          => $mode,
            'configuration' => $configuration,
            'notes'         => $notes,
        ];

        return $this->patch('organizations/'.$organization_id.'/firewall/access_rules/rules/'.$identifier, $data);
    }

    /**
     * Delete access rule (permission needed: #organization:edit)
     * Remove an access rule so it is no longer evaluated during requests. This will apply to all zones owned by the organization
     *
     * @param string $organization_id
     * @param string $identifier
     */
    public function delete_rule($organization_id, $identifier)
    {
        return $this->delete('organizations/'.$organization_id.'/firewall/access_rules/rules/'.$identifier);
    }
}
