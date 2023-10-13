<?php
if ( ! class_exists( 'NinukisCaching' ) ) {
	class NinukisCaching {
		public static function get_instance(): self {
			return new NinukisCaching();
		}

		public function purgeAllCaches() {

		}

		/**
		 * Purge the cache for the given paths.
		 *
		 * @param array $paths Paths that need to be purged.
*/
		public function purge_cache( $urls ) {
		}

		public function purge_page_cache( $id ) {

		}
	}
}
