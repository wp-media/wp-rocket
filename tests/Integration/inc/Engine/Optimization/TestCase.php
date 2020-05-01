<?php


namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

abstract class TestCase extends FilesystemTestCase {
	protected $cnames;
	protected $zones;
	protected   $settings;

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
		$func = $set ? 'add_filter' : 'remove_filter';
		$callback = $value === 0 ? 'return_false' : 'return_true';

		switch( $key ) {
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
}
