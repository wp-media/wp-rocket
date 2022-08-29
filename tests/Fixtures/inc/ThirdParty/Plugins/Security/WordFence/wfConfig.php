<?php
/**
 * Mocked wfConfig to the minimum requirement for tests to run.
 */
if ( ! class_exists( 'wfconfig' ) ) {
	class wfConfig {

		public static $whitelisted     = [
            'whitelisted' => ''
        ];
        
		public static function get( $key, $default = '' ) {
			return self::$whitelisted[$key];
		}

        public static function set( $key, $value ) {
			self::$whitelisted[$key] = $value;
		}
	}
}
