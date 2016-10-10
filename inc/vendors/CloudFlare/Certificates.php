<?php

namespace Cloudflare;

/**
 * CloudFlare API wrapper
 *
 * CloudFlare CA
 * API to create CloudFlare-issued SSL certificates that can be installed on your origin server.
 * Use your Certificates API Key as your User Service Key when calling these endpoints
 * (see the section on request headers for details)
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class Certificates extends Api
{
    /**
     * List Certificates
     * List all existing CloudFlare-issued Certificates for a given zone. Use your Certificates API Key as your
     * User Service Key when calling this endpoint
     */
    public function certificates($page = null, $per_page = null, $direction = null)
    {
        $data = [
            'page'      => $page,
            'per_page'  => $per_page,
            'direction' => $direction,
        ];

        return $this->get('certificates', $data);
    }

    /**
     * Create Certificate
     * Create a CloudFlare-signed certificate. Use your Certificates API Key as your User Service Key when
     * calling this endpoint
     *
     * @param array    $hostnames          Array of hostnames or wildcard names (e.g., *.example.com) bound to the certificate
     * @param string   $request_type       Signature type desired on certificate ("origin-rsa" (rsa), "origin-ecc" (ecdsa), or "keyless-certificate" (for Keyless SSL servers)
     * @param string   $csr                The Certificate Signing Request (CSR). Must be newline-encoded.
     * @param int|null $requested_validity The number of days for which the certificate should be valid
     */
    public function create($hostnames, $request_type, $csr, $requested_validity = null)
    {
        $data = [
            'hostnames'          => $hostnames,
            'request_type'       => $request_type,
            'csr'                => $csr,
            'requested_validity' => $requested_validity,
        ];

        return $this->post('certificates', $data);
    }

    /**
     * Certificate Details
     * Get an existing certificate by its serial number. Use your Certificates API Key as your User Service Key
     * when calling this endpoint
     *
     * @param string $identifier API item identifier tag
     */
    public function details($identifier)
    {
        return $this->get('certificates/'.$identifier);
    }

    /**
     * Revoke certificate
     * Revoke a created certificate for a zone. Use your Certificates API Key as your User Service Key when
     * calling this endpoint
     *
     * @param string $identifier API item identifier tag
     */
    public function revoke($identifier)
    {
        return $this->delete('certificates/'.$identifier);
    }
}
