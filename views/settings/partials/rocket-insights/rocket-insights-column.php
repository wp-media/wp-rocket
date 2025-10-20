<?php
/**
 * Rocket Insights column content for post listing pages.
 *
 * @since 3.20.1
 *
 * @var array $data {
 *     Template data.
 *
 *     @type string      $url        The URL of the post.
 *     @type object|null $row        Database row object for the URL (null if not tracked).
 *     @type bool        $has_credit Whether the user has credit available.
 * }
 */

defined( 'ABSPATH' ) || exit;

$url        = $data['url'] ?? '';
$row        = $data['row'] ?? null;
$has_credit = $data['has_credit'] ?? false;

// If row doesn't exist, show "Test the page" link.
if ( null === $row ) :
	?>
	<div class="wpr-ri-column wpr-ri-not-tracked" data-url="<?php echo esc_attr( $url ); ?>">
		<?php if ( $has_credit ) : ?>
			<a href="#" class="wpr-ri-test-page" data-url="<?php echo esc_attr( $url ); ?>">
				<?php esc_html_e( 'Test the page', 'rocket' ); ?>
			</a>
		<?php else : ?>
			<a href="#" class="wpr-ri-test-page wpr-ri-no-credit" data-url="<?php echo esc_attr( $url ); ?>">
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
$is_running        = $row->is_running();
$has_results       = 'completed' === $row->status || 'blurred' === $row->status;
$is_blurred        = isset( $row->is_blurred ) && $row->is_blurred;
$can_access_report = $row->can_access_report();
?>

<div class="wpr-ri-column" data-rocket-insights-id="<?php echo esc_attr( $row->id ); ?>" data-url="<?php echo esc_attr( $url ); ?>">
	<?php if ( $is_running ) : ?>
		<!-- Loading state -->
		<div class="wpr-ri-loading">
			<img class="wpr-loading-img" src="<?php echo esc_url( rocket_get_constant( 'WP_ROCKET_ASSETS_IMG_URL', '' ) . 'orange-loading.svg' ); ?>" alt="<?php esc_attr_e( 'Loading...', 'rocket' ); ?>"/>
		</div>
	<?php elseif ( $has_results ) : ?>
		<!-- Results state -->
		<?php if ( $is_blurred ) : ?>
			<!-- Blurred score - show score with tooltip and actions (Re-test clickable, See Report disabled) -->
			<div class="wpr-ri-blurred">
				<div class="wpr-btn-with-tool-tip">
					<?php
					$score_data = [
						'score'        => $row->score,
						'status'       => $row->status,
						'is_blurred'   => $row->is_blurred,
						'is_dashboard' => false,
					];
					
					if ( 'failed' !== $row->status ) {
						$score_data['status-color'] = $this->get_score_color_status( (int) $row->score );
					}
					
					$this->render_performance_score( $score_data );
					?>
				</div>
				
				<div class="wpr-ri-actions-wrapper">
					<?php if ( $has_credit ) : ?>
						<a href="#" class="wpr-ri-retest-link wpr-icon-bold-refresh" data-url="<?php echo esc_attr( $url ); ?>">
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
					
					<?php if ( ! $has_credit ) : ?>
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
				$score_data = [
					'score'        => $row->score,
					'status'       => $row->status,
					'is_blurred'   => $row->is_blurred,
					'is_dashboard' => false,
				];
				
				if ( 'failed' !== $row->status ) {
					$score_data['status-color'] = $this->get_score_color_status( (int) $row->score );
				}
				
				$this->render_performance_score( $score_data );
				?>
			</div>
			
			<div class="wpr-ri-actions-wrapper">
				<?php if ( $has_credit ) : ?>
					<a href="#" class="wpr-ri-retest-link wpr-icon-bold-refresh" data-url="<?php echo esc_attr( $url ); ?>">
						<?php esc_html_e( 'Re-test', 'rocket' ); ?>
					</a>
				<?php else : ?>
					<span class="wpr-ri-retest-link wpr-icon-bold-refresh wpr-ri-disabled">
						<?php esc_html_e( 'Re-test', 'rocket' ); ?>
					</span>
				<?php endif; ?>
				
				<?php
				// See report link - only show if report_url exists.
				$report_url = $row->report_url ?? '';
				
				if ( ! empty( $report_url ) && $can_access_report ) :
					?>
					<a href="<?php echo esc_url( $report_url ); ?>" class="wpr-ri-see-report-link wpr-icon-report" target="_blank" rel="noopener">
						<?php esc_html_e( 'See Report', 'rocket' ); ?>
					</a>
				<?php endif; ?>
				
				<?php if ( ! $has_credit ) : ?>
					<span class="wpr-ri-no-credit-text">
						<strong><?php esc_html_e( "You've reached your free limit.", 'rocket' ); ?></strong>
						<?php esc_html_e( 'Upgrade to continue.', 'rocket' ); ?>
					</span>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<!-- Failed or unknown state -->
		<div class="wpr-ri-error">
			<span class="wpr-icon-exclamation"></span>
			<?php esc_html_e( 'Failed', 'rocket' ); ?>
		</div>
	<?php endif; ?>
	
	<div class="wpr-ri-message" style="display: none;"></div>
</div>
