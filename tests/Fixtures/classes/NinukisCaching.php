<?php
if ( ! class_exists( 'NinukisCaching' ) ) {
	class NinukisCaching {
		public static function get_instance(): self {
			return new NinukisCaching();
		}

		public function purgeAllCaches() {

		}

		public function purge_cache( $url ) {

		}

		public function purge_page_cache( $id ) {

		}
	}
}
