<?php

namespace WP_Rocket;

use WP_Rocket\Event_Management\Subscriber_Interface;

class ProxySubscriber implements Subscriber_Interface
{

	protected $container;

	protected $subscriber_instance;

	protected $subscriber_name;

	/**
	 * @param $container
	 * @param $subscriber_name
	 */
	public function __construct($container, $subscriber_name)
	{
		$this->container = $container;
		$this->subscriber_name = $subscriber_name;
	}

	public static function get_subscribed_events()
	{
		return [];
	}

	public function __call($name, $arguments)
	{
		if( ! $this->subscriber_instance) {
			$this->subscriber_instance = $this->container->get($this->subscriber_name);
		}

		if( ! method_exists( $this, $name ) ) {
			return $this->subscriber_instance->{$name}(...$arguments);
		}

		return $this->{$name}(...$arguments);
	}
}
