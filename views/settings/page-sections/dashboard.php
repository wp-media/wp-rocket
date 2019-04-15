<?php
/**
 * Dashboard section template.
 *
 * @since 3.0
 *
 * @param array {
 *     Section arguments.
 *
 *     @type string $id    Page section identifier.
 *     @type string $title Page section title.
 *     @type array  $faq   {
 *         Items to populate the FAQ section.
 *
 *         @type string $id    Documentation item ID.
 *         @type string $url   Documentation item URL.
 *         @type string $title Documentation item title.
 *     }
 *     @type object $customer_data WP Rocket customer data.
 * }
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>
<div id="<?php echo esc_attr( $data['id'] ); ?>" class="wpr-Page">
	<div class="wpr-sectionHeader">
		<h2 class="wpr-title1 wpr-icon-home"><?php echo esc_html( $data['title'] ); ?></h2>
	</div>

	<?php
	$rocket_boxes = get_user_meta( get_current_user_id(), 'rocket_boxes', true );

	if ( ! in_array( 'rocket_activation_notice', (array) $rocket_boxes, true ) ) :
		?>
	<div class="wpr-notice">
		<div class="wpr-notice-container">
			<div class="wpr-notice-supTitle"><?php esc_html_e( 'Congratulations!', 'rocket' ); ?></div>
			<h2 class="wpr-notice-title">
			<?php esc_html_e( 'WP Rocket is now activated and already working for you.', 'rocket' ); ?>
			<br>
			<?php esc_html_e( 'Your website should be loading faster now!', 'rocket' ); ?>
			</h2>
				<div class="wpr-notice-description"><?php esc_html_e( 'To guarantee fast websites, WP Rocket applies 80% of web performance best practices.', 'rocket' ); ?><br> <?php esc_html_e( 'We also enable options that provide immediate benefits to your website.', 'rocket' ); ?></div>
				<div class="wpr-notice-continue"><?php esc_html_e( 'Continue to the options to further optimize your site!', 'rocket' ); ?></div>
				<a class="wpr-notice-close wpr-icon-close rocket-dismiss" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=rocket_ignore&box=rocket_activation_notice' ), 'rocket_ignore_rocket_activation_notice' ) ); ?>"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'rocket' ); ?></span></a>
		</div>
	</div>
	<?php endif; ?>

	<div class="wpr-Page-row">
		<div class="wpr-Page-col">
			<?php if ( ! defined( 'WP_ROCKET_WHITE_LABEL_ACCOUNT' ) || ! WP_ROCKET_WHITE_LABEL_ACCOUNT ) : ?>
			<div class="wpr-optionHeader">
				<h3 class="wpr-title2"><?php esc_html_e( 'My Account', 'rocket' ); ?></h3>
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

			<div class="wpr-field wpr-field-account">
				<div class="wpr-flex wpr-flex--egal">
					<div>
						<span class="wpr-title3"><?php esc_html_e( 'License', 'rocket' ); ?></span>
						<span class="wpr-infoAccount wpr-isValid" id="wpr-account-data"><?php echo esc_html( $data['customer_data']->licence_account ); ?></span><br>
						<span class="wpr-title3"><?php esc_html_e( 'Expiration Date', 'rocket' ); ?></span>
						<span class="wpr-infoAccount <?php echo esc_attr( $data['customer_data']->class ); ?>" id="wpr-expiration-data"><?php echo esc_html( $data['customer_data']->licence_expiration ); ?></span>
					</div>
					<div>
						<?php
						$this->render_action_button(
							'link',
							'view_account',
							[
								'label'      => __( 'View my account', 'rocket' ),
								'attributes' => [
									'target' => '_blank',
									'class'  => 'wpr-button wpr-button--icon wpr-button--small wpr-button--purple wpr-icon-user',
								],
							]
						);
						?>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php
				$this->render_settings_sections( $data['id'] );
			?>
		</div>

		<div class="wpr-Page-col wpr-Page-col--fixed">
			<div class="wpr-optionHeader">
				<h3 class="wpr-title2"><?php esc_html_e( 'Quick Actions', 'rocket' ); ?></h3>
			</div>

			<div class="wpr-fieldsContainer">
				<fieldset class="wpr-fieldsContainer-fieldset">
					<div class="wpr-field">
						<h4 class="wpr-title3"><?php esc_html_e( 'Remove all cached files', 'rocket' ); ?></h4>
						<?php
						$this->render_action_button(
							'link',
							'purge_cache',
							[
								'label'      => __( 'Clear cache', 'rocket' ),
								'parameters' => [
									'type' => 'all',
								],
								'attributes' => [
									'class' => 'wpr-button wpr-button--icon wpr-button--small wpr-icon-trash',
								],
							]
						);
						?>
					</div>

					<?php if ( get_rocket_option( 'manual_preload' ) ) : ?>
					<div class="wpr-field">
						<h4 class="wpr-title3"><?php esc_html_e( 'Start cache preloading', 'rocket' ); ?></h4>
						<?php
						$this->render_action_button(
							'link',
							'preload',
							[
								'label'      => __( 'Preload cache', 'rocket' ),
								'attributes' => [
									'class' => 'wpr-button wpr-button--icon wpr-button--small wpr-icon-refresh',
								],
							]
						);
						?>
					</div>
					<?php endif; ?>

					<?php if ( function_exists( 'opcache_reset' ) ) : ?>
					<div class="wpr-field">
						<h4 class="wpr-title3"><?php esc_html_e( 'Purge OPCache content', 'rocket' ); ?></h4>
						<?php
						$this->render_action_button(
							'link',
							'rocket_purge_opcache',
							[
								'label'      => __( 'Purge OPCache', 'rocket' ),
								'attributes' => [
									'class' => 'wpr-button wpr-button--icon wpr-button--small wpr-icon-trash',
								],
							]
						);
						?>
					</div>
					<?php endif; ?>
					<?php if ( get_rocket_option( 'async_css' ) && apply_filters( 'do_rocket_critical_css_generation', true ) ) : ?>
					<div class="wpr-field">
						<h4 class="wpr-title3"><?php esc_html_e( 'Regenerate Critical CSS', 'rocket' ); ?></h4>
						<?php
						$this->render_action_button(
							'link',
							'rocket_generate_critical_css',
							[
								'label'      => __( 'Regenerate Critical CSS', 'rocket' ),
								'attributes' => [
									'class' => 'wpr-button wpr-button--icon wpr-button--small wpr-icon-refresh',
								],
							]
						);
						?>
					</div>
					<?php endif; ?>
				</fieldset>
			</div>
		</div>
	</div>
	<div class="wpr-Page-row">
		<div class="wpr-Page-col">
			<div class="wpr-optionHeader">
				<h3 class="wpr-title2"><?php esc_html_e( 'Frequently Asked Questions', 'rocket' ); ?></h3>
			</div>
			<fieldset class="wpr-fieldsContainer-fieldset">
				<div class="wpr-field">
					<ul class="wpr-field-list">
					<?php foreach ( $data['faq'] as $rocket_faq_item ) : ?>
						<li class="wpr-icon-information"><a href="<?php echo esc_url( $rocket_faq_item['url'] ); ?>" data-beacon-article="<?php echo esc_attr( $rocket_faq_item['id'] ); ?>" target="_blank"><?php echo esc_html( $rocket_faq_item['title'] ); ?></a></li>
					<?php endforeach; ?>
					</ul>
				</div>
				<div class="wpr-field">
					<div class="wpr-flex wpr-flex--egal">
						<div>
							<h3 class="wpr-title2"><?php esc_html_e( 'Still cannot find a solution?', 'rocket' ); ?></h3>
							<p class="wpr-field-description"><?php esc_html_e( 'Submit a ticket and get help from our friendly and knowledgeable Rocketeers.', 'rocket' ); ?></p>
						</div>
						<div>
							<?php
							$this->render_action_button(
								'link',
								'ask_support',
								[
									'label'      => __( 'Ask support', 'rocket' ),
									'attributes' => [
										'class'  => 'wpr-button wpr-button--icon wpr-button--small wpr-button--blue wpr-icon-help wpr-js-askSupport',
										'target' => '_blank',
									],
								]
							);
							?>
						</div>
					</div>
				</div>
			</fieldset>
		</div>

		<div class="wpr-Page-col wpr-Page-col--fixed">
			<?php $this->render_part( 'documentation' ); ?>
		</div>
	</div>
</div>
