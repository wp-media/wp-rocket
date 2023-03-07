<?php

namespace WP_Rocket\Tests\Integration\inc\Addons\Cloudflare\Subscriber;

use Brain\Monkey\Functions;
use Exception;
use WP_Rocket\Tests\Integration\ActionTrait;

/**
 * @covers \WPMedia\Cloudflare\Subscriber::auto_purge
 * @group  DoCloudflare
 * @group  Addons
 */
class Test_AutoPurge extends TestCase {
	use ActionTrait;
	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::setApiCredentialsInOptionsWithFilter();
	}

	public function set_up()
	{
		parent::set_up();
		$this->unregisterAllCallbacksActionsExcept('after_rocket_clean_domain', 'auto_purge');
	}

	public function tear_down()
	{
		$this->restoreWpAction('after_rocket_clean_domain');
		parent::tear_down();
	}

	public function testShouldBailoutWhenUserCantPurgeCF() {

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
