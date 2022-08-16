<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\Renewal;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\Pricing;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\Renewal;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\License\Renewal::add_license_expire_warning
 *
 * @group License
 */
class Test_AddLicenseExpireWarning extends TestCase {
	private $pricing;
	private $user;
	private $renewal;
	private $options;

	protected function setUp(): void {
		parent::setUp();

		$this->stubEscapeFunctions();
		$this->stubTranslationFunctions();

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
	public function testShouldReturnExpected( $config, $args, $expected ) {
		$this->white_label = $config['white_label'];

		Functions\when( 'get_user_locale' )->justReturn( 'en_US' );

		$this->user->shouldReceive( 'is_license_expired' )
			->andReturn( $config['expired'] );

		$this->user->shouldReceive( 'is_auto_renew' )
			->andReturn( $config['auto_renew'] );

		$this->user->shouldReceive( 'get_license_expiration' )
			->andReturn( $config['expire_date'] );

		$this->user->shouldReceive( 'get_renewal_url' )
			->andReturn( $config['renewal_url'] );

		$this->options->shouldReceive( 'get' )
			->with( 'optimize_css_delivery', 0 )
			->andReturn( $config['ocd'] );

		$this->assertSame(
			$expected,
			$this->renewal->add_license_expire_warning( $args )
		);
	}
}
