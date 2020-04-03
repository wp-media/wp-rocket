<?php
// phpcs:ignoreFile

if ( ! class_exists( 'SitePress') ) {

	class SitePress {
		public $active_languages = [];

		/**
		 * See https://github.com/wpml/sitepress-multilingual-cms/blob/develop/sitepress.class.php#L1204
		 */
		public function get_active_languages() {
			return $this->active_languages;
		}
	}
}
