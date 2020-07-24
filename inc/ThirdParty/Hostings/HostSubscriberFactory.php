<?php

namespace WP_Rocket\ThirdParty\Hostings;

use WP_Rocket\Engine\Cache\AdminSubscriber;
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
	 * HostSubscriberFactory constructor.
	 *
	 * @param AdminSubscriber $admin_subscriber An Admin Subscriber object.
	 */
	public function __construct( AdminSubscriber $admin_subscriber ) {
		$this->admin_subscriber = $admin_subscriber;
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
				return new Pressable( $this->admin_subscriber );
			case 'cloudways':
				return new Cloudways();
			case 'spinupwp':
				return new SpinUpWP();
			case 'wpengine':
				return new WPEngine( $this->admin_subscriber );
			case 'wordpresscom':
				return new WordPressCom( $this->admin_subscriber );
			default:
				return new NullSubscriber();
		}
	}
}
