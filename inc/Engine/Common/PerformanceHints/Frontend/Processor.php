<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Frontend;

use WP_Rocket\Admin\Options_Data;
use WP_Filesystem_Direct;
use WP_Rocket\Engine\Common\Utils;

class Processor {

	/**
	 * Array of Factories.
	 *
	 * @var array
	 */
	private $factories;

	/**
	 * Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * WordPress filesystem.
	 *
	 * @var WP_Filesystem_Direct
	 */
	protected $filesystem;

	/**
	 * Instantiate the class
	 *
	 * @param array                     $factories Array of factories.
	 * @param Options_Data              $options Options instance.
	 * @param WP_Filesystem_Direct|null $filesystem WordPress filesystem.
	 */
	public function __construct( array $factories, Options_Data $options, ?WP_Filesystem_Direct $filesystem = null ) {
		$this->factories  = $factories;
		$this->options    = $options;
		$this->filesystem = $filesystem ?: rocket_direct_filesystem();
	}

	/**
	 * Apply Performance Hints Optimizations.
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function maybe_apply_optimizations( string $html ): string {
		if ( empty( $this->factories ) ) {
			return $html;
		}

		if ( is_user_logged_in() && $this->options->get( 'cache_logged_user', 0 ) ) {
			return $html;
		}

		global $wp;

		$url       = untrailingslashit( home_url( add_query_arg( [], $wp->request ) ) );
		$is_mobile = $this->is_mobile();

		// Set flag as true by default.
		$optimization_applied = true;

		foreach ( $this->factories as $factory ) {
			$row = $factory->queries()->get_row( $url, $is_mobile );

			if ( empty( $row ) ) {
				// Flag false if optimization has not been applied.
				$optimization_applied = false;
				continue;
			}

			$html = $factory->get_frontend_controller()->optimize( $html, $row );
		}

		// Check if all optimizations were applied: if not, inject beacon.
		if ( ! $optimization_applied ) {
			$html = $this->inject_beacon( $html, $url, $is_mobile );
		}

		return $html;
	}

	/**
	 * The `inject_beacon` function is used to inject a JavaScript beacon into the HTML content
	 *
	 * @param string $html The HTML content where the beacon will be injected.
	 * @param string $url The current URL.
	 * @param bool   $is_mobile True for mobile device, false otherwise.
	 *
	 * @return string The modified HTML content with the beacon script injected just before the closing body tag.
	 */
	private function inject_beacon( $html, $url, $is_mobile ): string {
		if ( rocket_get_constant( 'DONOTROCKETOPTIMIZE' ) && ! Utils::is_saas_visit() ) {
			return $html;
		}

		$min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		if ( ! $this->filesystem->exists( rocket_get_constant( 'WP_ROCKET_ASSETS_JS_PATH' ) . 'wpr-beacon' . $min . '.js' ) ) {
			return $html;
		}

		$default_width_threshold  = $is_mobile ? 393 : 1600;
		$default_height_threshold = $is_mobile ? 830 : 700;
		/**
		 * Filters the width threshold for the beacon.
		 *
		 * @param int    $width_threshold The width threshold. Default is 393 for mobile and 1920 for others.
		 * @param bool   $is_mobile       True if the current device is mobile, false otherwise.
		 * @param string $url             The current URL.
		 *
		 * @return int The filtered width threshold.
		 */
		$width_threshold = rocket_apply_filter_and_deprecated(
			'rocket_performance_hints_optimization_width_threshold',
			[ $default_width_threshold, $is_mobile, $url ],
			'3.16.4',
			'rocket_lcp_width_threshold'
		);

		/**
		 * Filters the height threshold for the beacon.
		 *
		 * @param int    $height_threshold The height threshold. Default is 830 for mobile and 1080 for others.
		 * @param bool   $is_mobile        True if the current device is mobile, false otherwise.
		 * @param string $url              The current URL.
		 *
		 * @return int The filtered height threshold.
		 */
		$height_threshold = rocket_apply_filter_and_deprecated(
			'rocket_performance_hints_optimization_height_threshold',
			[ $default_height_threshold, $is_mobile, $url ],
			'3.16.4',
			'rocket_lcp_height_threshold'
		);

		if ( ! is_int( $width_threshold ) ) {
			$width_threshold = $default_width_threshold;
		}

		if ( ! is_int( $height_threshold ) ) {
			$height_threshold = $default_height_threshold;
		}

		$default_delay = 500;

		/**
		 * Filters the delay before the beacon is triggered.
		 *
		 * @param int $delay The delay in milliseconds. Default is 500.
		 */
		$delay = rocket_apply_filter_and_deprecated(
			'rocket_performance_hints_optimization_delay',
			[ $default_delay ],
			'3.16.4',
			'rocket_lcp_delay'
		);

		if ( ! is_int( $delay ) ) {
			$delay = $default_delay;
		}

		$data = [
			'ajax_url'         => admin_url( 'admin-ajax.php' ),
			'nonce'            => wp_create_nonce( 'rocket_beacon' ),
			'url'              => $url,
			'is_mobile'        => $is_mobile,
			'width_threshold'  => $width_threshold,
			'height_threshold' => $height_threshold,
			'delay'            => $delay,
			'debug'            => rocket_get_constant( 'WP_ROCKET_DEBUG' ),
			'status'           => [],
		];

		$data_modified = null;
		foreach ( $this->factories as $factory ) {
			$data          = $data_modified ?? $data;
			$data_modified = $factory->get_frontend_controller()->add_custom_data( $data );
		}

		$inline_script = '<script>var rocket_beacon_data = ' . wp_json_encode( $data_modified ) . '</script>';

		// Get the URL of the script.
		$script_url = rocket_get_constant( 'WP_ROCKET_ASSETS_JS_URL' ) . 'wpr-beacon' . $min . '.js';

		// Create the script tag.
		$script_tag             = "<script data-name=\"wpr-wpr-beacon\" src='{$script_url}' async></script>"; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		$last_body_tag_position = strrpos( $html, '</body>' );

		if ( false !== $last_body_tag_position ) {
			// Append the script tag just before the last closing body tag especially in cases where there's an iframe.
			$html = substr_replace( $html, $inline_script . $script_tag . '</body>', $last_body_tag_position, 7 );
		} else {
			// Append to the end of html if </body> is not found.
			$html .= $inline_script . $script_tag;
		}

		return $html;
	}

	/**
	 * Determines if the page is mobile and separate cache for mobile files is enabled.
	 *
	 * @return bool
	 */
	private function is_mobile(): bool {
		return $this->options->get( 'cache_mobile', 0 )
			&& $this->options->get( 'do_caching_mobile_files', 0 )
			&& wp_is_mobile();
	}
}
