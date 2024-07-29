<?php
/**
 * Upgrade section template.
 *
 * @since 3.7.3
 */

defined( 'ABSPATH' ) || exit;

$data = isset( $data ) ? $data : []; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
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
			<?php foreach ( $data['upgrades'] as $rocket_upgrade ) : ?>
			<div class="wpr-Upgrade-<?php echo esc_attr( $rocket_upgrade['name'] ); ?>">
				<?php if ( true === $data['is_promo_active'] ) : ?>
					<div class="wpr-upgrade-saving">
						<?php
						// translators: %s = price.
						printf( esc_html__( 'Save $%s', 'rocket' ), esc_html( $rocket_upgrade['saving'] ) );
						?>
					</div>
					<?php endif; ?>
				<h3 class="wpr-upgrade-title"><?php echo esc_html( $rocket_upgrade['name'] ); ?></h3>
				<div class="wpr-upgrade-prices"><span class="wpr-upgrade-price-symbol">$</span> <?php echo esc_html( $rocket_upgrade['price'] ); ?>
				<?php if ( true === $data['is_promo_active'] ) : ?>
					<del class="wpr-upgrade-price-regular">$ <?php echo esc_html( $rocket_upgrade['regular_price'] ); ?></del>
				<?php endif; ?>
				</div>
				<div class="wpr-upgrade-websites">
				<?php
				// translators: %s = number of websites.
				printf( esc_html__( '%s websites', 'rocket' ),  esc_html( $rocket_upgrade['websites'] ) );
				?>
				</div>
				<a href="<?php echo esc_url( $rocket_upgrade['upgrade_url'] ); ?>" class="wpr-upgrade-link" target="_blank" rel="noopener noreferrer">
				<?php
				// translators: %s = license name.
				printf( esc_html__( 'Upgrade to %s', 'rocket' ), esc_html( $rocket_upgrade['name'] ) );
				?>
				</a>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
