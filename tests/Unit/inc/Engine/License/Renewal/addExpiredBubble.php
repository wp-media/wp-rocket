<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\Renewal;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\{Pricing, User};
use WP_Rocket\Engine\License\Renewal;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\License\Renewal::add_expired_bubble
 *
 * @group License
 */
class Test_AddExpiredBubble extends TestCase {
	private $pricing;
	private $user;
	private $renewal;
	private $options;

	protected function setUp(): void {
		parent::setUp();

		$this->pricing = Mockery::mock( Pricing::class );
		$this->user    = Mockery::mock( User::class );
		$this->options =Mockery::mock( Options_Data::class );
		$this->renewal = new Renewal(
				$this->pricing,
				$this->user,
				$this->options,
				'views'
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $title, $expected ) {
		$this->user->shouldReceive( 'is_license_expired' )
			->andReturn( $config['expired'] );

		$this->user->shouldReceive( 'is_auto_renew' )
			->andReturn( $config['auto_renew'] );

		$this->user->shouldReceive( 'get_license_expiration' )
			->andReturn( $config['expire_date'] );

		$this->options->shouldReceive( 'get' )
			->with( 'optimize_css_delivery', 0 )
			->andReturn( $config['ocd'] );

		Functions\when( 'get_transient' )->justReturn( $config['transient'] );
		Functions\when( 'get_current_user_id' )->justReturn( 1 );

		$this->assertSame(
			$expected,
			$this->renewal->add_expired_bubble( $title )
		);
	}
}
