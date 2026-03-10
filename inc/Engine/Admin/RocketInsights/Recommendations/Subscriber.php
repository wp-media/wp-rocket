<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Recommendations;

use WP_Rocket\Engine\Admin\RocketInsights\Context\Context;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Recommendations Subscriber.
 *
 * Handles events and hooks for the Recommendations widget.
 *
 * @since 3.21
 */
class Subscriber implements Subscriber_Interface {
	/**
	 * Render instance.
	 *
	 * @var Render
	 */
	private $render;

	/**
	 * Context instance.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Constructor.
	 *
	 * @param Render  $render  Render instance.
	 * @param Context $context Context instance.
	 */
	public function __construct( Render $render, Context $context ) {
		$this->render  = $render;
		$this->context = $context;
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array Array of events.
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_sidebar' => 'render_recommendations_widget',
		];
	}

	/**
	 * Render the recommendations widget in the sidebar.
	 *
	 * Only renders if Rocket Insights is enabled and not on the dashboard tab.
	 *
	 * @return void
	 */
	public function render_recommendations_widget(): void {
		// Check if Rocket Insights is enabled.
		if ( ! $this->context->is_allowed() ) {
			return;
		}

		$this->render->render_recommendations_widget();
	}
}
