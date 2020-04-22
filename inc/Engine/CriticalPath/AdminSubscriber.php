<?php

namespace WP_Rocket\Engine\CriticalPath;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Event_Management\Subscriber_Interface;

class AdminSubscriber extends Abstract_Render implements Subscriber_Interface {
	private $options;

	public function __construct( Options_Data $options, $critical_path, $template_path, Beacon $beacon ) {
		parent::__construct( $template_path );

		$this->options = $options;
		$this->critical_css_path = $critical_path . get_current_blog_id() . '/posts/';
		$this->beacon = $beacon;
	}

	public static function get_subscribed_events() {
		return [
			'rocket_after_options_metabox' => 'cpcss_section',
			'rocket_metabox_cpcss_content' => 'cpcss_actions',
		];
	}

	public function cpcss_section() {
		echo $this->generate( 'container' );
	}

	public function cpcss_actions() {
		$status = $this->is_enabled();

		$data = [
			'disabled'             => $status['enabled'] ? '' : 'disabled',
			'disabled_description' => $status['description'],
			'beacon'               => '',
		];

		if ( $this->cpcss_exists() ) {
			echo $this->generate( 'regenerate', $data );
		} else {
			echo $this->generate( 'generate', $data );
		}
	}

	private function is_enabled() {
		global $post;

		if ( ! $this->options->get( 'async_css', 0 ) ) {
			return [
				'enabled'     => false,
				'description' => __( 'Enable Optimize CSS delivery in WP Rocket settings to use this feature', 'rocket' )
			];
		}

		if ( 'publish' !== $post->post_status ) {
			return [
				'enabled'     => false,
				'description' => __( 'publish the post to use this feature', 'rocket' )
			];
		}

		if ( is_rocket_post_excluded_option( 'async_css' ) ) {
			return [
				'enabled'     => false,
				'description' => __( 'Enable Optimize CSS delivery in the options above to use this feature', 'rocket' )
			];
		}

		return [
			'enabled'     => true,
			'description' => '',
		];
	}

	private function cpcss_exists() {
		global $post;

		$post_cpcss = "{$this->critical_css_path}{$post->post_type}-{$post->ID}.css";

		return rocket_direct_filesystem()->exists( $post_cpcss );
	}
}
