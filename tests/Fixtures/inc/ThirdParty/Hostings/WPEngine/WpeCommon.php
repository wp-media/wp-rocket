<?php
/**
 * Mocked WpeCommon for WP Engine to the minimum requirement for tests to run.
 */
if ( ! class_exists( 'WpeCommon' ) ) {
	class WpeCommon {
		private static $purge_memcached_called     = 0;
		private static $purge_varnish_cache_called = 0;

		public static function purge_memcached() {
			self::$purge_memcached_called++;
		}

		public static function purge_varnish_cache() {
			self::$purge_varnish_cache_called++;
		}

		public static function resetCounters() {
			self::$purge_memcached_called     = 0;
			self::$purge_varnish_cache_called = 0;
		}

		public static function getNumberTimesPurgeMemcachedCalled() {
			return self::$purge_memcached_called;
		}

		public static function getNumberTimesVarnishCacheCalled() {
			return self::$purge_varnish_cache_called;
		}
	}
}
