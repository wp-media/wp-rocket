<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\AdminSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Cache\AdminSubscriber;
use WP_Rocket\Engine\Cache\AdvancedCache;
use WP_Rocket\Engine\Cache\WPCache;
use WP_Rocket\Tests\Unit\TestCase;
use function Brain\Monkey\Functions;


/**
 * @covers WP_Rocket\Engine\Cache\AdminSubscriber::add_purge_term_link
 */
class Test_onUpdate extends TestCase {

	private $subscriber;

	public function setUp() : void {
		parent::setUp();

		Functions\stubTranslationFunctions();

		$this->subscriber = new AdminSubscriber(
			Mockery::mock( AdvancedCache::class ),
			Mockery::mock( WPCache::class )
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config )
	{
		if( $config['is_superior'] ) {
			Functions\expect('rocket_generate_advanced_cache_file')->never();
		} else {
			Functions\expect('rocket_generate_advanced_cache_file');
		}
		$this->subscriber->on_update( $config['new_version'], $config['old_version'] );
	}
}
