<?php

namespace Smush\Core;

class Settings {
	protected static $instance;
	protected $lazyload_enabled;
	protected $lazyload_formats;

	public static function get_instance() {
		if ( ! isset( static::$instance ) ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	public function get( $option ) {
		return 'lazy_load' === $option ? (bool) $this->lazyload_enabled : false;
	}

	public function get_setting( $option ) {
		return 'wp-smush-lazy_load' === $option ? [ 'format' => $this->lazyload_formats ] : [];
	}

	public function set_settings( $lazyload_enabled, array $lazyload_formats ) {
		$this->lazyload_enabled = $lazyload_enabled;
		$this->lazyload_formats = $lazyload_formats;
	}
}
