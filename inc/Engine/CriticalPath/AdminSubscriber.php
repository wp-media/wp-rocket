<?php

namespace WP_Rocket\Engine\CriticalPath;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Event_Management\Subscriber_Interface;

class AdminSubscriber extends Abstract_Render implements Subscriber_Interface {
	/**
	 * Instance of the Beacon handler.
	 *
	 * @var Beacon
	 */
	private $beacon;

	/**
	 * Path to the critical-css directory.
	 *
	 * @var string
	 */
	private $critical_css_path;

	/**
	 * Instance of options handler.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Array of reasons to disable actions.
	 *
	 * @var array
	 */
	private $disabled_data;

	/**
	 * Creates an instance of the subscriber.
	 *
	 * @param Options_Data $options       WP Rocket Options instance.
	 * @param Beacon       $beacon        Beacon instance.
	 * @param string       $critical_path Path to the critical CSS base folder.
	 * @param string       $template_path Path to the templates folder.
	 */
	public function __construct( Options_Data $options, Beacon $beacon, $critical_path, $template_path ) {
		parent::__construct( $template_path );

		$this->beacon            = $beacon;
		$this->options           = $options;
		$this->critical_css_path = $critical_path . get_current_blog_id() . '/posts/';
	}

	/**
	 * Events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_after_options_metabox'  => 'cpcss_section',
			'rocket_metabox_cpcss_content'  => 'cpcss_actions',
			'admin_enqueue_scripts'         => 'enqueue_admin_edit_script',
			'rocket_first_install_options'  => 'add_async_css_mobile_option',
			'wp_rocket_upgrade'             => [ 'set_async_css_mobile_default_value', 11, 2 ],
			'rocket_hidden_settings_fields' => 'add_hidden_async_css_mobile',
		];
	}

	/**
	 * Enqueue CPCSS generation / deletion script on edit.php page.
	 *
	 * @since 3.6
	 *
	 * @param string $page The current admin page.
	 *
	 * @return void
	 */
	public function enqueue_admin_edit_script( $page ) {
		global $post, $pagenow;

		// Bailout if the page is not Post / Page.
		if ( ! in_array( $page, [ 'edit.php', 'post.php' ], true ) ) {
			return;
		}

		// Bailout if the CPCSS is not enabled for this Post / Page.
		if ( $this->is_enabled() ) {
			return;
		}

		$post_id = ( 'post-new.php' === $pagenow ) ? '' : $post->ID;

		wp_enqueue_script(
			'wpr-edit-cpcss-script',
			rocket_get_constant( 'WP_ROCKET_ASSETS_JS_URL' ) . 'wpr-cpcss.js',
			[],
			rocket_get_constant( 'WP_ROCKET_VERSION' ),
			true
		);
		wp_localize_script(
			'wpr-edit-cpcss-script',
			'rocket_cpcss',
			[
				'rest_url'       => rest_url( "wp-rocket/v1/cpcss/post/{$post_id}" ),
				'rest_nonce'     => wp_create_nonce( 'wp_rest' ),
				'generate_btn'   => __( 'Generate Specific CPCSS', 'rocket' ),
				'regenerate_btn' => __( 'Regenerate specific CPCSS', 'rocket' ),
			]
		);
	}

	/**
	 * Displays the critical CSS block in WP Rocket options metabox.
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	public function cpcss_section() {
		$data = [
			'disabled_description' => $this->get_disabled_description(),
			'async_css_mobile'     => $this->options->get( 'async_css_mobile', 0 ),
		];

		echo $this->generate( 'container', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Displays the content inside the critical CSS block.
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	public function cpcss_actions() {
		$data = [
			'disabled'     => $this->is_enabled(),
			'beacon'       => $this->beacon->get_suggest( 'specific_cpcss' ),
			'cpcss_exists' => $this->cpcss_exists(),
		];

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->generate(
			'generate',
			$data // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}

	/**
	 * Adds async_css_mobile option to WP Rocket options.
	 *
	 * @since 3.6
	 *
	 * @param array $options WP Rocket options array.
	 *
	 * @return array
	 */
	public function add_async_css_mobile_option( $options ) {
		$options = (array) $options;

		$options['async_css_mobile'] = 1;

		return $options;
	}

	/**
	 * Sets the default value of async_css_mobile to 0 when upgrading from < 3.6.
	 *
	 * @since 3.6
	 *
	 * @param string $new_version New WP Rocket version.
	 * @param string $old_version Previous WP Rocket version.
	 */
	public function set_async_css_mobile_default_value( $new_version, $old_version ) {
		if ( version_compare( $old_version, '3.6', '>' ) ) {
			return;
		}

		$this->options->set( 'async_css_mobile', 0 );

		update_option( 'wp_rocket_settings', $this->options->get_options() );
	}

	/**
	 * Adds async_css_mobile to the hidden settings fields.
	 *
	 * @since 3.6
	 *
	 * @param array $hidden_settings_fields An array of hidden settings fields ID.
	 *
	 * @return array
	 */
	public function add_hidden_async_css_mobile( $hidden_settings_fields ) {
		$hidden_settings_fields = (array) $hidden_settings_fields;

		$hidden_settings_fields[] = 'async_css_mobile';

		return $hidden_settings_fields;
	}

	/**
	 * Gets data for the disabled checks.
	 *
	 * @since 3.6
	 *
	 * @return array
	 */
	private function get_disabled_data() {
		global $post;

		if ( rocket_get_constant( 'WP_ROCKET_IS_TESTING', false ) ) {
			$this->disabled_data = null;
		}

		if ( isset( $this->disabled_data ) ) {
			return $this->disabled_data;
		}

		if ( 'publish' !== $post->post_status ) {
			$this->disabled_data['not_published'] = 1;
		}

		if ( ! $this->options->get( 'async_css', 0 ) ) {
			$this->disabled_data['option_disabled'] = 1;
		}

		if ( get_post_meta( $post->ID, '_rocket_exclude_async_css', true ) ) {
			$this->disabled_data['option_excluded'] = 1;
		}

		return $this->disabled_data;
	}

	/**
	 * Checks if critical CSS generation is enabled for the current post.
	 *
	 * @since 3.6
	 *
	 * @return bool
	 */
	private function is_enabled() {
		return ! empty( $this->get_disabled_data() );
	}

	/**
	 * Returns the reason why actions are disabled.
	 *
	 * @since 3.6
	 *
	 * @return string
	 */
	private function get_disabled_description() {
		global $post;

		$disabled_data = $this->get_disabled_data();

		if ( empty( $disabled_data ) ) {
			return '';
		}

		$notice = __( '%l to use this feature.', 'rocket' );
		$list   = [
			// translators: %s = post type.
			'not_published'   => sprintf( __( 'Publish the %s', 'rocket' ), $post->post_type ),
			'option_disabled' => __( 'Enable Optimize CSS delivery in WP Rocket settings', 'rocket' ),
			'option_excluded' => __( 'Enable Optimize CSS delivery in the options above', 'rocket' ),
		];

		return wp_sprintf_l( $notice, array_intersect_key( $list, $disabled_data ) );
	}

	/**
	 * Checks if a specific critical css file exists for the current post.
	 *
	 * @since 3.6
	 *
	 * @return bool
	 */
	private function cpcss_exists() {
		global $post;

		$post_cpcss = "{$this->critical_css_path}{$post->post_type}-{$post->ID}.css";

		return rocket_direct_filesystem()->exists( $post_cpcss );
	}
}
