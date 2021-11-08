<?php
/**
 * Mocked wordfence to the minimum requirement for tests to run.
 */
if ( ! class_exists( 'wordfence' ) ) {
	class wordfence {
		public static $white_listed_ips     = [];
		public static function whitelistIP( $IP ) {
			self::$white_listed_ips[]=$IP;
			return true;
		}
		public static function getWhiteListedIPs( ) {

			return self::$white_listed_ips;
		}
	}
}
