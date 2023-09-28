<?php

namespace WP_Rocket\Engine\CriticalPath\Admin;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CriticalPath\ProcessorService;
use WP_Rocket\Engine\CriticalPath\TransientTrait;

class Admin {
	use TransientTrait;

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
			! $this->is_async_css_enabled()
			||
			! current_user_can( 'rocket_manage_options' )
			||
			! current_user_can( 'rocket_regenerate_critical_css' )
		) {
			wp_send_json_error();

			return;
		}

		$cpcss_pending = get_transient( 'rocket_cpcss_generation_pending' );

		if ( ! empty( $cpcss_pending ) ) {
			$cpcss_pending = $this->process_cpcss_pending_queue( (array) $cpcss_pending );
		}

		if ( false !== $cpcss_pending && empty( $cpcss_pending ) ) {
			delete_transient( 'rocket_cpcss_generation_pending' );
		}

		if ( empty( $cpcss_pending ) ) {
			$this->generation_complete();
			wp_send_json_success( [ 'status' => 'cpcss_complete' ] );

			return;
		}

		set_transient( 'rocket_cpcss_generation_pending', $cpcss_pending, HOUR_IN_SECONDS );
		wp_send_json_success( [ 'status' => 'cpcss_running' ] );
	}

	/**
	 * Pull one item off of the CPCSS Pending Queue and process it.
	 *
	 * @since 3.6
	 *
	 * @param array $cpcss_pending CPCSS Pending Queue.
	 *
	 * @return array remaining queue.
	 */
	private function process_cpcss_pending_queue( array $cpcss_pending ) {
		$cpcss_item = reset( $cpcss_pending );
		if ( empty( $cpcss_item ) ) {
			return $cpcss_pending;
		}

		// Threshold 'check' > 10 = timed out.
		$timeout           = ( $cpcss_item['check'] > 10 );
		$additional_params = [
			'timeout'   => $timeout,
			'is_mobile' => ! empty( $cpcss_item['mobile'] ) ? (bool) $cpcss_item['mobile'] : false,
			'item_type' => $cpcss_item['type'],
		];
		$cpcss_generation  = $this->processor->process_generate(
			$cpcss_item['url'],
			$cpcss_item['path'],
			$additional_params
		);

		// Increment this item's threshold count.
		++$cpcss_pending[ $cpcss_item['path'] ]['check'];

		$this->cpcss_heartbeat_notices( $cpcss_generation, $cpcss_item );

		// Remove the item from the queue when (a) the CPCSS API returns success or error or (b) timeouts.
		if (
			is_wp_error( $cpcss_generation )
			||
			'cpcss_generation_successful' === $cpcss_generation['code']
			||
			'cpcss_generation_failed' === $cpcss_generation['code']
			||
			$timeout
		) {
			unset( $cpcss_pending[ $cpcss_item['path'] ] );
		}

		return $cpcss_pending;
	}

	/**
	 * CPCSS heartbeat update notices transients.
	 *
	 * @since 3.6
	 *
	 * @param array|WP_Error $cpcss_generation CPCSS regeneration reply.
	 * @param array          $cpcss_item       Item processed.
	 */
	private function cpcss_heartbeat_notices( $cpcss_generation, $cpcss_item ) {
		$mobile    = isset( $cpcss_item['mobile'] ) ? $cpcss_item['mobile'] : 0;
		$transient = (array) get_transient( 'rocket_critical_css_generation_process_running' );

		// Initializes the transient.
		if ( ! isset( $transient['items'] ) ) {
			$transient['items'] = [];
		}

		if ( is_wp_error( $cpcss_generation ) ) {
			$this->update_running_transient( $transient, $cpcss_item['path'], $mobile, $cpcss_generation->get_error_message(), false );
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
			$this->update_running_transient( $transient, $cpcss_item['path'], $mobile, $cpcss_generation['message'], ( 'cpcss_generation_successful' === $cpcss_generation['code'] ) );
		}
	}

	/**
	 * Launches when the CPCSS generation is complete.
	 *
	 * @since 3.6
	 */
	private function generation_complete() {
		$running = get_transient( 'rocket_critical_css_generation_process_running' );

		if ( false === $running ) {
			return;
		}

		if ( ! isset( $running['total'], $running['items'] ) ) {
			return;
		}

		if ( $running['total'] > count( $running['items'] ) ) {
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
	}

	/**
	 * Enqueue CPCSS heartbeat script on all admin pages.
	 *
	 * @since 3.6
	 */
	public function enqueue_admin_cpcss_heartbeat_script() {
		if ( ! $this->is_async_css_enabled() ) {
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
	 * Add Regenerate Critical CSS link to WP Rocket admin bar item
	 *
	 * @since 3.6
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 *
	 * @return void
	 */
	public function add_regenerate_menu_item( $wp_admin_bar ) {
		if ( 'local' === wp_get_environment_type() ) {
			return;
		}

		if ( ! current_user_can( 'rocket_regenerate_critical_css' ) ) {
			return;
		}

		if ( ! is_admin() ) {
			return;
		}

		if ( ! $this->is_async_css_enabled() ) {
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

	/**
	 * Checks if the "async_css" option is enabled.
	 *
	 * @since 3.6
	 *
	 * @return bool true when "async_css" option is enabled.
	 */
	private function is_async_css_enabled() {
		return (bool) $this->options->get( 'async_css', 0 );
	}
}
