<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\WarmUp;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Utils;
use WP_Rocket\Engine\License\API\User;

class Controller {
	/**
	 * Array of Factories.
	 *
	 * @var array
	 */
	private $factories;

	/**
	 * Plugin options instance.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * APIClient Instance.
	 *
	 * @var APIClient
	 */
	private $api_client;

	/**
	 * User instance
	 *
	 * @var User
	 */
	private $user;

	/**
	 * Queue instance.
	 *
	 * @var Queue
	 */
	private $queue;

	/**
	 * Constructor
	 *
	 * @param array        $factories Array of factories.
	 * @param Options_Data $options Options instance.
	 * @param APIClient    $api_client APIClient instance.
	 * @param User         $user User instance.
	 * @param Queue        $queue Queue instance.
	 */
	public function __construct( array $factories, Options_Data $options, APIClient $api_client, User $user, Queue $queue ) {
		$this->factories  = $factories;
		$this->options    = $options;
		$this->api_client = $api_client;
		$this->user       = $user;
		$this->queue      = $queue;
	}

	/**
	 * Should terminate early if true.
	 *
	 * @return bool
	 */
	private function is_allowed(): bool {
		return ! (
			'local' === wp_get_environment_type() ||
			$this->user->is_license_expired_grace_period() ||
			(bool) $this->options->get( 'remove_unused_css', 0 )
		);
	}

	/**
	 * Send home URL for warm up.
	 *
	 * @return void
	 */
	public function warm_up_home(): void {
		if ( ! $this->is_allowed() ) {
			return;
		}

		if ( empty( $this->factories ) ) {
			return;
		}

		$this->send_to_saas( home_url() );
		$this->queue->add_job_warmup();
	}

	/**
	 * Fetch links and send them to SaaS for warm up.
	 *
	 * @return void
	 */
	public function warm_up(): void {
		if ( ! $this->is_allowed() ) {
			return;
		}

		if ( empty( $this->factories ) ) {
			return;
		}

		$links = $this->fetch_links();

		foreach ( $links as $link ) {
			$this->queue->add_job_warmup_url( $link );
		}
	}

	/**
	 * Fetch links from homepage.
	 *
	 * @return array
	 */
	public function fetch_links(): array {
		$user_agent = 'WP Rocket/Pre-fetch Home Links Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1';

		$home_url = home_url();
		$args     = [
			'user-agent' => $user_agent,
			'timeout'    => 60,
		];

		$response = wp_safe_remote_get( $home_url, $args );

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

		$reject_uri_pattern = '/(?:.+/)?feed(?:/(?:.+/?)?)?$|/(?:.+/)?embed/|/wc-api/v(.*)|/(index.php/)?(.*)wp-json(/.*|$)';

		// Filter links.
		$links = array_filter(
			$links,
			function ( $link ) use ( $home_url, $reject_uri_pattern ) {
				$link_host = wp_parse_url( $link );
				$site_host = wp_parse_url( $home_url );
				/**
				 * Check for valid link.
				 * Check that no external link.
				 * Check that it's not home.
				 */
				$is_valid_url        = wp_http_validate_url( $link );
				$is_same_host        = isset( $link_host['host'] ) ? $link_host['host'] === $site_host['host'] : false;
				$is_not_home         = ! Utils::is_home( $link );
				$is_not_excluded_uri = ! (bool) preg_match( '#' . $reject_uri_pattern . '#i', $link );

				return $is_valid_url && $is_same_host && $is_not_home && $is_not_excluded_uri;
			}
		);

		// Remove duplicate links.
		$links = array_unique( $links );

		$default_limit = 10;

		/**
		 * Filters the number of links to return from the homepage.
		 *
		 * @param int $links_limit number of links to return.
		 */
		$links_limit = rocket_apply_filter_and_deprecated(
			'rocket_performance_hints_warmup_links_number',
			[ $default_limit ],
			'3.16.4',
			'rocket_atf_warmup_links_number'
		);

		if ( ! is_int( $links_limit ) || $links_limit < 1 ) {
			$links_limit = $default_limit;
		}

		$links = array_slice( $links, 0, $links_limit );

		return $links;
	}

	/**
	 * Send link to SaaS to do the warmup.
	 *
	 * @param string $url Url to send.
	 *
	 * @return void
	 */
	public function send_to_saas( string $url ) {
		$this->api_client->add_to_performance_hints_queue( $url );

		if ( $this->is_mobile() ) {
			$this->api_client->add_to_performance_hints_queue( $url, 'mobile' );
		}
	}

	/**
	 * Check if the mobile cache is set.
	 *
	 * @return bool
	 */
	private function is_mobile(): bool {
		return $this->options->get( 'cache_mobile', 1 ) && $this->options->get( 'do_caching_mobile_files', 1 );
	}

	/**
	 * Add wpr_imagedimensions to URL query.
	 *
	 * @param string $url URL to be sent.
	 *
	 * @return string
	 */
	public function add_wpr_imagedimensions_query_arg( string $url ): string {
		if ( empty( $this->factories ) && ! $this->options->get( 'remove_unused_css', 0 ) ) {
			return $url;
		}

		return add_query_arg(
			[
				'wpr_imagedimensions' => 1,
			],
			$url
		);
	}
}
