<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Plugin;

use WP_Rocket\Event_Management\Subscriber_Interface;

class OptionsBackupSubscriber implements Subscriber_Interface {

	/**
	 * Options backup service.
	 *
	 * @var OptionsBackup
	 */
	private $backup;

	/**
	 * Constructor.
	 *
	 * @param OptionsBackup $backup Options backup service.
	 */
	public function __construct( OptionsBackup $backup ) {
		$this->backup = $backup;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_subscribed_events(): array {
		return [
			'wp_rocket_upgrade' => [ 'backup_options', 1, 2 ],
		];
	}

	/**
	 * Triggers the backup before the upgrade routines run.
	 *
	 * @param string $new_version Incoming plugin version.
	 * @param string $old_version Currently installed plugin version.
	 */
	public function backup_options( string $new_version, string $old_version ): void {
		$this->backup->backup( $new_version, $old_version );
	}
}
