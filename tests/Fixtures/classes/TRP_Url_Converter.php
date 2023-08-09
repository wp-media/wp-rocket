<?php
if ( ! class_exists( 'TRP_Url_Converter' ) ) {
    class TRP_Url_Converter {

		public static $url = '';

		public static $lang = '';

		public function get_lang_from_url_string( $url ) {
			return self::$lang;
		}

		public function get_url_for_language( $code, $url ) {
			$parts = parse_url( $url );
			$path = isset( $parts['path'] ) ? $parts['path'] : '';
			$code = explode( '_', $code );

			return $parts['scheme'] . '://' . $parts['host'] . '/' . $code[0] . $path;
		}
    }
}
