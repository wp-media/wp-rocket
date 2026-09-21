<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\RocketCDN\APIHandler;

use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use WP_Rocket\Engine\Common\JobManager\APIHandler\AbstractSafeAPIClient;
use WP_Rocket\Engine\License\API\User;

/**
 * Class to Interact with the RocketCDN API - website search.
 */
class WebsiteSearch extends AbstractSafeAPIClient {

	/**
	 * Option name used as a short-lived lock to guard against firing duplicate,
	 * near-simultaneous requests to the RocketCDN website-search endpoint (e.g.
	 * two callers both racing to resolve a 404 from the subscription status
	 * endpoint before the transient cache has been (re)populated).
	 *
	 * @var string
	 */
	const WEBSITE_SEARCH_FETCH_LOCK = 'rocket_cdn_website_search_fetch_lock';

	/**
	 * Maximum time, in seconds, a fetch lock is honored before being considered
	 * stale (e.g. left behind by a request that crashed or timed out) and
	 * reclaimable by a later caller.
	 *
	 * @var int
	 */
	const WEBSITE_SEARCH_FETCH_LOCK_TTL = 30;

	/**
	 * The site URL to search for in the RocketCDN API.
	 *
	 * @var string
	 */
	private $site_url;

	/**
	 * Set site_url.
	 *
	 * @param string $site_url Site url.
	 * @return void
	 */
	public function set_site_url( string $site_url ): void {
		$this->site_url = $site_url;
	}

	/**
	 * Get the transient key for making this API Client calls.
	 *
	 * @return string The transient key.
	 */
	protected function get_transient_key() {
		return 'rocket_cdn_website_search';
	}

	/**
	 * Get the API URL for website search.
	 *
	 * @return string The API URL.
	 */
	protected function get_api_url() {
		if ( empty( $this->site_url ) ) {
			return '';
		}
		return add_query_arg(
			'url',
			untrailingslashit( $this->site_url ),
			APIClient::ROCKETCDN_API . 'website/search/'
		);
	}

	/**
	 * Check RocketCDN free account creation status.
	 *
	 * @return array|false
	 */
	public function find() {
		$cached = get_transient( $this->get_transient_key() );
		if ( false !== $cached ) {
			return $cached;
		}

		$token = get_option( 'rocketcdn_user_token' );

		if ( empty( $token ) ) {
			return false;
		}

		if ( ! $this->acquire_website_search_fetch_lock() ) {
			// Another (possibly concurrent) request is already fetching this same
			// data - reuse whatever it leaves behind instead of firing a duplicate
			// request against the RocketCDN API.
			$cached = get_transient( $this->get_transient_key() );

			return false !== $cached ? $cached : false;
		}

		try {
			$args = [
				'headers' => [
					'Authorization' => 'Token ' . $token,
				],
			];

			$response = $this->send_get_request( $args, true );

			if ( is_wp_error( $response ) ) {
				return false;
			}

			$status_code = wp_remote_retrieve_response_code( $response );
			$body        = wp_remote_retrieve_body( $response );

			if ( empty( $body ) ) {
				return false;
			}

			$body = json_decode( $body, true );
			if ( ! is_array( $body ) ) {
				return false;
			}

			$final = [
				'subscription_status' => $body['subscription_status'] ?? 'cancelled',
				'plan_type'           => $body['subscription_plan_type'] ?? 'free',
				'status_code'         => $status_code,
				'website_status'      => $body['status'] ?? '',
			];
			set_transient( $this->get_transient_key(), $final, HOUR_IN_SECONDS );

			return $final;
		} finally {
			$this->release_website_search_fetch_lock();
		}
	}

	/**
	 * Attempts to acquire the short-lived website-search fetch lock.
	 *
	 * Relies on add_option()'s pre-existence check (a fresh, non-cached
	 * get_option() read for this non-autoloaded option) to keep sequential
	 * callers from re-fetching once another has already populated the cache.
	 *
	 * @return bool True if the lock was acquired, false if another request already holds it.
	 */
	private function acquire_website_search_fetch_lock(): bool {
		if ( add_option( self::WEBSITE_SEARCH_FETCH_LOCK, time(), '', false ) ) {
			return true;
		}

		$locked_at = get_option( self::WEBSITE_SEARCH_FETCH_LOCK );

		// Reclaim a stale lock left behind by a request that crashed or timed out
		// before it could release it.
		if ( is_numeric( $locked_at ) && ( time() - (int) $locked_at ) > self::WEBSITE_SEARCH_FETCH_LOCK_TTL ) {
			delete_option( self::WEBSITE_SEARCH_FETCH_LOCK );

			return add_option( self::WEBSITE_SEARCH_FETCH_LOCK, time(), '', false );
		}

		return false;
	}

	/**
	 * Releases the website-search fetch lock.
	 *
	 * @return void
	 */
	private function release_website_search_fetch_lock(): void {
		delete_option( self::WEBSITE_SEARCH_FETCH_LOCK );
	}

	/**
	 * Validate response code.
	 *
	 * @param array $response Response object.
	 * @return bool
	 */
	protected function valid_response_code( $response ) {
		return in_array( wp_remote_retrieve_response_code( $response ), [ 200, 404 ], true );
	}

	/**
	 * Validate response body.
	 *
	 * @param array $response Response object.
	 * @return bool
	 */
	protected function valid_response_body( $response ) {
		return ! empty( wp_remote_retrieve_body( $response ) );
	}
}
