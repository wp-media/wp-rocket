<?php
/**
 * RocketCDN CName change notice template.
 *
 * @since 3.17.3
 *
 * @param array $data {
 *      @type string $old_cname Old RocketCDN CName.
 *      @type string $new_cname New RocketCDN CName.
 *      @type string $support_url Contact Support url.
 * }
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>
<div class="notice notice-alt notice-info is-dismissible" id="rocketcdn-change-cname-notice">
	<p><?php esc_html_e( sprintf( 'We\'ve updated your RocketCDN CNAME from %1$s to %2$s.', $data['old_cname'], $data['new_cname'] ), 'rocket' ); ?></p>
	<p><?php esc_html_e( sprintf( 'The change is already applied to the plugin settings. If you were using the CNAME in your code, make sure to update it to: %1$s.', $data['new_cname'] ), 'rocket' ); ?></p>
	<p><a href="<?php echo esc_url( $data['support_url'] ); ?>" target="_blank" rel="noopener" class="wpr-button" id="rocketcdn-change-cname-button"><?php esc_html_e( 'contact support', 'rocket' ); ?></a></p>
</div>
