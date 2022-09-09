<?php

namespace RankMath;

if (! class_exists('RankMath\Helper')) {

	class Helper {
		public static function is_module_active(string $name) {
			return true;
		}
	}
}

namespace RankMath\Sitemap;
if (! class_exists('RankMath\Sitemap\Router')) {

	class Router {

		public static $sitemap = '';

		public static function get_base_url(string $name) {
			return self::$sitemap;
		}
	}
}
