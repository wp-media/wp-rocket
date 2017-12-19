<?php

namespace Cloudflare;

/**
 * CloudFlare API wrapper
 *
 * CloudFlare IPs
 * CloudFlare IP space
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class IPs extends Api
{
    /**
     * CloudFlare IPs
     * Get CloudFlare IPs
     */
    public function ips()
    {
        return $this->get('/ips');
    }
}
