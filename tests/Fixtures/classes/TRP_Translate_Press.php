<?php

if ( ! class_exists( 'TRP_Translate_Press' ) ) {
    class TRP_Translate_Press {

		public function get_component( $component ) {
			switch ( $component ) {
				case 'language_switcher':
					return new TRP_Language_Switcher();
				case 'settings':
					return new TRP_Settings();
				case 'languages':
					return new TRP_Languages();
				case 'url_converter';
					return new TRP_Url_Converter();
			}
		}

		public static function get_trp_instance() {
			return new self;
		}
	}
}
