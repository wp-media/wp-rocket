<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\StudioPress;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\ThirdParty\Hostings\StudioPress;

/**
 * Test class covering the always-on registration contract of \WP_Rocket\ThirdParty\Hostings\StudioPress
 *
 * @group  StudioPress
 * @group  ThirdParty
 */
class Test_HooksAreRegistered extends TestCase {

	public function testShouldRegisterBothHooksRegardlessOfHostResolver() {
		$container = apply_filters( 'rocket_container', '' );

		$this->assertTrue( $container->has( 'studiopress_accelerator' ) );

		$subscriber = $container->get( 'studiopress_accelerator' );

		$this->assertInstanceOf( StudioPress::class, $subscriber );

		$this->assertNotFalse(
			has_action( 'admin_init', [ $subscriber, 'clear_cache_after_accelerator' ] )
		);

		$this->assertNotFalse(
			has_action( 'rocket_after_clean_domain', [ $subscriber, 'clean_accelerator_cache' ] )
		);
	}
}
