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

		return (bool) $this->options->get( 'rocket_preload_fonts', 1 );
	}
}
