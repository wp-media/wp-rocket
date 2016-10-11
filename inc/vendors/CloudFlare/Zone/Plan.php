<?php

namespace Cloudflare\Zone;

use Cloudflare\Api;

/**
 * CloudFlare API wrapper
 *
 * Zone Plan
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class Plan extends Api
{
    /**
     * Available plans (permission needed: #billing:read)
     * List all plans the zone can subscribe to.
     *
     * @param string $zone_identifier
     */
    public function available($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/plans');
    }

    /**
     * Available plans (permission needed: #billing:read)
     *
     * @param string $zone_identifier
     * @param string $identifier      API item identifier tag
     */
    public function details($zone_identifier, $identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/plans/'.$identifier);
    }

    /**
     * Change plan (permission needed: #billing:edit)
     * Change the plan level for the zone. This will cancel any previous subscriptions and subscribe the zone to the new plan.
     *
     * @param string $zone_identifier
     * @param string $identifier      API item identifier tag
     */
    public function change($zone_identifier, $identifier)
    {
        return $this->put('zones/'.$zone_identifier.'/plans/'.$identifier.'/subscribe');
    }
}
