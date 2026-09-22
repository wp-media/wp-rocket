<?php
/**
 * Licence expired notice partial.
 *
 * Renders a notice banner prompting the user to renew their WP Rocket licence
 * to continue using RocketCDN.
 *
 * @param array $data {
 *     Notice data.
 *
 *     @type string $renewal_url URL to the licence renewal page.
 *     @type bool   $is_reseller Whether the account is a reseller account.
 * }
 *
 * @since 3.22
 */

?>

<div class="wpr-rocketcdn-notice wpr-cdn-expired__notice wpr-field" id="wpr-cdn-licence-banner">
	<div class="wpr-notice-container wpr-flex">
		<div class="wpr-notice-70">
			<p>
				<?php
					printf(
						// translators: %1$s = opening <strong> tag, %2$s = closing </strong> tag.
						esc_html__( '%1$sYour WP Rocket license has expired%2$s. Renew now to keep using RocketCDN Free.', 'rocket' ),
						'<strong>',
						'</strong>'
					);
					?>
			</p>
		</div>
		<?php if ( empty( $data['is_reseller'] ) ) : ?>
			<a target="_blank" rel="noopener noreferrer" class="wpr-rocketcdn-btn" href="<?php echo esc_url( $data['renewal_url'] ); ?>">
				<?php esc_html_e( 'Renew', 'rocket' ); ?>
			</a>
		<?php endif; ?>
	</div>
</div>
