<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\Renewal;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\Pricing;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\Renewal;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\License\Renewal::add_localize_script_data
 *
 * @group License
 */
class AddLocalizeScriptData extends TestCase {
	private $pricing;
	private $user;
	private $renewal;

	public function setUp() : void {
		parent::setUp();

		$this->pricing = Mockery::mock( Pricing::class );
		$this->user    = Mockery::mock( User::class );
		$this->renewal =  new Renewal(
			$this->pricing,
			$this->user,
			Mockery::mock( Options_Data::class ),
			'views'
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $data, $expected ) {
		$this->user->shouldReceive( 'is_auto_renew' )
			->atMost()
			->once()
			->andReturn( $config['auto_renew'] );

		$this->user->shouldReceive( 'is_license_expired' )
			->atMost()
			->once()
			->andReturn( $config['license_expired'] );

		$this->user->shouldReceive( 'get_license_expiration' )
			->atMost()
			->twice()
			->andReturn( $config['licence_expiration'] );

		$this->assertSame(
			$expected,
			$this->renewal->add_localize_script_data( $data )
		);
	}
}
