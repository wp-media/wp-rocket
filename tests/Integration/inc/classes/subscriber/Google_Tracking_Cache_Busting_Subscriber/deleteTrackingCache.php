<?php

namespace WP_Rocket\Tests\Integration\inc\classes\subscriber\Google_Tracking_Cache_Busting_Subscriber;

use WP_Rocket\Tests\Integration\inc\classes\subscriber\DeleteTrackingCacheTestCase;

/**
 * @covers \WP_Rocket\Subscriber\Google_Tracking_Cache_Busting_Subscriber::delete_tracking_cache
 * @group  ThirdParty
 * @group  GoogleTracking
 */
class Test_DeleteTrackingCache extends DeleteTrackingCacheTestCase {
	protected $path_to_test_data = '/inc/classes/subscriber/Google_Tracking_Cache_Busting_Subscriber/deleteTrackingCache.php';
	protected $option_name       = 'google_analytics_cache';
	protected $subscriber_name   = 'google_tracking_subscriber';
	protected $factory_types     = [ 'ga', 'gtm' ];
}
