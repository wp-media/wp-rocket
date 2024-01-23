<?php
if(! class_exists('WP_Sitemaps')) {
	class WP_Sitemaps
	{
		public static $enabled;

		public static function sitemaps_enabled(){
			return self::$enabled;
		}
	}
}
