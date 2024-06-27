<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\WarmUp;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\Common\Utils;
use WP_Rocket\Engine\License\API\User;

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
	 * @param ContextInterface $context ATF Context.
	 * @param Options_Data     $options Options instance.
	 * @param APIClient        $api_client APIClient instance.
	 * @param User             $user User instance.
	 * @param Queue            $queue Queue instance.
	 */
	public function __construct( ContextInterface $context, Options_Data $options, APIClient $api_client, User $user, Queue $queue ) {
		$this->context    = $context;
		$this->options    = $options;
		$this->api_client = $api_client;
		$this->user       = $user;
		$this->queue      = $queue;
	}

	/**
	 * Send home URL for warm up.
	 *
	 * @return void
	 */
	public function warm_up_home(): void {
		if ( (bool) $this->options->get( 'remove_unused_css', 0 ) ) {
			return;
		}

		if ( ! $this->context->is_allowed() ) {
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
		if ( 'local' === wp_get_environment_type() ) {
			return;
		}

		if ( (bool) $this->options->get( 'remove_unused_css', 0 ) ) {
			return;
		}

		if ( ! $this->context->is_allowed() ) {
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
		if ( $this->user->is_license_expired_grace_period() ) {
			return [];
		}

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
		$links_limit = apply_filters( 'rocket_atf_warmup_links_number', $default_limit );

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
		$this->api_client->add_to_atf_queue( $url );

		if ( $this->is_mobile() ) {
			$this->api_client->add_to_atf_queue( $url, 'mobile' );
		}
	}

	/**
	 * Add wpr_imagedimensions to URL query.
	 *
	 * @param string $url URL to be sent.
	 *
	 * @return string
	 */
	public function add_wpr_imagedimensions_query_arg( string $url ): string {
		if ( ! $this->context->is_allowed() ) {
			return $url;
		}

		return add_query_arg(
			[
				'wpr_imagedimensions' => 1,
			],
			$url
		);
	}

	/**
	 * Check if the mobile cache is set.
	 *
	 * @return bool
	 */
	private function is_mobile(): bool {
		$plugin_version = (string) get_rocket_option( 'version', '' );
		if ( ! $plugin_version ) { // We are warming up a fresh installation. Options are not set yet.
			return true;
		}

		return $this->options->get( 'cache_mobile', 0 ) && $this->options->get( 'do_caching_mobile_files', 0 );
	}
}
