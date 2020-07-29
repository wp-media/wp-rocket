<?php
namespace WP_Rocket\Engine\Media;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

/**
 * Event subscriber to control Embeds behavior.
 *
 * @since  3.7 Moved to new architecture.
 * @since  3.2
 */
class EmbedsSubscriber implements Subscriber_Interface {
	use ReturnTypesTrait;

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.7
	 * @access public
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( rocket_bypass() ) {
			return [];
		}

		return [
			'init'                        => [ 'remove_wp_vars_and_filters', 9999 ],
			'rest_endpoints'              => 'disable_embeds_remove_embed_endpoint',
			'oembed_response_data'        => 'disable_embeds_filter_oembed_response_data',
			'embed_oembed_discover'       => 'return_false',
			'tiny_mce_plugins'            => 'disable_embeds_tiny_mce_plugin',
			'rewrite_rules_array'         => 'disable_embeds_rewrites',
			'enqueue_block_editor_assets' => 'disable_embeds_enqueue_block_editor_assets',
			'wp_default_scripts'          => 'disable_embeds_remove_script_dependencies',
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
	 * Remove the 'wpembed' TinyMCE plugin.
	 *
	 * @since  3.7 Moved to new architecture.
	 * @since  2.10
	 *
	 * @param array $plugins List of TinyMCE plugins.
	 *
	 * @return array The modified list.
	 */
	public function disable_embeds_tiny_mce_plugin( $plugins ) {
		return array_diff( $plugins, [ 'wpembed' ] );
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
	public function disable_embeds_rewrites( $rules ) {
		if ( empty( $rules ) ) {
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
	public function disable_embeds_remove_embed_endpoint( $endpoints ) {
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
	 * @return array|false Response data or false if in a REST API context.
	 */
	public function disable_embeds_filter_oembed_response_data( $data ) {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return false;
		}

		return $data;
	}

	/**
	 * Enqueue JavaScript for the block editor.
	 *
	 * @since 3.7 Moved to new architecture.
	 * @since 3.3.3
	 *
	 * This is used to unregister the `core-embed/wordpress` block type.
	 */
	public function disable_embeds_enqueue_block_editor_assets() {
		wp_enqueue_script(
			'rocket-disable-embeds',
			WP_ROCKET_ASSETS_JS_URL . 'editor/editor.js',
			[
				'wp-edit-post',
				'wp-editor',
				'wp-dom',
			],
			WP_ROCKET_VERSION,
			true
		);
	}

	/**
	 * Remove wp-embed dependency of core packages.
	 *
	 * @since 3.7 Moved to new architecture.
	 * @since 3.3.3
	 *
	 * @param \WP_Scripts $scripts WP_Scripts instance, passed by reference.
	 */
	public function disable_embeds_remove_script_dependencies( $scripts ) {
		if ( ! empty( $scripts->registered['wp-edit-post'] ) ) {
			$scripts->registered['wp-edit-post']->deps = array_diff(
				$scripts->registered['wp-edit-post']->deps,
				[ 'wp-embed' ]
			);
		}
	}
}
