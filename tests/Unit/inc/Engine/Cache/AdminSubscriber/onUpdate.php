<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\AdminSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Cache\{AdminSubscriber,AdvancedCache,WPCache};
use WP_Rocket\Tests\Fixtures\WP_Filesystem_Direct;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers WP_Rocket\Engine\Cache\AdminSubscriber::on_update
 */
class Test_onUpdate extends TestCase {
	private $subscriber;

	public function setUp(): void {
		parent::setUp();

		Functions\stubTranslationFunctions();

		$this->subscriber = new AdminSubscriber(
			Mockery::mock( AdvancedCache::class ),
			Mockery::mock( WPCache::class ),
			Mockery::mock( WP_Filesystem_Direct::class )
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config )
	{
		if ( $config['is_superior'] ) {
			Functions\expect( 'rocket_generate_advanced_cache_file' )->never();
		} else {
			Functions\expect( 'rocket_generate_advanced_cache_file' )->once();
		}

		$this->subscriber->on_update( $config['new_version'], $config['old_version'] );
	}
}
