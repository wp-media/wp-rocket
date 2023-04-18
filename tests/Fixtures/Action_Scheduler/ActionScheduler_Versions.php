<?php
if( ! class_exists( 'ActionScheduler_Versions' ) ) {
	class ActionScheduler_Versions {
		private static $instance = NULL;
		private $versions = array();

		public static function instance() {
			if ( empty(self::$instance) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}
		public function register( $version_string, $initialization_callback ) {
			if ( isset($this->versions[$version_string]) ) {
				return FALSE;
			}
			$this->versions[$version_string] = $initialization_callback;
			return TRUE;
		}
		public function latest_version() {
			$keys = array_keys($this->versions);
			if ( empty($keys) ) {
				return false;
			}
			uasort( $keys, 'version_compare' );
			return end($keys);
		}
	}
}
