<?php
/**
 * Upgrade item template.
 */

defined( 'ABSPATH' ) || exit;

$initial_item = $data['type'] === 'stacked' ? reset($data['item']) : $data['item'];
?>
<div class="wpr-upgrade-item wpr-Upgrade-<?php echo esc_attr( $initial_item['name'] ); ?>">
	<?php if ( $data['is_promo_active'] ) { ?>
		<div class="wpr-upgrade-saving">
			<?php
			// translators: %1$s = span opening tag, %2$s = price, %3$s = span closing tag.
			printf( esc_html__( 'Save $%1$s%2$s%3$s', 'rocket' ), '<span>', esc_html( $initial_item['saving'] ), '</span>' );
			?>
		</div>
	<?php } ?>
	<h3 class="wpr-upgrade-title"><?php echo esc_html( $initial_item['name'] ); ?></h3>
	<div class="wpr-upgrade-prices"><span class="wpr-upgrade-price-symbol">$</span> <span class="wpr-upgrade-price-value"><?php echo esc_html( $initial_item['price'] ); ?></span>
		<?php if ( $data['is_promo_active'] ) { ?>
			<del class="wpr-upgrade-price-regular">$ <span><?php echo esc_html( $initial_item['regular_price'] ); ?></span></del>
		<?php } ?>
	</div>
	<div class="wpr-upgrade-websites<?php if ( 'stacked' !== $data['type'] ) { ?> notstacked<?php } ?>">
	<?php if ( 'stacked' === $data['type'] ) { ?>
		<select id="rocket_stacked_select">
			<?php foreach ( $data['item'] as $stacked_item_key => $stacked ) { ?>
				<option
					data-name="<?php echo esc_attr( $stacked['name'] )?>"
					data-price="<?php echo esc_attr( $stacked['price'] )?>"
					data-url="<?php echo esc_url( $stacked['upgrade_url'] )?>"
					<?php if ( $data['is_promo_active'] ) { ?>
						data-saving="<?php echo esc_attr( $stacked['saving'] )?>"
						data-regular-price="<?php echo esc_attr( $stacked['regular_price'] )?>"
					<?php } ?>
					value="<?php echo esc_attr( $stacked_item_key )?>"
				><?php echo esc_html( $stacked['websites'] ) . ' ' . esc_html__( 'Websites', 'rocket' ); ?></option>
			<?php } ?>
		</select>
	<?php } else { ?>
			<?php
			// translators: %s = number of websites.
			printf( esc_html__( '%s websites', 'rocket' ),  esc_html( $initial_item['websites'] ) );
			?>
	<?php } ?>
	</div>

	<a href="<?php echo esc_url( $initial_item['upgrade_url'] ); ?>" class="wpr-upgrade-link" target="_blank" rel="noopener noreferrer">
		<?php
		// translators: %s = license name.
		printf( esc_html__( 'Upgrade to %s', 'rocket' ), esc_html( $initial_item['name'] ) );
		?>
	</a>
</div>
