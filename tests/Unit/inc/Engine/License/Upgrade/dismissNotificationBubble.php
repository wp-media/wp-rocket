<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\Upgrade;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\License\API\Pricing;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\Upgrade;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\Upgrade::dismiss_notification_bubble
 *
 * @group License
 */
class DismissNotificationBubble extends TestCase {
	private $pricing;
	private $user;
	private $upgrade;

	public function setUp() : void {
		parent::setUp();

		$this->pricing = Mockery::mock( Pricing::class );
		$this->user    = Mockery::mock( User::class );
		$this->upgrade =  new Upgrade(
			$this->pricing,
			$this->user,
			'views'
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->user->shouldReceive( 'get_license_type' )
			->atMost()
			->once()
			->andReturn( $config['licence_account'] );

		$this->user->shouldReceive( 'is_license_expired' )
			->atMost()
			->once()
			->andReturn( $config['licence_expired'] );

		$this->user->shouldReceive( 'get_creation_date' )
		           ->atMost()
		           ->once()
		           ->andReturn( $config['date_created'] );

		$this->user->shouldReceive( 'get_license_expiration' )
			->atMost()
			->once()
			->andReturn( $config['licence_expiration'] );

		$this->pricing->shouldReceive( 'is_promo_active' )
			->atMost()
			->once()
			->andReturn( $config['promo_active'] );

		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\expect( 'get_transient' )
			->atMost()
			->once()
			->with( 'rocket_promo_seen_1' )
            ->andReturn( $config['transient'] );

        if ( $expected ) {
            Functions\expect( 'set_transient' )
                ->once()
                ->with( 'rocket_promo_seen_1', 1, 2 * WEEK_IN_SECONDS );
        } else {
            Functions\expect( 'set_transient' )->never();
        }

		$this->upgrade->dismiss_notification_bubble();
	}
}
