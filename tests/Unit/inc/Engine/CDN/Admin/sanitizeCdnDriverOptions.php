<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Admin;

use Mockery;
use WP_Rocket\Engine\Admin\Settings\Settings;
use WP_Rocket\Engine\CDN\Admin\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Admin\Subscriber::sanitize_cdn_driver_options
 * @group  CDN
 * @group  AdminOnly
 */
class Test_SanitizeCdnDriverOptions extends TestCase {

	private $subscriber;
	private $settings;

	public function set_up() {
		parent::set_up();

		$this->subscriber = new Subscriber();
		$this->settings   = Mockery::mock( Settings::class );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedValue( array $config, array $expected ) {
		$this->settings->shouldReceive( 'sanitize_checkbox' )
			->with( $config['input'], 'byocdn' )
			->andReturn( $expected['byocdn'] );

		$this->settings->shouldReceive( 'sanitize_checkbox' )
			->with( Mockery::any(), 'rocketcdn' )
			->andReturn( $expected['rocketcdn'] );

		$result = $this->subscriber->sanitize_cdn_driver_options( $config['input'], $this->settings );

		$this->assertSame( $expected['byocdn'], $result['byocdn'] );
		$this->assertSame( $expected['rocketcdn'], $result['rocketcdn'] );
	}
}
