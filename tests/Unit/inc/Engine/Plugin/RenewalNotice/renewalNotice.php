<?php

namespace WP_Rocket\Tests\Unit\Inc\Engine\Plugin\RenewalNotice;

use Mockery;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\Plugin\RenewalNotice;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Plugin\RenewalNotice::renewal_notice
 *
 * @group Plugin
 */
class TestRenewalNotice extends TestCase {
	private $user;
	private $renewal;

	protected function setUp(): void {
		parent::setUp();

		$this->user    = Mockery::mock( User::class );
		$this->renewal = Mockery::mock(
			RenewalNotice::class . '[generate]',
			[
				$this->user,
				'views/plugins',
			]
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->rocket_version = $config['current_version'];

		$this->user->shouldReceive( 'is_license_expired' )
			->once()
			->andReturn( $config['license_expired'] );

		if ( ! empty( $expected ) ) {
			$this->user->shouldReceive( 'get_renewal_url' )
				->once()
				->andReturn( $config['renewal_url'] );

			$this->renewal->shouldReceive( 'generate' )
				->once()
				->with(
					'update-renewal-expired-notice',
					$expected['data']
				);
		} else {
			$this->renewal->shouldReceive( 'generate' )
				->never();
		}

		$this->renewal->renewal_notice( $config['version'] );
	}
}
