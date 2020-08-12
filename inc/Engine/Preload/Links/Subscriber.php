<?php

namespace WP_Rocket\Engine\Preload\Links;

use WP_Filesystem_Direct;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Options Data instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * WP_Filesystem_Direct instance.
	 *
	 * @var WP_Filesystem_Direct
	 */
	private $filesystem;

	/**
	 * Instantiate the class.
	 *
	 * @param Options_Data         $options    Options Data instance.
	 * @param WP_Filesystem_Direct $filesystem The Filesystem object.
	 */
	public function __construct( Options_Data $options, $filesystem ) {
		$this->options    = $options;
		$this->filesystem = $filesystem;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'wp_enqueue_scripts' => 'add_preload_script',
		];
	}

	/**
	 * Adds the inline script to the footer when the option is enabled
	 *
	 * @since 3.7
	 *
	 * @return void
	 */
	public function add_preload_script() {
		if ( ! (bool) $this->options->get( 'preload_links', 0 ) || rocket_bypass() ) {
			return;
		}

		$js_assets_path = rocket_get_constant( 'WP_ROCKET_PATH' ) . 'assets/js/';

		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NoExplicitVersion
		if ( ! wp_script_is( 'rocket-browser-checker' ) ) {
			$checker_filename = rocket_get_constant( 'SCRIPT_DEBUG' ) ? 'browser-checker.js' : 'browser-checker.min.js';

			wp_enqueue_script(
				'rocket-browser-checker',
				'',
				[],
				'',
				true
			);
			wp_add_inline_script(
				'rocket-browser-checker',
				$this->filesystem->get_contents( "{$js_assets_path}{$checker_filename}" )
			);
		}

		$preload_filename = rocket_get_constant( 'SCRIPT_DEBUG' ) ? 'preload-links.js' : 'preload-links.min.js';

		// Register handle with no src to add the inline script after.
		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NoExplicitVersion
		wp_enqueue_script(
			'rocket-preload-links',
			'',
			[],
			'',
			true
		);
		wp_add_inline_script(
			'rocket-preload-links',
			$this->filesystem->get_contents( "{$js_assets_path}{$preload_filename}" )
		);
	}
}
