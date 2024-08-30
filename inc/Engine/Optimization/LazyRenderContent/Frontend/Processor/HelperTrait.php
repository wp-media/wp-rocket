<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Processor;

trait HelperTrait {

	/**
	 * Get filtered elements depth.
	 *
	 * @return int
	 */
	protected function get_depth() {
		/**
		 * Filter the depth integer value.
		 * The actual applied depth in the source is the default + 1 after the body
                 * or rocket_lazy_render_content_depth+1 after the body if the filter is set
		 *
		 * @param int $depth Depth value.
		 */
		return wpm_apply_filters_typed( 'integer', 'rocket_lazy_render_content_depth', 2 );
	}

	/**
	 * Get processed tags.
	 *
	 * @return array|string[]
	 */
	protected function get_processed_tags() {
		/**
		 * Filter the processed element tags.
		 *
		 * @param array|string[] $tags Tags to be processed.
		 */
		return wpm_apply_filters_typed(
			'array',
			'rocket_lazy_render_content_processed_tags',
			[
				'DIV',
				'MAIN',
				'FOOTER',
				'SECTION',
				'ARTICLE',
				'HEADER',
			]
		);
	}
}
