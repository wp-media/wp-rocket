<?php
/**
 * Dashboard Global Score disabled state.
 *
 * Displayed in place of the Global Score when Rocket Insights is disabled or unavailable.
 *
 * @param array $data {
 *     Disabled state data.
 *
 *     @type string $title       Banner title.
 *     @type string $description Banner description.
 * }
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="wpr-dash-score wpr-dash-score--disabled">
	<div class="wpr-dash-section-header">
		<div class="wpr-dash-section-header__heading">
			<h3 class="wpr-dash-section-header__title"><?php esc_html_e( 'Rocket Insights Global Score', 'rocket' ); ?></h3>
		</div>
	</div>

	<div class="wpr-dash-banner">
		<p class="wpr-dash-banner__title"><?php echo esc_html( $data['title'] ); ?></p>
		<p class="wpr-dash-banner__text"><?php echo esc_html( $data['description'] ); ?></p>
	</div>
</section>
