<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Processor;

trait HelperTrait {

	/**
	 * Get filtered elements depth.
	 *
	 * @since 3.17
	 *
	 * @return int
	 */
	protected function get_depth() {
		/**
		 * Filter the depth integer value.
		 * The actual applied depth in the source is the default + 1 after the body or rocket_lrc_depth+1 after the body if the filter is set
		 *
		 * @param int $depth Depth value.
		 */
		return wpm_apply_filters_typed( 'integer', 'rocket_lrc_depth', 2 );
	}

	/**
	 * Get filtered element maximum count.
	 *
	 * @since 3.17
	 *
	 * @return int
	 */
	protected function get_max_tags() {
		/**
		 * Filter the maximal number of processed tags.
		 * High values allow to process more elements but expose to a risk of performance issue because of the regex replacement process.
		 *
		 * @param int $depth Depth value.
		 */
		return wpm_apply_filters_typed( 'integer', 'rocket_lrc_max_hashes', 200 );
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
		 * @since 3.17
		 *
		 * @param array|string[] $tags Tags to be processed.
		 */
		$tags = wpm_apply_filters_typed(
			'array',
			'rocket_lrc_processed_tags',
			[
				'DIV',
				'MAIN',
				'FOOTER',
				'SECTION',
				'ARTICLE',
				'HEADER',
			]
		);

		/**
		 * Convert tags to upper case here before
		 */
		return array_map( 'strtoupper', $tags );
	}
}
