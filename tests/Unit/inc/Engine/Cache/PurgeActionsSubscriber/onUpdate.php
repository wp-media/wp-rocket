<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\PurgeActionsSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Cache\{PurgeActionsSubscriber,Purge};
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Cache\PurgeActionsSubscriber::on_update
 */
class Test_onUpdate extends TestCase {
	private $subscriber;

	public function setUp(): void {
		parent::setUp();

		Functions\stubTranslationFunctions();

		$this->subscriber = new PurgeActionsSubscriber(
			Mockery::mock( Options_Data::class ),
			Mockery::mock( Purge::class )
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
