<?php

namespace WP_Rocket\Engine\CriticalPath;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Event_Management\Subscriber_Interface;

class AdminSubscriber extends Abstract_Render implements Subscriber_Interface {
	/**
	 * Beacon instance
	 *
	 * @var Beacon
	 */
	private $beacon;

	/**
	 * WP Rocket Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Array of reasons to disable actions
	 *
	 * @var array
	 */
	private $disabled_data;

	/**
	 * Constructor
	 *
	 * @param Options_Data $options WP Rocket Options instance.
	 * @param Beacon       $beacon Beacon instance.
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
	 * Events this subscriber wants to listen to
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_after_options_metabox' => 'cpcss_section',
			'rocket_metabox_cpcss_content' => 'cpcss_actions',
		];
	}

	/**
	 * Displays the critical CSS block in WP Rocket options metabox
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	public function cpcss_section() {
		$data = [
			'disabled_description' => $this->get_disabled_description(),
		];

		echo $this->generate( 'container', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Displays the content inside the critical CSS block
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	public function cpcss_actions() {
		$data = [
			'disabled' => $this->is_enabled(),
			'beacon'   => '',
		];

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->generate(
			$this->cpcss_exists() ? 'regenerate' : 'generate',
			$data // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}

	/**
	 * Gets data for the disabled checks
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

		if ( ! isset( $this->disabled_data ) ) {
			if ( 'publish' !== $post->post_status ) {
				$this->disabled_data['not_published'] = 1;
			}

			if ( ! $this->options->get( 'async_css', 0 ) ) {
				$this->disabled_data['option_disabled'] = 1;
			}

			if ( get_post_meta( $post->ID, '_rocket_exclude_async_css', true ) ) {
				$this->disabled_data['option_excluded'] = 1;
			}
		}

		return $this->disabled_data;
	}

	/**
	 * Checks if critical CSS generation is enabled for the current post
	 *
	 * @since 3.6
	 *
	 * @return bool
	 */
	private function is_enabled() {
		return ! empty( $this->get_disabled_data() );
	}

	/**
	 * Returns the reason why actions are disabled
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
	 * Checks if a specific critical css file exists for the current post
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
