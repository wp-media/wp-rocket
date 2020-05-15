<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

abstract class TestCase extends FilesystemTestCase {
	protected        $cnames;
	protected        $zones;
	protected        $settings;
	protected static $original_settings;
	protected        $old_settings = [];

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$original_settings = (array) get_option( 'wp_rocket_settings', [] );
	}

	public static function tearDownAfterClass() {
		parent::setUpBeforeClass();

		update_option( 'wp_rocket_settings', self::$original_settings );
	}

	public function setUp() {
		$this->wp_content_dir = 'vfs://public/wp-content';

		parent::setUp();

		$this->old_settings = array_key_exists( 'settings', $this->config )
			? array_merge( self::$original_settings, $this->config['settings'] )
			: self::$original_settings;
		update_option( 'wp_rocket_settings', $this->old_settings );
	}

	public function tearDown() {
		parent::tearDown();

		delete_option( 'wp_rocket_settings' );
	}

	protected function setSettings() {
		foreach ( (array) $this->settings as $key => $value ) {
			$this->handleSetting( $key, $value );
		}
	}

	protected function unsetSettings() {
		foreach ( (array) $this->settings as $key => $value ) {
			$this->handleSetting( $key, $value, false );
		}
	}

	protected function handleSetting( $key, $value, $set = true ) {
		$func     = $set ? 'add_filter' : 'remove_filter';
		$callback = $value === 0 ? 'return_false' : 'return_true';

		switch ( $key ) {
			case 'minify_concatenate_css':
				$func( 'pre_get_rocket_option_minify_concatenate_css', [ $this, $callback ] );
				break;

			case 'minify_concatenate_js':
				$func( 'pre_get_rocket_option_minify_concatenate_js', [ $this, $callback ] );
				break;

			case 'cdn':
				$func( 'pre_get_rocket_option_cdn', [ $this, $callback ] );
				break;

			case 'cdn_cnames':
				$this->cnames = $value;
				$func( 'pre_get_rocket_option_cdn_cnames', [ $this, 'set_cnames' ] );
				break;

			case 'cdn_zone':
				$this->zones = $value;
				$func( 'pre_get_rocket_option_cdn_zone', [ $this, 'set_zones' ] );
		}
	}

	public function return_key() {
		return 123456;
	}

	public function set_cnames() {
		return $this->cnames;
	}

	public function set_zones() {
		return $this->zones;
	}

	protected function skipGzCheck( $file ) {
		if ( function_exists( 'gzencode' ) ) {
			return false;
		}

		// If `gzencode()` function does not exist and the file is .gz, skip it.
		return ( substr( $file, - 3 ) === '.gz' );
	}
}
