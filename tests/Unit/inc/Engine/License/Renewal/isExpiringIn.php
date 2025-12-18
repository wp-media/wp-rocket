<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\Renewal;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\Pricing;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\Renewal;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\Renewal::is_expiring_in
 *
 * @group License
 */
class IsExpiringIn extends TestCase {
	private $pricing;
	private $user;
	private $renewal;

	public function setUp(): void {
		parent::setUp();

		$this->pricing = Mockery::mock( Pricing::class );
		$this->user    = Mockery::mock( User::class );
		$this->renewal = new Renewal(
			$this->pricing,
			$this->user,
			Mockery::mock( Options_Data::class ),
			'views'
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->user->shouldReceive( 'get_license_expiration' )
			->once()
			->andReturn( $config['license_expiration'] );

		$result = $this->renewal->is_expiring_in( $config['duration_in_days'] );

		$this->assertSame( $expected, $result );
	}
}
