<?php

namespace WP_Rocket\Dependencies\WPMedia\PluginFamily\Model;

/**
 * Handles the data to be passed to the frontend.
 */
class PluginFamily {

	/**
	 * An array of referrers for wp rocket.
	 *
	 * @var array
	 */
	protected $wp_rocket_referrer = [
		'imagify'           => 'imagify',
		'seo-by-rank-math'  => '',
		'backwpup'          => '',
		'uk-cookie-consent' => '',
	];

	/**
	 * Get filtered plugins.
	 *
	 * @param string $main_plugin Main plugin installed.
	 *
	 * @return array
	 */
	public function get_filtered_plugins( string $main_plugin ): array {
		$plugins = require_once 'wp_media_plugins.php';

		return $this->filter_plugins_by_activation( $plugins, $main_plugin );
	}

	/**
	 * Filter plugins family data by activation status and returns both categorized and uncategorized format.
	 *
	 * @param array  $plugins Array of family plugins.
	 * @param string $main_plugin Main plugin installed.
	 *
	 * @return array
	 */
	public function filter_plugins_by_activation( array $plugins, string $main_plugin ): array {
		if ( empty( $plugins ) ) {
			return [];
		}

		list( $active_plugins, $inactive_plugins ) = [ [], [] ];

		foreach ( $plugins as $cat => $cat_data ) {
			foreach ( $cat_data['plugins'] as $plugin => $data ) {

				$plugin_path      = $plugin . '.php';
				$plugin_slug      = dirname( $plugin );
				$main_plugin_slug = dirname( $main_plugin );
				$wpr_referrer     = 'wp-rocket' !== $main_plugin_slug ? $this->wp_rocket_referrer[ $main_plugin_slug ] : '';

				/**
				 * Check for activated plugins and pop them out of the array
				 * to re-add them back using array_merge to be displayed after
				 * plugins that are not installed or not activated.
				 */
				if ( is_plugin_active( $plugin_path ) && $main_plugin . '.php' !== $plugin_path ) {
					// set cta data of active plugins.
					$plugins[ $cat ]['plugins'][ $plugin ]['cta'] = [
						'text' => __( 'Activated', 'rocket' ),
						'url'  => '#',
					];

					// Send active plugin to new array.
					$active_plugins[ $plugin ] = $plugins[ $cat ]['plugins'][ $plugin ];

					// Remove active plugin from current category.
					$active_plugin = $plugins[ $cat ]['plugins'][ $plugin ];
					unset( $plugins[ $cat ]['plugins'][ $plugin ] );

					// Send active plugin to the end of array in current category.
					$plugins[ $cat ]['plugins'][ $plugin ] = $active_plugin;

					// Remove category with active plugin from current array.
					$active_cat = $plugins[ $cat ];
					unset( $plugins[ $cat ] );

					// Send category with active plugins to the end of array.
					$plugins[ $cat ] = $active_cat;
					continue;
				}

				$install_activate_url = admin_url( 'admin-post.php' );

				$args = [
					'action'            => 'plugin_family_install_' . $plugin_slug,
					'_wpnonce'          => wp_create_nonce( 'plugin_family_install_' . $plugin_slug ),
					'plugin_to_install' => rawurlencode( $plugin ),
				];

				if ( 'imagify' === $plugin_slug ) {
					$args = [
						'action'           => 'install_imagify_from_partner_' . $main_plugin_slug,
						'_wpnonce'         => wp_create_nonce( 'install_imagify_from_partner' ),
						'_wp_http_referer' => rawurlencode( $this->get_current_url() ),
					];
				}

				$install_activate_url = add_query_arg( $args, $install_activate_url );

				// Set Installation link.
				$plugins[ $cat ]['plugins'][ $plugin ]['cta'] = [
					'text' => __( 'Install', 'rocket' ),
					'url'  => $install_activate_url,
				];

				// Create unique CTA data for WP Rocket.
				if ( 'wp-rocket/wp-rocket' === $plugin ) {
					$url = 'https://wp-rocket.me/?utm_source=' . $wpr_referrer . '-coupon&utm_medium=plugin&utm_campaign=' . $wpr_referrer;

					$plugins[ $cat ]['plugins'][ $plugin ]['cta'] = [
						'text' => __( 'Get it Now', 'rocket' ),
						'url'  => $url,
					];

					$plugins[ $cat ]['plugins'][ $plugin ]['link'] = $url;
				}

				// Set activation text.
				if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin_path ) ) {
					$plugins[ $cat ]['plugins'][ $plugin ]['cta']['text'] = __( 'Activate', 'rocket' );

					if ( 'wp-rocket/wp-rocket' === $plugin ) {
						$plugins[ $cat ]['plugins'][ $plugin ]['cta']['url'] = $install_activate_url;
					}
				}

				// Send inactive plugins to new array.
				$inactive_plugins[ $plugin ] = $plugins[ $cat ]['plugins'][ $plugin ];
			}

			// Remove main plugin from categorized array.
			if ( isset( $plugins[ $cat ]['plugins'][ $main_plugin ] ) ) {
				unset( $plugins[ $cat ]['plugins'][ $main_plugin ] );
			}
		}

		$uncategorized = array_merge( $inactive_plugins, $active_plugins );
		// Remove main plugin from uncategorized array.
		unset( $uncategorized[ $main_plugin ] );

		return [
			'categorized'   => $plugins,
			'uncategorized' => $uncategorized,
		];
	}

	/**
	 * Get the current URL.
	 * Gotten from Imagify_Partner Package.
	 *
	 * @return string
	 */
	protected function get_current_url(): string {
		if ( ! isset( $_SERVER['SERVER_PORT'], $_SERVER['HTTP_HOST'] ) ) {
			return '';
		}

		$port = (int) wp_unslash( $_SERVER['SERVER_PORT'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$port = 80 !== $port && 443 !== $port ? ( ':' . $port ) : '';
		$url  = ! empty( $GLOBALS['HTTP_SERVER_VARS']['REQUEST_URI'] ) ? $GLOBALS['HTTP_SERVER_VARS']['REQUEST_URI'] : ( ! empty( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash

		return 'http' . ( is_ssl() ? 's' : '' ) . '://' . $_SERVER['HTTP_HOST'] . $port . $url; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	}
}
