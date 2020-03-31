<?php

namespace WP_Rocket\Tests\Integration\inc\classes\subscriber\FacebookTrackingCacheBustingSubscriber;

use WP_Rocket\Tests\Integration\inc\classes\subscriber\DeleteTrackingCacheTestCase;

/**
 * @covers \WP_Rocket\Subscriber\Facebook_Tracking_Cache_Busting_Subscriber::delete_cache
 * @group  ThirdParty
 * @group  FacebookTracking
 */
class Test_DeleteCache extends DeleteTrackingCacheTestCase {
	protected $path_to_test_data = '/inc/classes/subscriber/FacebookTrackingCacheBustingSubscriber/deleteCache.php';
	protected $option_name       = 'facebook_pixel_cache';
	protected $subscriber_name   = 'facebook_tracking_subscriber';
	protected $factory_types     = [ 'fbsdk', 'fbpix' ];
}
