<?php
namespace WP_Rocket\Event_Management;

/**
 * The event manager manages events using the WordPress plugin API.
 *
 * @since 3.1
 * @author Carl Alexander <contact@carlalexander.ca>
 */
class Event_Manager {
	/**
	 * Adds a callback to a specific hook of the WordPress plugin API.
	 *
	 * @uses add_filter()
	 *
	 * @param string   $hook_name     Name of the hook.
	 * @param callable $callback      Callback function.
	 * @param int      $priority      Priority.
	 * @param int      $accepted_args Number of arguments.
	 */
	public function add_callback( $hook_name, $callback, $priority = 10, $accepted_args = 1 ) {
		add_filter( $hook_name, $callback, $priority, $accepted_args );
	}

	/**
	 * Add an event subscriber.
	 *
	 * The event manager registers all the hooks that the given subscriber
	 * wants to register with the WordPress Plugin API.
	 *
	 * @param Subscriber_Interface $subscriber Subscriber_Interface implementation.
	 */
	public function add_subscriber( Subscriber_Interface $subscriber ) {
		if ( $subscriber instanceof Event_Manager_Aware_Subscriber_Interface ) {
			$subscriber->set_event_manager( $this );
		}

		foreach ( $subscriber->get_subscribed_events() as $hook_name => $parameters ) {
			$this->add_subscriber_callback( $subscriber, $hook_name, $parameters );
		}
	}

	/**
	 * Checks the WordPress plugin API to see if the given hook has
	 * the given callback. The priority of the callback will be returned
	 * or false. If no callback is given will return true or false if
	 * there's any callbacks registered to the hook.
	 *
	 * @uses has_filter()
	 *
	 * @param string $hook_name Hook name.
	 * @param mixed  $callback  Callback.
	 *
	 * @return bool|int
	 */
	public function has_callback( $hook_name, $callback = false ) {
		return has_filter( $hook_name, $callback );
	}

	/**
	 * Removes the given callback from the given hook. The WordPress plugin API only
	 * removes the hook if the callback and priority match a registered hook.
	 *
	 * @uses remove_filter()
	 *
	 * @param string   $hook_name Hook name.
	 * @param callable $callback  Callback.
	 * @param int      $priority  Priority.
	 *
	 * @return bool
	 */
	public function remove_callback( $hook_name, $callback, $priority = 10 ) {
		return remove_filter( $hook_name, $callback, $priority );
	}

	/**
	 * Remove an event subscriber.
	 *
	 * The event manager removes all the hooks that the given subscriber
	 * wants to register with the WordPress Plugin API.
	 *
	 * @param Subscriber_Interface $subscriber Subscriber_Interface implementation.
	 */
	public function remove_subscriber( Subscriber_Interface $subscriber ) {
		foreach ( $subscriber->get_subscribed_events() as $hook_name => $parameters ) {
			$this->remove_subscriber_callback( $subscriber, $hook_name, $parameters );
		}
	}

	/**
	 * Adds the given subscriber's callback to a specific hook
	 * of the WordPress plugin API.
	 *
	 * @param Subscriber_Interface $subscriber Subscriber_Interface implementation.
	 * @param string               $hook_name  Hook name.
	 * @param mixed                $parameters Parameters, can be a string, an array or a multidimensional array.
	 */
	private function add_subscriber_callback( Subscriber_Interface $subscriber, $hook_name, $parameters ) {
		if ( is_string( $parameters ) ) {
			$this->add_callback( $hook_name, [ $subscriber, $parameters ] );
		} elseif ( is_array( $parameters ) && count( $parameters ) !== count( $parameters, COUNT_RECURSIVE ) ) {
			foreach ( $parameters as $parameter ) {
				$this->add_subscriber_callback( $subscriber, $hook_name, $parameter );
			}
		} elseif ( is_array( $parameters ) && isset( $parameters[0] ) ) {
			$this->add_callback( $hook_name, [ $subscriber, $parameters[0] ], isset( $parameters[1] ) ? $parameters[1] : 10, isset( $parameters[2] ) ? $parameters[2] : 1 );
		}
	}

	/**
	 * Removes the given subscriber's callback to a specific hook
	 * of the WordPress plugin API.
	 *
	 * @param Subscriber_Interface $subscriber Subscriber_Interface implementation.
	 * @param string               $hook_name  Hook name.
	 * @param mixed                $parameters Parameters, can be a string, an array or a multidimensional array.
	 */
	private function remove_subscriber_callback( Subscriber_Interface $subscriber, $hook_name, $parameters ) {
		if ( is_string( $parameters ) ) {
			$this->remove_callback( $hook_name, [ $subscriber, $parameters ] );
		} elseif ( is_array( $parameters ) && count( $parameters ) !== count( $parameters, COUNT_RECURSIVE ) ) {
			foreach ( $parameters as $parameter ) {
				$this->remove_subscriber_callback( $subscriber, $hook_name, $parameter );
			}
		} elseif ( is_array( $parameters ) && isset( $parameters[0] ) ) {
			$this->remove_callback( $hook_name, [ $subscriber, $parameters[0] ], isset( $parameters[1] ) ? $parameters[1] : 10 );
		}
	}
}
