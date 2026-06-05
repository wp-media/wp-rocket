<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Subscriber;

/**
 * @covers \WP_Rocket\Engine\CDN\Subscriber::add_cdn_driver_options_on_update
 * @group  CDN
 */
class Test_AddCdnDriverOptionsOnUpdate extends TestCase {

	private $hook_name = 'wp_rocket_upgrade';
	private $options_api;
	protected $config;

	public function set_up() {
		parent::set_up();

		$container         = apply_filters( 'rocket_container', null );
		$this->options_api = $container->get( 'options_api' );

		$this->unregisterAllCallbacksExcept( $this->hook_name, 'add_cdn_driver_options_on_update', 10 );
	}

	public function tear_down() {
		$settings = $this->options_api->get( 'settings', [] );
		unset( $settings['byocdn'], $settings['rocketcdn'] );
		$this->options_api->set( 'settings', $settings );

		$this->restoreWpHook( $this->hook_name );
		parent::tear_down();
	}

	public function configTestData() {
		if ( empty( $this->config ) ) {
			$this->loadTestDataConfig();
		}

		return isset( $this->config['test_data'] )
			? $this->config['test_data']
			: $this->config;
	}

	protected function loadTestDataConfig() {
		$obj            = new \ReflectionObject( $this );
		$filename       = $obj->getFileName();
		$this->config   = $this->getTestData( dirname( $filename ), basename( $filename, '.php' ) );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, array $expected ) {
		do_action( $this->hook_name, $config['new_version'], $config['old_version'] );

		$settings = $this->options_api->get( 'settings', [] );

		foreach ( $expected as $key => $value ) {
			switch ( $key ) {
				case 'byocdn':
					$this->assertSame( $value, $settings['byocdn'] ?? null );
					break;
				case 'rocketcdn':
					$this->assertSame( $value, $settings['rocketcdn'] ?? null );
					break;
				case 'no_change':
					$this->assertArrayNotHasKey( 'byocdn', $settings );
					$this->assertArrayNotHasKey( 'rocketcdn', $settings );
					break;
			}
		}
	}
}
