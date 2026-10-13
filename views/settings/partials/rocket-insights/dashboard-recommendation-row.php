<?php
/**
 * Dashboard recommendation row content (title, impact tags and description).
 *
 * @param array $data Formatted recommendation.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wpr-dash-recs__heading">
	<span class="wpr-dash-recs__title"><?php echo esc_html( $data['title'] ); ?></span>
	<?php if ( ! empty( $data['impact_tags'] ) ) : ?>
		<span class="wpr-dash-recs__impact">
			<span class="wpr-dash-recs__impact-label"><?php esc_html_e( 'Impact on', 'rocket' ); ?></span>
			<?php foreach ( $data['impact_tags'] as $rocket_metric => $rocket_value ) : ?>
				<span class="wpr-dash-recs__tag" data-impact-value="<?php echo esc_attr( (string) $rocket_value ); ?>"><?php echo esc_html( $rocket_metric ); ?></span>
			<?php endforeach; ?>
		</span>
	<?php endif; ?>
</div>
<p class="wpr-dash-recs__description">
	<?php echo esc_html( $data['description'] ); ?>
	<?php if ( ! empty( $data['learn_more_url'] ) ) : ?>
		<a href="<?php echo esc_url( $data['learn_more_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'More info', 'rocket' ); ?></a>
	<?php endif; ?>
</p>
