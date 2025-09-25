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
	<button data-beacon-id="<?php echo esc_attr( '' ); ?>" data-wpr_track_button="Need Help" data-wpr_track_context="Addons" class="wpr-infoAction wpr-infoAction--help wpr-icon-help"><?php esc_html_e( 'Need Help?', 'rocket' ); ?></button>
</div>

<?php
$wp_rocket_pm_quota_banner_class = 'wpr-notice wpr-pma-notice wpr-error-notice';
if ( ! isset( $data['can_add_pages'] ) || $data['can_add_pages'] ) {
	$wp_rocket_pm_quota_banner_class .= ' hidden';
}
?>
<div class="<?php echo esc_attr( $wp_rocket_pm_quota_banner_class ); ?>" id="wpr-pma-quota-banner">
	<div class="wpr-notice-container">
		<div class="wpr-notice-description">
			<?php
			printf(
			// Translators: %1$s = opening strong tag, %2$s = closing strong tag.
				esc_html__( 'You\'ve %1$sreached your free limit.%2$s Upgrade to continue.', 'rocket' ),
				'<strong>',
				'</strong>'
			);
			?>
		</div>
		<a class="wpr-notice-close" target="_blank" href="<?php echo esc_url( $data['upgrade_url'] ); ?>">
			<?php esc_html_e( 'Upgrade Now', 'rocket' ); ?>
		</a>
	</div>
</div>

<?php if ( ! empty( $data['can_add_pages'] ) ) : ?>
	<p class="wpr-pma-summary-info">
		<?php
		printf(
		// Translators: %1$s = opening strong tag, %2$s: number of pages, %3$s = closing strong tag, %4$s: number of tests available.
			esc_html__( 'You can analyze up to %1$s%2$s pages%3$s and run %1$s%4$s tests per month%3$s. Want more?', 'rocket' ),
			'<strong>',
			esc_html( $data['pma_addon_limit'] ), // number of pages.
			'</strong>',
			esc_html( $data['pma_addon_limit'] ) // total number of tests available.
		);
		?>
		<a href="<?php echo esc_url( $data['upgrade_url'] ); ?>" target="_blank"><?php esc_html_e( 'Upgrade Now', 'rocket' ); ?></a>
	</p>
<?php endif; ?>


<table class="wp-rocket-data-table widefat wpr-pma-urls-table <?php echo empty( $data['items'] ) ? 'hidden' : ''; ?>" >
	<tbody>
		<?php
		if ( ! empty( $data['items'] ) ) :
			$this->render_global_score_row( $data['global_score'] );
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
			foreach ( $data['items'] as $wpr_pma_record ) {
				$this->render_performance_monitoring_list_row( $wpr_pma_record );
			}
			?>
		<?php endif; ?>
	</tbody>
</table>


<div class="wpr-pma-add-section">
	<input type="text"
			class="wpr-speed-radar-input"
			placeholder="<?php esc_attr_e( 'Enter a page URL to monitor', 'rocket' ); ?>"
			id="wpr-speed-radar-url-input" />
	
	<div id="wpr-pma-add-url-button-container">
		<?php
		$this->render_add_page_btn( 'rocket-insights', $data );
		?>
	</div>
</div>
