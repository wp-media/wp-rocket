<?php

namespace WP_Rocket\Engine\CriticalPath\Admin;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CriticalPath\ProcessorService;

class Admin {
	/**
	 * Instance of options handler.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Instance of ProcessorService.
	 *
	 * @var ProcessorService
	 */
	private $processor;

	/**
	 * Creates an instance of the class.
	 *
	 * @param Options_Data     $options   Options instance.
	 * @param ProcessorService $processor ProcessorService instance.
	 */
	public function __construct( Options_Data $options, ProcessorService $processor ) {
		$this->options   = $options;
		$this->processor = $processor;
	}

	/**
	 * Check the CPCSS heartbeat.
	 *
	 * @since 3.6
	 */
	public function cpcss_heartbeat() {
		check_ajax_referer( 'cpcss_heartbeat_nonce', '_nonce', true );

		if (
			! $this->options->get( 'async_css', 0 )
			||
			! current_user_can( 'rocket_manage_options' )
			||
			! current_user_can( 'rocket_regenerate_critical_css' ) ) {
				wp_send_json_error();
				return;
		}

		$cpcss_pending = get_transient( 'rocket_cpcss_generation_pending' );

		if ( false === $cpcss_pending ) {
			$cpcss_pending = [];
		}

		$cpcss_item = reset( $cpcss_pending );
		if ( ! empty( $cpcss_item ) ) {
			$k                = key( $cpcss_pending );
			$timeout          = (bool) ( $cpcss_item['check'] > 10 );
			$cpcss_generation = $this->processor->process_generate(
										$cpcss_item['url'],
										$cpcss_item['path'],
										$timeout,
										( ! empty( $cpcss_item['mobile'] ) ? $cpcss_item['mobile'] : false )
									);
			$cpcss_pending[ $k ]['check'] ++;
			$this->cpcss_heartbeat_notices( $cpcss_generation );

			if (
				is_wp_error( $cpcss_generation )
				||
				'cpcss_generation_successful' === $cpcss_generation['code']
				||
				'cpcss_generation_failed' === $cpcss_generation['code']
				||
				$timeout
				) {
				// CPCSS API returned a success / error reply or it timeout.
				unset( $cpcss_pending[ $k ] );
			}
		}

		set_transient( 'rocket_cpcss_generation_pending', $cpcss_pending, HOUR_IN_SECONDS );

		if ( empty( $cpcss_pending ) ) {
			$this->generation_complete();
			wp_send_json_success( [ 'status' => 'cpcss_complete' ] );
			return;
		}

		wp_send_json_success( [ 'status' => 'cpcss_running' ] );
	}

	/**
	 * CPCSS heartbeat update notices transients.
	 *
	 * @param array|WP_Error $cpcss_generation CPCSS regeneration reply.
	 */
	private function cpcss_heartbeat_notices( $cpcss_generation ) {
		$transient = get_transient( 'rocket_critical_css_generation_process_running' );

		if ( is_wp_error( $cpcss_generation ) ) {
			$transient['items'][] = $cpcss_generation->get_error_message();
			set_transient( 'rocket_critical_css_generation_process_running', $transient, HOUR_IN_SECONDS );
			return;
		}

		if (
			isset( $cpcss_generation['code'] )
			&&
			(
				'cpcss_generation_successful' === $cpcss_generation['code']
				||
				'cpcss_generation_failed' === $cpcss_generation['code']
			)
		) {
			$transient['items'][] = $cpcss_generation['message'];
			$transient['generated']++;
			set_transient( 'rocket_critical_css_generation_process_running', $transient, HOUR_IN_SECONDS );
		}
	}

	/**
	 * Launches when the CPCSS generation is complete.
	 *
	 * @since 3.6
	 */
	private function generation_complete() {
		$running = get_transient( 'rocket_critical_css_generation_process_running' );

		if (
			false !== $running
			&&
			$running['total'] !== count( $running['items'] )
		) {
			return;
		}

		/**
		 * Fires when the critical CSS generation process is complete.
		 *
		 * @since 2.11
		 */
		do_action( 'rocket_critical_css_generation_process_complete' );

		rocket_clean_domain();
		set_transient( 'rocket_critical_css_generation_process_complete', get_transient( 'rocket_critical_css_generation_process_running' ), HOUR_IN_SECONDS );
		delete_transient( 'rocket_critical_css_generation_process_running' );
		delete_transient( 'rocket_critical_css_generation_pending' );
	}

	/**
	 * Enqueue CPCSS heartbeat script on all admin pages.
	 *
	 * @since 3.6
	 */
	public function enqueue_admin_cpcss_heartbeat_script() {
		if ( ! $this->options->get( 'async_css', 0 ) ) {
			return;
		}

		wp_enqueue_script(
			'wpr-heartbeat-cpcss-script',
			rocket_get_constant( 'WP_ROCKET_ASSETS_JS_URL' ) . 'wpr-cpcss-heartbeat.js',
			[],
			rocket_get_constant( 'WP_ROCKET_VERSION' ),
			true
		);

		wp_localize_script(
			'wpr-heartbeat-cpcss-script',
			'rocket_cpcss_heartbeat',
			[
				'nonce' => wp_create_nonce( 'cpcss_heartbeat_nonce' ),
			]
		);
	}

	/**
	 * Add Regenerate Critical CSS link to admin bar
	 *
	 * @since 3.6
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 * @return void
	 */
	public function add_regenerate_admin_bar( $wp_admin_bar ) {
		if ( ! current_user_can( 'rocket_regenerate_critical_css' ) ) {
			return;
		}

		if ( ! is_admin() ) {
			return;
		}

		if ( ! (bool) $this->options->get( 'async_css', 0 ) ) {
			return;
		}

		// This filter is documented in inc/Engine/CriticalPath/CriticalCSS.php.
		if ( ! apply_filters( 'do_rocket_critical_css_generation', true ) ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
			return;
		}

		$referer = '';
		$action  = 'rocket_generate_critical_css';

		if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
			$referer_url = filter_var( wp_unslash( $_SERVER['REQUEST_URI'] ), FILTER_SANITIZE_URL );
			$referer     = '&_wp_http_referer=' . rawurlencode( remove_query_arg( 'fl_builder', $referer_url ) );
		}

		$wp_admin_bar->add_menu(
			[
				'parent' => 'wp-rocket',
				'id'     => 'regenerate-critical-path',
				'title'  => __( 'Regenerate Critical Path CSS', 'rocket' ),
				'href'   => wp_nonce_url( admin_url( "admin-post.php?action={$action}{$referer}" ), $action ),
			]
		);
	}
}
