<?php

namespace WP_Rocket\Engine\Cache;

class AdvancedCache  {
	private $template_path;

	public function __construct( $template_path ) {
		$this->template_path = $template_path;
	}

	public function get_advanced_cache_content() {
		$content = rocket_direct_filesystem()->get_contents( $this->template_path . '/advanced-cache.php' );

		/**
		 * Filter the content of advanced-cache.php file.
		 *
		 * @since 2.1
		 *
		 * @param string $content The content that will be printed in advanced-cache.php.
		*/
		return (string) apply_filters( 'rocket_advanced_cache_file', $content );
	}
}
