<?php
/**
 * Import form template.
 *
 * @since 3.0
 *
 * @param array $data {
 *     Import form data.
 *
 *     @type array  $upload_dir  Array containing data about the upload dir, and an error key and message if needed.
 *     @type string $size        Max upload size in a human readable format.
 *     @type string $bytes       Raw max upload size.
 *     @type string $action      WordPress action associated with the form.
 *     @type string $submit_text Content for the submit button.
 * }
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

if ( ! empty( $data['upload_dir']['error'] ) ) {
	?>
	<div class="error"><p><?php _e( 'Before you can upload your import file, you will need to fix the following error:', 'rocket' ); ?></p>
	<p><strong><?php echo esc_html( $data['upload_dir']['error'] ); ?></strong></p></div>
<?php
} else {
	?>
	<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="POST" enctype="multipart/form-data">
	<p>
	<input type="file" id="upload" name="import" size="25" />
	<br />
	<label for="upload">
	<?php
	// translators: %s is the maximum upload size set on the current server.
	printf( __( 'Choose a file from your computer (maximum size: %s)', 'rocket' ), esc_html( $data['size'] ) );
	?>
	</label>
	<input type="hidden" name="max_file_size" value="<?php echo esc_attr( $data['bytes'] ); ?>" />
	</p>
	<input type="hidden" name="action" value="<?php echo esc_attr( $data['action'] ); ?>" />
	<?php
	wp_nonce_field( $data['action'] );
	?>
	<input type="submit" class="wpr-button wpr-button--icon wpr-button--small wpr-button--purple" value="<?php echo esc_attr( $data['submit_text'] ); ?>" />
	</form>
	<?php
}
