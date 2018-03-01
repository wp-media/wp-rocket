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
 * }
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>

<div id="<?php echo esc_attr( $data['id'] ); ?>" class="wpr-Page">
	<div class="wpr-sectionHeader">
		<h2 class="wpr-title1 wpr-icon-home"><?php echo $data['title']; ?></h2>
	</div>

	<div class="wpr-Page-row">
		<div class="wpr-Page-col">
			<div class="wpr-optionHeader">
				<h3 class="wpr-title2"><?php esc_html_e( 'My account', 'rocket' ); ?></h3>
				<?php
				$this->render_action_button( 'button', 'refresh_account', [
					'label' => __( 'Refresh info', 'rocket' ),
					'attributes' => [
						'class'  => 'wpr-infoAction wpr-icon-refresh',
					],
					] );
					?>
			</div>

			<?php esc_html_e( 'License' ); ?>
			<?php esc_html_e( 'Expiration date' ); ?>
			<?php
			$this->render_action_button( 'link', 'view_account', [
				'label'      => __( 'View my account', 'rocket' ),
				'icon'       => '',
				'attributes' => [
					'target' => '_blank',
					'class'  => 'wpr-button wpr-button--icon wpr-button--small wpr-button--purple wpr-icon-user',
				],
				] );
			?>

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
						<h4><?php esc_html_e( 'Remove all cached files', 'rocket' ); ?></h4>
						<?php
						$this->render_action_button( 'link', 'purge_cache', [
							'label'      => __( 'Clear cache', 'rocket' ),
							'icon'       => '',
							'parameters' => [
								'type' => 'all',
							],
							'attributes' => [
								'class' => 'wpr-button wpr-button--icon wpr-button--small wpr-icon-trash',
							],
							] );
						?>
					</div>

					<div class="wpr-field">
						<h4><?php esc_html_e( 'Start cache preloading', 'rocket' ); ?></h4>
						<?php
						$this->render_action_button( 'link', 'preload', [
							'label'      => __( 'Preload cache', 'rocket' ),
							'icon'       => '',
							'attributes' => [
								'class' => 'wpr-button wpr-button--icon wpr-button--small wpr-icon-refresh',
							],
							] );
						?>
					</div>

					<div class="wpr-field">
						<h4><?php esc_html_e( 'Purge OPCache content', 'rocket' ); ?></h4>
						<?php
						$this->render_action_button( 'link', 'rocket_purge_opcache', [
							'label'      => __( 'Purge OPCache', 'rocket' ),
							'icon'       => '',
							'attributes' => [
								'class' => 'wpr-button wpr-button--icon wpr-button--small wpr-icon-trash',
							],
							] );
						?>
					</div>
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
					<?php foreach ( $data['faq'] as $faq_item ) : ?>
						<li class="wpr-icon-information"><a href="<?php echo esc_attr( $faq_item['url'] ); ?>" data-beacon-article="<?php echo esc_attr( $faq_item['id'] ); ?>"><?php echo esc_html( $faq_item['title'] ); ?></a></li>
					<?php endforeach; ?>
					</ul>
				</div>
				<div class="wpr-field">
					<div class="wpr-flex">
						<div>
							<h3 class="wpr-title2"><?php esc_html_e( 'Still can not find a solution?', 'rocket' ); ?></h3>
							<p class="wpr-field-description"><?php esc_html_e( 'Submit a ticket and get help from our friendly and knowledgeable Rocketeers.', 'rocket' ); ?></p>
						</div>
						<div>
							<?php
							$this->render_action_button( 'button', 'ask_support', [
								'label'      => __( 'Ask support', 'rocket' ),
								'icon'       => '',
								'attributes' => [
									'class' => 'wpr-button wpr-button--icon wpr-button--small wpr-button--blue wpr-icon-help',
								],
								] ); ?>
						</div>
					</div>
				</div>
			</fieldset>
		</div>

		<div class="wpr-Page-col wpr-Page-col--fixed">
			<?php $this->render_documentation_block(); ?>
		</div>
	</div>

	<div id="rocket-analytics-info" class="screen-reader-text">
		<p><?php _e( 'Below is a detailed view of all data WP Rocket will collect <strong>if granted permission.</strong>', 'rocket' ); ?>
		<?php echo rocket_data_collection_preview_table(); ?>
		<p><?php _e( 'WP Rocket will never transmit any domain names or email addresses (except for license validation), IP addresses, or third-party API keys.', 'rocket' ); ?></p>
		<button><?php _e( 'Activate WP Rocket analytics', 'rocket' ); ?></button>
	</div>

	<?php add_thickbox(); ?>
</div>
