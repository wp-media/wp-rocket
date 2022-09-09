<?php
namespace {

	use The_SEO_Framework\Bridges\Sitemap;

	function the_seo_framework() {
		return Sitemap::get_instance();
	}
}


namespace The_SEO_Framework\Bridges {
	class Sitemap {

		public $loaded = 'loaded';

		public static $endpoints = [];

		public static $url = '';

		public static $sitemap = '';

		public static function get_instance() {
			return new self;
		}

		public function get_sitemap_endpoint_list() {
			return self::$endpoints;
		}

		public function get_expected_sitemap_endpoint_url($id) {
			return self::$url;
		}

		public function get_sitemap_xml_url() {
			return self::$sitemap;
		}

		public function can_run_sitemap() {
			return true;
		}
	}
}

