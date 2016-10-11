<?php

namespace Cloudflare\Zone;

use Cloudflare\Api;

/**
 * CloudFlare API wrapper
 *
 * Analytics
 * CloudFlare Analytics
 *
 * @author James Bell <james@james-bell.co.uk>
 *
 * @version 1
 */
class Analytics extends Api
{
    /**
     * Dashboard (permission needed: #analytics:read)
     * The dashboard view provides both totals and timeseries data for the given zone and time period across the entire CloudFlare network.
     *
     * @param string          $zone_identifier
     * @param string|int|null $since           The (inclusive) beginning of the requested time frame. This value can be a negative integer representing the number of minutes in the past relative to time the request is made,
     *                                         or can be an absolute timestamp that conforms to RFC 3339. At this point in time, it cannot exceed a time in the past greater than one year.
     * @param string|int|null $until           The (exclusive) end of the requested time frame. This value can be a negative integer representing the number of minutes in the past relative to time the request is made,
     *                                         or can be an absolute timestamp that conforms to RFC 3339. If omitted, the time of the request is used.
     * @param bool            $continuous      When set to true, the range returned by the response acts like a sliding window to provide a contiguous time-window.
     *                                         Analytics data is processed and aggregated asynchronously and can sometimes lead to recent data points being incomplete if this value is set to false.
     *                                         If a start date provided is earlier than a date for which data is available, the API will return 0's for those dates until the first available date with data
     */
    public function dashboard($zone_identifier, $since = null, $until = null, $continuous = null)
    {
        $data = [
            'since'      => $since,
            'until'      => $until,
            'continuous' => $continuous,
        ];

        return $this->get('zones/'.$zone_identifier.'/analytics/dashboard', $data);
    }

    /**
     * Analytics by Co-locations (permission needed: #analytics:read)
     * This view provides a breakdown of analytics data by datacenter. Note: This is available to Enterprise customers only.
     *
     * @param string          $zone_identifier
     * @param string|int|null $since           The (inclusive) beginning of the requested time frame. This value can be a negative integer representing the number of minutes in the past relative to time the request is made,
     *                                         or can be an absolute timestamp that conforms to RFC 3339. At this point in time, it cannot exceed a time in the past greater than one year.
     * @param string|int|null $until           The (exclusive) end of the requested time frame. This value can be a negative integer representing the number of minutes in the past relative to time the request is made,
     *                                         or can be an absolute timestamp that conforms to RFC 3339. If omitted, the time of the request is used.
     * @param bool            $continuous      When set to true, the range returned by the response acts like a sliding window to provide a contiguous time-window.
     *                                         Analytics data is processed and aggregated asynchronously and can sometimes lead to recent data points being incomplete if this value is set to false.
     *                                         If a start date provided is earlier than a date for which data is available, the API will return 0's for those dates until the first available date with data
     */
    public function colos($zone_identifier, $since = null, $until = null, $continuous = null)
    {
        $data = [
            'since'      => $since,
            'until'      => $until,
            'continuous' => $continuous,
        ];

        return $this->get('zones/'.$zone_identifier.'/analytics/colos', $data);
    }
}
