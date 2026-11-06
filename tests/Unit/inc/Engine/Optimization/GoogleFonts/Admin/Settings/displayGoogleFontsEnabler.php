<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\GoogleFonts\Admin\Settings;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Optimization\GoogleFonts\Admin\Settings;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\GoogleFonts\Admin\Settings::enable_google_fonts()
 *
 * @group  GoogleFontsAdmin
 */
class Test_DisplayGoogleFontsEnabler extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/GoogleFonts/Admin/Settings/displayGoogleFontsEnabler.php';
	private $beacon;
	private $options;
	private $settings;

	public function setUp() : void {
		parent::setUp();

		$this->beacon       = Mockery::mock( Beacon::class );
		$this->options      = Mockery::mock( Options_Data::class );
		$this->settings = new Settings(
			$this->options,
			$this->beacon,
			'wp-content/plugins/wp-rocket/views'
		);

		Functions\when( 'check_ajax_referer' )->justReturn( true );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testEnableGoogleFonts( $config, $expect ) {
		Functions\when( 'current_user_can' )->justReturn( $config['user-can'] );
		Filters\expectApplied( 'pre_get_rocket_option_minify_google_fonts' )
			->with( true )
			->andReturn( $config['filter-not-set'] );

		if ( ! $config['user-can'] ) {
			$this->shouldBail();
			return;
		}

		if ( ! $config['filter-not-set'] ) {
			$this->shouldBail();
			return;
		}

		$this->options->shouldReceive( 'get' )
		              ->with( 'minify_google_fonts', 0 )
		              ->andReturn( $config['gf-minify'] );

		if ( $config['gf-minify'] ) {
			$this->shouldBail();
			return;
		}

		$this->shouldDisplay( $config, $expect );
	}

	public function shouldBail() {
		$this->beacon->shouldReceive( 'get_suggest' )->never();
		$this->options->shouldReceive( 'get' )->never();

		$this->settings->display_google_fonts_enabler();
	}

	public function shouldDisplay( $config, $expect ) {
		$this->beacon->shouldReceive( 'get_suggest' )
		             ->once()
		             ->with( 'google_fonts' )
		             ->andReturn(
			             [
				             'en' => [
					             'id'  => '5e8687c22c7d3a7e9aea4c4a',
					             'url' => 'https://docs.wp-rocket.me/article/1312-optimize-google-fonts',
				             ],
				             'fr' => [
					             'id'  => '5e970f512c7d3a7e9aeaf9fb',
					             'url' => 'https://fr.docs.wp-rocket.me/article/1314-optimiser-les-google-fonts/?utm_source=wp_plugin&utm_medium=wp_rocket',
				             ],
			             ]
		             );

		$this->settings->display_google_fonts_enabler();
	}
}
