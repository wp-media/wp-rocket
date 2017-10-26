<?php

namespace Cloudflare\Zone;

use Cloudflare\Api;

/**
 * CloudFlare API wrapper
 *
 * Page rules for a Zone
 * A rule describing target patterns for requests and actions to perform on matching requests
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class Pagerules extends Api
{
    /**
     * Create a page rule [BETA] (permission needed: #zone:edit)
     *
     * @param string      $zone_identifier API item identifier tag
     * @param array       $targets         Targets to evaluate on a request
     * @param array       $actions         The set of actions to perform if the targets of this rule match the request.
     *                                     Actions can redirect the url to another url or override settings (but not both)
     * @param int|null    $priority        A number that indicates the preference for a page rule over another. In the case where
     *                                     you may have a catch-all page rule (e.g., #1: '/images/') but want a rule that is more
     *                                     specific to take precedence (e.g., #2: '/images/special/'), you'll want to specify a
     *                                     higher priority on the latter (#2) so it will override the first.
     * @param string|null $status          Status of the page rule
     */
    public function create($zone_identifier, $targets, $actions, $priority = null, $status = 'active')
    {
        $data = [
            'targets'  => $targets,
            'actions'  => $actions,
            'priority' => $priority,
            'status'   => $status,
        ];

        return $this->post('zones/'.$zone_identifier.'/pagerules', $data);
    }

    /**
     * List page rules [BETA] (permission needed: #zone:read)
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string|null $status          Status of the page rule
     * @param string|null $order           Field to order page rules by (status, priority)
     * @param string|null $direction       Direction to order page rules (asc, desc)
     * @param string|null $match           Whether to match all search requirements or at least one (any) (any, all)
     */
    public function list_pagerules($zone_identifier, $status = null, $order = null, $direction = null, $match = null)
    {
        $data = [
            'status'    => $status,
            'order'     => $order,
            'direction' => $direction,
            'match'     => $match,
        ];

        return $this->get('zones/'.$zone_identifier.'/pagerules', $data);
    }

    /**
     * Page rule details [BETA] (permission needed: #zone:read)
     *
     * @param string $zone_identifier API item identifier tag
     * @param string $identifier
     */
    public function details($zone_identifier, $identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/pagerules/'.$identifier);
    }

    /**
     * Change a page rule [BETA] (permission needed: #zone:edit)
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string      $identifier
     * @param array|null  $targets         Targets to evaluate on a request
     * @param array|null  $actions         The set of actions to perform if the targets of this rule match the request.
     *                                     Actions can redirect the url to another url or override settings (but not both)
     * @param int|null    $priority        A number that indicates the preference for a page rule over another. In the case where
     *                                     you may have a catch-all page rule (e.g., #1: '/images/') but want a rule that is more
     *                                     specific to take precedence (e.g., #2: '/images/special/'), you'll want to specify a
     *                                     higher priority on the latter (#2) so it will override the first.
     * @param string|null $status          Status of the page rule
     */
    public function change($zone_identifier, $identifier, $targets = null, $actions = null, $priority = null, $status = null)
    {
        $data = [
            'targets'  => $targets,
            'actions'  => $actions,
            'priority' => $priority,
            'status'   => $status,
        ];

        return $this->patch('zones/'.$zone_identifier.'/pagerules/'.$identifier, $data);
    }

    /**
     * Update a page rule [BETA] (permission needed: #zone:edit)
     * Replace a page rule. The final rule will exactly match the data passed with this request.
     *
     * @param string      $zone_identifier API item identifier tag
     * @param string      $identifier
     * @param array       $targets         Targets to evaluate on a request
     * @param array       $actions         The set of actions to perform if the targets of this rule match the request.
     *                                     Actions can redirect the url to another url or override settings (but not both)
     * @param int|null    $priority        A number that indicates the preference for a page rule over another. In the case where
     *                                     you may have a catch-all page rule (e.g., #1: '/images/') but want a rule that is more
     *                                     specific to take precedence (e.g., #2: '/images/special/'), you'll want to specify a
     *                                     higher priority on the latter (#2) so it will override the first.
     * @param string|null $status          Status of the page rule
     */
    public function update($zone_identifier, $identifier, $targets, $actions, $priority = null, $status = null)
    {
        $data = [
            'targets'  => $targets,
            'actions'  => $actions,
            'priority' => $priority,
            'status'   => $status,
        ];

        return $this->put('zones/'.$zone_identifier.'/pagerules/'.$identifier, $data);
    }

    /**
     * Delete a page rule [BETA] (permission needed: #zone:edit)
     *
     * @param string $zone_identifier API item identifier tag
     * @param string $identifier
     */
    public function delete_pagerule($zone_identifier, $identifier)
    {
        return $this->delete('zones/'.$zone_identifier.'/pagerules/'.$identifier);
    }
}
