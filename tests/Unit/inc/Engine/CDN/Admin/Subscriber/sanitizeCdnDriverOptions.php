<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Admin\Subscriber;

use Mockery;
use WP_Rocket\Engine\CDN\Admin\{
	Settings,
	Subscriber
};
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Admin\Settings\Settings as AdminSettings;

/**
 * @covers \WP_Rocket\Engine\CDN\Admin\Subscriber::sanitize_cdn_driver_options
 * @group  CDN
 */
class Test_SanitizeCdnDriverOptions extends TestCase {

	private $subscriber;
	private $settings;
	private $admin_settings;

	public function set_up() {
		parent::set_up();
		
		$this->admin_settings = Mockery::mock( AdminSettings::class );

		$this->settings   = Mockery::mock( Settings::class );
		$this->subscriber = new Subscriber( $this->settings );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedValue( array $config, array $expected ) {
		$this->admin_settings->shouldReceive( 'sanitize_checkbox' )
			->with( $config['input'], 'byocdn' )
			->andReturn( $expected['byocdn'] );

		$this->admin_settings->shouldReceive( 'sanitize_checkbox' )
			->with( Mockery::any(), 'rocketcdn' )
			->andReturn( $expected['rocketcdn'] );

		$result = $this->subscriber->sanitize_cdn_driver_options( $config['input'], $this->admin_settings );

		$this->assertSame( $expected['byocdn'], $result['byocdn'] );
		$this->assertSame( $expected['rocketcdn'], $result['rocketcdn'] );
	}
}
