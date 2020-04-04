<?php
// phpcs:ignoreFile

if ( ! class_exists( 'SitePress') ) {

	class SitePress {
		public $active_languages = [];
		public $home_root;
		public $uris_config = [];

		/**
		 * See https://github.com/wpml/sitepress-multilingual-cms/blob/develop/sitepress.class.php#L1204
		 */
		public function get_active_languages() {
			return $this->active_languages;
		}

		public function language_url( $lang ) {
			if ( ! array_key_exists( $lang, $this->uris_config ) ) {
				return $this->home_root;
			}
			return rtrim( $this->home_root, '/\\' ) . $this->uris_config[ $lang ];
		}
	}
}
