<?php

namespace WP_Rocket\Engine\Media\Lazyload\CSS\Data;

class LazyloadCSSContentFactory {

	/**
	 * Make LazyloadedContent instance.
	 *
	 * @param array  $formatted_urls Formatted URls.
	 * @param string $content Content.
	 * @return LazyloadedContent
	 */
	public function make_lazyloaded_content( array $formatted_urls, string $content ): LazyloadedContent {
		$lazyloaded_content = new LazyloadedContent();
		$lazyloaded_content->set_urls( $formatted_urls );
		$lazyloaded_content->set_content( $content );
		return $lazyloaded_content;
	}

	/**
	 * Make ProtectedContent instance.
	 *
	 * @param array  $protected_files Protected files.
	 * @param string $content Content.
	 * @return ProtectedContent
	 */
	public function make_protected_content( array $protected_files, string $content ): ProtectedContent {
		$protected_content = new ProtectedContent();
		$protected_content->set_content( $content );
		$protected_content->set_protected_files_mapping( $protected_files );
		return $protected_content;
	}
}
