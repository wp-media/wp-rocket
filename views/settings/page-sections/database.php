<?php
/**
 * Database section template.
 *
 * @since 3.0
 *
 * @param array {
 *     Section arguments.
 *
 *     @type string $id    Page section identifier.
 *     @type string $title Page section title.
 * }
 */

defined( 'ABSPATH' ) || exit;

?>

<div id="<?php echo esc_attr( $data['id'] ); ?>" class="wpr-Page">
	<div class="wpr-sectionHeader">
		<h2 class="wpr-title1 wpr-icon-database"><?php echo esc_html( $data['title'] ); ?></h2>
	</div>
	<?php $this->render_settings_sections( $data['id'] ); ?>
	<div class="wpr-fieldsContainer-helper wpr-icon-important">
		<?php esc_html_e( 'Backup your database before you run a cleanup!', 'rocket' ); ?>
		<p><?php esc_html_e( 'Once a database optimization has been performed, there is no way to undo it.', 'rocket' ); ?></p>
	</div>
	<input type="submit" class="wpr-button" name="wp_rocket_settings[submit_optimize]" value="<?php esc_attr_e( 'Optimize', 'rocket' ); ?>">
</div>
