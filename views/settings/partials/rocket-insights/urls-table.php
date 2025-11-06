<?php
/**
 * Performance Monitoring URLs Table partial.
 *
 * @since 3.20
 *
 * @var array $data {
 *     Data for the performance monitoring URLs table.
 *
 *     @type array $items List of performance monitoring records.
 * }
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wpr-optionHeader">
	<h3 class="wpr-title2"><?php esc_html_e( 'Performance Summary', 'rocket' ); ?></h3>
	<?php if ( ! empty( $data['help'] ) ) : ?>
	<a href="<?php echo esc_url( $data['help']['url'] ); ?>" data-beacon-id="<?php echo esc_attr( $data['help']['id'] ); ?>" data-wpr_track_button="Need Help" data-wpr_track_context="Addons" class="wpr-infoAction wpr-infoAction--help wpr-icon-help" target="_blank"><?php esc_html_e( 'Need Help?', 'rocket' ); ?></a>
	<?php endif; ?>
</div>

<?php
$rocket_insights_quota_banner_class = 'wpr-notice wpr-ri-notice wpr-ri-free-limit-notice';
if ( ! isset( $data['show_quota_banner'] ) || ! $data['show_quota_banner'] ) {
	$rocket_insights_quota_banner_class .= ' hidden';
}
?>
<div class="<?php echo esc_attr( $rocket_insights_quota_banner_class ); ?>" id="wpr-ri-quota-banner">
	<div class="wpr-notice-container">
		<div class="wpr-notice-description wpr-notice-70">
			<?php
			printf(
			// Translators: %1$s = opening strong tag, %2$s = closing strong tag.
				esc_html__( '%1$sCongrats!%2$s You fully enjoyed your free plan. Upgrade to keep testing, or wait for your free limit to reset.', 'rocket' ),
				'<strong>',
				'</strong>'
			);
			?>
		</div>
		<a class="wpr-notice-close" href="<?php echo esc_url( $data['upgrade_url'] ); ?>">
			<?php esc_html_e( 'Upgrade Now', 'rocket' ); ?>
		</a>
	</div>
</div>

<?php if ( ! empty( $data['can_add_pages'] ) && ! empty( $data['is_free'] ) && ! $data['show_quota_banner'] ) : ?>
	<p class="wpr-ri-summary-info">
		<?php
		printf(
		// Translators: %1$s = opening strong tag, %2$s: number of pages, %3$s = closing strong tag, %4$s: number of tests available.
			esc_html__( 'You can analyze up to %1$s%2$s pages%3$s and run %1$s%4$s tests per month%3$s. Want more?', 'rocket' ),
			'<strong>',
			esc_html( $data['rocket_insights_addon_limit'] ), // number of pages.
			'</strong>',
			esc_html( $data['rocket_insights_addon_limit'] ) // total number of tests available.
		);
		?>
		<a href="<?php echo esc_url( $data['upgrade_url'] ); ?>"><?php esc_html_e( 'Upgrade Now', 'rocket' ); ?></a>
	</p>
<?php endif; ?>


<table class="wp-rocket-data-table widefat wpr-ri-urls-table <?php echo empty( $data['items'] ) ? 'hidden' : ''; ?>" >
	<tbody>
		<?php
		if ( ! empty( $data['items'] ) ) :
			$this->render_global_score_row( $data['global_score'] );
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
			foreach ( $data['items'] as $rocket_insights_record ) {
				$this->render_performance_monitoring_list_row( $rocket_insights_record );
			}
			?>
		<?php endif; ?>
	</tbody>
</table>


<div class="wpr-ri-add-section">
	<input type="text"
			class="wpr-speed-radar-input"
			placeholder="<?php esc_attr_e( 'Enter a page URL to monitor', 'rocket' ); ?>"
			id="wpr-speed-radar-url-input" />

	<div id="wpr_rocket_insights_add_page_btn_wrapper">
		<?php
		$this->render_add_page_btn( 'rocket-insights', $data );
		?>
	</div>
</div>
