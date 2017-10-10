<?php

namespace Cloudflare\Zone\SSL;

use Cloudflare\Api;
use Cloudflare\Zone;

/**
 * CloudFlare API wrapper
 *
 * Analyze Certificate
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class Analyze extends Api
{
    /**
     * Analyze Certificate (permission needed: #ssl:read)
     * Returns the set of hostnames, the signature algorithm, and the expiration date of the certificate.
     *
     * @param string      $identifier
     * @param string      $certificate   The zone's SSL certificate or certificate and the intermediate(s)
     * @param string|null $bundle_method A ubiquitous bundle is a bundle that has a higher probability of
     *                                   being verified everywhere, even by clients using outdated or unusual
     *                                   trust stores. An optimal bundle is a bundle with the shortest chain and
     *                                   newest intermediates. A forced method attempt to use the certificate/chain
     *                                   as defined by the input
     */
    public function analyze($identifier, $certificate, $bundle_method = null)
    {
        $data = [
            'certificate'   => $certificate,
            'bundle_method' => $bundle_method,
        ];

        return $this->post('/zones/'.$identifier.'/ssl/analyze', $data);
    }
}
