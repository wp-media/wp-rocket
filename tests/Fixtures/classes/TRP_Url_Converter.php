<?php
if ( ! class_exists( 'TRP_Url_Converter' ) ) {
    class TRP_Url_Converter {

		public static $url = '';

		public static $lang = '';

		public function get_lang_from_url_string(string $url) {
			return self::$lang;
		}

		public function get_url_for_language(string $code) {
			return self::$url;
		}
    }
}
