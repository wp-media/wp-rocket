<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\MCP\Auth\Discovery;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Discovery endpoints handler
	 *
	 * @var Endpoints
	 */
	private $endpoints;

	/**
	 * Subscriber constructor.
	 *
	 * @param Endpoints $endpoints The discovery endpoints handler.
	 */
	public function __construct( Endpoints $endpoints ) {
		$this->endpoints = $endpoints;
	}

	/**
	 * Return the subscribed events.
	 *
	 * @return array<string, string|array>
	 */
	public static function get_subscribed_events(): array {
		return array(
			'template_redirect' => 'handle_request',
			'query_vars'        => 'add_oauth_query_vars',
			'rocket_activation' => 'add_rewrite_rules',
			'init'              => 'add_rewrite_rules',
		);
	}

	/**
	 * Add the OAuth query var to WordPress's list of recognised vars.
	 *
	 * @param string[] $vars Existing query vars.
	 * @return string[] Modified list.
	 */
	public function add_oauth_query_vars( array $vars ): array {
		return $this->endpoints->add_oauth_query_vars( $vars );
	}

	/**
	 * Register rewrite rules for the .well-known paths.
	 *
	 * Called both on the 'init' action (normal requests) and directly during
	 * plugin activation before flush_rewrite_rules().
	 *
	 * @return void
	 */
	public function add_rewrite_rules(): void {
		$this->endpoints->add_rewrite_rules();
	}

	/**
	 * Serve the discovery document if the request matches.
	 *
	 * @return void
	 */
	public function handle_request(): void {
		$this->endpoints->handle_request();
	}
}
