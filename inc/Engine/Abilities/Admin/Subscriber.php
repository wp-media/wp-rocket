<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Abilities\Admin;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * MCP Adapter notice instance.
	 *
	 * @var AdapterNotice
	 */
	private $adapter_notice;

	/**
	 * Constructor.
	 *
	 * @param AdapterNotice $adapter_notice MCP Adapter notice instance.
	 */
	public function __construct( AdapterNotice $adapter_notice ) {
		$this->adapter_notice = $adapter_notice;
	}

	/**
	 * Get the events to which this subscriber wants to listen.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'admin_notices' => 'display_mcp_adapter_notice',
		];
	}

	/**
	 * Displays the MCP Adapter migration notice.
	 *
	 * @return void
	 */
	public function display_mcp_adapter_notice(): void {
		$this->adapter_notice->display();
	}
}
