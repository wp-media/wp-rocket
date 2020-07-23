<?php

namespace WP_Rocket\ThirdParty\Hostings;

use WP_Rocket\Engine\Cache\AdminSubscriber;
use WP_Rocket\Event_Management\Event_Manager;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\NullSubscriber;
use WP_Rocket\ThirdParty\SubscriberFactoryInterface;

/**
 * Host Subscriber Factory
 *
 * @since 3.6.3
 */
class HostSubscriberFactory implements SubscriberFactoryInterface {

	/**
	 * An Admin Subscriber object.
	 *
	 * @var AdminSubscriber
	 */
	protected $admin_subscriber;

	/**
	 * An Event Manager object.
	 *
	 * @var Event_Manager
	 */
	protected $event_manager;

	/**
	 * HostSubscriberFactory constructor.
	 *
	 * @param AdminSubscriber $admin_subscriber An Admin Subscriber object.
	 * @param Event_Manager   $event_manager    An EventManager object.
	 */
	public function __construct( AdminSubscriber $admin_subscriber, Event_Manager $event_manager ) {
		$this->admin_subscriber = $admin_subscriber;
		$this->event_manager    = $event_manager;
	}

	/**
	 * Get a Subscriber Interface object.
	 *
	 * @since 3.6.3
	 *
	 * @return Subscriber_Interface A Subscribe Interface for the current host.
	 */
	public function get_subscriber() {
		$host_service = HostResolver::get_host_service( rocket_get_constant( 'WP_ROCKET_IS_TESTING', false ) );

		switch ( $host_service ) {
			case 'pressable':
				$pressable_subscriber = new Pressable( $this->admin_subscriber );
				$pressable_subscriber->set_event_manager( $this->event_manager );

				return $pressable_subscriber;
			case 'cloudways':
				return new Cloudways();
			case 'spinupwp':
				return new SpinUpWP();
			case 'wpengine':
				return new WPEngine();
			default:
				return new NullSubscriber();
		}
	}
}
