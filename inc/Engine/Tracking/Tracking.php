<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Tracking;

use Mixpanel;

class Tracking {
	/**
	 * Mixpanel instance.
	 *
	 * @var Mixpanel
	 */
	private $tracker;

	/**
	 * Tracking constructor.
	 *
	 * @param string $token Mixpanel token.
	 */
	public function __construct( $token ) {
		$this->tracker = Mixpanel::getInstance( $token );
	}

	/**
	 * Track an event.
	 *
	 * @param string $event      Event name.
	 * @param array  $properties Event properties.
	 */
	public function track( $event, $properties = [] ) {
		$this->tracker->track( $event, $properties );
	}
}
