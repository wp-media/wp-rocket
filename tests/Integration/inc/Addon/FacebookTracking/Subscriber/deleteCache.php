<?php

namespace WP_Rocket\Tests\Integration\inc\Addon\FacebookTracking\Subscriber;

use WP_Rocket\Tests\Integration\inc\Addon\DeleteTrackingCacheTestCase;

/**
 * @covers \WP_Rocket\Addon\FacebookTracking\Subscriber::delete_cache
 * @group  Addon
 * @group  FacebookTracking
 */
class Test_DeleteCache extends DeleteTrackingCacheTestCase {
	protected $path_to_test_data = '/inc/Addon/FacebookTracking/Subscriber/deleteCache.php';
	protected $option_name       = 'facebook_pixel_cache';
	protected $subscriber_name   = 'facebook_tracking';
	protected $factory_types     = [ 'fbsdk', 'fbpix' ];
}
