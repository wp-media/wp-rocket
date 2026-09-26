<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization;

use WP_Rocket\Tests\Integration\FilesystemTestCase;
use WP_Rocket\Tests\Integration\ResetsCdnDriverStateTrait;

abstract class TestCase extends FilesystemTestCase {
	use ResetsCdnDriverStateTrait;

	protected static $use_settings_trait = true;
	protected        $cnames;
	protected        $zones;

	public function set_up() {
		$this->default_vfs_structure = '/vfs-structure/optimizeMinify.php';

		parent::set_up();

		// Also reset before the test runs, in case an earlier, unrelated test file resolved
		// these same container singletons under a different cdn_type first.
		$this->reset_cdn_driver_memo();
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

		// cdn_driver_byocdn / cdn_subscriber are container singletons — reset their memoized
		// state so the next data set (which may use different cdn_cnames) isn't affected.
		$this->reset_cdn_driver_memo();
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
				// DriverFactory resolves the active driver from
				// Context::get_effective_cdn_state(), which needs cdn_type === 'byocdn' to
				// route to the Custom (CNAME) driver these cdn_cnames-based data sets
				// exercise — the default 'rocketcdn' value would otherwise route to the
				// Disabled driver and silently stop the CDN rewrite.
				$func( 'pre_get_rocket_option_cdn_type', [ $this, 'return_byocdn' ] );
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

	/**
	 * @return string
	 */
	public function return_byocdn(): string {
		return 'byocdn';
	}

	public function set_cnames() {
		return $this->cnames;
	}

	public function set_zones() {
		return $this->zones;
	}

	protected function assertFilesExists( $files ) {
		foreach ( $files as $file ) {
			if ( $this->skipGzCheck( $file ) ) {
				continue;
			}

			$this->assertTrue( $this->filesystem->exists( $file ) );
		}
	}

	protected function skipGzCheck( $file ) {
		if ( function_exists( 'gzencode' ) ) {
			return false;
		}

		// If `gzencode()` function does not exist and the file is .gz, skip it.
		return ( substr( $file, - 3 ) === '.gz' );
	}
}
