<?php
/**
 * Import page section template.
 *
 * @since 3.0
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>

<div id="tools" class="wpr-Page">
	<div class="wpr-sectionHeader">
		<h2 class="wpr-title1 wpr-icon-tools"><?php esc_html_e( 'Tools', 'rocket' ); ?></h2>
	</div>
	<div>
	<?php _e( 'Export settings', 'rocekt' ); ?>
	<p><?php _e( 'Download a backup file of your settings', 'rocket' ); ?></p>
	<?php
	$this->render_action_button( 'link', 'rocket_export', [
		'label'      => __( 'Download settings', 'rocket' ),
		'attributes' => [
			'class' => 'wpr-button wpr-button--icon wpr-button--small wpr-button--purple',
		],
	] );
	?>
	</div>
	<div>
	<?php _e( 'Import settings', 'rocket' ); ?>
	<?php $this->render_import_form(); ?>
	</div>
	<div>
	<?php _e( 'Rollback', 'rocekt' ); ?>
	<p>
	<?php
	// translators: %s = WP Rocket version number.
	printf( __( 'Has version %s caused an issue on your website?', 'rocket' ), WP_ROCKET_VERSION );
	?>
	</p>
	<p><?php _e( 'You can rollback to the previous major version here.<br>Then send us a support request.', 'rocket' ); ?></p>
	<?php
	$this->render_action_button( 'link', 'rocket_rollback', [
		// translators: %s = WP Rocket previous version.
		'label'      => sprintf( __( 'Reinstall version %s', 'rocket' ), WP_ROCKET_LASTVERSION ),
		'attributes' => [
			'class' => 'wpr-button wpr-button--icon',
		],
	] );
	?>
	</div>
</div>
