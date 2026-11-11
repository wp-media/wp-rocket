<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\CDN\Cloudflare;

use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\CDN\Cloudflare::disable_cloudflare_option
 *
 * @group ThirdParty
 * @group CloudflarePlugin
 */
class TestDisableCloudflareOption extends TestCase {
	/**
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * @var Options
	 */
	protected $options_api;

	public function set_up() {
		parent::set_up();

		$container         = apply_filters( 'rocket_container', null );
		$this->options     = $container->get( 'options' );
		$this->options_api = $container->get( 'options_api' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config ) {
		Functions\expect( 'is_plugin_active' )->andReturn( $config['plugin_active'] );

		do_action( 'enable_cloudflare/cloudflare.php' );

		$settings = $this->options_api->get( 'settings' );

		$this->options->set_values( $settings );

		$this->assertFalse( $this->options->get( 'do_cloudflare', false ) );
	}
}
