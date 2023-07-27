<?php
use TRP_Url_Converter;

if ( ! class_exists( 'TRP_Translate_Press' ) ) {
    class TRP_Translate_Press {

		public function get_component( $component ) {
			if ( 'url_converter'  === $component ) {
				return new TRP_Url_Converter();
			}
		}

		public static function get_trp_instance() {
			return new self;
		}
	}
}
