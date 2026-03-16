<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Abilities;

use WP\MCP\Core\McpAdapter;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Options ability instance.
	 *
	 * @var Options
	 */
	private $options;

	/**
	 * Constructor.
	 *
	 * @param Options $options The options ability instance.
	 */
	public function __construct( Options $options ) {
		$this->options = $options;
	}

	/**
	 * Get the events to which this subscriber wants to listen.
	 *
	 * @return array The events and their corresponding callback methods.
	 */
	public static function get_subscribed_events(): array {
		return [
			'plugins_loaded'                   => [ 'init_mcp', 99 ],
			'wp_abilities_api_categories_init' => [
				[ 'register_options_category' ],
			],
			'wp_abilities_api_init'            => [
				[ 'register_get_options_ability' ],
			],
		];
	}

	/**
	 * Initialize the MCP adapter if the class exists.
	 *
	 * @return void
	 */
	public function init_mcp(): void {
		if ( ! class_exists( McpAdapter::class ) ) {
			return;
		}

		McpAdapter::instance();
	}

	/**
	 * Register the WP Rocket options ability category.
	 *
	 * @return void
	 */
	public function register_options_category(): void {
		$this->options->register_options_category();
	}

	/**
	 * Register the ability to get WP Rocket options.
	 *
	 * @return void
	 */
	public function register_get_options_ability(): void {
		$this->options->register_get_options_ability();
	}
}
