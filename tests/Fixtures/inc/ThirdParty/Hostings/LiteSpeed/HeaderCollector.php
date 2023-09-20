<?php
/**
 * Class HeaderCollector
 * Using this in combination with function header override
 * for the namespace My\Application\Namespace
 * we can make assertions on headers sent
 */
if ( ! class_exists( 'HeaderCollector' ) ) {
	class HeaderCollector {

		public static $headers = [];

		//call this in your test class setUp so headers array is clean before each test
		public static function clean() {
			self::$headers = [];
		}
	}
}
