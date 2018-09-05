<?php
/**
 * Import page section template.
 *
 * @since 3.0
 */

use WP_Rocket\Logger;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

// Debug mode.
$log_description = '';

if ( rocket_direct_filesystem()->exists( Logger::get_log_file_path() ) ) {
	$stats = Logger::get_log_file_stats();

	if ( ! is_wp_error( $stats ) ) {
		// translators: %1$s = formatted file size, %2$s = formatted number of entries (don't use %2$d).
		$log_description .= sprintf( __( 'Files size: %1$s. Number of entries: %2$s.', 'rocket' ), '<strong>' . esc_html( $stats['bytes'] ) . '</strong>', '<strong>' . esc_html( $stats['entries'] ) . '</strong>' );

		// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
		$log_description .= '<br/>' . sprintf( __( '%1$sDownload the file%2$s.', 'rocket' ), '<a href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=rocket_download_debug_file' ), 'download_debug_file' ) ) . '">', '</a>' );

		// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
		$log_description .= ' - ' . sprintf( __( '%1$sDelete the file%2$s.', 'rocket' ), '<a href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=rocket_delete_debug_file' ), 'delete_debug_file' ) ) . '">', '</a>' );
	}
}
?>

<div id="tools" class="wpr-Page">
	<div class="wpr-sectionHeader">
		<h2 class="wpr-title1 wpr-icon-tools"><?php esc_html_e( 'Tools', 'rocket' ); ?></h2>
	</div>
	<div class="wpr-tools">
		<div class="wpr-tools-col">
			<div class="wpr-title3 wpr-tools-label wpr-icon-export"><?php _e( 'Export settings', 'rocket' ); ?></div>
			<div class="wpr-field-description"><?php _e( 'Download a backup file of your settings', 'rocket' ); ?></div>
		</div>
		<div class="wpr-tools-col">
			<?php
			$this->render_action_button( 'link', 'rocket_export', [
				'label'      => __( 'Download settings', 'rocket' ),
				'attributes' => [
					'class' => 'wpr-button wpr-button--icon wpr-button--small wpr-button--purple wpr-icon-chevron-down',
				],
			] );
			?>
		</div>
	</div>

	<?php $this->render_import_form(); ?>

	<div class="wpr-tools">
		<div class="wpr-tools-col">
			<div class="wpr-title3 wpr-tools-label wpr-icon-rollback"><?php _e( 'Rollback', 'rocket' ); ?></div>
			<div class="wpr-field-description">
				<?php
				// translators: %s = WP Rocket version number.
				printf( __( 'Has version %s caused an issue on your website?', 'rocket' ), WP_ROCKET_VERSION );
				?><br><br>
				<?php _e( 'You can rollback to the previous major version here.<br>Then send us a support request.', 'rocket' ); ?>
			</div>
		</div>
		<div class="wpr-tools-col">
			<?php
			$this->render_action_button( 'link', 'rocket_rollback', [
				// translators: %s = WP Rocket previous version.
				'label'      => sprintf( __( 'Reinstall version %s', 'rocket' ), WP_ROCKET_LASTVERSION ),
				'attributes' => [
					'class' => 'wpr-button wpr-button--icon wpr-button--small wpr-button--red wpr-icon-refresh',
				],
			] );
			?>
		</div>
	</div>

	<!-- Temporary hide the option. The logger can still be activated by adding the following to the wp-config.php file: define( 'WP_ROCKET_DEBUG', true );
	<div class="wpr-tools">
		<div class="wpr-tools-col wpr-radio">
			<div class="wpr-title3 wpr-tools-label">
				<input id="debug_enabled" name="wp_rocket_settings[debug_enabled]" value="1"<?php checked( true, Logger::debug_enabled() ); ?> type="checkbox">
				<label for="debug_enabled">
					<span data-l10n-active="On" data-l10n-inactive="Off" class="wpr-radio-ui"></span>
					<?php esc_html_e( 'Debug mode', 'rocket' ); ?>
				</label>
			</div>

			<div class="wpr-field-description">
				<?php esc_html_e( 'Create a debug log file.', 'rocket' ); ?>
			</div>
		</div>
		<div class="wpr-tools-col">
			<?php echo $log_description; ?>
		</div>
	</div>
	-->
</div>
