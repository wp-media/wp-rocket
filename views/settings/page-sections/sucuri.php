<?php
/**
 * Sucuri Cache section template.
 *
 * @since  3.2
 * @author Grégory Viguier
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
		<h2 class="wpr-title1">
			<img src="<?php echo esc_url( WP_ROCKET_ASSETS_IMG_URL . 'logo-sucuri.png' ); ?>" width="99" height="35" alt="<?php echo esc_attr( $data['title'] ); ?>">
		</h2>
	</div>
	<?php $this->render_settings_sections( $data['id'] ); ?>
	<?php if ( current_user_can( 'rocket_purge_sucuri_cache' ) ) : ?>
	<div class="wpr-optionHeader">
		<h3 class="wpr-title2"><?php echo esc_html( $data['title'] ); ?></h3>
	</div>
	<div class="wpr-fieldsContainer">
		<div class="wpr-fieldsContainer-description">
			<?php
			printf(
				// translators: %s is a "Learn more" link.
				esc_html__( 'Purges cached resources for your website. %s', 'rocket' ),
				'<a href="https://kb.sucuri.net/firewall/Performance/clearing-cache" target="_blank">' . esc_html__( 'Learn more', 'rocket' ) . '</a>'
			);
			?>
		</div><br>
		<?php
			$this->render_action_button(
				'link',
				'rocket_purge_sucuri',
				[
					'label'      => __( 'Clear all Sucuri cache files', 'rocket' ),
					'attributes' => [
						'class' => 'wpr-button wpr-button--icon wpr-button--small wpr-button--purple wpr-icon-trash',
					],
				]
			);
		?>
	</div>
	<?php endif; ?>
</div>
