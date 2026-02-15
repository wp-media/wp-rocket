<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\PreloadFonts\Context;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Context\ContextInterface;

class Context implements ContextInterface {
	/**
	 * Instance of the Option_Data class.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * List of allowed extensions.
	 *
	 * @var string[]
	 */
	private $extensions = [
		'woff2',
		'woff',
		'ttf',
	];

	/**
	 * Constructor.
	 *
	 * @param Options_Data $options Instance of the Option_Data class.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Determine if the action is allowed.
	 *
	 * @param array $data Data to pass to the context.
	 * @return bool
	 */
	public function is_allowed( array $data = [] ): bool {
		if ( $this->options->get( 'wp_rocket_no_licence' ) ) {
			return false;
		}

		return (bool) $this->options->get( 'auto_preload_fonts', 0 );
	}

	/**
	 * Determines if the page is mobile and separate cache for mobile files is enabled.
	 *
	 * @return bool
	 */
	public function is_mobile_allowed(): bool {
		return $this->options->get( 'cache_mobile', 0 )
			&& $this->options->get( 'do_caching_mobile_files', 0 )
			&& wp_is_mobile();
	}

	/**
	 * Get array of fonts to be excluded.
	 *
	 * @return array
	 */
	public function get_exclusions(): array {
		/**
		 * Filters excluded fonts.

		 * @param string[] $exclusions Array of fonts to exclude.
		 */
		return wpm_apply_filters_typed( 'string[]', 'rocket_preload_fonts_excluded_fonts', [] );
	}

	/**
	 * Get filtered allowed list of extensions.
	 *
	 * @return string[]
	 */
	public function get_extensions(): array {
		/**
		 * Filters the list of processed font extensions.
		 *
		 * @param string[] $processed_extensions Array of processed font extensions.
		 */
		return wpm_apply_filters_typed( 'string[]', 'rocket_preload_fonts_processed_extensions', $this->extensions );
	}
}
