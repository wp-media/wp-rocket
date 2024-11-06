<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Hostings;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\{NullSubscriber, ReturnTypesTrait};
use WP_Term;

/**
 * Compatibility class for SpinUpWP
 *
 * @since 3.6.2
 */
class SpinUpWP extends NullSubscriber implements Subscriber_Interface {
	use ReturnTypesTrait;

	/**
	 * Array of events this subscriber wants to listen to.
	 *
	 * @since 3.6.2
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'do_rocket_generate_caching_files'    => 'return_false',
			'rocket_display_varnish_options_tab'  => 'return_false',
			'rocket_cache_mandatory_cookies'      => 'return_empty_array',
			'rocket_after_clean_domain'           => 'purge_site',
			'wp_rocket_loaded'                    => 'remove_actions',
			'after_rocket_clean_file'             => 'purge_url',
			'rocket_rucss_after_clearing_usedcss' => 'purge_url',
			'rocket_saas_complete_job_status'     => 'purge_url',
			'after_rocket_clean_term'             => [ 'purge_term_urls', 10, 2 ],
			'rocket_after_clean_terms'            => 'purge_urls',
		];
	}

	/**
	 * Purge SpinUpWP cache after clean domain.
	 *
	 * @since 3.6.2
	 *
	 * @return void
	 */
	public function purge_site() {
		if ( ! function_exists( 'spinupwp_purge_site' ) ) {
			return;
		}

		spinupwp_purge_site();
	}

	/**
	 * Remove rocket_clean_domain which prevents a double clear of the cache.
	 *
	 * @since 3.6.2
	 *
	 * @return void
	 */
	public function remove_actions() {
		remove_action( 'switch_theme', 'rocket_clean_domain' );
	}

	/**
	 * Purge URL in SpinUpWP
	 *
	 * @param string $url URL.
	 *
	 * @return void
	 */
	public function purge_url( $url ) {
		if ( ! function_exists( 'spinupwp_purge_url' ) ) {
			return;
		}

		spinupwp_purge_url( trailingslashit( $url ) );
	}

	/**
	 * Purge multiple URLs
	 *
	 * @param array $urls Array of URLs.
	 *
	 * @return void
	 */
	public function purge_urls( $urls ) {
		foreach ( $urls as $url ) {
			$this->purge_url( $url );
		}
	}

	/**
	 * Purge URLs related to a term
	 *
	 * @param WP_Term $term The term object.
	 * @param array   $urls Array of URLs.
	 *
	 * @return void
	 */
	public function purge_term_urls( $term, $urls ) {
		$this->purge_urls( $urls );
	}
}
