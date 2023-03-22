<?php
if ( ! class_exists( 'Ninukis_Plugin' ) ) {
    class Ninukis_Plugin {
		public static function get_instance(): self {
			return new Ninukis_Plugin();
		}

		public function purgeAllCaches() {

		}
    }
}
