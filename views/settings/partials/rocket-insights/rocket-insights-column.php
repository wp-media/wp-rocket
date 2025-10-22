<?php
/**
 * Rocket Insights column content for post listing pages.
 *
 * @since 3.20.1
 *
 * @var array $data {
 *     Template data.
 *
 *     @type string      $wpr_rocket_insights_url        The URL of the post.
 *     @type object|null $wpr_rocket_row        Database row object for the URL (null if not tracked).
 *     @type bool        $wpr_has_credit Whether the user has credit available.
 * }
 */

defined( 'ABSPATH' ) || exit;

$wpr_rocket_insights_url = $data['url'] ?? ''; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpr_rocket_row          = $data['row'] ?? null; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpr_has_credit          = $data['has_credit'] ?? false; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

/**
 * Prepare score data array for performance score rendering.
 *
 * @param object $row Database row object.
 * @return array Score data array.
 */
function prepare_score_data( $row ) {
	$score_data = [
		'score'        => $row->score,
		'status'       => $row->status,
		'is_blurred'   => $row->is_blurred,
		'is_dashboard' => false,
	];

	if ( 'failed' !== $row->status ) {
		$score_data['status-color'] = $this->get_score_color_status( (int) $row->score ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	}

	return $score_data;
}

// If row doesn't exist, show "Test the page" link.
if ( null === $wpr_rocket_row ) :
	?>
	<div class="wpr-ri-column wpr-ri-not-tracked" data-url="<?php echo esc_attr( $wpr_rocket_insights_url ); ?>">
		<?php if ( $wpr_has_credit ) : ?>
			<a href="#" class="wpr-ri-test-page" data-url="<?php echo esc_attr( $wpr_rocket_insights_url ); ?>">
				<?php esc_html_e( 'Test the page', 'rocket' ); ?>
			</a>
		<?php else : ?>
			<a href="#" class="wpr-ri-test-page wpr-ri-no-credit" data-url="<?php echo esc_attr( $wpr_rocket_insights_url ); ?>">
				<?php esc_html_e( 'Test the page', 'rocket' ); ?>
			</a>
			<div class="wpr-ri-credit-message">
				<strong><?php esc_html_e( "You've reached your free limit.", 'rocket' ); ?></strong>
				<?php esc_html_e( 'Upgrade to continue.', 'rocket' ); ?>
			</div>
		<?php endif; ?>
		<div class="wpr-ri-message" style="display: none;"></div>
	</div>
	<?php
	return;
endif;

// Determine the state based on row status.
$wpr_is_running        = $wpr_rocket_row->is_running(); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpr_has_results       = 'completed' === $wpr_rocket_row->status || 'blurred' === $wpr_rocket_row->status; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpr_is_blurred        = isset( $wpr_rocket_row->is_blurred ) && $wpr_rocket_row->is_blurred; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$wpr_can_access_report = $wpr_rocket_row->can_access_report(); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

// Prepare data used by both blurred and normal score renderers.
$score_data = prepare_score_data( $wpr_rocket_row ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>

<div class="wpr-ri-column" data-rocket-insights-id="<?php echo esc_attr( $wpr_rocket_row->id ); ?>" data-url="<?php echo esc_attr( $wpr_rocket_insights_url ); ?>">
	<?php if ( $wpr_is_running ) : ?>
		<!-- Loading state -->
		<div class="wpr-ri-loading">
			<img class="wpr-loading-img" src="<?php echo esc_url( rocket_get_constant( 'WP_ROCKET_ASSETS_IMG_URL', '' ) . 'orange-loading.svg' ); ?>" alt="<?php esc_attr_e( 'Loading...', 'rocket' ); ?>"/>
		</div>
	<?php elseif ( $wpr_has_results ) : ?>
		<!-- Results state -->
		<?php if ( $wpr_is_blurred ) : ?>
			<!-- Blurred score - show score with tooltip and actions (Re-test clickable, See Report disabled) -->
			<div class="wpr-ri-blurred">
				<div class="wpr-btn-with-tool-tip">
					<?php
					$score_data = prepare_score_data( $wpr_rocket_row ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

					$this->render_performance_score( $score_data );
					?>
				</div>
				
				<div class="wpr-ri-actions-wrapper">
					<?php if ( $wpr_has_credit ) : ?>
						<a href="#" class="wpr-ri-retest-link wpr-icon-bold-refresh" data-url="<?php echo esc_attr( $wpr_rocket_insights_url ); ?>">
							<?php esc_html_e( 'Re-test', 'rocket' ); ?>
						</a>
					<?php else : ?>
						<span class="wpr-ri-retest-link wpr-icon-bold-refresh wpr-ri-disabled">
							<?php esc_html_e( 'Re-test', 'rocket' ); ?>
						</span>
					<?php endif; ?>
					
					<span class="wpr-ri-see-report-link wpr-icon-report wpr-ri-disabled">
						<?php esc_html_e( 'See Report', 'rocket' ); ?>
					</span>
					
					<?php if ( ! $wpr_has_credit ) : ?>
						<span class="wpr-ri-no-credit-text">
							<strong><?php esc_html_e( "You've reached your free limit.", 'rocket' ); ?></strong>
							<?php esc_html_e( 'Upgrade to continue.', 'rocket' ); ?>
						</span>
					<?php endif; ?>
				</div>
			</div>
		<?php else : ?>
			<!-- Normal score with actions -->
			<div class="wpr-ri-score-wrapper">
				<?php
				$score_data = prepare_score_data( $wpr_rocket_row ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

				$this->render_performance_score( $score_data );
				?>
			</div>
			
			<div class="wpr-ri-actions-wrapper">
				<?php if ( $wpr_has_credit ) : ?>
					<a href="#" class="wpr-ri-retest-link wpr-icon-bold-refresh" data-url="<?php echo esc_attr( $wpr_rocket_insights_url ); ?>">
						<?php esc_html_e( 'Re-test', 'rocket' ); ?>
					</a>
				<?php else : ?>
					<span class="wpr-ri-retest-link wpr-icon-bold-refresh wpr-ri-disabled">
						<?php esc_html_e( 'Re-test', 'rocket' ); ?>
					</span>
				<?php endif; ?>
				
				<?php
				// See report link - only show if report_url exists.
				$wpr_report_url = $wpr_rocket_row->report_url ?? ''; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

				if ( ! empty( $wpr_report_url ) && $wpr_can_access_report ) :
					?>
					<a href="<?php echo esc_url( $wpr_report_url ); ?>" class="wpr-ri-see-report-link wpr-icon-report" target="_blank" rel="noopener">
						<?php esc_html_e( 'See Report', 'rocket' ); ?>
					</a>
				<?php endif; ?>
				
				<?php if ( ! $wpr_has_credit ) : ?>
					<span class="wpr-ri-no-credit-text">
						<strong><?php esc_html_e( "You've reached your free limit.", 'rocket' ); ?></strong>
						<?php esc_html_e( 'Upgrade to continue.', 'rocket' ); ?>
					</span>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<!-- Failed or unknown state -->
		<div class="wpr-ri-score-wrapper">
			<div class="wpr-btn-with-tool-tip">
				<div class="wpr-percentage-indicator">
					<div class="wpr-percentage-circle status-red">
						<span class="wpr-failed-score wpr-icon-exclamation"></span>
					</div>
				</div>
				<div class="wpr-tooltip">
					<div class="wpr-tooltip-content">
						<?php esc_html_e( 'Something went wrong with this URL', 'rocket' ); ?>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>
	
	<div class="wpr-ri-message" style="display: none;"></div>
</div>
