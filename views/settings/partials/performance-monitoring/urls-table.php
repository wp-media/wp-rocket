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
<div class="wpr-notice wpr-pma-notice">
	<div class="wpr-notice-container">
		<div class="wpr-notice-description"><?php echo wp_kses_post( __( '<strong>Congrats!</strong> You can now monitor up to 10 pages, run on-demand tests, and access advanced GTmetrix reports.', 'rocket' ) ); ?></div>
		<a id="wpr-congratulations-notice" class="wpr-notice-close wpr-icon-close rocket-dismiss" href="http://localhost:10003/wp-admin/admin-post.php?action=rocket_ignore&amp;box=rocket_activation_notice&amp;_wpnonce=7b9071d8e2"><span class="screen-reader-text">Dismiss this notice</span></a>
	</div>
</div>

<div class="wpr-notice wpr-pma-notice wpr-error-notice">
	<div class="wpr-notice-container">
		<div class="wpr-notice-description"><?php echo wp_kses_post( __( "You've <strong>reached your free limit.</strong> Upgrade to continue.", 'rocket' ) ); ?></div>
		<a id="wpr-congratulations-notice"  class="wpr-notice-close" href=""><?php esc_html_e( 'Upgrade Now', 'rocket' ); ?></a>
	</div>
</div>

<div class="wpr-optionHeader">
	<h3 class="wpr-title2"><?php esc_html_e( 'Performance Summary', 'rocket' ); ?></h3>
	<button data-beacon-id="<?php echo esc_attr( '' ); ?>" data-wpr_track_button="Need Help" data-wpr_track_context="Addons" class="wpr-infoAction wpr-infoAction--help wpr-icon-help"><?php esc_html_e( 'Need Help?', 'rocket' ); ?></button>
</div>

<?php if ( empty( $data['items'] ) || 'in-progress' === $data['global_score'] ['status'] ) : ?>
	<p class="wpr-pma-summary-info">
		<?php
		printf(
			// translators: %1$s: number of pages, %2$s: number of tests available.
			wp_kses_post( __( 'You can analyze up to <strong>%1$s pages</strong> and run <strong>%2$s test per month.</strong> Want more? ', 'rocket' ) ),
			'1', // number of pages.
			'3'  // total number of tests available.
		);
		?>
		<a href="#"><?php esc_html_e( 'Upgrade Now', 'rocket' ); ?></a>
	</p>
<?php endif; ?>

<?php if ( ! empty( $data['items'] ) ) : ?>
	<table class="wp-rocket-data-table widefat wpr-pma-urls-table">
		<tbody>
		<?php
		$this->render_global_score_row( $data['global_score'] );
		?>
		<?php
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		foreach ( $data['items'] as $wpr_pma_record ) {
			$this->render_performance_monitoring_list_row( $wpr_pma_record, $data['credit'] );
		}
		?>

		</tbody>
	</table>
<?php endif; ?>

<div class="wpr-pma-add-section">
	<input type="text"
			class="wpr-speed-radar-input"
			placeholder="<?php esc_attr_e( 'Enter a page address to monitor', 'rocket' ); ?>"
			id="wpr-speed-radar-url-input" />

	<?php
	$this->render_action_button(
		'link',
		'add_page_speed_radar',
		[
			'label'      => __( 'ADD PAGE +', 'rocket' ),
			'url'        => '#',
			'attributes' => [
				'class' => 'wpr-button wpr-button--icon wpr-button--small wpr-button--purple',
				'id'    => 'add_page_speed_radar',
			],
		]
	);
	?>
</div>
