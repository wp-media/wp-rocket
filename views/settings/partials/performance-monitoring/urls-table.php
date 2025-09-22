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
<div class="wpr-notice wpr-pma-notice hidden">
	<div class="wpr-notice-container">
		<div class="wpr-notice-description">
			<?php
			printf(
				// Translators: %1$s = opening strong tag, %2$s = closing strong tag.
				esc_html__( '%1$sCongrats!%2$s You can now monitor up to 10 pages, run on-demand tests, and access advanced GTmetrix reports.', 'rocket' ),
				'<strong>',
				'</strong>'
			);
			?>
		</div>
		<a class="wpr-notice-close wpr-icon-close rocket-dismiss" href="#">
			<span class="screen-reader-text">
				<?php esc_html_e( 'Dismiss this notice', 'rocket' ); ?>
			</span>
		</a>
	</div>
</div>

<div class="wpr-optionHeader">
	<h3 class="wpr-title2"><?php esc_html_e( 'Performance Summary', 'rocket' ); ?></h3>
	<button data-beacon-id="<?php echo esc_attr( '' ); ?>" data-wpr_track_button="Need Help" data-wpr_track_context="Addons" class="wpr-infoAction wpr-infoAction--help wpr-icon-help"><?php esc_html_e( 'Need Help?', 'rocket' ); ?></button>
</div>

<?php if ( isset( $data['can_add_pages'] ) && ! $data['can_add_pages'] ) : ?>
	<div class="wpr-notice wpr-pma-notice wpr-error-notice">
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
<?php endif; ?>

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
			placeholder="<?php esc_attr_e( 'Enter a page address to monitor', 'rocket' ); ?>"
			id="wpr-speed-radar-url-input" />

	<?php
	$rocket_pma_add_page_button_args = [
		'label'      => __( 'ADD PAGE +', 'rocket' ),
		'url'        => '#',
		'attributes' => [
			'class' => 'wpr-button wpr-button--icon wpr-button--small wpr-button--purple wpr-pma-add-url-button',
			'id'    => 'add_page_speed_radar',
		],
	];
	if ( $data['reach_max_url'] ) {
		$rocket_pma_add_page_button_args['attributes']['class'] .= ' wpr-btn-with-tool-tip disabled';
		$rocket_pma_add_page_button_args['tool_tip']             = esc_html__( 'Maximum number of URLs reached for your license.', 'rocket' );
		$rocket_pma_add_page_button_args['url']                  = '#';
		$rocket_pma_add_page_button_args['disabled']             = true;
	}
	$this->render_action_button(
		'link',
		'add_page_speed_radar',
		$rocket_pma_add_page_button_args
	);
	?>
</div>
