<?php
if ( ! class_exists( 'TRP_Settings' ) ) {
    class TRP_Settings {

		public function get_settings()
		{
			return [
				'publish-languages' => [
					'fr_FR',
					'en_US',
				],
				'url-slugs' => [
					'fr_FR' => 'fr',
					'en_US' => 'en',
				],
			];
		}
	}
}
