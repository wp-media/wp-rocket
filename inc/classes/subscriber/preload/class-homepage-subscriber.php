<?php
namespace WP_Rocket\Subscriber\Preload;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Preload\Homepage;

/**
 * Homepage Preload Subscriber
 *
 * @since 3.2
 * @author Remy Perona
 */
class Homepage_Subscriber implements Subscriber_Interface {
	/**
	 * Homepage Preload instance
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @var Homepage
	 */
	private $homepage_preloader;

	/**
	 * WP Rocket Options instance.
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @param Homepage     $homepage_preloader Homepage Preload instance.
	 * @param Options_Data $options            WP Rocket Options instance.
	 */
	public function __construct( Homepage $homepage_preloader, Options_Data $options ) {
		$this->homepage_preloader = $homepage_preloader;
		$this->options            = $options;
	}

	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_purge_time_event'         => [ 'preload', 11 ],
			'pagely_cache_purge_after'        => [ 'preload', 11 ],
			'update_option_' . WP_ROCKET_SLUG => [
				[ 'maybe_launch_preload', 11, 2 ],
				[ 'maybe_cancel_preload', 10, 2 ],
			],
		];
	}

	/**
	 * Launches the homepage preload
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @param string $lang The language code to preload.
	 * @return void
	 */
	public function preload( $lang = '' ) {
		if ( ! $this->options->get( 'manual_preload' ) ) {
			return;
		}

		$urls = [];

		if ( ! $lang ) {
			$urls = get_rocket_i18n_uri();
		} else {
			$urls[] = get_rocket_i18n_home_url( $lang );
		}

		$this->homepage_preloader->cancel_preload();
		$this->homepage_preloader->preload( $urls );
	}

	/**
	 * Cancels any preload currently running if the option is deactivated
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @param array $old_value Previous option values.
	 * @param array $value     New option values.
	 * @return void
	 */
	public function maybe_cancel_preload( $old_value, $value ) {
		if ( isset( $old_value['manual_preload'], $value['manual_preload'] ) && $old_value['manual_preload'] !== $value['manual_preload'] && 0 === (int) $value['manual_preload'] ) {
			$this->homepage_preloader->cancel_preload();
		}
	}

	/**
	 * Launches the preload if the option is activated
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @param array $old_value Previous option values.
	 * @param array $value     New option values.
	 * @return void
	 */
	public function maybe_launch_preload( $old_value, $value ) {
		if ( isset( $old_value['manual_preload'], $value['manual_preload'] ) && $old_value['manual_preload'] !== $value['manual_preload'] && 1 === (int) $value['manual_preload'] ) {
			$this->preload();
		}
	}
}
