<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Support;

trait CommentTrait {
	/**
	 * Add a comment to the HTML
	 *
	 * @param string $feature The feature name.
	 * @param string $html The HTML content.
	 *
	 * @return string
	 */
	public function add_meta_comment( $feature, $html ) {
		// This filter is documented in inc/Engine/Support/Meta.php.
		if ( wpm_apply_filters_typed( 'boolean', 'rocket_disable_meta_generator', false ) ) {
			return $html;
		}

		$result = preg_replace( '#</html>#', '</html><!-- wpr_' . $feature . ' -->', $html, 1 );

		if ( null === $result ) {
			return $html;
		}

		return $result;
	}
}
