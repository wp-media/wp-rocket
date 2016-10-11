<?php

namespace Cloudflare\User;

use Cloudflare\Api;

/**
 * CloudFlare API wrapper
 *
 * Billing
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class Billing extends Api
{
    /**
     * Billing Profile (permission needed: #billing:read)
     * Access your billing profile object
     */
    public function billing()
    {
        return $this->get('/user/billing/profile');
    }

    /**
     * Billing History (permission needed: #billing:read)
     * Access your billing profile object
     *
     * @param int|null    $page       Page number of paginated results
     * @param int|null    $per_page   Number of items per page
     * @param string|null $order      Field to order billing history by
     * @param string|null $type       The billing item type
     * @param string|null $occured_at When the billing item was created
     * @param string|null $action     The billing item action
     */
    public function history($page = null, $per_page = null, $order = null, $type = null, $occured_at = null, $action = null)
    {
        $data = [
            'page'       => $page,
            'per_page'   => $per_page,
            'order'      => $order,
            'type'       => $type,
            'occured_at' => $occured_at,
            'action'     => $action,
        ];

        return $this->get('/user/billing/history', $data);
    }
}
