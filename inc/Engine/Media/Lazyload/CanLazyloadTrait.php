<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Lazyload;

trait CanLazyloadTrait {
	/**
	 * Checks if lazyload should be applied
	 *
	 * @since 3.3
	 *
	 * @return bool
	 */
	protected function should_lazyload() {
		if (
			rocket_get_constant( 'REST_REQUEST', false )
			||
			rocket_get_constant( 'DONOTLAZYLOAD', false )
			||
			rocket_get_constant( 'DONOTROCKETOPTIMIZE', false )
			||
			rocket_get_constant( 'DONOTCACHEPAGE', false )
		) {
			return false;
		}

		if (
			is_admin()
			||
			is_feed()
			||
			is_preview()
		) {
			return false;
		}

		if (
			is_search()
			&&
			// This filter is documented in inc/classes/Buffer/class-tests.php.
			! (bool) apply_filters( 'rocket_cache_search', false )
		) {
			return false;
		}

		// Exclude Page Builders editors.
		$excluded_parameters = [
			'fl_builder',
			'et_fb',
			'ct_builder',
		];

		foreach ( $excluded_parameters as $excluded ) {
			if ( isset( $_GET[ $excluded ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return false;
			}
		}

		return true;
	}
}
