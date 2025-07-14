<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Cache\UrlValidation;

abstract class AbstractUrlValidation {
	/**
	 * Disable caching invalid page urls.
	 *
	 * @param bool $can_cache Filter callback passed value.
	 *
	 * @return bool
	 */
	public function disable_cache_on_not_valid_url( $can_cache ) {
		if ( $this->is_disabled() ) {
			return $can_cache;
		}

		if ( $this->is_not_valid_url() ) {
			return false;
		}

		return $can_cache;
	}

	/**
	 * Stop optimizing those invalid pages by returning empty html string,
	 * So it fall back to the normal page's HTML.
	 *
	 * @param string $html Page's buffer HTML.
	 *
	 * @return string
	 */
	public function stop_optimizations_for_not_valid_url( $html ) {
		if ( $this->is_disabled() ) {
			return $html;
		}

		return $this->is_not_valid_url() ? '' : $html;
	}

	/**
	 * Check if url validation is disabled by filter
	 *
	 * @return bool
	 */
	protected function is_disabled(): bool {
		/**
		 * Filters whether to disable URL validation.
		 *
		 * @param bool $disable True to disable URL validation, false to enable it.
		 */
		return wpm_apply_filters_typed( 'boolean', 'rocket_disable_url_validation', false );
	}

	/**
	 * Check if current url is not valid
	 *
	 * @return bool
	 */
	abstract protected function is_not_valid_url(): bool;

	/**
	 * Retrieves the current URL for validation purposes.
	 *
	 * @return string The current URL.
	 */
	protected function get_current_url() {
		global $wp;
		$current_url = home_url( add_query_arg( [], $wp->request ?? '' ) );
		/**
		 * Filters the current URL used for validation.
		 *
		 * @param string $current_url The current URL.
		 */
		return wpm_apply_filters_typed( 'string', 'rocket_current_url', $current_url );
	}
}
