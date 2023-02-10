<?php

namespace WP_Rocket\Tests\Integration\inc\Addons\Cloudflare\Subscriber;

use Brain\Monkey\Functions;

/**
 * @covers \WPMedia\Cloudflare\Subscriber::auto_purge
 * @group  DoCloudflare
 * @group  Addons
 */
class Test_AutoPurge extends TestCase {

	public function testShouldBailoutWhenUserCantPurgeCF() {
		$this->setApiCredentialsInOptionsWithFilter();

		$user = $this->factory->user->create( [ 'role' => 'contributor' ] );
		wp_set_current_user( $user );

		Functions\expect( 'is_wp_error' )->never();

		do_action( 'after_rocket_clean_domain' );
	}

	public function testShouldBailoutWhenNoPageRule() {
		$this->setApiCredentialsInOptions();

		$admin = get_role( 'administrator' );
		$admin->add_cap( 'rocket_purge_cloudflare_cache' );
		$user = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user );

		Functions\expect( 'is_wp_error' )
			->ordered()
			->once()
			->with( null )
			->andAlsoExpectIt()
			->once()
			->with( 0 );

		do_action( 'after_rocket_clean_domain' );
	}
}
