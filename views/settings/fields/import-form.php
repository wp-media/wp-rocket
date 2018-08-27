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
	<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" enctype="multipart/form-data" class="wpr-tools">
		<div class="wpr-tools-col">
			<label for="upload" class="wpr-title3 wpr-tools-label wpr-icon-import"><?php _e( 'Import settings', 'rocket' ); ?></label>
			<div class="wpr-upload">
				<input type="file" accept=".txt,.json" id="upload" name="import" size="25" />
				<small for="upload" class="wpr-field-description">
					<?php
					// translators: %s is the maximum upload size set on the current server.
					printf( __( 'Choose a file from your computer (maximum size: %s)', 'rocket' ), esc_html( $data['size'] ) );
					?>
				</small>
			</div>
			<input type="hidden" name="max_file_size" value="<?php echo esc_attr( $data['bytes'] ); ?>" />
			<input type="hidden" name="action" value="<?php echo esc_attr( $data['action'] ); ?>" />
		</div>
		<div class="wpr-tools-col">
			<?php
			wp_nonce_field( $data['action'], $data['action'] . '_nonce' );
			?>
			<button type="submit" class="wpr-button wpr-button--icon wpr-button--small wpr-button--purple wpr-icon-chevron-up" value="<?php echo esc_attr( $data['submit_text'] ); ?>"><?php echo esc_attr( $data['submit_text'] ); ?></button>
		</div>
	</form>
	<?php
}
