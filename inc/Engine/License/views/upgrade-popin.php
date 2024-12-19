<?php
/**
 * Upgrade section template.
 *
 * @since 3.7.3
 *
 * @var array $data
 * @var object $this
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wpr-Popin wpr-Popin-Upgrade">
	<div class="wpr-Popin-header">
		<h2 class="wpr-title1"><?php esc_html_e( 'Speed Up More Websites', 'rocket' ); ?></h2>
		<button class="wpr-Popin-close wpr-Popin-Upgrade-close wpr-icon-close"></button>
	</div>
	<div class="wpr-Popin-content">
		<p>
		<?php
		// translators: %1$s = opening strong tag, %2$s = closing strong tag.
		printf( esc_html__( 'You can use WP Rocket on more websites by upgrading your license. To upgrade, simply pay the %1$sprice difference%2$s between your current and new licenses, as shown below.', 'rocket' ), '<strong>', '</strong>' );
		?>
		</p>
		<p>
		<?php
		// translators: %1$s = opening strong tag, %2$s = closing strong tag.
		printf( esc_html__( '%1$sN.B.%2$s: Upgrading your license does not change your expiration date', 'rocket' ), '<strong>', '</strong>' );
		?>
		</p>
		<div class="wpr-Popin-flex">
			<?php
			foreach ( $data['upgrades'] as $rocket_upgrade_type => $rocket_upgrade ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $this->generate(
					'upgrade-item',
					[
						'type'            => $rocket_upgrade_type, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'item'            => $rocket_upgrade, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'is_promo_active' => $data['is_promo_active'], // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					]
				);
			}
			?>
		</div>
	</div>
</div>
