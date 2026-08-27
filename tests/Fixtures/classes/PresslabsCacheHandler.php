<?php
namespace Presslabs\Cache;

if ( ! class_exists( __NAMESPACE__ . '\CacheHandler' ) ) {
	class CacheHandler {
		public function invalidate_url( $url, $purge = false ) {
		}

		public function purge_cache( $type ) {
		}
	}
}
