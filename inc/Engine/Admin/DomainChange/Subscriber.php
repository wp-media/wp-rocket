<?php

namespace WP_Rocket\Engine\Admin\DomainChange;

use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Common\Ajax\AjaxHandler;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {

	/**
	 * Handle basic ajax operations.
	 *
	 * @var AjaxHandler
	 */
	protected $ajax_handler;

	/**
	 * Beacon instance
	 *
	 * @var Beacon
	 */
	protected $beacon;

	/**
	 * Name of the option saving the last base URL.
	 *
	 * @var string
	 */
	const LAST_BASE_URL_OPTION = 'wp_rocket_last_base_url';

	/**
	 * Instantiate the class.
	 *
	 * @param AjaxHandler $ajax_handler Handle basic ajax operations.
	 * @param Beacon      $beacon Beacon instance.
	 */
	public function __construct( AjaxHandler $ajax_handler, Beacon $beacon ) {
		$this->ajax_handler = $ajax_handler;
		$this->beacon       = $beacon;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return string[]
	 */
	public static function get_subscribed_events() {

		return [
			'admin_init'                                 => 'maybe_launch_domain_changed',
			'admin_notices'                              => 'maybe_display_domain_change_notice',
			'rocket_domain_changed'                      => 'maybe_clean_cache_domain_change',
			'rocket_notice_args'                         => 'add_regenerate_configuration_action',
			'admin_post_rocket_regenerate_configuration' => 'regenerate_configuration',
		];
	}

	/**
	 * Maybe launch the domain changed event.
	 *
	 * @return void
	 */
	public function maybe_launch_domain_changed() {
		if ( wp_doing_ajax() ) {
			return;
		}

		$base_url              = trailingslashit( get_option( 'home' ) );
		$base_url_encoded      = base64_encode( $base_url ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$last_base_url_encoded = get_option( self::LAST_BASE_URL_OPTION );

		if ( ! $last_base_url_encoded ) {
			update_option( self::LAST_BASE_URL_OPTION, $base_url_encoded, true );
			return;
		}

		if ( $base_url_encoded === $last_base_url_encoded ) {
			return;
		}

		update_option( self::LAST_BASE_URL_OPTION, $base_url_encoded, true );

		$last_base_url = base64_decode( $last_base_url_encoded ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

		set_transient( 'rocket_domain_changed', $last_base_url_encoded, 2 * rocket_get_constant( 'WEEK_IN_SECONDS', 604800 ) );

		/**
		 * Fires when the domain of the website has been changed.
		 *
		 * @param string $current_url current URL from the website.
		 * @param string $old_url old URL from the website.
		 */
		do_action( 'rocket_detected_domain_changed', $base_url, $last_base_url );
	}

	/**
	 * Maybe clean cache on domain change.
	 *
	 * @return void
	 */
	public function maybe_clean_cache_domain_change() {

		$options = get_option( rocket_get_constant( 'WP_ROCKET_SLUG' ) );

		if ( ! $options ) {
			return;
		}

		/**
		 * Fires after WP Rocket options that require a cache purge have changed
		 *
		 * @param array $value An array of submitted values for the settings.
		 */
		do_action( 'rocket_domain_options_changed', $options );
	}

	/**
	 * Maybe display a notice when domain change.
	 *
	 * @return void
	 */
	public function maybe_display_domain_change_notice() {

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		$notice = get_transient( 'rocket_domain_changed' );

		if ( ! $notice || is_multisite() ) {
			return;
		}

		$beacon = $this->beacon->get_suggest( 'domain_change' );

		$args = [
			'status'         => 'warning',
			'dismissible'    => '',
			'dismiss_button' => false,
			'message'        => sprintf(
			// translators: %1$s = <strong>, %2$s = </strong>, %3$s = <a>, %4$s = </a>.
				__( '%1$sWP Rocket:%2$s We detected that the website domain has changed. The configuration files must be regenerated for the page cache and all other optimizations to work as intended. %3$sLearn More%4$s', 'rocket' ),
				'<strong>',
				'</strong>',
				'<a href="' . esc_url( $beacon['url'] ) . '" data-beacon-article="' . esc_attr( $beacon['id'] ) . '" target="_blank" rel="noopener noreferrer">',
				'</a>'
			),
			'action'         => 'regenerate_configuration',
		];

		rocket_notice_html( $args );
	}

	/**
	 * Add mapping on notice.
	 *
	 * @param array $args Arguments from the notice.
	 *
	 * @return array
	 */
	public function add_regenerate_configuration_action( $args ) {
		if ( ! key_exists( 'action', $args ) || 'regenerate_configuration' !== $args['action'] ) {
			return $args;
		}

		$params = [
			'action' => 'rocket_regenerate_configuration',
		];

		$args['action'] = '<a class="wp-core-ui button" href="' . add_query_arg( $params, wp_nonce_url( admin_url( 'admin-post.php' ), 'rocket_regenerate_configuration' ) ) . '">' . __( 'Regenerate WP Rocket configuration files now', 'rocket' ) . '</a>';

		return $args;
	}

	/**
	 * Regenerate configurations.
	 *
	 * @return void
	 */
	public function regenerate_configuration() {
		if ( ! $this->ajax_handler->validate_referer(
			'rocket_regenerate_configuration',
			'rocket_manage_options'
			) ) {
			return;
		}

		$last_base_url_encoded = get_transient( 'rocket_domain_changed' );

		if ( ! $last_base_url_encoded ) {
			return;
		}

		$last_base_url = base64_decode( $last_base_url_encoded ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$base_url      = trailingslashit( get_option( 'home' ) );

		/**
		 * Fires when the domain of the website has been changed and user clicked on notice.
		 *
		 * @param string $current_url current URL from the website.
		 * @param string $old_url old URL from the website.
		 */
		do_action( 'rocket_domain_changed', $base_url, $last_base_url );

		delete_transient( 'rocket_domain_changed' );

		$this->ajax_handler->redirect();
	}
}
