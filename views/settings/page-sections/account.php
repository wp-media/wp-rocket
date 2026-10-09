<?php
/**
 * Account section template.
 *
 * @since 3.23.6
 *
 * @param array $data {
 *     Section arguments.
 *
 *     @type string $id            Page section identifier.
 *     @type string $title         Page section title.
 *     @type array  $customer_data WP Rocket customer data.
 * }
 */

defined( 'ABSPATH' ) || exit;

?>
<div id="<?php echo esc_attr( $data['id'] ); ?>" class="wpr-Page wpr-account">
	<div class="wpr-sectionHeader">
		<h2 class="wpr-title1"><?php echo esc_html( $data['title'] ); ?></h2>
	</div>

	<?php if ( ! defined( 'WP_ROCKET_WHITE_LABEL_ACCOUNT' ) || ! WP_ROCKET_WHITE_LABEL_ACCOUNT ) : ?>
	<div class="wpr-account-section">
		<div class="wpr-account-header">
			<h3 class="wpr-account-title">
				<?php esc_html_e( 'WP Rocket', 'rocket' ); ?>
			</h3>
			<?php
			$this->render_action_button(
				'button',
				'refresh_account',
				[
					'label'      => __( 'Refresh info', 'rocket' ),
					'attributes' => [
						'class' => 'wpr-infoAction wpr-icon-refresh',
					],
				]
			);
			?>
		</div>

		<div class="wpr-account-card wpr-account-card--license">
			<div class="wpr-account-row wpr-account-row--license">
				<div class="wpr-account-license">
					<div class="wpr-account-line">
						<span class="wpr-account-label"><?php esc_html_e( 'License', 'rocket' ); ?></span>
						<span class="wpr-account-badge wpr-isValid" id="wpr-account-data"><?php echo esc_html( $data['customer_data']['license_type'] ); ?></span>
						<?php if ( $data['customer_data']['is_from_one_dot_com'] ) : ?>
							<span class="wpr-account-partner">
								<?php esc_html_e( 'with', 'rocket' ); ?>
								<img src="<?php echo esc_url( rocket_get_constant( 'WP_ROCKET_ASSETS_IMG_URL' ) . 'one-com-logo.svg' ); ?>" width="80" alt="One.com">
							</span>
						<?php endif; ?>
					</div>
					<?php
					/**
					 * Fires when displaying the license information on the account page
					 *
					 * @since 3.23.6
					 */
					do_action( 'rocket_account_license_info' );
					?>
				</div>
				<div class="wpr-account-actions">
					<?php
					$this->render_action_button(
						'link',
						'view_account',
						[
							'label'      => __( 'View My Account', 'rocket' ),
							'attributes' => [
								'target' => '_blank',
								'class'  => 'wpr-account-button wpr-account-button--neutral',
							],
						]
					);
					?>
				</div>
			</div>

			<div class="wpr-account-row">
				<span id="wpr-expiration-label" class="wpr-account-label"><?php echo esc_html( $data['customer_data']['license_expiration_label'] ); ?></span>
				<span class="wpr-account-value <?php echo esc_attr( $data['customer_data']['license_class'] ); ?>" id="wpr-expiration-data"><?php echo esc_html( $data['customer_data']['license_expiration'] ); ?></span>
			</div>

			<div class="wpr-account-row">
				<span class="wpr-account-label"><?php esc_html_e( 'Plugin Updates', 'rocket' ); ?></span>
				<?php if ( ! empty( $data['customer_data']['can_update_plugin'] ) ) : ?>
					<span class="wpr-account-value wpr-isValid wpr-icon-check" id="wpr-plugin-updates-data"></span>
				<?php else : ?>
					<span class="wpr-account-value wpr-isInvalid" id="wpr-plugin-updates-data"><?php echo esc_html( $data['customer_data']['update_blocked_reason'] ); ?></span>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<?php
	/**
	 * Fires after the license information on the WP Rocket settings account page
	 *
	 * @since 3.23.6
	 */
	do_action( 'rocket_account_after_license_info' );
	?>
</div>
