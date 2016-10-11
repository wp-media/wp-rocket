<?php

namespace Cloudflare;

/**
 * CloudFlare API wrapper
 *
 * Organizations
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class Organizations extends Api
{
    /**
     * Organization details (permission needed: #organization:read)
     * Get information about a specific organization that you are a member of
     *
     * @param string $identifier
     */
    public function organization($identifier)
    {
        return $this->get('/organizations/'.$identifier);
    }

    /**
     * Update organization (permission needed: #organization:edit)
     * Update an existing Organization
     *
     * @param string|null $identifier
     * @param string|null $name       Organization Name
     */
    public function update($identifier = null, $name = null)
    {
        $data = [
            'name' => $name,
        ];

        return $this->get('/organizations/'.$identifier, $data);
    }
}
