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

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>

<div id="<?php echo esc_attr( $data['id'] ); ?>" class="wpr-Page">
	<div class="wpr-sectionHeader">
		<h2 class="wpr-title1 wpr-icon-database"><?php echo esc_html( $data['title'] ); ?></h2>
		<p><?php _e( 'Backup your database before you run a cleanup!', 'rocket' ); ?></p>
		<p><?php _e( 'Once a database optimization has been performed, there is not way to undo it.', 'rocket' ); ?></p>
	</div>
	<?php $this->render_settings_sections( $data['id'] ); ?>
</div>
