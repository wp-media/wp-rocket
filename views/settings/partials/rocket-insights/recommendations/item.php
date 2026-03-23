<?php
/**
 * Single Recommendation Item template.
 *
 * @since 3.21
 *
 * @param array $data {
 *     Data for a single recommendation item.
 *
 *     @type string $option_slug    Option slug identifier (e.g., 'delay_js').
 *     @type string $title          Recommendation title.
 *     @type string $description    Description text explaining the recommendation.
 *     @type string $learn_more_url URL for "More info" link.
 *     @type string $icon_slug      Icon identifier for the recommendation.
 *     @type array  $impact_tags    Array of impact tags with values (e.g., ['LCP' => 100, 'TBT' => 25]).
 *     @type int    $priority       Priority order for the recommendation.
 * }
 */

defined( 'ABSPATH' ) || exit;

$rocket_option_slug    = $data['option_slug'];
$rocket_title          = $data['title'];
$rocket_description    = $data['description'];
$rocket_learn_more_url = $data['learn_more_url'];
$rocket_icon_slug      = $data['icon_slug'];
$rocket_impact_tags    = $data['impact_tags'];
?>
<div class="wpr-recommendation-item">
	<div class="wpr-recommendation-item__inner">
		<div class="wpr-recommendation-item__wrapper">
			<div class="wpr-recommendation-item__header">
				<div class="wpr-recommendation-item__title-row">
					<span class="wpr-recommendation-item__title">
						<?php echo esc_html( $rocket_title ); ?>
					</span>
					<?php
					$this->render_action_button(
						'link',
						'',
						[
							'label'      => __( 'Activate', 'rocket' ),
							'url'        => esc_url( $data['section'] ),
							'attributes' => [
								'class'               => 'wpr-recommendation-item__activate',
								'data-recommendation' => esc_attr( $rocket_option_slug ),
							],
						]
					);
					?>
				</div>

				<?php if ( ! empty( $rocket_impact_tags ) ) : ?>
					<div class="wpr-recommendation-item__impact">
						<span class="wpr-recommendation-item__impact-label">
							<?php esc_html_e( 'Impact on', 'rocket' ); ?>
						</span>
						<div class="wpr-recommendation-item__impact-tags">
							<?php foreach ( $rocket_impact_tags as $rocket_metric => $rocket_value ) : ?>
								<span class="wpr-impact-tag" data-impact-value="<?php echo esc_attr( $rocket_value ); ?>">
									<?php echo esc_html( $rocket_metric ); ?>
								</span>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<p class="wpr-recommendation-item__description">
				<?php echo esc_html( $rocket_description ); ?>
				<?php if ( ! empty( $rocket_learn_more_url ) ) : ?>
					<a href="<?php echo esc_url( $rocket_learn_more_url ); ?>"
						target="_blank"
						rel="noopener noreferrer"
						class="wpr-recommendation-item__more-info">
						<?php esc_html_e( 'More info', 'rocket' ); ?>
					</a>
				<?php endif; ?>
			</p>
		</div>
	</div>
</div>
