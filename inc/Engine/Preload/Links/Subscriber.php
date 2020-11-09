<?php

namespace WP_Rocket\Engine\Preload\Links;

use WP_Filesystem_Direct;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Options Data instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * WP_Filesystem_Direct instance.
	 *
	 * @var WP_Filesystem_Direct
	 */
	private $filesystem;

	/**
	 * Script enqueued status.
	 *
	 * @var bool
	 */
	private $is_enqueued = false;

	/**
	 * Instantiate the class.
	 *
	 * @param Options_Data         $options    Options Data instance.
	 * @param WP_Filesystem_Direct $filesystem The Filesystem object.
	 */
	public function __construct( Options_Data $options, $filesystem ) {
		$this->options    = $options;
		$this->filesystem = $filesystem;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'wp_enqueue_scripts' => 'add_preload_script',
		];
	}

	/**
	 * Adds the inline script to the footer when the option is enabled
	 *
	 * @since 3.7
	 *
	 * @return void
	 */
	public function add_preload_script() {
		if ( $this->is_enqueued ) {
			return;
		}
		if ( ! (bool) $this->options->get( 'preload_links', 0 ) || rocket_bypass() ) {
			return;
		}

		$js_assets_path = rocket_get_constant( 'WP_ROCKET_PATH' ) . 'assets/js/';

		if ( ! wp_script_is( 'rocket-browser-checker' ) ) {
			$checker_filename = rocket_get_constant( 'SCRIPT_DEBUG' ) ? 'browser-checker.js' : 'browser-checker.min.js';

			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NoExplicitVersion
			wp_register_script(
				'rocket-browser-checker',
				'',
				[],
				'',
				true
			);
			wp_enqueue_script( 'rocket-browser-checker' );
			wp_add_inline_script(
				'rocket-browser-checker',
				$this->filesystem->get_contents( "{$js_assets_path}{$checker_filename}" )
			);
		}

		$preload_filename = rocket_get_constant( 'SCRIPT_DEBUG' ) ? 'preload-links.js' : 'preload-links.min.js';

		// Register handle with no src to add the inline script after.
		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NoExplicitVersion
		wp_register_script(
			'rocket-preload-links',
			'',
			[
				'rocket-browser-checker',
			],
			'',
			true
		);
		wp_enqueue_script( 'rocket-preload-links' );
		wp_add_inline_script(
			'rocket-preload-links',
			$this->filesystem->get_contents( "{$js_assets_path}{$preload_filename}" )
		);
		wp_localize_script(
			'rocket-preload-links',
			'RocketPreloadLinksConfig',
			$this->get_preload_links_config()
		);

		$this->is_enqueued = true;
	}

	/**
	 * Gets the Preload Links script configuration parameters.
	 *
	 * @since 3.7
	 *
	 * @return string[] Preload Links script configuration parameters.
	 */
	private function get_preload_links_config() {
		$use_trailing_slash = $this->use_trailing_slash();
		$images_ext         = 'jpg|jpeg|gif|png|tiff|bmp|webp|avif';

		$config = [
			'excludeUris'       => $this->get_uris_to_exclude( $use_trailing_slash ),
			'usesTrailingSlash' => $use_trailing_slash,
			'imageExt'          => $images_ext,
			'fileExt'           => $images_ext . '|php|pdf|html|htm',
			'siteUrl'           => home_url(),
			'onHoverDelay'      => 100, // milliseconds. -1 disables the "on hover" feature.
			'rateThrottle'      => 3, // on hover: limits the number of links preloaded per second.
		];

		/**
		 * Preload Links script configuration parameters.
		 *
		 * This array of parameters are passed as RocketPreloadLinksConfig object and used by the
		 * `preload-links.min.js` script to configure the behavior of the Preload Links feature.
		 *
		 * @since 3.7
		 *
		 * @param string[] $config Preload Links script configuration parameters.
		 */
		$filtered_config = apply_filters( 'rocket_preload_links_config', $config );

		if ( ! is_array( $filtered_config ) ) {
			return $config;
		}

		return array_merge( $config, $filtered_config );
	}

	/**
	 * Gets the URIs to exclude.
	 *
	 * @since 3.7
	 *
	 * @param bool $use_trailing_slash When true, uses trailing slash.
	 *
	 * @return string
	 */
	private function get_uris_to_exclude( $use_trailing_slash ) {
		$site_url = site_url();
		$uris     = get_rocket_cache_reject_uri();
		$uris     = str_replace( [ '/(.*)|', '/(.*)/|' ], '/|', $uris );

		foreach ( [ '/wp-admin', '/logout' ] as $uri ) {
			$uris .= "|{$uri}";
			if ( $use_trailing_slash ) {
				$uris .= '/';
			}
		}

		foreach ( [ wp_logout_url(), wp_login_url() ] as $uri ) {
			if ( strpos( $uri, '?' ) !== false ) {
				continue;
			}
			$uris .= '|' . str_replace( $site_url, '', $uri );
		}

		return $uris;
	}

	/**
	 * Checks if the given URL has a trailing slash.
	 *
	 * @since 3.7
	 *
	 * @param string $url URL to check.
	 *
	 * @return bool
	 */
	private function has_trailing_slash( $url ) {
		return substr( $url, -1 ) === '/';
	}

	/**
	 * Indicates if the site uses a trailing slash in the permalink structure.
	 *
	 * @since 3.7
	 *
	 * @return bool when true, uses `/`; else, no.
	 */
	private function use_trailing_slash() {
		return $this->has_trailing_slash( get_permalink() );
	}
}
