<?php

namespace WP_Rocket\Tests\Integration\inc\Addon\GoogleTracking\Subscriber;

use WP_Rocket\Tests\Integration\inc\Addon\DeleteTrackingCacheTestCase;

/**
 * @covers \WP_Rocket\Addon\GoogleTracking\Subscriber::delete_tracking_cache
 * @group  Addon
 * @group  GoogleTracking
 */
class Test_DeleteTrackingCache extends DeleteTrackingCacheTestCase {
	protected $path_to_test_data = '/inc/Addon/GoogleTracking/Subscriber/deleteTrackingCache.php';
	protected $option_name       = 'google_analytics_cache';
	protected $subscriber_name   = 'google_tracking';
	protected $factory_types     = [ 'gtm', 'ga' ];
}
