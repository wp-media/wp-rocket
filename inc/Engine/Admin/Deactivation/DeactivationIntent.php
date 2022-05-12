<?php

namespace WP_Rocket\Engine\Admin\Deactivation;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;

class DeactivationIntent extends Abstract_Render {
	/**
	 * Options instance.
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * Options_Data instance.
	 *
	 * @since 3.0
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @since 3.0
	 *
	 * @param string       $template Template path.
	 * @param Options      $options_api Options instance.
	 * @param Options_Data $options     Options_Data instance.
	 */
	public function __construct( $template, Options $options_api, Options_Data $options ) {
		parent::__construct( $template );

		$this->options_api = $options_api;
		$this->options     = $options;
	}

	/**
	 * Checks if the deactivation modal is snoozed
	 *
	 * @since 3.11.1
	 *
	 * @return bool
	 */
	private function is_snoozed(): bool {
		if ( 1 === (int) get_option( 'wp_rocket_hide_deactivation_form', 0 ) ) {
			return true;
		}

		if ( false !== get_transient( 'rocket_hide_deactivation_form' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Inserts the deactivation intent form on plugins page
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	public function insert_deactivation_intent_form() {
		if ( $this->is_snoozed() ) {
			return;
		}

		$data = [
			'form_action' => admin_url( 'admin-post.php?action=rocket_deactivation' ),
		];

		echo $this->generate( 'form', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Deactivate the plugin and set the snooze value
	 *
	 * @since 3.11.1
	 *
	 * @param int $snooze Snooze value.
	 *
	 * @return void
	 */
	public function deactivate_and_snooze( $snooze ) {
		$this->set_snooze( $snooze );
		deactivate_plugins( 'wp-rocket/wp-rocket.php' );
	}

	/**
	 * Sets the snooze value
	 *
	 * @since 3.11.1
	 *
	 * @param int $snooze Snooze value.
	 *
	 * @return void
	 */
	private function set_snooze( int $snooze ) {
		if ( 0 === $snooze ) {
			add_option( 'wp_rocket_hide_deactivation_form', 1 );
			return;
		}

		set_transient( 'rocket_hide_deactivation_form', 1, $snooze * DAY_IN_SECONDS );
	}

	/**
	 * Activates WP Rocket safe mode by deactivating possibly layout breaking options
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	public function activate_safe_mode() {
		/**
		 * Filters the array of options to reset when activating safe mode
		 *
		 * @since 3.7
		 *
		 * @param array $options Array of options to reset.
		 */
		$reset_options = apply_filters(
			'rocket_safe_mode_reset_options',
			[
				'async_css'              => 0,
				'lazyload'               => 0,
				'lazyload_iframes'       => 0,
				'lazyload_youtube'       => 0,
				'minify_css'             => 0,
				'minify_concatenate_css' => 0,
				'minify_js'              => 0,
				'minify_concatenate_js'  => 0,
				'defer_all_js'           => 0,
				'delay_js'               => 0,
				'remove_unused_css'      => 0,
				'minify_google_fonts'    => 0,
				'cdn'                    => 0,
			]
		);

		$this->options->set_values( $reset_options );
		$this->options_api->set( 'settings', $this->options->get_options() );
	}

	/**
	 * Add modal assets on the plugins page
	 *
	 * @since 3.11.1
	 *
	 * @param string $hook The current admin page.
	 *
	 * @return void
	 */
	public function add_modal_assets( $hook ) {
		if ( 'plugins.php' !== $hook ) {
			return;
		}

		if ( $this->is_snoozed() ) {
			return;
		}

		wp_enqueue_style( 'wpr-modal', rocket_get_constant( 'WP_ROCKET_ASSETS_CSS_URL' ) . 'wpr-modal.css', null, rocket_get_constant( 'WP_ROCKET_VERSION' ) );
		wp_enqueue_script( 'micromodal', rocket_get_constant( 'WP_ROCKET_ASSETS_JS_URL' ) . 'micromodal.min.js', null, '0.4.10', true );
		wp_add_inline_script(
			'micromodal',
			'window.addEventListener("DOMContentLoaded", (event) => {
			document.getElementById("deactivate-wp-rocket").addEventListener("click", (event) => {event.preventDefault();});MicroModal.init();
		  });'
		);
	}

	/**
	 * Add data attribute to WP Rocket deactivation link for the modal
	 *
	 * @since 3.11.1
	 *
	 * @param string[] $actions An array of plugin action links.
	 *
	 * @return array
	 */
	public function add_data_attribute( $actions ) {
		if ( ! isset( $actions['deactivate'] ) ) {
			return $actions;
		}

		$deactivate_link = str_replace( '<a', '<a data-micromodal-trigger="wpr-deactivation-modal"', $actions['deactivate'] );

		$actions['deactivate'] = $deactivate_link;

		return $actions;
	}
}
