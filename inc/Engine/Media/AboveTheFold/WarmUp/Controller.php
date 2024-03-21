<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\WarmUp;

use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\JobManager\APIHandler\APIClient;

class Controller {

	/**
	 * ATF context.
	 *
	 * @var ContextInterface
	 */
	protected $context;

	/**
	 * Plugin options instance.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * API Client Instance.
	 *
	 * @var APIClient
	 */
	private $api_client;

	/**
	 * Constructor
	 *
	 * @param ContextInterface $context ATF Context.
	 * @param Options_Data     $options Options instance.
	 * @param APIClient        $api_client API Client instance.
	 */
	public function __construct( ContextInterface $context, Options_Data $options, APIClient $api_client ) {
		$this->context    = $context;
		$this->options    = $options;
		$this->api_client = $api_client;
	}

	/**
	 * Send links for warm up.
	 *
	 * @return void
	 */
	public function warm_up(): void {
		if ( (bool) $this->options->get( 'remove_unused_css', 0 ) ) {
			return;
		}

		if ( ! $this->context->is_allowed() ) {
			return;
		}

		$this->send_to_saas( $this->fetch_links() );
	}

	/**
	 * Fetch links from homepage.
	 *
	 * @return array
	 */
	public function fetch_links(): array {
		$home_url = home_url();
		$args     = [
			'user-agent' => 'WP Rocket/Pre-fetch Home Links',
			'timeout'    => 60,
		];

		$response = wp_remote_get( $home_url, $args );

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return [];
		}

		$html = wp_remote_retrieve_body( $response );

		if ( ! preg_match_all( '/<a\s+(?:[^>]*?\s+)?href=(["\'])(.*?)\1/', $html, $matches ) ) {
			return [];
		}

		$links = $matches[2];

		// Cater for relative urls.
		$links = array_map(
				function ( $link ) {
					$link_path = wp_parse_url( $link );

					// Return if absolute url.
					if ( isset( $link_path['path'], $link_path['scheme'] ) ) {
							return $link;
					}

					// Transform to absolute url if relative.
					if ( isset( $link_path['path'] ) ) {
						return home_url( $link );
					}

					return $link;
				},
			$links
			);

		// Filter links.
		$links = array_filter(
			$links,
			function ( $link ) use ( $home_url ) {
				$link_host = wp_parse_url( $link );
				$site_host = wp_parse_url( $home_url );

				/**
				 * Check for valid link.
				 * Check that no external link.
				 */
				return wp_http_validate_url( $link ) && $link_host['host'] === $site_host['host'];
			}
		);

		// Remove duplicate links.
		$links = array_unique( $links );

		/**
		 * Filters the number of links to return from the homepage.
		 *
		 * @param int number of links to return.
		 */
		$link_number = apply_filters( 'rocket_atf_warmup_links_number', 10 );
		$links       = array_slice( $links, 0, $link_number );

		return $links;
	}

	/**
	 * Send fetched links to SaaS to do the warmup.
	 *
	 * @param array $links Array of links to be sent.
	 * @return void
	 */
	private function send_to_saas( $links ) {
		if ( empty( $links ) ) {
			return;
		}

		/**
		 * Filter the delay between each request.
		 *
		 * @param int $delay_between the defined delay.
		 *
		 * @returns int
		 */
		$delay_between = (int) apply_filters( 'rocket_delay_between_requests', 500000 );

		foreach ( $links as $link ) {
			$this->api_client->add_to_atf_queue( $link );

			usleep( $delay_between );
		}
	}
}
