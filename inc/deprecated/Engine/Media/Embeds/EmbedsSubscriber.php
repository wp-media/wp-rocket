<?php
namespace WP_Rocket\deprecated\Engine\Media\Embeds;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\deprecated\DeprecatedClassTrait;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

/**
 * Event subscriber to control Embeds behavior.
 *
 * @since  3.10 deprecated
 * @since  3.7 Moved to new architecture.
 * @since  3.2
 */
class EmbedsSubscriber implements Subscriber_Interface {
	use DeprecatedClassTrait;
	use ReturnTypesTrait;

	/**
	 * The Options Data instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * EmbedsSubscriber constructor.
	 *
	 * @param Options_Data $options An Options Data instance.
	 */
	public function __construct( Options_Data $options ) {
		self::deprecated_class( '3.10' );
		$this->options = $options;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.7
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'init'                        => [ 'remove_wp_vars_and_hooks', 9999 ],
			'rest_endpoints'              => 'remove_embed_endpoint',
			'oembed_response_data'        => 'empty_oembed_response_data',
			'embed_oembed_discover'       => 'return_false',
			'rewrite_rules_array'         => 'remove_embeds_rewrite_rules',
			'enqueue_block_editor_assets' => 'enqueue_disable_embeds_script',
			'wp_default_scripts'          => 'remove_wp_embed_dependency',
		];
	}

	/**
	 * Remove WP Query Vars and hooks relating to embeds.
	 *
	 * Replaces old architecture's rocket_disable_embeds_init().
	 *
	 * @since 3.7
	 *
	 * @return void
	 */
	public function remove_wp_vars_and_hooks() {
		if ( ! $this->can_disable_embeds() ) {
			return;
		}

		global $wp;

		// Remove the embed query var.
		$wp->public_query_vars = array_diff(
			$wp->public_query_vars,
			[
				'embed',
			]
		);

		// Don't filter oEmbed results.
		remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );

		// Remove oEmbed discovery links.
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

		// Remove oEmbed-specific JavaScript from the front-end and back-end.
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );

		// Remove filter of the oEmbed result before any HTTP requests are made.
		remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10 );
	}

	/**
	 * Remove all rewrite rules related to embeds.
	 *
	 * @since  3.7 Moved to new architecture.
	 * @since  2.10
	 *
	 * @param array $rules WordPress rewrite rules.
	 *
	 * @return array Rewrite rules without embeds rules.
	 */
	public function remove_embeds_rewrite_rules( $rules ) {
		if ( empty( $rules ) || ! $this->can_disable_embeds() ) {
			return $rules;
		}

		foreach ( $rules as $rule => $rewrite ) {
			if ( false !== strpos( $rewrite, 'embed=true' ) ) {
				unset( $rules[ $rule ] );
			}
		}

		return $rules;
	}

	/**
	 * Remove the oembed/1.0/embed REST route.
	 *
	 * @since 3.7 Moved to new architecture.
	 * @since 3.3.3
	 *
	 * @param array $endpoints Registered REST API endpoints.
	 *
	 * @return array Filtered REST API endpoints.
	 */
	public function remove_embed_endpoint( $endpoints ) {
		if ( ! $this->can_disable_embeds() ) {
			return $endpoints;
		}

		unset( $endpoints['/oembed/1.0/embed'] );

		return $endpoints;
	}

	/**
	 * Disables sending internal oEmbed response data in proxy endpoint.
	 *
	 * @since 3.7 Moved to new architecture.
	 * @since 3.3.3
	 *
	 * @param array $data The response data.
	 *
	 * @return array Response data
	 */
	public function empty_oembed_response_data( $data ) {
		if (
			! rocket_get_constant( 'REST_REQUEST' )
			||
			! $this->can_disable_embeds()
		) {
			return $data;
		}

		return [];
	}

	/**
	 * Enqueue JavaScript for the block editor.
	 *
	 * This is used to unregister the `core-embed/wordpress` block type.
	 *
	 * @since 3.7 Moved to new architecture.
	 * @since 3.3.3
	 *
	 * @return void
	 */
	public function enqueue_disable_embeds_script() {
		if ( ! $this->can_disable_embeds() ) {
			return;
		}
	}

	/**
	 * Remove wp-embed dependency of core packages.
	 *
	 * @since 3.7 Moved to new architecture.
	 * @since 3.3.3
	 *
	 * @param \WP_Scripts $scripts WP_Scripts instance, passed by reference.
	 */
	public function remove_wp_embed_dependency( $scripts ) {
		if ( ! $this->can_disable_embeds() ) {
			return;
		}

		if ( ! empty( $scripts->registered['wp-edit-post'] ) ) {
			$scripts->registered['wp-edit-post']->deps = array_diff(
				$scripts->registered['wp-edit-post']->deps,
				[ 'wp-embed' ]
			);
		}
	}

	/**
	 * Check for embeds enabled.
	 *
	 * @since 3.7
	 *
	 * @return bool
	 */
	private function can_disable_embeds() {
		return ! rocket_bypass() && (bool) $this->options->get( 'embeds', 0 );
	}
}
