<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Abilities;

use WP\MCP\Core\McpAdapter;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	private $options;

	public function __construct( Options $options ) {
		$this->options = $options;
	}

	public static function get_subscribed_events(): array {
		return [
			'init' => 'init_mcp',
			'wp_abilities_api_categories_init' => 'register_wpr_category',
		];
	}

	public function init_mcp() {
		if ( ! class_exists( McpAdapter::class ) ) {
			return;
		}

		McpAdapter::instance();
	}

	public function register_wpr_category() {
		wp_register_ability_category(
			'wp-rocket-options',
			[
				'label' => __( 'WP Rocket Options', 'rocket' ),
				'description' => __( 'Abilities that retrieve or update WP Rocket options', 'rocket' ),
			]
		);
	}
}
