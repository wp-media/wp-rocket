<?php

namespace Cloudflare\Zone;

use Cloudflare\Api;

/**
 * CloudFlare API wrapper
 *
 * Accelerated Mobile Links
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class Aml extends Api
{
    /**
     * Get AML Settings (permission needed: #zone_settings:edit)
     * Fetch AML configuration for a zone
     *
     * @param string $zone_identifier
     */
    public function viewer($zone_identifier)
    {
        return $this->get('zones/'.$zone_identifier.'/amp/viewer');
    }

    /**
     * Update AML Settings (permission needed: #zone_settings:edit)
     * Update AML configuration for a zone
     *
     * @param bool|null   $enabled            Enable Accelerated Mobile Links on mobile browsers.
     * @param array|null  $subdomains         Your contact email address, repeated
     * @param string|null $prepend_links_with Your current password
     */
    public function change_email($enabled = null, $subdomains = null, $prepend_links_with = null)
    {
        $data = [
            'enabled'            => $enabled,
            'subdomains'         => $subdomains,
            'prepend_links_with' => $prepend_links_with,
        ];

        return $this->put('zones/'.$zone_identifier.'/amp/viewer', $data);
    }
}
