<?php

defined( 'ABSPATH' ) || exit;

if ( get_rocket_option( 'embeds', 0 ) ) {
	/**
	 * Disable embeds on init.
	 *
	 * - Removes the needed query vars.
	 * - Disables oEmbed discovery.
	 * - Completely removes the related JavaScript.
	 *
	 * @since 2.10
	 * @author Remy Perona
	 */
	function rocket_disable_embeds_init() {
		global $wp;

		// Remove the embed query var.
		$wp->public_query_vars = array_diff(
			$wp->public_query_vars,
			[
				'embed',
			]
		);

		// Remove the oembed/1.0/embed REST route.
		add_filter( 'rest_endpoints', 'rocket_disable_embeds_remove_embed_endpoint' );

		// Disable handling of internal embeds in oembed/1.0/proxy REST route.
		add_filter( 'oembed_response_data', 'rocket_disable_embeds_filter_oembed_response_data' );

		// Turn off oEmbed auto discovery.
		add_filter( 'embed_oembed_discover', '__return_false' );

		// Don't filter oEmbed results.
		remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );

		// Remove oEmbed discovery links.
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

		// Remove oEmbed-specific JavaScript from the front-end and back-end.
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );
		add_filter( 'tiny_mce_plugins', 'rocket_disable_embeds_tiny_mce_plugin' );

		// Remove all embeds rewrite rules.
		add_filter( 'rewrite_rules_array', 'rocket_disable_embeds_rewrites' );

		// Remove filter of the oEmbed result before any HTTP requests are made.
		remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10 );

		// Load block editor JavaScript.
		add_action( 'enqueue_block_editor_assets', 'rocket_disable_embeds_enqueue_block_editor_assets' );

		// Remove wp-embed dependency of wp-edit-post script handle.
		add_action( 'wp_default_scripts', 'rocket_disable_embeds_remove_script_dependencies' );
	}
	add_action( 'init', 'rocket_disable_embeds_init', 9999 );

	/**
	 * Removes the 'wpembed' TinyMCE plugin.
	 *
	 * @since 2.10
	 * @author Remy Perona
	 *
	 * @param array $plugins List of TinyMCE plugins.
	 * @return array The modified list.
	 */
	function rocket_disable_embeds_tiny_mce_plugin( $plugins ) {
		return array_diff( $plugins, [ 'wpembed' ] );
	}

	/**
	 * Remove all rewrite rules related to embeds.
	 *
	 * @since 2.10
	 * @author Remy Perona
	 *
	 * @param array $rules WordPress rewrite rules.
	 * @return array Rewrite rules without embeds rules.
	 */
	function rocket_disable_embeds_rewrites( $rules ) {
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
	 * Removes the oembed/1.0/embed REST route.
	 *
	 * @since 3.3.3
	 *
	 * @param array $endpoints Registered REST API endpoints.
	 * @return array Filtered REST API endpoints.
	 */
	function rocket_disable_embeds_remove_embed_endpoint( $endpoints ) {
		unset( $endpoints['/oembed/1.0/embed'] );

		return $endpoints;
	}

	/**
	 * Disables sending internal oEmbed response data in proxy endpoint.
	 *
	 * @since 3.3.3
	 *
	 * @param array $data The response data.
	 * @return array|false Response data or false if in a REST API context.
	 */
	function rocket_disable_embeds_filter_oembed_response_data( $data ) {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return false;
		}

		return $data;
	}

	/**
	 * Enqueues JavaScript for the block editor.
	 *
	 * @since 3.3.3
	 *
	 * This is used to unregister the `core-embed/wordpress` block type.
	 */
	function rocket_disable_embeds_enqueue_block_editor_assets() {
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
	 * Removes wp-embed dependency of core packages.
	 *
	 * @since 3.3.3
	 *
	 * @param \WP_Scripts $scripts WP_Scripts instance, passed by reference.
	 */
	function rocket_disable_embeds_remove_script_dependencies( $scripts ) {
		if ( ! empty( $scripts->registered['wp-edit-post'] ) ) {
			$scripts->registered['wp-edit-post']->deps = array_diff(
				$scripts->registered['wp-edit-post']->deps,
				[ 'wp-embed' ]
			);
		}
	}
}
