<?php
/**
 * Upgrade item template.
 *
 * @var array $data
 */

defined( 'ABSPATH' ) || exit;

$rocket_initial_item = 'stacked' === $data['type'] ? reset( $data['item'] ) : $data['item'];
?>
<div class="wpr-upgrade-item wpr-Upgrade-<?php echo esc_attr( $rocket_initial_item['name'] ); ?>">
	<?php if ( $data['is_promo_active'] ) { ?>
		<div class="wpr-upgrade-saving">
			<?php
			// translators: %1$s = span opening tag, %2$s = price, %3$s = span closing tag.
			printf( esc_html__( 'Save $%1$s%2$s%3$s', 'rocket' ), '<span>', esc_html( $rocket_initial_item['saving'] ), '</span>' );
			?>
		</div>
	<?php } ?>
	<h3 class="wpr-upgrade-title"><?php echo esc_html( $rocket_initial_item['name'] ); ?></h3>
	<div class="wpr-upgrade-prices"><span class="wpr-upgrade-price-symbol">$</span> <span class="wpr-upgrade-price-value"><?php echo esc_html( $rocket_initial_item['price'] ); ?></span>
		<?php if ( $data['is_promo_active'] ) { ?>
			<del class="wpr-upgrade-price-regular">$ <span><?php echo esc_html( $rocket_initial_item['regular_price'] ); ?></span></del>
		<?php } ?>
	</div>
	<div class="wpr-upgrade-websites
	<?php
	if ( 'stacked' !== $data['type'] ) {
		?>
		notstacked<?php } ?>">
	<?php if ( 'stacked' === $data['type'] && 1 < count( $data['item'] ) ) { ?>
		<div class="custom-select" id="rocket_stacked_select">
			<button class="select-button" role="combobox" aria-label="select button" aria-haspopup="listbox" aria-expanded="false" aria-controls="select-dropdown">
				<span class="selected-value has-style-bold"><?php echo esc_html( $rocket_initial_item['websites'] ) . ' ' . esc_html__( 'Websites', 'rocket' ); ?></span>
				<span class="custom-select-arrow"></span>
			</button>
			<ul class="select-dropdown" role="listbox" id="select-dropdown">
				<?php foreach ( $data['item'] as $rocket_stacked_item_key => $rocket_stacked ) { ?>
				<li role="option"
					data-name="<?php echo esc_attr( $rocket_stacked['name'] ); ?>"
					data-price="<?php echo esc_attr( $rocket_stacked['price'] ); ?>"
					data-url="<?php echo esc_url( $rocket_stacked['upgrade_url'] ); ?>"
					<?php if ( $data['is_promo_active'] ) { ?>
						data-saving="<?php echo esc_attr( $rocket_stacked['saving'] ); ?>"
						data-regular-price="<?php echo esc_attr( $rocket_stacked['regular_price'] ); ?>"
					<?php } ?>
				>
					<input type="radio" id="plan_<?php echo esc_attr( $rocket_stacked_item_key ); ?>" name="multi-plans"/>
					<label for="multi50"><?php echo esc_html( $rocket_stacked['websites'] ) . ' ' . esc_html__( 'Websites', 'rocket' ); ?></label>
				</li>
				<?php } ?>
			</ul>
		</div>
	<?php } else { ?>
			<?php
			// translators: %s = number of websites.
			printf( esc_html__( '%s websites', 'rocket' ),  esc_html( $rocket_initial_item['websites'] ) );
			?>
	<?php } ?>
	</div>

	<a href="<?php echo esc_url( $rocket_initial_item['upgrade_url'] ); ?>" class="wpr-upgrade-link" target="_blank" rel="noopener noreferrer">
		<?php
		// translators: %s = license name.
		printf( esc_html__( 'Upgrade to %s', 'rocket' ), esc_html( $rocket_initial_item['name'] ) );
		?>
	</a>
</div>
