<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Plugin;

class OptionsBackup {

	const KEEP_COUNT = 4;

	private string $config_path;
	private string $options_slug;

	public function __construct( string $config_path, string $options_slug ) {
		$this->config_path  = $config_path;
		$this->options_slug = $options_slug;
	}

	/**
	 * Backs up the current options before a plugin upgrade runs.
	 *
	 * Skips if the upgrade is a rollback (new < old) or if a backup for
	 * $new_version already exists (only the first snapshot per version is kept).
	 *
	 * @param string $new_version Incoming plugin version.
	 * @param string $old_version Currently installed plugin version.
	 * @return bool True when a backup was written, false when skipped or failed.
	 */
	public function backup( string $new_version, string $old_version ): bool {
		if ( version_compare( $new_version, $old_version, '<' ) ) {
			return false;
		}

		if ( $this->backup_exists_for_version( $new_version ) ) {
			return false;
		}

		$options = get_option( $this->options_slug );

		if ( false === $options ) {
			return false;
		}

		rocket_init_config_dir();

		$filename = 'wp_rocket_settings_backup_' . $new_version . '_' . gmdate( 'Y-m-d-H-i-s' ) . '.json';
		$written  = rocket_put_content(
			$this->config_path . $filename,
			(string) wp_json_encode( $options, JSON_PRETTY_PRINT )
		);

		if ( ! $written ) {
			return false;
		}

		$this->garbage_collect();

		return true;
	}

	/**
	 * Checks whether a backup file for the given version already exists.
	 *
	 * @param string $version Plugin version to look up.
	 * @return bool
	 */
	private function backup_exists_for_version( string $version ): bool {
		$files = glob( $this->config_path . 'wp_rocket_settings_backup_' . $version . '_*.json' );
		return ! empty( $files );
	}

	/**
	 * Deletes oldest backup files beyond KEEP_COUNT, sorted by modification time.
	 */
	private function garbage_collect(): void {
		$files = glob( $this->config_path . 'wp_rocket_settings_backup_*.json' );

		if ( ! $files || count( $files ) <= self::KEEP_COUNT ) {
			return;
		}

		usort(
			$files,
			function ( string $a, string $b ): int {
				return filemtime( $b ) - filemtime( $a );
			}
		);

		$filesystem = rocket_direct_filesystem();

		foreach ( array_slice( $files, self::KEEP_COUNT ) as $old_file ) {
			$filesystem->delete( $old_file );
		}
	}
}
