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
		$status = $this->is_enabled();
		$data   = [
			'disabled_description' => $status['description'],
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
		$status = $this->is_enabled();
		$data   = [
			'disabled' => $status['disabled'],
			'beacon'   => '',
		];

		if ( $this->cpcss_exists() ) {
			echo $this->generate( 'regenerate', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			echo $this->generate( 'generate', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Checks if critical CSS generation is enabled for the current post
	 *
	 * @since 3.6
	 *
	 * @return array
	 */
	private function is_enabled() {
		global $post;

		if ( ! $this->options->get( 'async_css', 0 ) ) {
			return [
				'disabled'    => true,
				'description' => __( 'Enable Optimize CSS delivery in WP Rocket settings to use this feature', 'rocket' ),
			];
		}

		if ( 'publish' !== $post->post_status ) {
			return [
				'disabled'    => true,
				'description' => __( 'Publish the post to use this feature', 'rocket' ),
			];
		}

		if ( get_post_meta( $post->ID, '_rocket_exclude_async_css', true ) ) {
			return [
				'disabled'    => true,
				'description' => __( 'Enable Optimize CSS delivery in the options above to use this feature', 'rocket' ),
			];
		}

		return [
			'disabled'    => false,
			'description' => '',
		];
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
