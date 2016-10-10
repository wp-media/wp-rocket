<?php

namespace Cloudflare\User\Billing\Subscriptions;

use Cloudflare\Api;
use Cloudflare\User;

/**
 * CloudFlare API wrapper
 *
 * Billing
 * Zone Subscription
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class Zones extends Api
{
    /**
     * List (permission needed: #billing:read)
     * List all of your zone plan subscriptions
     */
    public function list_zones()
    {
        return $this->get('/user/billing/subscriptions/zones');
    }

    /**
     * Search, sort, and paginate (permission needed: #billing:read)
     * Search, sort, and paginate your subscriptions
     *
     * @param int|null    $page         Page number of paginated results
     * @param int|null    $per_page     Number of items per page
     * @param string|null $order        Field to order subscriptions by
     * @param string|null $status       The state of the subscription
     * @param string|null $price        The price of the subscription that will be billed, in US dollars
     * @param string|null $activated_on When the subscription was activated
     * @param string|null $expires_on   When the subscription will expire
     * @param string|null $expired_on   When the subscription expired
     * @param string|null $cancelled_on When the subscription was cancelled
     * @param string|null $renewed_on   When the subscription was renewed
     * @param string|null $direction    Direction to order subscriptions
     * @param string|null $match        Whether to match all search requirements or at least one (any)
     */
    public function search_sort_paginate($page = null, $per_page = null, $order = null, $status = null, $price = null, $activated_on = null, $expires_on = null, $expired_on = null, $cancelled_on = null, $renewed_on = null, $direction = null, $match = null)
    {
        $data = [
            'page'         => $page,
            'per_page'     => $per_page,
            'order'        => $order,
            'status'       => $status,
            'price'        => $price,
            'activated_on' => $activated_on,
            'expires_on'   => $expires_on,
            'expired_on'   => $expired_on,
            'cancelled_on' => $cancelled_on,
            'renewed_on'   => $renewed_on,
            'direction'    => $direction,
            'match'        => $match,
        ];

        return $this->get('/user/billing/subscriptions/zones', $data);
    }

    /**
     * Info (permission needed: #billing:read)
     * Billing subscription details
     *
     * @param string $identifier API item identifier tag
     */
    public function info($identifier)
    {
        return $this->get('/user/billing/subscriptions/zones/'.$identifier);
    }
}
