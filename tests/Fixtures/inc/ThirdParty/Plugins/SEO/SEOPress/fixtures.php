<?php
if(! function_exists('seopress_get_toggle_option')) {
	function seopress_get_toggle_option( $name ) {
		return '1';
	}
}

if(! class_exists("SitemapOption")) {
	class SitemapOption {
		public function isEnabled() {
			return '1';
		}
	}
}

if(! function_exists('seopress_get_service')) {
	function seopress_get_service($name) {
		return new SitemapOption();
	}
}
