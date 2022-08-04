<?php
/**
 * Mocked wfConfig to the minimum requirement for tests to run.
 */
if ( ! class_exists( 'wfconfig' ) ) {
	class wfConfig {

		public static $get_list     = [
            'whitelisted' => ''
        ];

        public static $white_listed = [];
        
		public static function get( $key, $default = '' ) {
			return self::$get_list[$key];
		}

        public static function set( $key, $value ) {
			self::$white_listed[$key] = $value;
		}
	}
}
