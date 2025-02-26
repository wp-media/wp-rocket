<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\PreconnectExternalDomains\Context;

use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Common\Context\AbstractContext;

class Context extends AbstractContext implements ContextInterface {
	/**
	 * Determine if the action is allowed.
	 *
	 * @param array $data Data to pass to the context.
	 * @return bool
	 */
	public function is_allowed( array $data = [] ): bool {
		if ( $this->options->get( 'wp_rocket_no_licence', 0 ) ) {
			return false;
		}

		/**
		 * Filters to manage above the fold optimization
		 *
		 * @param bool $allow True to allow, false otherwise.
		 */
		return wpm_apply_filters_typed( 'boolean', 'rocket_preconnect_external_domains_optimization', true );
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
}
