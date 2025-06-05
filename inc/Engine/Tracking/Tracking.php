<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Tracking;

use WP_Rocket\Admin\Options_Data;
use WPMedia\Mixpanel\Tracking as MixpanelTracking;

class Tracking {
	/**
	 * Options Data instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Mixpanel Tracking instance.
	 *
	 * @var MixpanelTracking
	 */
	private $mixpanel;

	/**
	 * Constructor.
	 *
	 * @param Options_Data     $options Options Data instance.
	 * @param MixpanelTracking $mixpanel Mixpanel Tracking instance.
	 */
	public function __construct( Options_Data $options, MixpanelTracking $mixpanel ) {
		$this->options  = $options;
		$this->mixpanel = $mixpanel;

		$this->mixpanel->identify( $this->options->get( 'consumer_email', '' ) );
	}

	/**
	 * Track option change.
	 *
	 * @param mixed $old_value The old value of the option.
	 * @param mixed $value     The new value of the option.
	 */
	public function track_option_change( $old_value, $value ) {
		// TODO: Refactor this to use the Optin from the WP Mixpanel package.
		if ( ! $this->options->get( 'analytics_enabled', false ) ) {
			return;
		}

		$options_to_track = [
			'auto_preload_fonts',
		];

		foreach ( $options_to_track as $option_tracked ) {
			if ( ! isset( $old_value[ $option_tracked ], $value[ $option_tracked ] ) ) {
				continue;
			}

			if ( $old_value[ $option_tracked ] === $value[ $option_tracked ] ) {
				continue;
			}

			$host = wp_parse_url( get_site_url(), PHP_URL_HOST );

			$this->mixpanel->track(
				'WPM Option Changed',
				[
					'brand'          => 'WP Media',
					'product'        => 'WP Rocket',
					'context'        => 'wp_plugin',
					'domain'         => $this->mixpanel->hash( $host ),
					'option_name'    => $option_tracked,
					'previous_value' => $old_value[ $option_tracked ],
					'new_value'      => $value[ $option_tracked ],
				]
			);
		}
	}
}
