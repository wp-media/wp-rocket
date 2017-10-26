<?php

namespace Cloudflare\Zone\SSL;

use Cloudflare\Api;
use Cloudflare\Zone;

/**
 * CloudFlare API wrapper
 *
 * Certificate Packs
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class CertificatePacks extends Api
{
    /**
     * List all certificate packs (permission needed: #ssl:read)
     * For a given zone, list all certificate packs
     *
     * @param string $identifier
     */
    public function certificate_packs($identifier)
    {
        return $this->get('/zones/'.$identifier.'/ssl/certificate_packs');
    }
}
