<?php
/**
 * Mocked WpeCommon for WP Engine to the minimum requirement for tests to run.
 */
class WpeCommon {
	public static function purge_memcached() {}

	public static function purge_varnish_cache() {}
}
