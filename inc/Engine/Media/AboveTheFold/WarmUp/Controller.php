<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\WarmUp;

use WP_Rocket\Engine\Common\JobManager\Managers\ManagerInterface;

class Controller {

	/**
	 * Above the fold Job Manager.
	 *
	 * @var ManagerInterface
	 */
	private $manager;

	/**
	 * Constructor
	 *
	 * @param ManagerInterface $manager Above the fold Job Manager.
	 */
	public function __construct( ManagerInterface $manager ) {
		$this->manager = $manager;
	}

	/**
	 * Process links fetched from homepage.
	 *
	 * @return void
	 */
	public function process_links(): void {
		if ( ! $this->manager->is_allowed() ) {
			return;
		}

		error_log( print_r( $this->fetch_links(), true ) );  // To be replaced by sending links to SaaS.
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
}
