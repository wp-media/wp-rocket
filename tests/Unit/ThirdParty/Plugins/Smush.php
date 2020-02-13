<?php

namespace WP_Rocket\Tests\Unit\ThirdParty\Plugins\Smush;

use Brain\Monkey\Functions;
use WP_Rocket\Subscriber\Third_Party\Plugins\Smush_Subscriber;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers Smush_Subscriber::is_smush_lazyload_active
 * @group ThirdParty
 * @group Smush
 */
class Smush extends TestCase {

	protected function setUp() {
		parent::setUp();

		Functions\expect( 'rocket_get_constant' )
			->with( 'WP_SMUSH_PREFIX' )
			->andReturn( 'wp-smush-' );
	}

	public function testShouldDisableWPRocketLazyLoad() {
		$this->mockCommonWpFunctions();

		$subscriber = new Smush_Subscriber();

		Functions\expect( 'get_option' )
			->once() // called once
			->andReturn( [ 'lazy_load' => true ] );

		$this->assertContains( 'Smush', $subscriber->is_smush_lazyload_active( [] ) );
	}

	public function testShouldNotDisableWPRocketLazyLoad() {
		$subscriber = new Smush_Subscriber();

		Functions\expect( 'get_option' )
			->once() // called once
			->andReturn( [] );

		$this->assertEmpty( $subscriber->is_smush_lazyload_active( [] ) );
	}

	public function testShouldNotMaybeDeactivateLazyload() {
		$subscriber = new Smush_Subscriber();

		Functions\expect( 'get_option' )
			->once() // called once
			->andReturn();

		Functions\expect( 'update_rocket_option' )->never();

		$subscriber->maybe_deactivate_rocket_lazyload();
	}

	public function testShouldMaybeDeactivateLazyload() {
		$subscriber = new Smush_Subscriber();

		Functions\expect( 'get_option' )
			->once() // called once
			->andReturn( [ 'lazy_load' => true ] );

		Functions\expect( 'update_rocket_option' )
			->once()
			->with( 'lazyload', '0' );

		$subscriber->maybe_deactivate_rocket_lazyload();
	}
}
