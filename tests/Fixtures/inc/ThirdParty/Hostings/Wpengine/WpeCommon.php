<?php
/**
 * Mocked WpeCommon for WP Engine to the minimum requirement for tests to run.
 */
if ( ! class_exists( 'WpeCommon') ) {
	class WpeCommon {
		public static function purge_memcached() {}

		public static function purge_varnish_cache() {}
	}
}
