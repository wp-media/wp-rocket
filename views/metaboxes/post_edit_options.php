<?php
/**
 * Post edit metabox options.
 *
 * @data array {
 *     Data to populate the template.
 *
 *     @type bool $excluded_url Whether the current URL is excluded from cache or not.
 *     @type array $fields Array of arrays to populate the fields.
 * }
 */

defined( 'ABSPATH' ) || exit;

wp_nonce_field( 'rocket_box_option', '_rocketnonce', false, true );
?>

<div class="misc-pub-section">
	<input name="rocket_post_nocache" id="rocket_post_nocache" type="checkbox" title="<?php esc_html_e( 'Never cache this page', 'rocket' ); ?>" <?php checked( $data['excluded_url'], true ); ?>><label for="rocket_post_nocache"><?php esc_html_e( 'Never cache this page', 'rocket' ); ?></label>
</div>

<div class="misc-pub-section">
	<p><?php esc_html_e( 'Activate these options on this post:', 'rocket' ); ?></p>
	<?php
	foreach ( $data['fields'] as $rocket_field ) {
		?>
		<input name="rocket_post_exclude_hidden[<?php echo esc_attr( $rocket_field['id'] ); ?>]" type="hidden" value="on">
		<input name="rocket_post_exclude[<?php echo esc_attr( $rocket_field['id'] ); ?>]" id="rocket_post_exclude_<?php echo esc_attr( $rocket_field['id'] ); ?>" type="checkbox"<?php echo $rocket_field['title']; ?><?php echo $rocket_field['checked']; ?><?php echo $rocket_field['disabled']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?>>
		<label for="rocket_post_exclude_<?php echo esc_attr( $rocket_field['id'] ); ?>"<?php echo $rocket_field['title']; ?><?php echo $rocket_field['class']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?>><?php echo esc_html( $rocket_field['label'] ); ?></label><br>

		<?php
	}
	?>

	<p class="rkt-note">
	<?php
	// translators: %1$s = opening strong tag, %2$s = closing strong tag.
	printf( esc_html__( '%1$sNote:%2$s None of these options will be applied if this post has been excluded from cache in the global cache settings.', 'rocket' ), '<strong>', '</strong>' );
	?>
	</p>
</div>

<?php
/**
 * Fires after WP Rocket’s metabox.
 *
 * @since 3.6
 */
do_action( 'rocket_after_options_metabox' );
