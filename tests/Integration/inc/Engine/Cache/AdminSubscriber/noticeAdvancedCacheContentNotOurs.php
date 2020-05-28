<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Cache\AdminSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\AdminSubscriber;
use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * @covers WP_Rocket\Engine\Cache\AdminSubscriber::notice_advanced_cache_content_not_ours
 *
 * @group  AdminOnly
 * @group  AdvancedCache
 */
class Test_NoticeAdvancedCacheContentNotOurs extends AdminTestCase {
	private static $subscriber;

	public static function setUpBeforeClass() {
		$container = apply_filters( 'rocket_container', null );
		self::$subscriber = $container->get( 'admin_cache_subscriber' );
	}

	public function testShouldCheckActionIsRegistered() {
		$this->assertSame(
			10,
			has_action( 'admin_notices', [ self::$subscriber, 'notice_advanced_cache_content_not_ours'] )
		);
	}
}
