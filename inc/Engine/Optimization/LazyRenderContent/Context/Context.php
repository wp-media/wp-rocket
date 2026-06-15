<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\LazyRenderContent\Context;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Context\ContextInterface;

class Context implements ContextInterface {
	/**
	 * Plugin options.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Context constructor.
	 *
	 * @param Options_Data $options Plugin options.
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
		if ( get_option( 'wp_rocket_no_licence' ) ) {
			return false;
		}

		if ( is_user_logged_in() && $this->options->get( 'cache_logged_user', 0 ) ) {
			return false;
		}

		/**
		 * Filters to manage lazy render content optimization
		 *
		 * @param bool $allow True to allow, false otherwise.
		 */
		return wpm_apply_filters_typed( 'boolean', 'rocket_lrc_optimization', true );
	}
}
