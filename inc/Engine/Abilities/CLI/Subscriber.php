<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Abilities\CLI;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Abilities WP-CLI command instance.
	 *
	 * @var Command
	 */
	private $command;

	/**
	 * Constructor.
	 *
	 * @param Command $command Abilities WP-CLI command instance.
	 */
	public function __construct( Command $command ) {
		$this->command = $command;
	}

	/**
	 * Get the events to which this subscriber wants to listen.
	 *
	 * @return array The events and their corresponding callback methods.
	 */
	public static function get_subscribed_events(): array {
		return [
			'cli_init' => 'register_commands',
		];
	}

	/**
	 * Registers the `wp rocket abilities-catalog` command with WP-CLI.
	 *
	 * @return void
	 */
	public function register_commands(): void {
		\WP_CLI::add_command( 'rocket abilities-catalog', [ $this->command, 'abilities_catalog' ] );
	}
}
