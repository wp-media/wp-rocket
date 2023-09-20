<?php

namespace WP_Rocket\ThirdParty\Hostings;

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
				return new Pressable();
			case 'cloudways':
				return new Cloudways();
			case 'spinupwp':
				return new SpinUpWP();
			case 'wpengine':
				return new WPEngine();
			case 'o2switch':
				return new O2Switch();
			case 'wordpresscom':
				return new WordPressCom();
			case 'savvii':
				return new Savvii();
			case 'dreampress':
				return new Dreampress();
			case 'wpxcloud':
				return new WPXCloud();
			case 'litespeed':
				return new LiteSpeed();
			case 'godaddy':
				return new Godaddy();
			case 'kinsta':
				return new Kinsta();
			case 'onecom':
				return new OneCom();
			case 'proisp':
				return new ProIsp();
			default:
				return new NullSubscriber();
		}
	}
}
