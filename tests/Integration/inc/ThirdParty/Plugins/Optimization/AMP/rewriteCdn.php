<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Optimization\AMP;

use AMP_Options_Manager;
use AMP_Theme_Support;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Optimization\AMP::rewrite_cdn
 * @group  ThirdParty
 * @group  WithAmp
 */
class Test_RewriteCdn extends TestCase {
	protected $cnames;
	protected $cdn_zone;
	protected $cdn_option;

	public function setUp() {
		parent::setUp();

		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}
		// Updating the AMP settings will trigger this to run.
		Functions\when( 'rocket_generate_config_file' )->justReturn();
	}

	public function tearDown() {
		remove_filter( 'pre_get_rocket_option_cdn', [ $this, 'setCdnOption' ] );
		remove_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'setCnames' ] );
		remove_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'setCDNZone' ] );

		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->cnames     = $config['cdn_cnames']['value'];
		$this->cdn_zone   = $config['cdn_zone']['value'];
		$this->cdn_option = $config['cdn']['value'];

		add_filter( 'pre_get_rocket_option_cdn', [ $this, 'setCdnOption' ] );
		add_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'setCnames' ] );
		add_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'setCDNZone' ] );

		// Set and then check the AMP theme support setting.
		if ( ! is_null( $config['amp_options']['theme_support'] ) ) {
			add_theme_support( AMP_Theme_Support::SLUG );

			$this->setSettings( 'theme_support', $config['amp_options']['theme_support'] );
			$options = get_option( AMP_Options_Manager::OPTION_NAME );
			$this->assertEquals( $config['amp_options']['theme_support'], $options['theme_support'] );
		} else {
			delete_option( AMP_Options_Manager::OPTION_NAME );
		}

		$html_expected = $this->config['original'];
		if ( $expected[ 'shouldRewrite' ] ) {
			$html_expected = $this->config['rewrite'];
		}

		do_action( 'wp' );

		$this->assertSame(
			$this->format_the_html( $html_expected ),
			$this->format_the_html( apply_filters( 'rocket_buffer', $this->config['original'] ) )
		);
	}

	public function setCdnOption() {
		return $this->cdn_option;
	}

	public function setCnames() {
		return $this->cnames;
	}

	public function setCDNZone() {
		return $this->cdn_zone;
	}

	public function providerTestData() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data'];
	}

	private function loadConfig() {
		$this->config = $this->getTestData( __DIR__, 'rewriteCdn' );
	}
}
