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
			'admin_enqueue_scripts'        => 'enqueue_admin_edit_script',
		];
	}

	/**
	 * Enqueue CPCSS generation / deletion script on edit.php page.
	 *
	 * @since 3.6
	 *
	 * @param  string $page The current admin page.
	 * @return void
	 */
	public function enqueue_admin_edit_script( $page ) {
		// Bailout if the page is not Post / Page.
		if ( ! in_array( $page, [ 'edit.php', 'post.php' ], true ) ) {
			return;
		}
		// Bailout if the CPCSS is not enabled for this Post / Page.
		$status = $this->is_enabled();
		if ( $status['disabled'] ) {
			return;
		}
		wp_enqueue_script( 'wpr-edit-cpcss-script', WP_ROCKET_ASSETS_JS_URL . 'wpr-cpcss.js', [ 'jquery' ], WP_ROCKET_VERSION, true );
	}

	/**
	 * Displays the critical CSS block in WP Rocket options metabox
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	public function cpcss_section() {
		global $post, $pagenow;

		$status = $this->is_enabled();
		$data   = [
			'disabled_description' => $status['description'],
			'cpcss_rest_url'       => rest_url( 'wp-rocket/v1/cpcss/post/' . ( 'post-new.php' !== $pagenow ? $post->ID : '' ) ),
			'cpcss_rest_nonce'     => wp_create_nonce( 'wp_rest' ),
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
			'disabled'     => $status['disabled'],
			'beacon'       => '',
			'cpcss_exists' => $this->cpcss_exists(),
		];

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->generate(
			'generate',
			$data // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
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

		$excluded_async_css = get_post_meta( $post->ID, '_rocket_exclude_async_css', true );

		if ( 'publish' !== $post->post_status && $excluded_async_css ) {
			return [
				'disabled'    => true,
				'description' => __( 'Publish the post and enable Optimize CSS delivery in the options above to use this feature', 'rocket' ),
			];
		}

		if ( 'publish' !== $post->post_status ) {
			return [
				'disabled'    => true,
				'description' => __( 'Publish the post to use this feature', 'rocket' ),
			];
		}

		if ( $excluded_async_css ) {
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
