<?php

/**
 * Class TaskScheduler
 */
abstract class TaskScheduler {
	private static $plugin_file = '';
	/** @var TaskScheduler */
	private static $instance = NULL;
	/** @var TaskScheduler_JobStore */
	private static $store = NULL;

	/**
	 * Get the absolute system path to the plugin directory, or a file therein
	 * @static
	 * @param string $path
	 * @return string
	 */
	public static function plugin_path( $path ) {
		$base = dirname(self::$plugin_file);
		if ( $path ) {
			return trailingslashit($base).$path;
		} else {
			return untrailingslashit($base);
		}
	}

	/**
	 * Get the absolute URL to the plugin directory, or a file therein
	 * @static
	 * @param string $path
	 * @return string
	 */
	public static function plugin_url( $path ) {
		return plugins_url($path, self::$plugin_file);
	}

	/**
	 * Initialize the plugin
	 *
	 * @static
	 * @param string $plugin_file
	 * @return void
	 */
	public static function init( $plugin_file ) {
		self::$plugin_file = $plugin_file;
		TaskScheduler_JobStore::instance();
	}


	final public function __clone() {
		trigger_error("Singleton. No cloning allowed!", E_USER_ERROR);
	}

	final public function __wakeup() {
		trigger_error("Singleton. No serialization allowed!", E_USER_ERROR);
	}

	final private function __construct() {}
}
 